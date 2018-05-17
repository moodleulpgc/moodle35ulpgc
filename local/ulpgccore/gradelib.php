<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * ulpgccore lib
 *
 * @package    local
 * @subpackage ulpgccore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Category aggregation types


// ULPGC aggregates
define('GRADE_AGGREGATE_ULPGC_SUM', 25);
define('GRADE_AGGREGATE_ULPGC_MEAN_EXAM', 27);
define('GRADE_AGGREGATE_ULPGC_MEAN_ACTV', 30);
define('GRADE_AGGREGATE_ULPGC_MEAN_CONVO', 35);
define('GRADE_AGGREGATE_ULPGC_FINAL', 40);
define('GRADE_AGGREGATE_ULPGC_NONE', 45);

define ('GRADE_ULPGC_AGGREGATIONS', '25,27,30,35,40,45');

define ('GRADECAT_LOCKED_NAME', 1);
define ('GRADECAT_LOCKED_AGG', 2);
define ('GRADECAT_LOCKED_BOTH', 3);

/// DETAILED SCALE GRADES
define('GRADE_NORMAL_SCALE_DISPLAY', 0);
define('GRADE_DETAILED_SCALE_DISPLAY', 1);
define('GRADE_DEFAULT_SCALE_DISPLAY', isset($CFG->grade_scaledisplaymode) ? $CFG->grade_scaledisplaymode : GRADE_NORMAL_SCALE_DISPLAY);

///////////////////////////////////////////////////////////////////////////////
//   Grade & Gradebook functions                                            //
/////////////////////////////////////////////////////////////////////////////

/**
 * Returns string representation of grade value
 *
 * @param float $value The grade value
 * @param object $grade_item Grade item object passed by reference to prevent scale reloading
 * @param bool $localized use localised decimal separator
 * @param int $displaytype type of display. For example GRADE_DISPLAY_TYPE_REAL, GRADE_DISPLAY_TYPE_PERCENTAGE, GRADE_DISPLAY_TYPE_LETTER
 * @param int $decimals The number of decimal places when displaying float values
 * @return string
 */
function local_ulpgccore_format_gradevalue($value, &$grade_item, $userid) {  // ecastro ULPGC DETAILED SCALES userid param
    global $CFG, $DB;
    if ($grade_item->gradetype == GRADE_TYPE_NONE or $grade_item->gradetype == GRADE_TYPE_TEXT or 
            empty($userid) or is_null($value) or    
            ($grade_item->gradetype != GRADE_TYPE_VALUE and $grade_item->gradetype != GRADE_TYPE_SCALE) ) {
        return '';
    }

    /// START DETAILED SCALE GRADES  // ecastro ULPGC CICEI
    $area = '';
    $textsummary = '';
    if ($grade_item->itemmodule == 'forum' && $grade_item->gradetype == GRADE_TYPE_SCALE) {
        $mod = 'forum';
        $area = 'post';
        include_once($CFG->dirroot.'/mod/forum/lib.php');
        $userentries = forum_get_user_posts($grade_item->iteminstance, $userid);
    } elseif($grade_item->itemmodule == 'glossary' && $grade_item->gradetype == GRADE_TYPE_SCALE) {
        $mod = 'glossary';
        $area = 'entry';
        include_once($CFG->dirroot.'/mod/glossary/lib.php');
        $userentries = glossary_get_user_entries($grade_item->iteminstance, $userid);
    }
    if($area) {
        require_once($CFG->dirroot."/mod/$mod/lib.php");
        $scale = $DB->get_record('scale', array('id' => $grade_item->scaleid));
        $scalevalues = explode(',', $scale->scale);
        $numvalues = count($scalevalues);
        $ratingsummary = array();
        for ($i=1;$i<=$numvalues;$i++) {
            $ratingsummary[$i] = 0;
        }

        require_once($CFG->dirroot . '/rating/lib.php');
        $rm = new rating_manager();
        $cm = get_coursemodule_from_instance($mod, $grade_item->iteminstance);
        $context = context_module::instance($cm->id);
        $options = new stdClass;
        $options->context = $context;
        $options->component = "mod_$mod";
        $options->ratingarea = $area;
        foreach ($userentries as $userentry) {
            $options->itemid = $userentry->id;
            $ratings = $rm->get_all_ratings_for_item($options);
            foreach ($ratings as $rating){
                $ratingsummary[$rating->rating]++;
            }
        }

        $textsummary = '';
        foreach ($ratingsummary as $ratingcount) {
            $textsummary .= $ratingcount.'/';
        }
        $textsummary = substr($textsummary, 0, -1);
    }
        
    if($textsummary) {
        $textsummary = ' <br /> '.$textsummary; 
    }
        
    return $textsummary;
}


