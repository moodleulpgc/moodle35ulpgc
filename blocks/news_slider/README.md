# README #
moodle-block_news_slider

### Moodle News Slider ###
A news slider that displays unread course announcements and site announcements.  Can also be used on course pages to show unread announcements for that specific course.

# General configuration #

- Configurable max length of excerpt
- Configurable max length of subject
- Configurable to use caching for the user session plus cache expiry time

# Per instance configuration  #

- Option to display site news or course news or both
- Maximum number of site / course announcements to display
- Maximum period (in days) to show site / course announcements for
- Display link to older news items
- Show bullet (dots) navigation on bottom of slider

# Guidelines for use #

You may want to experiment with the general configuration settings. E.g. for wider displays, you could increase max lengths and vice-versa.  Please note that caching
is switched on by default and set to expire every 5 minutes (300) for a user session by default.

## Using the slider on course pages ##

There is custom block region on course pages in the Adaptable theme called "course page slider region", designed for use with the news slider.  

See basic instructions below:

1. Add the news slider to the "Course slider" block region that can be seen by admins on the frontpage.  Configure it to appear on all pages.
2. Go to any course page. You should the news slider appear just above course content. Now configure this block to appear only on course pages.

# Version number #

Version 1.3 (2017122200)

### How do I get set up? ###

Installs at <moodleroot>/blocks/news_slider

## Settings ##

Site-wide configuration options are available under: 
Site Administration -> Plugins -> Blocks -> News slider

Per Instance block settings are available by editing block configuration.

# Dependencies #

Adaptable Theme version 2017053100 (1.4+)

### Compatibility ###

- Moodle 3.2, 3.3, 3.4
- Adaptable version 1.4

### Contribution ###

Developed by:

 * Manoj Solanki (Coventry University)
 * John Tutchings (Coventry University)

Co-maintained by:

 * Jeremy Hopkins (Coventry University)
 * Fernando Acedo (3-bits.com)
 
 ### Licenses ###

Adaptable is licensed under:
GPL v3 (GNU General Public License) - http://www.gnu.org/licenses
