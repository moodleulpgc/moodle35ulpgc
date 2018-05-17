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
 * Quiz makeexam customized PDF class.
 *
 * @package   quiz_makeexam
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/pdflib.php');


class makeexam_pdf extends pdf {


    /**
     * Puts a moodle image in the page.
     * Delegates to parent Image method for all other images
     * All parameteres identical to parent TCPDF method
     * @author Enrique Castro @ ULPGC
     */
    public function Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='',
                            $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false, $alt=false, $altimgs=array()) {
        global $CFG, $DB;
        //http://localhost/moodle25ulpgc/pluginfile.php/4924/question/questiontext/38/3/3739/Bienvenidos.gif
                                                      // $contextid, $component, $filearea  /38 questionusage/ 3 slot/ questionid=itemid
            //print_object($file);
            //print_object("xxx   xxxx");

        // process pluginfile calls
        if(strpos($file, $CFG->wwwroot.'/pluginfile.php/') !== false) {
            $p = strpos($file, '/pluginfile.php/');
            $file = substr($file, $p+16);
            $args = explode('/', $file);
            $contextid = array_shift($args);
            $component = array_shift($args);
            $filearea = array_shift($args);
            $filename = array_pop($args);
            $filename = str_replace('%20', ' ', $filename); // ecastro ULPGC
            /*
            $itemid = array_pop($args);
            */
            // ecastro to allow folders
            if($component == 'question') {
                $qusage = array_shift($args);
                $qslot = array_shift($args);
                $itemid = array_shift($args);
                $filepath = '/'.implode('/',$args).'/';
            } else {
                $itemid = array_pop($args);
                $filepath = '/';
            }

            if(!$itemid) {
                $itemid = 0;
            }


            $fs = get_file_storage();
            if($fs->file_exists($contextid, $component, $filearea, $itemid, $filepath, $filename)) {
                $sfile = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);
                $file = '@'.$sfile->get_content();
            } else {
                //$file = $CFG->dirroot.'/pix/f/image-128.png';
                $alt =  "file: $file  <br />filearea:   $contextid, $component, $filearea, $itemid, $filepath, $filename ";
                $this->writeHTML($alt, false, false, true, false, '');
                return; // ecastro ULPGC
            }
        } elseif(strpos($file, '/filter/tex/pix.php/') !== false) {
            $config = get_config('quiz_makeexam');
            $p = strpos($file, '/filter/tex/pix.php/');
            $filename = substr($file, $p+20);
            $pathname = $CFG->dataroot.'/filter/tex/'.$filename;
             if(file_exists($pathname)) { unlink($pathname); }

            $md5 = str_replace(".{$CFG->filter_tex_convertformat}",'',$filename);
            if ($texcache = $DB->get_record('cache_filters', array('filter'=>'tex', 'md5key'=>$md5))) {
                require_once($CFG->dirroot.'/filter/tex/lib.php');
                require_once($CFG->dirroot.'/filter/tex/latex.php');
                if (!file_exists($CFG->dataroot.'/filter/tex')) {
                    make_upload_directory('filter/tex');
                }

                // try and render with latex first
                $latex = new latex();
                                $density = $CFG->filter_tex_density;
                $background = $CFG->filter_tex_latexbackground;
                $texexp = $texcache->rawtext; // the entities are now decoded before inserting to DB
                $latex_path = $latex->render($texexp, $md5, $config->tex_imagescale, $config->tex_density, $background);
                if ($latex_path) {
                    copy($latex_path, $pathname);
                    $latex->clean_up($md5);

                } else {
                    // failing that, use mimetex
                    $texexp = $texcache->rawtext;
                    $texexp = str_replace('&lt;', '<', $texexp);
                    $texexp = str_replace('&gt;', '>', $texexp);
                    $texexp = preg_replace('!\r\n?!', ' ', $texexp);
                    //$texexp = '\Large '.$texexp;
                    $cmd = filter_tex_get_cmd($pathname, $texexp);
                    system($cmd, $status);
                }
            }

            //print_object($pathname);
            //$pathname = str_replace('.png', '.jpg', $pathname);
            if(file_exists($pathname)) {
                //$file = '@'.file_get_contents($pathname, FILE_BINARY);
                $file = $pathname;
                $this->setImageScale(4);
                //return parent::ImagePngAlpha($file, 0, 0, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi, $palign);

            } else {
                $alt =  "file11: $file  <br />filearea:   $pathname ";
                $this->writeHTML($alt, false, false, true, false, '');
                return; // ecastro ULPGC

            }

        } else {
                            $alt =  "file22: $file  <br />filearea:    ";
                //$this->writeHTML($alt, false, false, true, false, '');
                return; // ecastro ULPGC

        }


        return TCPDF::Image($file, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi, $palign,
                            $ismask, $imgmask, $border, $fitbox, $hidden, $fitonpage, $alt, $altimgs);
    }






}