function local_ulpgccore_gradecat_deletable($category, $context) {
    return !local_ulpgccore_gradecat_locked($category, $context);
}


function local_ulpgccore_gradecat_locked($category, $context) { 
    $locked = false;
    
    $depth = get_config('local_ulpgccore', 'gradebooklockingdepth');
    $canmanage = has_capability('local/ulpgccore:manage', $context);
    
    if($category->depth <= $depth && !$canmanage) {
        return GRADECAT_LOCKED_NAME;
    }
           
    $locknameword = get_config('local_ulpgccore', 'locknameword');
    $lockaggword = get_config('local_ulpgccore', 'lockaggword');
    
    // if locking words not defined there is nothing to check
    if(!$locknameword && !$lockaggword) {
        return false;
    }
    
    $canedit = has_capability('local/ulpgccore:gradecategoryedit', $context);
    
    // only check and set locked if needed for a non-editng user
    if(!$canedit) {
        $item = empty($category->grade_item) ? $category->get_grade_item() : $category->grade_item; 
        
        $lockname = (int)(strpos($item->iteminfo, $locknameword) !== false); 
    
        $lockagg = 2 * (int)(strpos($item->iteminfo, $lockaggword) !== false);
        
        $locked =  $lockagg + $lockname;
    }
    
    return $locked;
}




/**
 * Internal function that calculates the aggregated grade and new min/max for a grade category
 * Called by grade_category->aggregate_values_and_adjust_bounds for processing ULPGC aggregations
 *
 * @param array $grade_values An array of values to be aggregated
 * @param array $items The array of grade_items
 * @since Moodle 2.6.5, 2.7.2
 * @param array & $weights If provided, will be filled with the normalized weights
 *                         for each grade_item as used in the aggregation.
 *                         Some rules for the weights are:
 *                         1. The weights must add up to 1 (unless there are extra credit)
 *                         2. The contributed points column must add up to the course
 *                         final grade and this column is calculated from these weights.
 * @param array  $grademinoverrides User specific grademin values if different to the grade_item grademin (key is itemid)
 * @param array  $grademaxoverrides User specific grademax values if different to the grade_item grademax (key is itemid)
 * @return array containing values for:
 *                'grade' => the new calculated grade
 *                'grademin' => the new calculated min grade for the category
 *                'grademax' => the new calculated max grade for the category
 */
