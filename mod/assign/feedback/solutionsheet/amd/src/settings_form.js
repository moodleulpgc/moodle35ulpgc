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
 * Activity setting form js functionality.
 *
 * @package     assignfeedback_solutionsheet
 * @author      Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @copyright   2018 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module assignfeedback_solutionsheet/settings_form
 */

define(
['jquery', 'core/ajax', 'core/str', 'core/notification'],
function($, ajax, str, notification) {
    var settingsForm = {

        /**
         * Initialise module.
         */
        init: function() {
            settingsForm.radioGroup = $("input[name='assignfeedback_solutionsheet_showattype']");
            settingsForm.yesFromNowRadioButton = $('#id_assignfeedback_solutionsheet_showattype_1');
            settingsForm.yesFromNowValue = settingsForm.yesFromNowRadioButton.val();
            settingsForm.curentValue = $("input[name='assignfeedback_solutionsheet_showattype']:checked").val();

            settingsForm.radioGroup.click(settingsForm.displayConfirmDialog);
        },

        /**
         * Displays a confirmation pop-up dialog when click to "Yes, from now on" radio button.
         */
        displayConfirmDialog: function(e) {

            if (settingsForm.shouldDisplayConfirmDialog()) {
                e.preventDefault();
                str.get_strings([
                    {
                        key:        'confirmtitle',
                        component:  'assignfeedback_solutionsheet'
                    },
                    {
                        key:        'confirmtext',
                        component:  'assignfeedback_solutionsheet'
                    },
                    {
                        key:        'yes',
                        component:  'moodle'
                    },
                    {
                        key:        'no',
                        component:  'moodle'
                    }
                ]).done(function(s) {
                    notification.confirm(s[0], s[1], s[2], s[3], $.proxy(function() {
                        settingsForm.yesFromNowRadioButton.prop("checked", true);
                        settingsForm.curentValue = $("input[name='assignfeedback_solutionsheet_showattype']:checked").val();
                    }));
                });
            } else {
                settingsForm.curentValue = $("input[name='assignfeedback_solutionsheet_showattype']:checked").val();
            }
        },

        /**
         * Check if we need to display confirmation dialog.
         *
         * @returns {boolean}
         */
        shouldDisplayConfirmDialog: function () {
            var selectedValue = $("input[name='assignfeedback_solutionsheet_showattype']:checked").val();

            return (selectedValue === settingsForm.yesFromNowValue && settingsForm.curentValue !== settingsForm.yesFromNowValue);
        }
    };

    return {
        init: settingsForm.init
    };
});
