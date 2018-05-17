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
 * @package    atto_chemistry
 * @copyright  2014 Geoffrey Rowland <rowland.geoff@gmail.com>
 * Based on    @package atto_equation
 * @copyright  2013 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Atto text editor chemistry plugin.
 */

/**
 * Atto chemistry editor.
 *
 * @namespace M.atto_chemistry
 * @class Button
 * @extends M.editor_atto.EditorPlugin
 */

var COMPONENTNAME = 'atto_chemistry',
    LOGNAME = 'atto_chemistry',
    CSS = {
        CHEMISTRY_TEXT: 'atto_chemistry_chemistry',
        CHEMISTRY_PREVIEW: 'atto_chemistry_preview',
        SUBMIT: 'atto_chemistry_submit',
        LIBRARY: 'atto_chemistry_library',
        LIBRARY_GROUPS: 'atto_chemistry_groups',
        LIBRARY_GROUP_PREFIX: 'atto_chemistry_group'
    },
    SELECTORS = {
        LIBRARY: '.' + CSS.LIBRARY,
        LIBRARY_GROUP: '.' + CSS.LIBRARY_GROUPS + ' > div > div',
        CHEMISTRY_TEXT: '.' + CSS.CHEMISTRY_TEXT,
        CHEMISTRY_PREVIEW: '.' + CSS.CHEMISTRY_PREVIEW,
        SUBMIT: '.' + CSS.SUBMIT,
        LIBRARY_BUTTON: '.' + CSS.LIBRARY + ' button'
    },
    DELIMITERS = {
        START: '\\(\\ce{ ',
        END: ' }\\)'
    },
    TEMPLATES = {
        FORM: '' +
            '<form class="atto_form">' +
                '{{{library}}}' +
                '<label for="{{elementid}}_{{CSS.CHEMISTRY_TEXT}}">{{{get_string "editchemistry" component texdocsurl}}}</label>' +
                '<textarea class="fullwidth {{CSS.CHEMISTRY_TEXT}}" ' +
                        'id="{{elementid}}_{{CSS.CHEMISTRY_TEXT}}" rows="8"></textarea><br/>' +
                '<label for="{{elementid}}_{{CSS.CHEMISTRY_PREVIEW}}">{{get_string "preview" component}}</label>' +
                '<div describedby="{{elementid}}_cursorinfo" class="well well-small fullwidth {{CSS.CHEMISTRY_PREVIEW}}" ' +
                        'id="{{elementid}}_{{CSS.CHEMISTRY_PREVIEW}}"></div>' +
                '<div id="{{elementid}}_cursorinfo">{{get_string "cursorinfo" component}}</div>' +
                '<div class="mdl-align">' +
                    '<br/>' +
                    '<button class="{{CSS.SUBMIT}}">{{get_string "savechemistry" component}}</button>' +
                '</div>' +
            '</form>',
        LIBRARY: '' +
            '<div class="{{CSS.LIBRARY}}">' +
                '<ul>' +
                    '{{#each library}}' +
                        '<li><a aria-label="{{get_string grouptitle ../component}}" ' +
                        'title="{{get_string grouptitle ../component}}" ' +
                        'href="#{{../elementid}}_{{../CSS.LIBRARY_GROUP_PREFIX}}_{{@key}}">' +
                            '{{get_string groupname ../component}}' +
                        '</a></li>' +
                    '{{/each}}' +
                '</ul>' +
                '<div class="{{CSS.LIBRARY_GROUPS}}">' +
                    '{{#each library}}' +
                        '<div id="{{../elementid}}_{{../CSS.LIBRARY_GROUP_PREFIX}}_{{@key}}">' +
                            '<div role="toolbar">' +
                            '{{#split "\n" elements}}' +
                                '<button tabindex="-1" data-tex="{{this}}" aria-label="{{this}}" title="{{this}}">' +
                                    '{{../../DELIMITERS.START}}{{this}}{{../../DELIMITERS.END}}' +
                                '</button>' +
                            '{{/split}}' +
                            '</div>' +
                        '</div>' +
                    '{{/each}}' +
                '</div>' +
            '</div>'
    };