function local_ulpgccore_aggregate_values_bounds($grade_category, $grade_values, $items, & $weights = null, 
                                                    $grademinoverrides = array(), $grademaxoverrides = array(),
                                                    $novalue = array()) { 
    $category_item = $grade_category->load_grade_item();
    $grademin = $category_item->grademin;
    $grademax = $category_item->grademax;
    
    switch ($grade_category->aggregation) {

        case GRADE_AGGREGATE_ULPGC_MEAN_ACTV: // ecastro ULPGC for average of activities
            // Weighted average of all existing final grades without optional extra credit flag, weight is the range of grade (ususally grademax)
            // if any grade is failed (not pass) or empty, aggregated grade is failed with lowest grade.
            // hidden grade items are excluded from aggregation

            $grade_category->load_grade_item();

            // first remove hidden grade items if any
            foreach($items as $itemid => $item) {
                if($item->hidden) {
                    unset($items[$itemid]);
                    if(isset($grade_values[$itemid])) {
                        unset($grade_values[$itemid]);
                    }
                }
            }
            if(!$grade_values) {
                $agg_grade = null;
                break;
            }

            $failed = false;
            $empty = false;

            foreach($grade_values as $itemid=>$grade_value) {
                $weights[$itemid] = 0;
                $gradepass = grade_grade::standardise_score($items[$itemid]->gradepass, $items[$itemid]->grademin, $items[$itemid]->grademax, 0, 1);
                if(($grade_values[$itemid] < $gradepass)) {
                    $failed = true;
                }
                if((isset($novalue[$itemid]))) {
                    $empty = true;
                }
                
            }
            
            if($empty) {
                $agg_grade = null;
            } elseif($failed) {
                $agg_grade = min($grade_values);
                $minid = array_search($agg_grade, $grade_values);
                $weights[$minid] = 1;
            } else {
                // OK, let's calculate the average with all grades
                $agg_grade = local_ulpgccore_average_items_autoweigth($grade_values, $items, $weights);
            }

            //$agg_grade = 0.12;
            break;

        case GRADE_AGGREGATE_ULPGC_SUM:    // ecastro ULPGC for sum of activities
            // sum of existing final grades without optional extra credit flag, weight is the range of grade (ususally grademax)
            // if any grade is failed (not pass) or empty (no show), aggregated grade is failed with lowest grade.
            // hidden grade items are excluded from aggregation

            $grade_category->load_grade_item();

            // first remove hidden grade items if any
            foreach($items as $itemid => $item) {
                if($item->hidden) {
                    unset($items[$itemid]);
                    if(isset($grade_values[$itemid])) {
                        unset($grade_values[$itemid]);
                    }
                }
            }
            
            if(!$grade_values) {
                $agg_grade = null;
                break;
            }

            $failed = false;
            $empty = true;
            foreach($grade_values as $itemid=>$grade_value) {
                $weights[$itemid] = 0;
                $gradepass = grade_grade::standardise_score($items[$itemid]->gradepass, $items[$itemid]->grademin, $items[$itemid]->grademax, 0, 1);
                if(($grade_values[$itemid] < $gradepass)) {
                    $failed = true;
                }
                if(!isset($novalue[$itemid]) || isset($grade_values[$itemid])) {
                    $empty = false;
                }
            }

            $grade_category->aggregation = GRADE_AGGREGATE_SUM;
            $result = $grade_category->aggregate_values_and_adjust_bounds($grade_values,
                                                                        $items, $weights, $grademinoverrides, $grademaxoverrides);
            $grade_category->aggregation = GRADE_AGGREGATE_ULPGC_SUM;                                                        
            list($agg_grade, $grademin, $grademax) = array_values($result);
            
            if($empty) {
                $agg_grade = null;
            } elseif($failed) {
                $agg_grade = min($grade_values) / grade_grade::standardise_score($grademax, $grademin, $grademax, 0, 1); // Re-normalize score.
            }     
            
            break;

        case GRADE_AGGREGATE_ULPGC_MEAN_CONVO: // ecastro ULPGC for average of convocatory: activities + exams
            // Weighted average of all existing final grades without optional extra credit flag, weight is the range of grade (ususally grademax)
            // if any grade is failed (not pass), aggregated grade is failed.
            // last gradeitem is taken as EXAM and takes precedence: if failed, then aggregated grade is set to that
            // if exam is empty then the grade aggregation is also empty, disregarding any setting aggregateonlygraded

            $grade_category->load_grade_item();

            $exam = end($items);

            $failed = false;
            foreach($grade_values as $itemid=>$grade_value) {
                $weights[$itemid] = 0;
                $gradepass = grade_grade::standardise_score($items[$itemid]->gradepass, $items[$itemid]->grademin, $items[$itemid]->grademax, 0, 1);
                if($grade_values[$itemid] < $gradepass) {
                    $failed = true;
                }
            }

            // first check if exam is set. If not, then take convocatory as no-show and grade is void
            if(!isset($grade_values[$exam->id]) || isset($novalue[$exam->id])) {
                // exam is empty, no aggregation, return null grade
                $agg_grade = null;
                $weights[$exam->id] = 1;
                break;
            }

            $gradepass = grade_grade::standardise_score($grade_category->grade_item->gradepass, $grade_category->grade_item->grademin, $grade_category->grade_item->grademax, 0, 1);
            $exampass = grade_grade::standardise_score($items[$exam->id]->gradepass, $items[$exam->id]->grademin, $items[$exam->id]->grademax, 0, 1);

            // if here, exam is not empty. We must calculate a grade to set
            // check if any failed graded
            if($grade_values[$exam->id] < $exampass) {
                // exam failed, no aggregation
                $agg_grade = $grade_values[$exam->id];
                $weights[$exam->id] = 1;
            } elseif($failed) {
                // any failed, not the exam
                // TODO set down grade as percent of grade max
                $agg_grade =  $gradepass - 0.1*$gradepass ;
            } else {
                // OK, let's calculate the average with all grades
                $agg_grade = local_ulpgccore_average_items_autoweigth($grade_values, $items, $weights);
            }

            break;

        case GRADE_AGGREGATE_ULPGC_MEAN_EXAM: // ecastro ULPGC for average of exams
            // Weighted average of all existing final grades without optional extra credit flag, weight is the range of grade (ususally grademax)
            // if any grade is empty (no show), aggregated grade is failed with lowest grade.
            // if any grade is failed (not pass), aggregated grade is failed with lowest grade.
            // hidden grade items are excluded from aggregation

            $grade_category->load_grade_item();
            
            $empty = false;

            // first remove hidden grade items if any and set empty
            foreach($items as $itemid => $item) {
                if($item->hidden) {
                    unset($items[$itemid]);
                    if(isset($grade_values[$itemid])) {
                        unset($grade_values[$itemid]);
                    }
                } else {
                    $weights[$item->id] = 0;
                    if(!$grade_category->aggregateonlygraded && isset($novalue[$item->id])) {
                        $empty = true;
                    }
                }
            }
            if(!$grade_values) {
                $agg_grade = null;
                break;
            }

            if($empty) {
                $agg_grade = null;
            } else {
                $failed = false;
                foreach($grade_values as $itemid=>$grade_value) {
                    $weights[$itemid] = 0;
                    $gradepass = grade_grade::standardise_score($items[$itemid]->gradepass, $items[$itemid]->grademin, $items[$itemid]->grademax, 0, 1);
                    if($grade_values[$itemid] < $gradepass) {
                        $failed = true;
                    }
                }

                if($failed) {
                    $agg_grade = min($grade_values);
                    $minid = array_search($agg_grade, $grade_values);
                    $weights[$minid] = 1;
                } else {
                    // OK, let's calculate the average with all grades
                    $agg_grade = local_ulpgccore_average_items_autoweigth($grade_values, $items, $weights);
                }
            }

            break;

        case GRADE_AGGREGATE_ULPGC_FINAL: // ecastro ULPGC for final course grade from covocatory.
            // Searches backwards from last item to first looking fon non-empty, non-hidden item of type category
            // First found is returned as finalgrade

            $grade_category->load_grade_item();

            $agg_grade = null;
            $reversed = array_reverse($items, true);

            foreach($reversed as $itemid => $convo) {
                if(!$convo->hidden  && ($convo->itemtype == 'category')
                    && !(isset($novalue[$itemid]) && isset($grade_values[$itemid]))) {
                    $agg_grade = $grade_values[$itemid];
                    break;
                }
            }

            break;

        case GRADE_AGGREGATE_ULPGC_NONE:
            $agg_grade = null;
            break;
            
        default:
            $agg_grade = null;
            break;
    }

    return array('grade' => $agg_grade, 'grademin' => $grademin, 'grademax' => $grademax);
}



