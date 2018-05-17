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
 * Core renderer.
 *
 * @package    theme_boost_training
 * @copyright  2017 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_boost_training\output;
defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_boost_training
 * @copyright  2012 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost\output\core_renderer {

    /**
     * Renders the custom favicon.
     *
     * @return string
     */
    public function favicon() {
        if ($this->page->theme->settings->favicon) {
            return $this->page->theme->setting_file_url('favicon', 'favicon');
        }

        if (method_exists($this->page->theme, "image_url")) {
            return $this->page->theme->image_url('favicon', 'theme');
        } else {
            return $this->page->theme->pix_url('favicon', 'theme');
        }
    }

    /**
     * Renders the custom logo.
     *
     * @return bool
     */
    public function should_display_navbar_logo1() {
        if ($this->page->theme->settings->logo1) {
            return true;
        }

        return false;
    }

    /**
     * Renders the custom logo.
     *
     * @return string
     */
    public function get_logo_url1() {
        $imageurl = $this->page->theme->setting_file_url('logo1', 'logo1');
        if (!empty($imageurl)) {
            return $imageurl;
        }
        return '';
    }

    /**
     * Renders the custom logo.
     *
     * @return bool
     */
    public function should_display_navbar_logo2() {
        if ($this->page->theme->settings->logo2) {
            return true;
        }

        return false;
    }

    /**
     * Renders the custom logo
     *
     * @return string
     */
    public function get_logo_url2() {
        $imageurl = $this->page->theme->setting_file_url('logo2', 'logo2');
        if (!empty($imageurl)) {
            return $imageurl;
        }
        return '';
    }

    /**
     * Renders the flat menu
     *
     * @return bool|string
     */
    public function user_flat_menu() {
        global $CFG, $USER, $OUTPUT;

        if (!isset($USER->id) || !$USER->id) {
            return false;
        }

        $picture = $OUTPUT->user_picture($USER, array('size' => 65));
        $fullname = fullname($USER);

        return "
         <div class=\"user_flat_menu\" >
             <div class=\"user_picture\">
                 {$picture}
             </div>
             <div class=\"icones-right\">
                 <span class=\"bem-vindo\">" . get_string('welcome', 'theme_boost_training') . "</span><br>
                 <span>{$fullname}</span><br>
                 <a href=\"{$CFG->wwwroot}/user/profile.php?id={$USER->id}\" class=\"icones\"
                    ><i class=\"material-icons\" title=\"" . get_string('profile') . "\">account_box</i></a>
                 <a href=\"{$CFG->wwwroot}/grade/report/overview/\" class=\"icones\"
                    ><i class=\"material-icons\" title=\"" . get_string('grades', 'grades') . "\">assignment</i></a>
                 <a href=\"{$CFG->wwwroot}/user/preferences.php\" class=\"icones\"
                    ><i class=\"material-icons\" title=\"" . get_string('preferences', 'moodle') . "\">settings</i></a>
                 <a href=\"{$CFG->wwwroot}/login/logout.php?sesskey=" . sesskey() . "\" class=\"icones\"
                    ><i class=\"material-icons\" title=\"" . get_string('logout') . "\">exit_to_app</i></a>
             </div>
         </div>";
    }

    /**
     * Renders the icons footer
     *
     * @return string
     */
    public function get_icons_footer() {
        $returnicones = '';

        foreach ($this->page->theme->settings as $iconname => $setting) {
            if (strpos($iconname, 'icon_') === 0) {
                if (!empty($setting)) {

                    $icon = str_replace('icon_', '', $iconname);

                    if ($icon == 'website') {
                        $returnicones .= '<a target="_blank" href="' . $setting . '"><span
                                             class="footer-icon ' . $icon . '"><i class="material-icons">pages</i></span></a>';
                    } else {
                        $returnicones .= '<a target="_blank" href="' . $setting . '"><span
                                             class="footer-icon ' . $icon . '"><i class="fa fa-' . $icon . '"></i></span></a>';
                    }
                }
            }
        }

        return "<div class=\"icones\">$returnicones</div>";
    }

    /**
     * Renders the custom topo banner.
     *
     * @return string
     */
    public function get_topo_banner() {
        $imageurl = $this->page->theme->setting_file_url('topo_banner', 'topo_banner');
        if (!empty($imageurl)) {
            return $imageurl;
        }

        if (method_exists($this->page->theme, "image_url")) {
            return $this->page->theme->image_url('topo_banner', 'theme');
        } else {
            return $this->page->theme->pix_url('topo_banner', 'theme');
        }
    }

    /**
     * Renders the custom blocks.
     *
     * @return string
     */
    public function get_home_blocks() {
        $numblocks = 4;
        $rowclass = 'col-lg-3';

        if (empty($this->page->theme->settings->blocktitle_4)) {
            $numblocks = 3;
            $rowclass = 'col-lg-4';
        }
        if (empty($this->page->theme->settings->blocktitle_3)) {
            $numblocks = 2;
            $rowclass = 'col-lg-6';
        }
        if (empty($this->page->theme->settings->blocktitle_2) || empty($this->page->theme->settings->blocktitle_1)) {
            return "";
        }
        $returnblocks = '';
        for ($i = 1; $i <= $numblocks; $i++) {
            $blockcolor = $this->page->theme->settings->{"blockcolor_{$i}"};
            $blockicon = $this->page->theme->setting_file_url("blockicon_{$i}", "blockicon_{$i}");
            $blocktitle = $this->page->theme->settings->{"blocktitle_{$i}"};
            $blocktext = $this->page->theme->settings->{"blocktext_{$i}"};
            $blocklink = $this->page->theme->settings->{"blocklink_{$i}"};
            if (empty($blocklink)) {
                $blocklink = '#';
                $blocklinkoutput = '';
            } else {
                $blocklinkoutput = "<p><a target=\"_blank\" href=\"{$blocklink}\"><i class=\"material-icons\">open_in_new</i></a></p>";
            }

            $returnblocks .= "
                    <div class=\"row-col {$rowclass} col-sm-6\">
                        <div class=\"teaser\" style=\"background-color: {$blockcolor};\">
                            <div class=\"teaser_icon size_small\">
                                <img src=\"{$blockicon}\">
                            </div>
                            <h3 class=\"numbered\">
                                <a target=\"_blank\" href=\"{$blocklink}\">{$blocktitle}</a>
                            </h3>
                            <p>{$blocktext}</p>
                            {$blocklinkoutput}
                        </div>
                    </div>";
        }
        return "<section id=\"about\">
            <div class=\"container\">
                <div class=\"row\">
                    {$returnblocks}
                </div>
            </div>
        </section>";
    }
}
