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
 * Main class for plugin 'media_bustreaming'
 *
 * @package   media_bustreaming
 * @copyright 2018 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Player that creates bustreaming embedding.
 *
 * @package   media_bustreaming
 * @author    2011 The Open University
 * @copyright 2018 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class media_bustreaming_plugin extends core_media_player_external {

    public function list_supported_urls(array $urls, array $options = array()) {
        // These only work with a SINGLE url (there is no fallback).
        if (count($urls) == 1) {
            $url = reset($urls);
            // Check against regex.
            if (preg_match($this->get_regex(), $url->out(false), $this->matches)) {            
                return array($url);
            }
        }

        return array();
    }

    /**
        * Utility function that sets width and height to defaults if not specified
        * as a parameter to the function (will be specified either if, (a) the calling
        * code passed it, or (b) the URL included it).
        * @param int $width Width passed to function (updated with final value)
        * @param int $height Height passed to function (updated with final value)
        */
    protected static function pick_video_size(&$width, &$height) {
        if (!get_config('media_videojs', 'limitsize')) {
            return;
        }
        if(!$width) {
            $width = '100%';
            $height = '440px';
        } else {
            parent::pick_video_size($width, $height);
        }
    }
    
    
    
    protected function embed_external(moodle_url $url, $name, $width, $height, $options) {

        $info = trim($name);
        if (empty($info) or strpos($info, 'http') === 0) {
            $info = get_string('pluginname', 'media_bustreaming');
        }
        $info = s($info);

        self::pick_video_size($width, $height);

        $videoid = end($this->matches);

        return <<<OET
<span class="mediaplugin mediaplugin_bustreaming">
<iframe title="$info" width="$width" height="$height"
  src="https://bustreaming.ulpgc.es/bustreaming/reproducirEmbed.php?$videoid" frameborder="0" allowfullscreen="1"></iframe>
</span>
OET;
    }

    /**
     * Returns regular expression used to match URLs for single bustreaming video
     * @return string PHP regular expression e.g. '~^https?://example.org/~'
     */
    protected function get_regex() {
        // Regex for standard bustreaming link.
        //$start = '~^https?://bustreaming.ulpgc.es/bustreaming_reproducirpublicacion';
        $start = '~^https?://(bustreaming.ulpgc.es)\/(bustreaming_reproducirpublicacion)';
        // Middle bit: Video key value.
        $middle = '.*(idpublicacion.*)';
        return $start . $middle . core_media_player_external::END_LINK_REGEX_PART;
    }

    public function get_embeddable_markers() {
        return array('bustreaming.ulpgc.es/bustreaming_reproducirpublicacion');
    }

    /**
     * Default rank
     * @return int
     */
    public function get_rank() {
        return 105;
    }
}
