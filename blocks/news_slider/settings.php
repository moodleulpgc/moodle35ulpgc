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
 *
 * Settings for Block news slider.
 *
 * @package   block_news_slider
 * @copyright 2017 Manoj Solanki (Coventry University)
 * @copyright
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtext('block_news_slider/excerptlength',
            get_string('excerptlength', 'block_news_slider'),
            get_string('excerptlengthdesc', 'block_news_slider'), NEWS_SLIDER_EXCERPT_LENGTH, PARAM_INT));

    $settings->add(new admin_setting_configtext('block_news_slider/subjectmaxlength',
            get_string('subjectmaxlength', 'block_news_slider'),
            get_string('subjectmaxlengthdesc', 'block_news_slider'), NEWS_SLIDER_SUBJECT_MAX_LENGTH, PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('block_news_slider/usecaching', get_string('usecaching', 'block_news_slider'),
            get_string('usecachingdesc', 'block_news_slider'), 0));

    $settings->add(new admin_setting_configtext('block_news_slider/cachingttl',
            get_string('cachingttl', 'block_news_slider'),
            get_string('cachingttldesc', 'block_news_slider'), NEWS_SLIDER_CACHING_TTL, PARAM_INT));
}