Y.namespace('M.atto_chemistry').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

    /**
     * The selection object returned by the browser.
     *
     * @property _currentSelection
     * @type Range
     * @default null
     * @private
     */
    _currentSelection: null,

    /**
     * The cursor position in the chemistry textarea.
     *
     * @property _lastCursorPos
     * @type Number
     * @default 0
     * @private
     */
    _lastCursorPos: 0,

    /**
     * A reference to the dialogue content.
     *
     * @property _content
     * @type Node
     * @private
     */
    _content: null,

    /**
     * The source chemistry we are editing in the text.
     *
     * @property _sourceChemistry
     * @type Object
     * @private
     */
    _sourceChemistry: null,

    /**
     * A reference to the tab focus set on each group.
     *
     * The keys are the IDs of the group, the value is the Node on which the focus is set.
     *
     * @property _groupFocus
     * @type Object
     * @private
     */
    _groupFocus: null,

    /**
     * Regular Expression patterns used to pick out the chemistrys in a String.
     *
     * @property _chemistryPatterns
     * @type Array
     * @private
     */
    _chemistryPatterns: [
        // We use space or not space because . does not match new lines.
        // $$\ce{ blah }$$.
        /\$\$(\\ce\{[\S\s]+?)\}\$\$/,
        // E.g. "\(\ce{ blah }\)".
        /\\\(\\ce\{([\S\s]+?)\}\\\)/,
        // E.g. "\[\ce{ blah }\]".
        /\\\[\\ce\{([\S\s]+?)\}\\\]/,
        // E.g. "[tex]\ce{ blah }[/tex]".
        /\[tex\]\\ce\{([\S\s]+?)\}\[\/tex\]/
    ],

    initializer: function() {
        this._groupFocus = {};

        // If there is a tex filter active - enable this button.
        if (this.get('texfilteractive')) {
            // Add the button to the toolbar.
            this.addButton({
                icon: 'icon',
                iconComponent: COMPONENTNAME,
                callback: this._displayDialogue
            });

            // We need custom highlight logic for this button.
            this.get('host').on('atto:selectionchanged', function() {
                if (this._resolveChemistry()) {
                    this.highlightButtons();
                } else {
                    this.unHighlightButtons();
                }
            }, this);

            // We need to convert these to a non dom node based format.
            this.editor.all('tex').each(function (texNode) {
                var replacement = Y.Node.create('<span>' +
                        DELIMITERS.START + ' ' + texNode.get('text') + ' ' + DELIMITERS.END +
                        '</span>');
                texNode.replace(replacement);
            });
        }

    },

    /**
     * Display the chemistry editor.
     *
     * @method _displayDialogue
     * @private
     */
    _displayDialogue: function() {
        this._currentSelection = this.get('host').getSelection();

        if (this._currentSelection === false) {
            return;
        }

        // This needs to be done before the dialogue is opened because the focus will shift to the dialogue.
        var chemistry = this._resolveChemistry();

        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('pluginname', COMPONENTNAME),
            focusAfterHide: true,
            width: 600,
            focusOnShowSelector: SELECTORS.CHEMISTRY_TEXT
        });

        var content = this._getDialogueContent();
        dialogue.set('bodyContent', content);

        var library = content.one(SELECTORS.LIBRARY);

        var tabview = new Y.TabView({
            srcNode: library
        });

        tabview.render();
        dialogue.show();
        // Trigger any JS filters to reprocess the new nodes.
        Y.fire(M.core.event.FILTER_CONTENT_UPDATED, {nodes: (new Y.NodeList(dialogue.get('boundingBox')))});

        if (chemistry) {
            content.one(SELECTORS.CHEMISTRY_TEXT).set('text', chemistry);
        }
        this._updatePreview(false);
    },

    /**
     * If there is selected text and it is part of an chemistry,
     * extract the chemistry (and set it in the form).
     *
     * @method _resolveChemistry
     * @private
     * @return {String|Boolean} The chemistry or false.
     */
    _resolveChemistry: function() {

        // Find the chemistry in the surrounding text.
        var selectedNode = this.get('host').getSelectionParentNode(),
            selection = this.get('host').getSelection(),
            text,
            returnValue = false;

        // Prevent resolving chemistrys when we don't have focus.
        if (!this.get('host').isActive()) {
            return false;
        }

        // Note this is a document fragment and YUI doesn't like them.
        if (!selectedNode) {
            return false;
        }

        // We don't yet have a cursor selection somehow so we can't possibly be resolving a chemistry equation that has selection.
        if (!selection || selection.length === 0) {
            return false;
        }

        this.sourceChemistry = null;

        selection = selection[0];

        text = Y.one(selectedNode).get('text');

        // For each of these patterns we have a RegExp which captures the inner component of the chemistry equation but also
        // includes the delimiters.
        // We first run the RegExp adding the global flag ("g"). This ignores the capture, instead matching the entire
        // chemistry equation including delimiters and returning one entry per match of the whole chemistry equation.
        // We have to deal with multiple occurences of the same chemistry in a String so must be able to loop on the
        // match results.
        Y.Array.find(this._chemistryPatterns, function(pattern) {
            // For each pattern in turn, find all whole matches (including the delimiters).
            var patternMatches = text.match(new RegExp(pattern.source, "g"));

            if (patternMatches && patternMatches.length) {
                // This pattern matches at least once. See if this pattern matches our current position.
                // Note: We return here to break the Y.Array.find loop - any truthy return will stop any subsequent
                // searches which is the required behaviour of this function.
                return Y.Array.find(patternMatches, function(match) {
                    // Check each occurrence of this match.
                    var startIndex = 0;
                    while(text.indexOf(match, startIndex) !== -1) {
                        // Determine whether the cursor is in the current occurrence of this string.
                        // Note: We do not support a selection exceeding the bounds of an chemistry.
                        var startOuter = text.indexOf(match, startIndex),
                            endOuter = startOuter + match.length,
                            startMatch = (selection.startOffset >= startOuter && selection.startOffset < endOuter),
                            endMatch = (selection.endOffset <= endOuter && selection.endOffset > startOuter);

                        if (startMatch && endMatch) {
                            // This match is in our current position - fetch the innerMatch data.
                            var innerMatch = match.match(pattern);
                            if (innerMatch && innerMatch.length) {
                                // We need the start and end of the inner match for later.
                                var startInner = text.indexOf(innerMatch[1], startOuter),
                                    endInner = startInner + innerMatch[1].length;

                                // We'll be returning the inner match for use in the editor itself.
                                returnValue = innerMatch[1];

                                // Save all data for later.
                                this.sourceChemistry = {
                                    // Outer match data.
                                    startOuterPosition: startOuter,
                                    endOuterPosition: endOuter,
                                    outerMatch: match,

                                    // Inner match data.
                                    startInnerPosition: startInner,
                                    endInnerPosition: endInner,
                                    innerMatch: innerMatch
                                };

                                // This breaks out of both Y.Array.find functions.
                                return true;
                            }
                        }

                        // Update the startIndex to match the end of the current match so that we can continue hunting
                        // for further matches.
                        startIndex = endOuter;
                    }
                }, this);
            }
        }, this);

        // We trim the chemistry when we load it and then add spaces when we save it.
        if (returnValue !== false) {
            returnValue = returnValue.trim();
        }
        return returnValue;
    },

    /**
     * Handle insertion of a new chemistry equation, or update of an existing one.
     *
     * @method _setChemistry
     * @param {EventFacade} e
     * @private
     */
    _setChemistry: function(e) {
        var input,
            selectedNode,
            text,
            value,
            host,
            newText;

        host = this.get('host');

        e.preventDefault();
        this.getDialogue({
            focusAfterHide: null
        }).hide();

        input = e.currentTarget.ancestor('.atto_form').one('textarea');

        value = input.get('value');
        if (value !== '') {
            host.setSelection(this._currentSelection);

            if (this.sourceChemistry) {
                // Replace the chemistry.
                selectedNode = Y.one(host.getSelectionParentNode());
                text = selectedNode.get('text');
                value = ' ' + value + ' ';
                newText =   text.slice(0, this.sourceChemistry.startInnerPosition) +
                            value +
                            text.slice(this.sourceChemistry.endInnerPosition);

                selectedNode.set('text', newText);
            } else {
                // Insert the new chemistry.
                value = DELIMITERS.START + ' ' + value + ' ' + DELIMITERS.END;
                host.insertContentAtFocusPoint(value);
            }

            // Clean the YUI ids from the HTML.
            this.markUpdated();
        }
    },

    /**
     * Smart throttle, only call a function every delay milli seconds,
     * and always run the last call. Y.throttle does not work here,
     * because it calls the function immediately, the first time, and then
     * ignores repeated calls within X seconds. This does not guarantee
     * that the last call will be executed (which is required here).
     *
     * @param {function} fn
     * @param {Number} delay Delay in milliseconds
     * @method _throttle
     * @private
     */
    _throttle: function(fn, delay) {
        var timer = null;
        return function () {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
              fn.apply(context, args);
            }, delay);
        };
    },

    /**
     * Update the preview div to match the current chemistry.
     *
     * @param {EventFacade} e
     * @method _updatePreview
     * @private
     */
    _updatePreview: function(e) {
        var textarea = this._content.one(SELECTORS.CHEMISTRY_TEXT),
            chemistry = textarea.get('value'),
            url,
            currentPos = textarea.get('selectionStart'),
            prefix = '',
            cursorLatex = '$\\Downarrow$ ',
            isChar,
            params;

        if (e) {
            e.preventDefault();
        }

        // Move the cursor so it does not break expressions.
        // Start at the very beginning.
        if (!currentPos) {
            currentPos = 0;
        }

        // First move back to the beginning of the line.
        while (chemistry.charAt(currentPos) === '\\' && currentPos >= 0) {
            currentPos -= 1;
        }
        isChar = /[a-zA-Z\{\}]/;
        if (currentPos !== 0) {
            // Now match to the end of the line.
            while (isChar.test(chemistry.charAt(currentPos)) &&
                   currentPos < chemistry.length &&
                   isChar.test(chemistry.charAt(currentPos-1))) {
                currentPos += 1;
            }
        }
        // Save the cursor position - for insertion from the library.
        this._lastCursorPos = currentPos;
        chemistry = prefix + chemistry.substring(0, currentPos) + cursorLatex + chemistry.substring(currentPos);

        chemistry = DELIMITERS.START + ' ' + chemistry + ' ' + DELIMITERS.END;
        // Make an ajax request to the filter.
        url = M.cfg.wwwroot + '/lib/editor/atto/plugins/chemistry/ajax.php';
        params = {
            sesskey: M.cfg.sesskey,
            contextid: this.get('contextid'),
            action: 'filtertext',
            text: chemistry
        };

        Y.io(url, {
            context: this,
            data: params,
            timeout: 500,
            on: {
                complete: this._loadPreview
            }
        });
    },

    /**
     * Load returned preview text into preview
     *
     * @param {String} id
     * @param {EventFacade} e
     * @method _loadPreview
     * @private
     */
    _loadPreview: function(id, preview) {
        var previewNode = this._content.one(SELECTORS.CHEMISTRY_PREVIEW);

        if (preview.status === 200) {
            previewNode.setHTML(preview.responseText);

            Y.fire(M.core.event.FILTER_CONTENT_UPDATED, {nodes: (new Y.NodeList(previewNode))});
        }
    },

    /**
     * Return the dialogue content for the tool, attaching any required
     * events.
     *
     * @method _getDialogueContent
     * @return {Node}
     * @private
     */
    _getDialogueContent: function() {
        var library = this._getLibraryContent(),
            throttledUpdate = this._throttle(this._updatePreview, 500),
            template = Y.Handlebars.compile(TEMPLATES.FORM);

        this._content = Y.Node.create(template({
            elementid: this.get('host').get('elementid'),
            component: COMPONENTNAME,
            library: library,
            texdocsurl: this.get('texdocsurl'),
            CSS: CSS
        }));

        // Sets the default focus.
        this._content.all(SELECTORS.LIBRARY_GROUP).each(function(group) {
            // The first button gets the focus.
            this._setGroupTabFocus(group, group.one('button'));
            // Sometimes the filter adds an anchor in the button, no tabindex on that.
            group.all('button a').setAttribute('tabindex', '-1');
        }, this);

        // Keyboard navigation in groups.
        this._content.delegate('key', this._groupNavigation, 'down:37,39', SELECTORS.LIBRARY_BUTTON, this);

        this._content.one(SELECTORS.SUBMIT).on('click', this._setChemistry, this);
        this._content.one(SELECTORS.CHEMISTRY_TEXT).on('valuechange', throttledUpdate, this);
        this._content.one(SELECTORS.CHEMISTRY_TEXT).on('mouseup', throttledUpdate, this);
        this._content.one(SELECTORS.CHEMISTRY_TEXT).on('keyup', throttledUpdate, this);
        this._content.delegate('click', this._selectLibraryItem, SELECTORS.LIBRARY_BUTTON, this);

        return this._content;
    },

    /**
     * Callback handling the keyboard navigation in the groups of the library.
     *
     * @param {EventFacade} e The event.
     * @method _groupNavigation
     * @private
     */
    _groupNavigation: function(e) {
        e.preventDefault();

        var current = e.currentTarget,
            parent = current.get('parentNode'), // This must be the <div> containing all the buttons of the group.
            buttons = parent.all('button'),
            direction = e.keyCode !== 37 ? 1 : -1,
            index = buttons.indexOf(current),
            nextButton;

        if (index < 0) {
            Y.log('Unable to find the current button in the list of buttons', 'debug', LOGNAME);
            index = 0;
        }

        index += direction;
        if (index < 0) {
            index = buttons.size() - 1;
        } else if (index >= buttons.size()) {
            index = 0;
        }
        nextButton = buttons.item(index);

        this._setGroupTabFocus(parent, nextButton);
        nextButton.focus();
    },

    /**
     * Sets tab focus for the group.
     *
     * @method _setGroupTabFocus
     * @param {Node} button The node that focus should now be set to.
     * @private
     */
    _setGroupTabFocus: function(parent, button) {
        var parentId = parent.generateID();

        // Unset the previous entry.
        if (typeof this._groupFocus[parentId] !== 'undefined') {
            this._groupFocus[parentId].setAttribute('tabindex', '-1');
        }

        // Set on the new entry.
        this._groupFocus[parentId] = button;
        button.setAttribute('tabindex', 0);
        parent.setAttribute('aria-activedescendant', button.generateID());
    },

    /**
     * Reponse to button presses in the TeX library panels.
     *
     * @method _selectLibraryItem
     * @param {EventFacade} e
     * @return {string}
     * @private
     */
    _selectLibraryItem: function(e) {
        var tex = e.currentTarget.getAttribute('data-tex'),
        oldValue,
        newValue,
        input,
        focusPoint = 0;

        e.preventDefault();

        // Set the group focus on the button.
        this._setGroupTabFocus(e.currentTarget.get('parentNode'), e.currentTarget);

        input = e.currentTarget.ancestor('.atto_form').one('textarea');

        oldValue = input.get('value');

        newValue = oldValue.substring(0, this._lastCursorPos);

        // Remove ] and [ from TeX space buttons.
        tex = tex.replace('$\]','$');
        tex = tex.replace('\[$','$');

        // Add space after negative ions.
        if (/[a-zA-Z]+[\^\d]*\-/.test(tex)) {
          tex += ' ';
        }

        // Add any necessary spaces before and after input from button.
        if (newValue.charAt(newValue.length - 1) !== ' ') {
            // Wrap arrows with spaces.
            if (/v$/.test(tex) || /\^$/.test(tex) || /^</.test(tex) || /^->/.test(tex) || /^v\s/.test(tex) || /^\^\s/.test(tex) || /\}\]$/.test(newValue) || />$/.test(newValue)){
                newValue += ' ';
            }
            // If required, inserts space between \bond{} instances.
            if (/^\\bond\{.{1,4}\}/.test(tex) && /\\bond\{.{1,4}\}$/.test(newValue)) {
                newValue += ' ';
            }
        }
        newValue += tex;
        focusPoint = newValue.length + 1;

        // Add space after tex insert, before cursor.
        if (oldValue.charAt(this._lastCursorPos) !== ' ') {
            if (/\sv$/.test(newValue) || /\s\^$/.test(newValue) || /\}\]$/.test(newValue) || />$/.test(newValue)) {
                newValue += ' ';
            }
        }
        newValue += oldValue.substring(this._lastCursorPos, oldValue.length);

        input.set('value', newValue);
        input.focus();

        var realInput = input.getDOMNode();
        if (typeof realInput.selectionStart === "number") {
            // Modern browsers have selectionStart and selectionEnd to control the cursor position.
            realInput.selectionStart = realInput.selectionEnd = focusPoint;
        } else if (typeof realInput.createTextRange !== "undefined") {
            // Legacy browsers (IE<=9) use createTextRange().
            var range = realInput.createTextRange();
            range.moveToPoint(focusPoint);
            range.select();
        }
        // Focus must be set before updating the preview for the cursor box to be in the correct location.
        this._updatePreview(false);
    },

    /**
     * Return the HTML for rendering the library of predefined buttons.
     *
     * @method _getLibraryContent
     * @return {string}
     * @private
     */
    _getLibraryContent: function() {
        var template = Y.Handlebars.compile(TEMPLATES.LIBRARY),
            library = this.get('library'),
            content = '';

        // Helper to iterate over a newline separated string.
        Y.Handlebars.registerHelper('split', function(delimiter, str, options) {
            var parts,
                current,
                out;
            if (typeof delimiter === "undefined" || typeof str === "undefined") {
                Y.log('Handlebars split helper: String and delimiter are required.', 'debug', 'moodle-atto_chemistry-button');
                return '';
            }

            out = '';
            parts = str.trim().split(delimiter);
            while (parts.length > 0) {
                current = parts.shift().trim();
                out += options.fn(current);
            }

            return out;
        });
        content = template({
            elementid: this.get('host').get('elementid'),
            component: COMPONENTNAME,
            library: library,
            CSS: CSS,
            DELIMITERS: DELIMITERS
        });

        var url = M.cfg.wwwroot + '/lib/editor/atto/plugins/chemistry/ajax.php';
        var params = {
            sesskey: M.cfg.sesskey,
            contextid: this.get('contextid'),
            action: 'filtertext',
            text: content
        };

        var preview = Y.io(url, {
            sync: true,
            data: params,
            method: 'POST'
        });

        if (preview.status === 200) {
            content = preview.responseText;
        }
        return content;
    }
}, {
    ATTRS: {
        /**
         * Whether the TeX filter is currently active.
         *
         * @attribute texfilteractive
         * @type Boolean
         */
        texfilteractive: {
            value: false
        },

        /**
         * The contextid to use when generating this preview.
         *
         * @attribute contextid
         * @type String
         */
        contextid: {
            value: null
        },

        /**
         * The content of the example library.
         *
         * @attribute library
         * @type object
         */
        library: {
            value: {}
        },

        /**
         * The link to the Moodle Docs page about TeX.
         *
         * @attribute texdocsurl
         * @type string
         */
        texdocsurl: {
            value: null
        }

    }
});
