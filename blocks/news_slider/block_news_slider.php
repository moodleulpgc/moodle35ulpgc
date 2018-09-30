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
 * Moodle News Slider block.  Displays course and site announcements.
 *
 * @package block_news_slider
 * @copyright 2017 Manoj Solanki (Coventry University)
 * @copyright
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot .'/course/lib.php'); // Included to be able to get site news older posts.
require_once(dirname(__FILE__) . '/lib.php');

/**
 * News Slider block implementation class.
 *
 * @package block_news_slider
 * @copyright 2017 Manoj Solanki (Coventry University)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_news_slider extends block_base {

    /** @var string The name of the block */
    public $blockname = null;

    /** @var int Display Mode all news */
    const DISPLAY_MODE_ALL_NEWS = 1;
    /** @var int Display Mode Site news only */
    const DISPLAY_MODE_SITE_NEWS = 2;
    /** @var int Display Mode Course news only */
    const DISPLAY_MODE_COURSE_NEWS = 3;

    /** @var int Default number of site items to show */
    const NEWS_SLIDER_DEFAULT_SITE_NEWS_ITEMS = 4;

    /** @var int Default site news period to show */
    const NEWS_SLIDER_DEFAULT_SITE_NEWS_PERIOD = 7; // In days.

    /** @var int Default number of course items to show */
    const NEWS_SLIDER_DEFAULT_COURSE_NEWS_ITEMS = 7;

    /** @var int Default course news period to show */
    const NEWS_SLIDER_DEFAULT_COURSE_NEWS_PERIOD = 7; // In days.

    /** @var string Default left banner title */
    const NEWS_SLIDER_DEFAULT_TITLE_BANNER = "Latest News";

    /** @var int Default no news display text */
    const DISPLAY_NO_NEWS_TEXT = "You do not have any unread news posts at the moment";

    /**
     * @var CACHENAME_SLIDER  The name of the cache used for storing slider data.
     */
    const CACHENAME_SLIDER = 'sliderdata';

    /**
     * @var CACHENAME_SLIDER_KEY  The key of the cache used for storing slider data.
     */
    const CACHENAME_SLIDER_KEY = 'sliderkey';

    /**
     * Adds title to block instance.
     */
    public function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', $this->blockname);
    }

    /**
     * Calls functions to load js and css and returns block instance content.
     */
    public function get_content() {
        global $COURSE, $USER, $OUTPUT, $PAGE, $ME;

        $config = get_config($this->blockname);
        if ($this->content !== null) {
            return $this->content;
        }

        //
        // Check if this is a valid page to display block on.  Must be either the dashboard, homepage or a course page.
        //
        $displayblock = false;

        // Check if we are on dashboard page or front page.
        if (($PAGE->pagetype == 'site-index') || ($PAGE->pagetype == 'my-index')) {
            $displayblock = true;

        } else {

            // Check for general course page first.
            $url = null;

            // Check if $PAGE->url is set.  It should be, but also using a fallback.
            if ($PAGE->has_set_url()) {
                $url = $PAGE->url;
            } else if ($ME !== null) {
                $url = new moodle_url(str_ireplace('/index.php', '/', $ME));
            }

            // In practice, $url should always be valid.
            if ($url !== null) {
                // Check if this is the course view page.
                if (strstr ($url->raw_out(), 'course/view.php')) {

                    // Get raw querystring params from URL.
                    $getparams = http_build_query($_GET);

                    // Check url paramaters.  Count should be 1 if course home page.
                    // Checking that section param doesn't exist as an extra.  Also checking raw querystring defined
                    // above.  This is due to section 0 not actually recording 'section' as a param.
                    $urlparams = $url->params();

                    if ((count ($urlparams) == 1) && (!array_key_exists('section', $urlparams)) &&
                            (!strstr ($getparams, 'section=')) ) {
                        $displayblock = true;
                    }
                }
            }
        }

        if ($displayblock == false) {
            return '';

        }

        $this->content = new stdClass;

        $PAGE->requires->css('/blocks/news_slider/slick/slick.css');
        $PAGE->requires->css('/blocks/news_slider/slick/slick-theme.css');

        $newscontent = "";  // Used to store news content.

        // Check if caching is being used.  Caching doesn't apply to course page slider.
        if ( (!empty ($config->usecaching) && ($COURSE->id <= 1) ) ) {
            $cache = cache::make($this->blockname, self::CACHENAME_SLIDER);

            $returnedcachedata = $cache->get(self::CACHENAME_SLIDER_KEY);

            // Use this to write data to cache in array format, ['lastcachebuildtime'] = last access time, ['data'] = actual data.
            $cachedatastore = array();

            $usercachettl = $config->cachingttl;

            $timenow = time();

            // If no data retrieved or lastcachebuildtime has no value.
            // Or if user's last cache has expired since it was last built.
            if ( ($returnedcachedata === false) || (!isset($returnedcachedata['lastcachebuildtime'])) ||
                ( $timenow > ($returnedcachedata['lastcachebuildtime'] + $usercachettl)) ) {

                $cachedatastore['data'] = self::build_news();

                // Now timestamp the cache with last build time.
                $cachedatastore['lastcachebuildtime'] = time();
                $cache->set(self::CACHENAME_SLIDER_KEY, $cachedatastore);
            } else {
                $cachedatastore['data'] = $returnedcachedata['data'];  // We got valid, non-expired data from cache.
            }

            $newscontent = $cachedatastore['data'];

        } else {
            $newscontent = self::build_news();
        }

        // If no news content, do not display slider.
        if (empty ($newscontent)) {
            return '';
        }

        if (!empty($this->config->showdots) && ($this->config->showdots == true)) {
            $showdots = true;
        } else if (!isset ($this->config->showdots)) {  // Check config setting is recognised.
            $showdots = true;
        } else {
            $showdots = false;
        }

        if(!$PAGE->user_is_editing()) { // ecastro ULPGC
            $PAGE->requires->js_call_amd($this->blockname . '/slider', 'init', array($showdots));
        }
        
        $this->content->text = html_writer::tag('div', $newscontent);

        return $this->content;
    }

    /**
     * Main function to control building news items and return html formatted content.
     *
     * @return array HTML formatted news content
     */
    private function build_news() {
        global $OUTPUT;

        $newsblock = $this->get_courses_news();

        if (empty ($newsblock)) {
            return '';
        }
        $newscontentjson = new stdClass();

        if (!empty ($this->config->bannertitle)) {
            $newscontentjson->title = $this->config->bannertitle;
        } else {
            $newscontentjson->title = $this::NEWS_SLIDER_DEFAULT_TITLE_BANNER;
        }

        $newscontentjson->news = array_values($newsblock);

        if (!empty($this->config->showdots) && ($this->config->showdots == true)) {
            $newscontentjson->slidercontainerstyles = ' style="height: 125px;" ';
        } else if (!isset ($this->config->showdots)) {  // Check config setting is recognised.
            $newscontentjson->slidercontainerstyles = ' style="height: 125px;" ';
        } else {
            $newscontentjson->slidercontainerstyles = '';
        }

        $newscontentfinal = $OUTPUT->render_from_template($this->blockname . '/slider', $newscontentjson);
        return $newscontentfinal;
    }

    /**
     * Gets course news for relevant courses.
     *
     * @return array An array of news posts
     */
    private function get_courses_news() {
        global $COURSE, $USER, $OUTPUT, $CFG, $SITE, $PAGE;

        // Get all courses news.
        $allcourses = enrol_get_my_courses('id, shortname', 'visible DESC,sortorder ASC');

        foreach ($allcourses as $c) {
            if (isset($USER->lastcourseaccess[$c->id])) {
                $c->lastaccess = $USER->lastcourseaccess[$c->id];
            } else {
                $c->lastaccess = 0;
            }
        }

        // This variable is created to pass in as an argument in calls to functions outside of this class
        // (i.e. news_slider_get_course_news).  This is done when the slider is displayed when a user
        // is not logged in, as the code complains (php errors) about the non-existence of config instances in
        // functions called that are outside of this class.
        $sliderconfig = new stdClass();

        // Check what type of news to display from config.
        if (!empty($this->config->displaymode)) {
            $newstype = $this->config->displaymode;
        } else {
            $newstype = $this::DISPLAY_MODE_ALL_NEWS;
        }

        if (!empty($this->config->siteitemstoshow)) {
            $sliderconfig->siteitemstoshow = $this->config->siteitemstoshow;
        } else {
            $sliderconfig->siteitemstoshow = $this::NEWS_SLIDER_DEFAULT_SITE_NEWS_ITEMS;
        }

        if (!empty($this->config->siteitemsperiod)) {
            $sliderconfig->siteitemsperiod = $this->config->siteitemsperiod;
        } else {
            $sliderconfig->siteitemsperiod = $this::NEWS_SLIDER_DEFAULT_SITE_NEWS_PERIOD;
        }

        if (!empty($this->config->courseitemstoshow)) {
            $sliderconfig->courseitemstoshow = $this->config->courseitemstoshow;
        } else {
            $sliderconfig->courseitemstoshow = $this::NEWS_SLIDER_DEFAULT_COURSE_NEWS_ITEMS;
        }

        if (!empty($this->config->courseitemsperiod)) {
            $sliderconfig->courseitemsperiod = $this->config->courseitemsperiod;
        } else {
            $sliderconfig->courseitemsperiod = $this::NEWS_SLIDER_DEFAULT_COURSE_NEWS_PERIOD;
        }

        $newsblock = new stdClass;
        $newsblock->headlines = array();
        $newsblock->newsitems = array();
        $coursenews = array();
        $tempnews = array();

        $newscontent = array();

        // Get course news.
        if ( ($newstype == $this::DISPLAY_MODE_ALL_NEWS) || ($newstype == $this::DISPLAY_MODE_COURSE_NEWS) ) {

            // First check if we're on a course page. If so, only get posts for that course.
            if ($COURSE->id > 1) {
                $tempnews = news_slider_get_course_news($COURSE, false, $sliderconfig);
                if (!empty($tempnews)) {
                    $this->format_course_news_items ($COURSE, $tempnews, $coursenews);
                }
            } else {
                $currenttotalcoursesretrieved = 0;
                foreach ($allcourses as $course) {
                    $tempnews = news_slider_get_course_news($course, false, $sliderconfig, $currenttotalcoursesretrieved);
                    if (!empty($tempnews)) {
                        $this->format_course_news_items ($course, $tempnews, $coursenews);
                    }

                } // End foreach.

            }
        }

        // Get site news.
        if ( ($newstype == $this::DISPLAY_MODE_ALL_NEWS) || ($newstype == $this::DISPLAY_MODE_SITE_NEWS) ) {
            global $SITE;
            $tempnews = news_slider_get_course_news($SITE, true, $sliderconfig);
            if (!empty($tempnews)) {
                $this->format_course_news_items ($SITE, $tempnews, $coursenews);
            }
        }

        if (empty($coursenews)) {
            $coursenews = array();

        } else {
            // Sort course news items.

            // Sory by pinned posts and date by creating sort keys.
            foreach ($coursenews as $key => $row) {
                // Replace 0 with the field's index/key.
                $dates[$key]  = $row['pinned'] . $row['datemodified'];
            }
            array_multisort($dates, SORT_DESC, $coursenews);

        }

        return $coursenews;
    }

    /**
     * Format news items ready for display and rendering by a template.
     *
     * @param stdClass $course The course from which to get the news items for the current user
     * @param array    $newsitems Array of news items to format
     * @param array    $returnedcoursenews The array to populate with formatted news items
     *
     * @return None
     *
     */
    private function format_course_news_items($course, $newsitems, &$returnedcoursenews) {
        global $SITE;

        $config = get_config($this->blockname);
        $excerptlength = $config->excerptlength;
        $subjectmaxlength = $config->subjectmaxlength;

        foreach ($newsitems as $news) {
            $newslink = new moodle_url('/mod/forum/discuss.php', array('d' => $news['discussion']));

            // Subject.  Trim if longer than $subjectmaxlength.
            $subject = $news['subject'];

            if (strlen($subject) > $subjectmaxlength) {
                $subject = preg_replace('/\s+?(\S+)?$/', '', substr($subject, 0, $subjectmaxlength)) . " ... ";
            }

            if (!empty($news['pinned'])) {
                $subject = $news['pinned'] . ' ' . $subject;
            }

            $headline = html_writer::tag('div', html_writer::link(new moodle_url('/mod/forum/discuss.php',
                    array('d' => $news['discussion'])), $subject),
                    array('class' => 'news_sliderNewsHeadline'));

            $readmorelink = '';

            // Replace p and <br> tags with a '' or space.  Fixes #33 with text being put together from html p and <br> tags.
            $news['message'] = str_replace('<p>', '', $news['message']);
            $news['message'] = str_replace(',</p>', ', ' , $news['message']);
            $news['message'] = str_replace('</p>', ' ' , $news['message']);

            if ( (!empty($excerptlength)) && ($excerptlength == 0) ) {
                $newsmessage = '<a href="' . $newslink . '">' . strip_tags($news['message']) . '</a>';
            } else if (strlen($news['message']) > $excerptlength) {
                $newsmessage = news_slider_truncate_news(strip_tags($news['message']), $excerptlength, ' .. ');
                $readmorelink = ' <a href="' . $newslink . '"><strong>[Read More]</strong></a>';
                $newsmessage .= $readmorelink;
            } else {
                $newsmessage = '<a href="' . $newslink . '">' . strip_tags($news['message']) . '</a>';
            }

            // Check if this is site news. If so, provide a link to older news if needed. (Issue #14).
            $oldernewslink = "";
            if ($course->id == $SITE->id) {

                $newsforum = forum_get_course_forum($SITE->id, 'news');

                if ($newsforum) {
                    global $CFG;
                    $oldnewsurl = $CFG->wwwroot . '/mod/forum/view.php?f=' . $newsforum->id . '&amp;showall=1';
                    if ($readmorelink != '') {
                        $oldernewslink .= ' | ';
                    }
                    $oldernewslink .= ' <a href="' . $oldnewsurl . '" title="Click here to view older posts">';
                    $oldernewslink .= '<strong>[Older posts]</strong></a>';
                } else {
                    print_error('cannotfindorcreateforum', 'forum');
                }
            }

            // Check config for displaying older posts.
            if (!empty($this->config->showoldnews) && ($this->config->showoldnews == true) ) {
                $newsmessage .= $oldernewslink;
            }

            // For small screen displays, prepare a shorter version of news message, regardless
            // of excerpt length config.
            $shortnewsexcerptlength = 50;
            $shortnewsmessage = news_slider_truncate_news(strip_tags($news['message']), $shortnewsexcerptlength, ' .. ');
            if (strstr ($shortnewsmessage, ' .. ')) {
                $shortnewsmessage .= $readmorelink;
            }
            $shortnewsmessage = '<a href="' . $newslink . '">' . $shortnewsmessage . ' </a>';

            // Shortname check.  If course announcement, add as link to course.
            $courseshortname = "Site Announcement";
            if ($course->id != $SITE->id) {
                $courselink = new moodle_url('/course/view.php', array('id' => $course->id));
                $courseshortname = '<a href="' . $courselink . '" title="View ' . $course->shortname . '">';
                $courseshortname .= '<strong> ' . $course->shortname . '</strong></a>';
            }

            $returnedcoursenews[] = array('headline'  => $headline,
                    'author'           => ', by ' . $news['author'],
                    'courseshortname'  => $courseshortname,
                    'message'          => $newsmessage,
                    'shortmessage'     => $shortnewsmessage,
                    'userdayofdate'    => date('l', $news['modified']) . ',',
                    'datemodified'     => $news['modified'],
                    'pinned'           => $news['pinned'],
                    'userdatemodified' => date('d/m/Y', $news['modified']),
                    'userid'           => $news['userid'],
                    'userpicture'      => $news['userpicture'],
                    'link'             => $newslink,
                    'profilelink'      => new moodle_url('/user/view.php', array('id' => $news['userid'], 'course' => $course->id))
            );

        }
    }

    /**
     * Allows multiple instances of the block.
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Sets block header to be hidden or visible
     *
     * @return bool if true then header will be visible.
     */
    public function hide_header() {
        return true;
    }

}
