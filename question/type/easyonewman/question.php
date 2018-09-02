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
 * marvin Molecular Editor question definition class.
 *
 * @package    qtype
 * @subpackage easyonewman
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/shortanswer/question.php');

class qtype_easyonewman_question extends qtype_shortanswer_question {
    // All comparisons in easyonewman are case sensitive!
    public function compare_response_with_answer(array $response, question_answer $answer) {

        $conformimportant = $this->conformimportant;
        $orientimportant = $this->orientimportant;
        $usranswer = $response['answer'];
        $coranswer = $answer->answer;
        $cor = explode("-", $coranswer);
        $usr = explode("-", $usranswer);

        // Strip image number for now!
        for ($i = 0; $i <= 5; $i++) {
            $cor[$i] = substr($cor[$i], 0, -1);
            $usr[$i] = substr($usr[$i], 0, -1);
        }
        if ($cor == $usr) {
            return 1;
        }
        $usrtemp = $usr;

        // Orientation important  - Only need to check from front face "I think"!
        if ($orientimportant == 1) {
            // Conformation important.
            if ($conformimportant == 1) {
                $returnflag = $this->check_conform_important($usr, $cor);
                return $returnflag;
            } else {
                // Conformation not important.
                $returnflag = $this->check_conform_not_important($usr, $cor);
                return $returnflag;
            }
        } else if ($orientimportant == 0) { // Orientation not important.
            if ($conformimportant == 1) {  // Conformation important.
                // Check from front.
                $returnflag1 = $this->check_conform_important($usrtemp, $cor);
                //Check mirror image front.
                //Make mirror image of usr response.
                $temp = $usrtemp[0];
                $usrtemp[0] = $usrtemp[2];
                $usrtemp[2] = $temp;
                $temp = $usrtemp[5];
                $usrtemp[5] = $usrtemp[3];
                $usrtemp[3] = $temp;
                $returnflag3 = $this->check_conform_important($usrtemp, $cor);
                        
                // Check from back.
                $usrtemp = array_reverse($usr);
                $returnflag2 = $this->check_conform_important($usrtemp, $cor);
                //Check mirror image front. 
                //Make mirror image of usr response.
                $temp = $usrtemp[0];
                $usrtemp[0] = $usrtemp[2];
                $usrtemp[2] = $temp;
                $temp = $usrtemp[5];
                $usrtemp[5] = $usrtemp[3];
                $usrtemp[3] = $temp;
                $returnflag3 = $this->check_conform_important($usrtemp, $cor);
                
                if ($returnflag1 == 1) {
                    $returnflag = 1;
                } else if ($returnflag2 == 1) {
                    $returnflag = 1;
                } else if ($returnflag3 == 1) {
                    $returnflag = 1;
                } else {
                    $returnflag = 0;
                }

            } else {
                // Conformation not important.
                $returnflag1 = $this->check_conform_not_important($usr, $cor);
                $usrtemp = array_reverse($usr);
                $returnflag2 = $this->check_conform_not_important($usrtemp, $cor);
                if ($returnflag1 == "1" || $returnflag2 == "1") {
                    $returnflag = 1;
                } else {
                    $returnflag = 0;
                }
            }
        }
        return $returnflag;
    }

    public function check_conform_important($usrtemp, $cor) {
        for ($i = 0; $i <= 5; $i++) {
            // Rotate usr array by 1 (clockwise).
            $newarray = array();
            foreach ($usrtemp as $key => $value) {
                if ($key == 4) {
                    $newarray[0] = $value;
                } else if ($key == 5) {
                    $newarray[1] = $value;
                } else {
                    $newarray[$key + 2] = $value;
                }
            }
                $usrtemp = $newarray;
                ksort($usrtemp);
            if ($cor == $usrtemp) {
                return 1;
            }
        }
        return 0;
    }

    public function check_conform_not_important($usr, $cor) {
        // Split arrays into front(odd) and back(even).
        $corodd = array();
        $coreven = array();
        $both = array(&$coreven, &$corodd);
        array_walk($cor, function($v, $k) use ($both) {
            $both[$k % 2][] = $v;
        });
        $usrodd = array();
        $usreven = array();
        $both = array(&$usreven, &$usrodd);
        array_walk($usr, function($v, $k) use ($both) {
            $both[$k % 2][] = $v;
        });
        $oddflag = false;
        $evenflag = false;
        // Check (even).
        $usrtemp = $usreven;
        for ($i = 0; $i <= 2; $i++) {
            // Rotate usr array by 1 (clockwise).
            $newarray = array();
            foreach ($usrtemp as $key => $value) {
                if ($key == 2) {
                    $newarray[0] = $value;
                } else {
                    $newarray[$key + 1] = $value;
                }
            }
                $usrtemp = $newarray;
                ksort($usrtemp);
            if ($coreven == $usrtemp) {
                $evenflag = true;
            }
        }
        // Check front(odd).
        $usrtemp = $usrodd;
        for ($i = 0; $i <= 2; $i++) {
            // Rotate usr array by 1 (clockwise).
            $newarray = array();
            foreach ($usrtemp as $key => $value) {
                if ($key == 2) {
                    $newarray[0] = $value;
                } else {
                    $newarray[$key + 1] = $value;
                }
            }
            $usrtemp = $newarray;
            ksort($usrtemp);
            if ($corodd == $usrtemp) {
                $oddflag = true;
            }
        }
        if ($oddflag == true && $evenflag == true) {
            return 1;
        } else {
            return 0;
        }
    }

    public function get_expected_data() {
        return array('answer' => PARAM_RAW, 'easyonewman' => PARAM_RAW, 'mol' => PARAM_RAW);
    }
}