/**
 * Internal function that re-calculates the aggregated grade weights for this grade category
 * Doesn' exist in standard moodle
 *
 * @param array $grade_values An array of values to be aggregated
 * @param array $items The array of grade_items
 * @param array & $weights will be filled with the normalized weights
 * @return float agg_grade averaged result
 */
function local_ulpgccore_average_items_autoweigth($grade_values, $items, &$weights = array()) {  
    $weightsum = 0;
    $agg_grade = 0;
    $sum       = 0;

    // all grades passed, then calculate aggregated average
    foreach ($grade_values as $itemid=>$grade_value) {
        if ($items[$itemid]->aggregationcoef > 0) {
            continue;
        }

        $weight = $items[$itemid]->grademax - $items[$itemid]->grademin;
        if ($weight <= 0) {
            continue;
        }

        $weightsum += $weight;
        $sum += $weight * $grade_value;
    }

    if ($weightsum == 0) {
        $agg_grade = $sum; // only extra credits
    } else {
        $agg_grade = $sum / $weightsum;
    }

    // Record the weights as used.
    if ($weights !== null) {
        foreach ($grade_values as $itemid=>$grade_value) {
            if ($items[$itemid]->aggregationcoef > 0) {
                // Ignore extra credit items, the weights have already been computed.
                continue;
            }
            if ($weightsum > 0) {
                $weight = $items[$itemid]->grademax - $items[$itemid]->grademin;
                $weights[$itemid] = $weight / $weightsum;
            } else {
                $weights[$itemid] = 0;
            }
        }
    }
    
    return $agg_grade;
}


