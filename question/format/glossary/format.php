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
 * Code for exporting questions as Moodle XML.
 *
 * @package    qformat_glossary
 * @copyright  2016 Daniel Thies <dethies@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/glossary/lib.php');
require_once($CFG->dirroot . '/question/format/xml/format.php');

/**
 * Question Import for Moodle XML glossary format.
 *
 * @copyright  2016 Daniel Thies <dethies@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qformat_glossary extends qformat_xml {
    /** @var string current category */
    public $currentcategory = '';

    // Overwrite export methods.
    public function writequestion($question) {
        global $CFG;
        $expout = '';
        if ($question->qtype == 'category') {
            $category = preg_replace('/<\\/text>/', '', $this->writetext($question->category));
            $category = preg_replace('/.*\\//', '', $category);
            $category = trim($category);
            $this->currentcategory = $category;
        }
        if ($question->qtype == 'match') {
            $subquestions = $question->options->subquestions;
            foreach ($subquestions as $subquestion) {
                $expout .= glossary_start_tag("ENTRY", 3, true);
                $expout .= glossary_full_tag("CONCEPT", 4, false, trim($subquestion->answertext));
                $expout .= glossary_full_tag("DEFINITION", 4, false, $subquestion->questiontext);
                $expout .= glossary_full_tag("FORMAT", 4, false, $subquestion->questiontextformat);
                $expout .= glossary_full_tag("TEACHERENTRY", 4, false, $subquestion->questiontextformat);
                if ($this->cattofile) {
                    $expout .= glossary_start_tag("CATEGORIES", 4, true);
                    $expout .= glossary_start_tag("CATEGORY", 5, true);
                    $expout .= glossary_full_tag('NAME', 6, false, $this->currentcategory);
                    $expout .= glossary_full_tag('USEDYNALINK', 6, false, 0);
                    $expout .= glossary_end_tag("CATEGORY", 5, true);
                    $expout .= glossary_end_tag("CATEGORIES", 4, true);
                };
                $expout .= $this->glossary_xml_export_files('ENTRYFILES', 4, $question->contextid,
                    'qtype_match', 'subquestion', $subquestion->id);
                $expout .= glossary_end_tag("ENTRY", 3, true);
            }

        }
        if ($question->qtype == 'shortanswer' ||
                ($question->qtype == 'multichoice' && !empty($question->options->single))) {
            $expout .= glossary_start_tag("ENTRY", 3, true);
            $answers = $question->options->answers;
            reset($answers);
            // Concept is first right answer.
            while (current($answers)) {
                if (current($answers)->fraction == 1) {
                    $expout .= glossary_full_tag("CONCEPT", 4, false, trim(current($answers)->answer));
                    next($answers);
                    break;
                }
                next($answers);
            }
            // Other right answers are aliases.
            $aliases = '';
            while (current($answers)) {
                if (current($answers)->fraction == 1) {
                    $aliases .= glossary_start_tag("ALIAS", 5, true);
                    $aliases .= glossary_full_tag("NAME", 6, false, trim(current($answers)->answer));
                    $aliases .= glossary_end_tag("ALIAS", 5, true);
                }
                next($answers);
            }

            $expout .= glossary_full_tag("DEFINITION", 4, false, $question->questiontext);
            $expout .= glossary_full_tag("FORMAT", 4, false, $question->questiontextformat);
            $expout .= glossary_full_tag("USEDYNALINK", 4, false, get_config('core', 'glossary_linkentries'));
            if (isset($question->options) && isset($question->options->usecase)) {
                $expout .= glossary_full_tag("CASESENSITIVE", 4, false, $question->options->usecase);
            } else {
                $expout .= glossary_full_tag("CASESENSITIVE", 4, false, get_config('core', 'glossary_casesensitive'));
            }
            $expout .= glossary_full_tag("FULLMATCH", 4, false, get_config('core', 'glossary_fullmatch'));
            $expout .= glossary_full_tag("TEACHERENTRY", 4, false, $question->questiontextformat);

            if ($aliases) {
                $expout .= glossary_start_tag("ALIASES", 4, true);
                $expout .= $aliases;
                $expout .= glossary_end_tag("ALIASES", 4, true);
            }

            if ($this->cattofile) {
                $expout .= glossary_start_tag("CATEGORIES", 4, true);
                $expout .= glossary_start_tag("CATEGORY", 5, true);
                $expout .= glossary_full_tag('NAME', 6, false, $this->currentcategory);
                $expout .= glossary_full_tag('USEDYNALINK', 6, false, 0);
                $expout .= glossary_end_tag("CATEGORY", 5, true);
                $expout .= glossary_end_tag("CATEGORIES", 4, true);
            };
            $expout .= $this->glossary_xml_export_files('ENTRYFILES', 4,
                $question->contextid, 'question', 'questiontext', $question->id);

            // Write the question tags.
            if ((get_config('core', 'version') >= 2016120503) &&
                    ($tags = core_tag_tag::get_item_tags_array('core_question', 'question', $question->id))) {
                $expout .= glossary_start_tag("TAGS", 4);
                foreach ($tags as $tag) {
                    $expout .= glossary_full_tag ("TAG", 5, false, $tag);
                }
                $expout .= glossary_end_tag("TAGS", 4);
            }

            $expout .= glossary_end_tag("ENTRY", 3, true);

        }
        return $expout;
    }

    // Duplicate function from glossary with component name added as argument.
    /**
     * Prepares file area to export as part of XML export
     *
     * @param string $tag XML tag to use for the group
     * @param int $taglevel
     * @param int $contextid
     * @param string $component
     * @param string $filearea
     * @param int $itemid
     * @return string
     */
    protected function glossary_xml_export_files($tag, $taglevel, $contextid, $component, $filearea, $itemid) {
        $co = '';
        $fs = get_file_storage();
        if ($files = $fs->get_area_files(
            $contextid, $component, $filearea, $itemid, 'itemid,filepath,filename', false)) {
            $co .= glossary_start_tag($tag, $taglevel, true);
            foreach ($files as $file) {
                $co .= glossary_start_tag('FILE', $taglevel + 1, true);
                $co .= glossary_full_tag('FILENAME', $taglevel + 2, false, $file->get_filename());
                $co .= glossary_full_tag('FILEPATH', $taglevel + 2, false, $file->get_filepath());
                $co .= glossary_full_tag('CONTENTS', $taglevel + 2, false, base64_encode($file->get_content()));
                $co .= glossary_end_tag('FILE', $taglevel + 1);
            }
            $co .= glossary_end_tag($tag, $taglevel);
        }
        return $co;
    }

    protected function presave_process($content) {
        // Override to add xml headers and footers and the global glossary settings.
        $co  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

        $co .= glossary_start_tag("GLOSSARY", 0, true);
        $co .= glossary_start_tag("INFO", 1, true);
        $co .= glossary_full_tag("NAME", 2, false, $this->category->name);
        $co .= glossary_full_tag("INTRO", 2, false, $this->category->info);
        $co .= glossary_full_tag("INTROFORMAT", 2, false, $this->category->infoformat);
        $co .= glossary_full_tag("ALLOWDUPLICATEDENTRIES", 2, false, get_config('core', 'glossary_dupentries'));
        $co .= glossary_full_tag("DISPLAYFORMAT", 2, false, 'dictionary');
        $co .= glossary_full_tag("SHOWSPECIAL", 2, false, 1);
        $co .= glossary_full_tag("SHOWALPHABET", 2, false, 1);
        $co .= glossary_full_tag("SHOWALL", 2, false, 1);
        $co .= glossary_full_tag("ALLOWCOMMENTS", 2, false, 0);
        $co .= glossary_full_tag("USEDYNALINK", 2, false, get_config('core', 'glossary_linkbydefault'));
        $co .= glossary_full_tag("DEFAULTAPPROVAL", 2, false, get_config('core', 'glossary_defaultapproval'));
        $co .= glossary_full_tag("GLOBALGLOSSARY", 2, false, 0);
        $co .= glossary_full_tag("ENTBYPAGE", 2, false, get_config('core', 'glossary_entbypage'));

        $co .= glossary_start_tag("ENTRIES", 2, true);

        $co .= $content;

        $co .= glossary_end_tag("ENTRIES", 2, true);

        $co .= glossary_end_tag("INFO", 1, true);
        $co .= glossary_end_tag("GLOSSARY", 0, true);
        return $co;
    }

    // Overwrite import methods.
    protected function readquestions($lines) {
        $uncategorizedquestions = array();
        $categorizedquestions = array();

        // We just need it as one big string.
        $lines = implode('', $lines);

        // Large exports are likely to take their time and memory.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);

        global $CFG;
        require_once($CFG->libdir . "/xmlize.php");

        $xml = xmlize($lines, 0);

        if ($xml) {
            $xmlentries = @$xml['GLOSSARY']['#']['INFO'][0]['#']['ENTRIES'][0]['#']['ENTRY'];
            $sizeofxmlentries = is_array($xmlentries) ? count($xmlentries) : 0;

            // Iterate through glossary entries.
            for ($i = 0; $i < $sizeofxmlentries; $i++) {
                // Extract entry information.
                $xmlentry = $xmlentries[$i];

                if (array_key_exists('CATEGORIES', $xmlentry['#'])) {
                    $xmlcategories = $xmlentry['#']['CATEGORIES'][0]['#']['CATEGORY'];
                } else {
                    // If no categories are specified, place it in the current one.
                    $qo = $this->import_headers($xmlentry);
                    $uncategorizedquestions[] = $qo;
                    $xmlcategories = array();
                }
                foreach ($xmlcategories as $category) {
                    // Place copy in each category that is specified.
                    $qc = $this->defaultquestion();
                    $qc->qtype = 'category';
                    $qc->category = trusttext_strip($category['#']['NAME'][0]['#']);
                    $categorizedquestions[] = $qc;
                    $qo = $this->import_headers($xmlentry);
                    $categorizedquestions[] = $qo;
                    if (!$this->catfromfile) {
                        // If category is not used, make only one copy.
                        break;
                    }
                }
            }
        }
        return array_merge($uncategorizedquestions, $categorizedquestions);
    }

    public function import_headers($xmlentry) {
        $concept = trim(trusttext_strip($xmlentry['#']['CONCEPT'][0]['#']));
        $definition = trusttext_strip($xmlentry['#']['DEFINITION'][0]['#']);
        $format = trusttext_strip($xmlentry['#']['FORMAT'][0]['#']);
        $usecase = trusttext_strip($xmlentry['#']['CASESENSITIVE'][0]['#']);

        // Create short answer question object from entry data.
        $qo = $this->defaultquestion();
        $qo->qtype = 'shortanswer';
        $qo->questiontextformat = $format;
        $qo->questiontext = $definition;

        // Import files embedded in the entry text.
        $questiontext = $this->import_text_with_files($xmlentry,
            array());
        $qo->questiontext = $questiontext['text'];
        $qo->name = s(substr(utf8_decode($definition), 0, 50));
        if ($format == FORMAT_HTML) {
            $qo->name = s(substr(utf8_decode(html_to_text($definition)), 0, 50));
        }
        $qo->answer[0] = $concept;
        $qo->usecase = $usecase;
        $qo->fraction[0] = 1;
        $qo->feedback[0] = array();
        $qo->feedback[0]['text'] = '';
        $qo->feedback[0]['format'] = FORMAT_PLAIN;

        if (!empty($questiontext['itemid'])) {
            $qo->questiontextitemid = $questiontext['itemid'];
        }

        // If there are aliases, add these as alternate answers.
        $xmlaliases = @$xmlentry['#']['ALIASES'][0]['#']['ALIAS']; // Ignore missing ALIASES.
        $sizeofxmlaliases = is_array($xmlaliases) ? count($xmlaliases) : 0;
        for ($k = 0; $k < $sizeofxmlaliases; $k++) {
            $xmlalias = $xmlaliases[$k];
            $aliasname = trim(trusttext_strip($xmlalias['#']['NAME'][0]['#']));
            $qo->answer[$k + 1] = $aliasname;
            $qo->fraction[$k + 1] = 1;
            $qo->feedback[$k + 1] = array();
            $qo->feedback[$k + 1]['text'] = '';
            $qo->feedback[$k + 1]['format'] = FORMAT_PLAIN;
        }

        // Read the question tags.
        $this->import_question_tags($qo, $xmlentry);

        return $qo;
    }

    // Overwrite this method from xml import.
    public function import_text_with_files($data, $path, $defaultvalue = '', $defaultformat = 'html') {
        $field  = array();
        $field['text'] = $this->getpath($data,
                array_merge($path, array('#', 'DEFINITION', 0, '#')), $defaultvalue, true);
        $field['format'] = $this->trans_format($this->getpath($data,
                array_merge($path, array('@', 'FORMAT')), $defaultformat));
        $itemid = $this->import_files_as_draft($this->getpath($data,
                array_merge($path, array('#', 'ENTRYFILES', 0, '#', 'FILE')), array(), false));
        if (!empty($itemid)) {
            $field['itemid'] = $itemid;
        }
        return $field;
    }

    // Overwrite this method from xml import.
    public function import_files_as_draft($xml) {
        global $USER;
        if (empty($xml)) {
            return null;
        }
        $fs = get_file_storage();
        $itemid = file_get_unused_draft_itemid();
        $filepaths = array();
        foreach ($xml as $file) {
            $filename = $this->getpath($file, array('#', 'FILENAME', 0, '#'), '', true);
            $filepath = $this->getpath($file, array('#', 'FILEPATH', 0, '#'), '/', true);
            $contents = $this->getpath($file, array('#', 'CONTENTS', 0, '#'), '/', true);
            $fullpath = $filepath . $filename;
            if (in_array($fullpath, $filepaths)) {
                debugging('Duplicate file in XML: ' . $fullpath, DEBUG_DEVELOPER);
                continue;
            }
            $filerecord = array(
                'contextid' => context_user::instance($USER->id)->id,
                'component' => 'user',
                'filearea'  => 'draft',
                'itemid'    => $itemid,
                'filepath'  => $filepath,
                'filename'  => $filename,
            );
            $fs->create_file_from_string($filerecord, base64_decode($contents));
            $filepaths[] = $fullpath;
        }
        return $itemid;
    }

    /**
     * Import all the glossary tags as question tags
     *
     * @param object $qo the question data that is being constructed.
     * @param array $xmlentry The xml representing the glossary entry.
     * @return array of objects representing the tags in the file.
     */
    public function import_question_tags($qo, $xmlentry) {

        if (get_config('core', 'version') < 2016120503) {
            return;
        }

        if (core_tag_tag::is_enabled('core_question', 'question')
            && array_key_exists('TAGS', $xmlentry['#'])
            && !empty($xmlentry['#']['TAGS'][0]['#']['TAG'])) {
            $qo->tags = array();
            foreach ($xmlentry['#']['TAGS'][0]['#']['TAG'] as $tagdata) {
                $qo->tags[] = $tagdata['#'];
            }
            return $qo->tags;
        }
    }

}
