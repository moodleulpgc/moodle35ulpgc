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
 * @package    atto_chemrender
 * @copyright  2016 UC Regents
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Atto text editor ChemRender plugin.
 */

/*
 *
 * ChemRender plugin for Atto Editor that provides a dialogue to upload, create, and modify chemical molecules and spectra,
 * and to select ChemDoolde and JMol specific display parameters.
 *
 * @namespace M.atto_chemrender
 * @class Button
 * @extends M.editor_atto.EditorPlugin
 */
var COMPONENTNAME = 'atto_chemrender',
        ELEMENTID = 0,
        CSS = {
            CHEMDOODLE_SKETCHER_OUTPUT: 'atto_chemrender_chemdoodle_sketcher_output',
            CHEMDOODLE_SKETCHER: 'atto_chemrender_chemdoodle_sketcher_iframe',
            ENTRY_CONTAINER: 'atto_chemrender_entry_container',
            FILE_BROWSER: 'atto_chemrender_file_browser',
            FILENAME_INPUT: 'atto_chemrender_filename_input',
            PREVIEW_BUTTON: 'atto_chemrender_preview_button',
            PREVIEW_CHEMDOODLE: 'atto_chemrender_preview_chemdoodle_iframe',
            PREVIEW_CONTAINER: 'atto_chemrender_preview_container',
            PREVIEW_JMOL: 'atto_chemrender_preview_jmol',
            PREVIEW_SKETCHER_BUTTON: 'atto_chemrender_preview_sketcher_button',
            RENDERER_SELECTION: 'atto_chemrender_renderer_selection',
            SUBMIT_BUTTON: 'atto_chemrender_submit_button',
            URL_INPUT: 'atto_chemrender_url_input',
            // Options for ChemDoodle.
            SIZE_OPTIONS: 'atto_chemrender_size_options',
            OPTION_WIDTH: 'atto_chemrender_size_option_width',
            OPTION_HEIGHT: 'atto_chemrender_size_option_height',
            // Options for JMol.
            JMOL_OPTIONS: 'atto_chemrender_jmol_options',
            OPTION_DOWNLOAD_LINK: 'atto_chemrender_jmol_option_download_link',
            OPTION_HELP_LINK: 'atto_chemrender_jmol_option_help_link',
            OPTION_SPIN: 'atto_chemrender_jmol_option_spin',
            OPTION_STYLESELECT: 'atto_chemrender_jmol_option_styleselect',
            OPTION_LABEL: 'atto_chemrender_jmol_option_label',
            OPTION_CONSOLE_COMMANDS: 'atto_chemrender_jmol_option_console_commands',
            OPTION_CONSOLE_COMMANDS_HELPLINK: 'atto_chemrender_jmol_option_console_commands_helplink',
            // Options for spectra.
            SPECTRUM_OPTIONS: 'atto_chemrender_spectrum_options',
            OPTION_XAXIS: 'atto_chemrender_spectrum_option_xaxis',
            OPTION_YAXIS: 'atto_chemrender_spectrum_option_yaxis'
        },
    SELECTORS = {
        CHEMDOODLE_SKETCHER_OUTPUT: '.' + CSS.CHEMDOODLE_SKETCHER_OUTPUT,
        CHEMDOODLE_SKETCHER: '.' + CSS.CHEMDOODLE_SKETCHER,
        FILE_BROWSER: '.' + CSS.FILE_BROWSER,
        FILENAME_INPUT: '.' + CSS.FILENAME_INPUT,
        JMOL_OPTIONS: '.' + CSS.JMOL_OPTIONS,
        OPTION_CONSOLE_COMMANDS: '.' + CSS.OPTION_CONSOLE_COMMANDS,
        OPTION_DOWNLOAD_LINK: '.' + CSS.OPTION_DOWNLOAD_LINK,
        OPTION_HEIGHT: '.' + CSS.OPTION_HEIGHT,
        OPTION_HELP_LINK: '.' + CSS.OPTION_HELP_LINK,
        OPTION_LABEL: '.' + CSS.OPTION_LABEL,
        OPTION_SPIN: '.' + CSS.OPTION_SPIN,
        OPTION_STYLESELECT: '.' + CSS.OPTION_STYLESELECT,
        OPTION_WIDTH: '.' + CSS.OPTION_WIDTH,
        OPTION_XAXIS: '.' + CSS.OPTION_XAXIS,
        OPTION_YAXIS: '.' + CSS.OPTION_YAXIS,
        PREVIEW_BUTTON: '.' + CSS.PREVIEW_BUTTON,
        PREVIEW_CHEMDOODLE: '.' + CSS.PREVIEW_CHEMDOODLE,
        PREVIEW_CONTAINER: '.' + CSS.PREVIEW_CONTAINER,
        PREVIEW_JMOL: '.' + CSS.PREVIEW_JMOL,
        PREVIEW_SKETCHER_BUTTON: '.' + CSS.PREVIEW_SKETCHER_BUTTON,
        RENDERER_SELECTION: '.' + CSS.RENDERER_SELECTION,
        SIZE_OPTIONS: '.' + CSS.SIZE_OPTIONS,
        SPECTRUM_OPTIONS: '.' + CSS.SPECTRUM_OPTIONS,
        SUBMIT_BUTTON: '.' + CSS.SUBMIT_BUTTON,
        URL_INPUT: '.' + CSS.URL_INPUT
    },
    TEMPLATE = '' +
            '<form class="atto_form">' +
            '<div class="{{CSS.ENTRY_CONTAINER}}">' +
            '<label for="{{elementid}}_{{CSS.URL_INPUT}}">{{get_string "enterurl" component}}</label>' +
            '<input class="fullwidth {{CSS.URL_INPUT}}" type="url" id="{{elementid}}_{{CSS.URL_INPUT}}" size="32"/><br/>' +
            '{{#if showFilepicker}}' +
            '<button class="{{CSS.FILE_BROWSER}}" type="button">{{get_string "browserepositories" component}}</button>' +
            '<br/>' +
            '{{/if}}' +
            '<br/>' +
            '<label for="{{elementid}}_{{CSS.OPTION_WIDTH}}" class="sameline">{{get_string "width" component}}</label>' +
            '<input class="{{CSS.OPTION_WIDTH}}" type="number" value="300" id="{{elementid}}_{{CSS.OPTION_WIDTH}}" size="8" min="1" max="1000"/>' +
            '<br/>' +
            '<label for="{{elementid}}_{{CSS.OPTION_HEIGHT}}" class="sameline">{{get_string "height" component}}</label>' +
            '<input class="{{CSS.OPTION_HEIGHT}}" type="number" value="300" id="{{elementid}}_atto_chemrender_height" size="8" min="1" max="1000"/>' +
            '<div class="{{CSS.SIZE_OPTIONS}}">' +
            '<label for="{{elementid}}_{{CSS.RENDERER_SELECTION}}" class="sameline">{{get_string "rendererselection" component}}</label>' +
            '<select class="{{CSS.RENDERER_SELECTION}}" id="{{elementid}}_{{CSS.RENDERER_SELECTION}}">' +
            '<option value="chemdoodle">ChemDoodle</option>' +
            '<option value="jmol">JMol</option>' +
            '</select>' +
            '</div>' +
            '<div class="{{CSS.JMOL_OPTIONS}}">' +
            '<label>{{get_string "jmoloptions" component}}</label>' +
            '<input type="checkbox" class="{{CSS.OPTION_DOWNLOAD_LINK}}" id="{{elementid}}_{{CSS.OPTION_DOWNLOAD_LINK}}" value="1">{{get_string "optiondownloadlink" component}}<br>' +
            '<input type="checkbox" class="{{CSS.OPTION_HELP_LINK}}" id="{{elementid}}_{{CSS.OPTION_HELP_LINK}}" value="1">{{get_string "optionhelplink" component}}<br>' +
            '<input type="checkbox" class="{{CSS.OPTION_SPIN}}" id="{{elementid}}_{{CSS.OPTION_SPIN}}" value="1">{{get_string "optionspin" component}}<br>' +
            '<input type="checkbox" class="{{CSS.OPTION_LABEL}}" id="{{elementid}}_{{CSS.OPTION_LABEL}}" value="1">{{get_string "optionlabel" component}}<br>' +
            '<input type="checkbox" class="{{CSS.OPTION_STYLESELECT}}" id="{{elementid}}_{{CSS.OPTION_STYLESELECT}} value="1">{{get_string "optionstyleselect" component}}<br>' +
            '<label for="{{elementid}}_{{CSS.OPTION_CONSOLE_COMMANDS}}" class="sameline">{{get_string "optionconsolecommand" component}}' +
            '<span class="helptooltip">' +
            '<a href="/help.php?component=atto_chemrender&identifier=optionconsolecommand&lang=en" title="{{get_string "optionconsolecommandhelp" component}}" target="_blank">' +
            '<img class="icon iconhelp {{CSS.OPTION_CONSOLE_COMMANDS_HELPLINK}}" aria-hidden="true" role="presentation" width="16" height="16" src="{{wwwroot}}/lib/editor/atto/plugins/chemrender/pix/help.svg" />' +
            '</a>' +
            '</span>' +
            '</label>' +
            '<br/>' +
            '<textarea cols="25" rows="3" class="{{CSS.OPTION_CONSOLE_COMMANDS}}" id="{{elementid}}_{{CSS.OPTION_CONSOLE_COMMANDS}}"></textarea> <br/>' +
            '</div>' +
            '<div class="{{CSS.SPECTRUM_OPTIONS}}">' +
            '<br/>' +
            '<label for="{{elementid}}_{{CSS.OPTION_XAXIS}}" class="sameline">{{get_string "optionaxis" component}}</label>' +
            '<input class="fullwidth {{CSS.OPTION_XAXIS}}" type="text" id="{{elementid}}_{{CSS.OPTION_XAXIS}}" size="32" placeholder="{{get_string "optionxaxis" component}}"/><br/>' +
            '<input class="fullwidth {{CSS.OPTION_YAXIS}}" type="text" id="{{elementid}}_{{CSS.OPTION_YAXIS}}" size="32" placeholder="{{get_string "optionyaxis" component}}"/><br/>' +
            '</div>' +
            '<br/>' +
            '<div class="mdl-align">' +
            '<button type="button" class="{{CSS.PREVIEW_BUTTON}}">{{get_string "preview" component}}</button>' +
            '</div>' +
            '</div>' +
            '<div class="{{CSS.PREVIEW_CONTAINER}}">' +
            '<label for="{{elementid}}_{{CSS.PREVIEW_JMOL}}">{{get_string "preview" component}}</label>' +
            '<div describedby="{{elementid}}_cursorinfo" class="{{CSS.PREVIEW_JMOL}}" id="{{elementid}}_{{CSS.PREVIEW_JMOL}}"></div>' +
            '<iframe id="{{elementid}}_{{CSS.PREVIEW_CHEMDOODLE}}" class="{{CSS.PREVIEW_CHEMDOODLE}}"></iframe>' +
            '<br/>' +
            '<div class="mdl-align">' +
            '<br/>' +
            '<button type="submit" class="{{CSS.SUBMIT_BUTTON}}">{{get_string "submit" component}}</button>' +
            '</div>' +
            '</div>' +
            '</form>',
            TEMPLATE_SKETCHER = '' +
            '<form class="atto_form">' +
            '<div class="{{CSS.ENTRY_CONTAINER}}">' +
            '<p/>' +
            '<iframe id="{{elementid}}_{{CSS.CHEMDOODLE_SKETCHER}}" src="{{wwwroot}}/lib/editor/atto/plugins/chemrender/iframe_sketcher.php" name="qualtrics" scrolling="auto" frameborder="no" align="center" height="400px" width="600px"></iframe>' +
            '<br/>' +
            '<label for="{{elementid}}_{{CSS.OPTION_WIDTH}}" class="sameline">{{get_string "width" component}}</label>' +
            '<input class="{{CSS.OPTION_WIDTH}}" type="number" value="300" id="{{elementid}}_{{CSS.OPTION_WIDTH}}" size="8" min="1" max="1000"/>' +
            '<br/>' +
            '<label for="{{elementid}}_{{CSS.OPTION_HEIGHT}}" class="sameline">{{get_string "height" component}}</label>' +
            '<input class="{{CSS.OPTION_HEIGHT}}" type="number" value="300" id="{{elementid}}_{{CSS.OPTION_HEIGHT}}" size="8" min="1" max="1000"/>' +
            '<br/>' +
            '<div class="{{CSS.SIZE_OPTIONS}}">' +
            '<label for="{{elementid}}_{{CSS.RENDERER_SELECTION}}" class="sameline">{{get_string "rendererselection" component}}</label>' +
            '<select class="{{CSS.RENDERER_SELECTION}}" id="{{elementid}}_{{CSS.RENDERER_SELECTION}}">' +
            '<option value="chemdoodle">ChemDoodle</option>' +
            '<option value="jmol">JMol</option>' +
            '</select>' +
            '</div>' +
            '<div class="{{CSS.JMOL_OPTIONS}}">' +
            '<br/>' +
            '<input type="checkbox" class="{{CSS.OPTION_DOWNLOAD_LINK}}" id="{{elementid}}_{{CSS.OPTION_DOWNLOAD_LINK}}" value="1">{{get_string "optiondownloadlink" component}}<br>' +
            '<input type="checkbox" class="{{CSS.OPTION_HELP_LINK}}" id="{{elementid}}_{{CSS.OPTION_HELP_LINK}}" value="1">{{get_string "optionhelplink" component}}<br>' +
            '<input type="checkbox" class="{{CSS.OPTION_SPIN}}" id="{{elementid}}_{{CSS.OPTION_SPIN}}" value="1">{{get_string "optionspin" component}}<br>' +
            '<input type="checkbox" class="{{CSS.OPTION_LABEL}}" id="{{elementid}}_{{CSS.OPTION_LABEL}}" value="1">{{get_string "optionlabel" component}}<br>' +
            '<input type="checkbox" class="{{CSS.OPTION_STYLESELECT}}" id="{{elementid}}_{{CSS.OPTION_STYLESELECT}} value="1">{{get_string "optionstyleselect" component}}<br>' +
            '<label for="{{elementid}}_{{CSS.OPTION_CONSOLE_COMMANDS}}" class="sameline">{{get_string "optionconsolecommand" component}}' +
            '<span class="helptooltip">' +
            '<a href="/help.php?component=atto_chemrender&identifier=optionconsolecommand&lang=en" title="{{get_string "optionconsolecommandhelp" component}}" target="_blank">' +
            '<img class="icon iconhelp {{CSS.OPTION_CONSOLE_COMMANDS_HELPLINK}}" aria-hidden="true" role="presentation" width="16" height="16" src="{{wwwroot}}/lib/editor/atto/plugins/chemrender/pix/help.svg" />' +
            '</a>' +
            '</span>' +
            '</label>' +
            '<br/>' +
            '<textarea cols="25" rows="3" class="{{CSS.OPTION_CONSOLE_COMMANDS}}" id="{{elementid}}_{{CSS.OPTION_CONSOLE_COMMANDS}}"></textarea> <br/>' +
            '</div>' +
            '<br/>' +
            '<div class="mdl-align">' +
            '<button type="button" class="{{CSS.PREVIEW_SKETCHER_BUTTON}}">{{get_string "preview" component}}</button>' +
            '</div>' +
            '<input class="fullwidth {{CSS.URL_INPUT}}" type="hidden" id="{{elementid}}_{{CSS.URL_INPUT}}" size="32"/>' +
            '<input class="{{CSS.CHEMDOODLE_SKETCHER_OUTPUT}}" type="hidden" id="{{elementid}}_{{CSS.CHEMDOODLE_SKETCHER_OUTPUT}}" size="32"/>' +
            '</div>' +
            '<div class="{{CSS.PREVIEW_CONTAINER}}">' +
            '<label for="{{elementid}}_{{CSS.PREVIEW_JMOL}}">{{get_string "preview" component}}</label>' +
            '<br/>' +
            '<div describedby="{{elementid}}_cursorinfo" class="{{CSS.PREVIEW_JMOL}}" id="{{elementid}}_{{CSS.PREVIEW_JMOL}}">' +
            '</div>' +
            '<iframe id="{{elementid}}_{{CSS.PREVIEW_CHEMDOODLE}}" class="{{CSS.PREVIEW_CHEMDOODLE}}"></iframe>' +
            '<br/>' +
            '<label for="{{elementid}}_{{CSS.FILENAME_INPUT}}">{{get_string "enterfilename" component}}</label>' +
            '<input class="fullwidth {{CSS.FILENAME_INPUT}}" type="text" id="{{elementid}}_{{CSS.FILENAME_INPUT}}" size="32"/><br/>' +
            '<div class="mdl-align">' +
            '<br/>' +
            '<button type="submit" class="{{CSS.SUBMIT_BUTTON}}">{{get_string "submit" component}}</button>' +
            '</div>' +
            '</div>' +
            '</form>';

Y.namespace('M.atto_chemrender').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {
    /**
     * A reference to the current selection at the time that the dialogue
     * was opened.
     *
     * @property _currentSelection
     * @type Range
     * @private
     */

    _currentSelection: null,
    /**
     * A reference to the dialogue content.
     *
     * @property _content
     * @type Node
     * @private
     */

    _content: null,
    /**
     * File type extensions associated with chemical files
     *
     * @property _fileExtension
     * @type Array
     * @private
     */

    _fileExtension: ["pdb", "mol", "jdx", "cif", "cml", "csmol", "mol2", "pdb.gz", "pse", "sdf", "xyz"],
    /**
     * File type extensions associated with chemical files produced with ChemDoodle Sketcher
     *
     * @property _fileExtension
     * @type Array
     * @private
     */

    _fileExtensionSketcher: ["mol"],
    initializer: function () {
        // Require Filepicker access and that the ChemRender filter is active
        if (this.get('host').canShowFilepicker('media') && this.get('chemrenderfilteractive')) {
            var items = [];
            iconfolder = M.cfg.wwwroot + '/lib/editor/atto/plugins/chemrender/ajax.php';
            url = M.cfg.wwwroot;
            items.push({
                text: '<img class="icon" aria-hidden="true" role="presentation" width="16" height="16" style="background-color:transparent;" src="' + url + '/lib/editor/atto/plugins/chemrender/pix/sketcher.svg">ChemDoodle Sketcher',
                buttonName: 'sketcher',
                callback: this._displaySketcherDialogue,
                tagMatchRequiresAll: false
            });

            items.push({
                text: '<img class="icon" aria-hidden="true" role="presentation" width="16" height="16" style="background-color:transparent;" src="' + url + '/lib/editor/atto/plugins/chemrender/pix/upload.svg">Insert molecule or spectrum file.',
                buttonName: 'load',
                callback: this._displayDialogue,
                tagMatchRequiresAll: false
            });

            this.addToolbarMenu({
                icon: 'icon',
                iconComponent: COMPONENTNAME,
                buttonName: 'chemRenderMenu',
                overlayWidth: '4',
                globalItemConfig: {
                    callback: this._changeStyle
                },
                items: items
            });

            var group = this.get('group'),
                    pluginname = this.name,
                    buttonClass = 'atto_' + pluginname + '_button',
                    button,
                    host = this.get('host');

            this._buttonHandlers.push(
                    host.on(['atto:selectionchanged', 'change'], function (e) {
                        this._highlightQueue['chemRenderMenu'] = Y.soon(Y.bind(function (e) {
                            if (this.selectionFilterMatches('a', e.selectedNodes, false, this._fileExtensionSketcher)) {
                                this.highlightButtons('chemRenderMenu');
                            } else {
                                this.unHighlightButtons('chemRenderMenu');
                            }
                        }, this, e));
                    }, this));

            this._buttonHandlers.push(
                    host.on(['atto:selectionchanged', 'change'], function (e) {
                        this._highlightQueue['unlink'] = Y.soon(Y.bind(function (e) {
                            if (this.selectionFilterMatches('a', e.selectedNodes, false, this._fileExtension)) {
                                this.highlightButtons('unlink');
                            } else {
                                this.unHighlightButtons('unlink');
                            }
                        }, this, e));
                    }, this));

            Y.Get.js([url + '/filter/chemrender/lib/chemdoodle/ChemDoodleWeb.js', url + '/filter/chemrender/lib/chemdoodle/uis/ChemDoodleWeb-uis.js'],
                    function (err) {
                        if (err) {
                            return;
                        }
                    });
        }
    },
    /**
     * Include filter for filetype in link
     *
     * Runs a filter on each node in the selection, and reports whether the
     * supplied selector(s) were found in the supplied Nodes.
     *
     * By default, all specified nodes must match the selection, but this
     * can be controlled with the requireAll property.
     *
     * @method selectionFilterMatches
     * @param {String} selector
     * @param {NodeList} [selectedNodes] For performance this should be passed. If not passed, this will be looked up each time.
     * @param {Boolean} [requireAll=true] Used to specify that "any" match is good enough.
     * @param {Array} fileExtensions Array used to specify the file types to match.
     * @return {Boolean}
     */
    selectionFilterMatches: function (selector, selectedNodes, requireAll, fileExtensions) {
        var host = this.get('host');
        if (typeof requireAll === 'undefined') {
            requireAll = true;
        }
        if (!selectedNodes) {
            // Find this because it was not passed as a param.
            selectedNodes = host.getSelectedNodes();
        }

        var allMatch = selectedNodes.size() > 0,
                anyMatch = false;

        var editor = this.editor,
                stopFn = function (node) {
                    // The function getSelectedNodes only returns nodes within the editor, so this test is safe.
                    return node === editor;
                };

        // If we do not find at least one match in the editor, no point trying to find them in the selection.
        if (!editor.one(selector)) {
            return false;
        }

        selectedNodes.each(function (node) {
            // Check each node, if it doesn't match the tags AND is not within the specified tags then fail this thing.
            if (requireAll) {
                // Check for at least one failure.
                if (!allMatch || !node.ancestor(selector, true, stopFn)) {
                    allMatch = false;
                }
            } else {
                // Check for at least one match.
                if (!anyMatch && node.ancestor(selector, true, stopFn)) {
                    // Check if it contains one of the acceptable filetypes.
                    fileExtension = node.ancestor().getAttribute('href').split('/').pop().split('?').shift().split('.').pop().toLowerCase();
                    if (fileExtensions.indexOf(fileExtension) !== -1) {
                        anyMatch = true;
                    }
                }
            }
        }, this);

        if (requireAll) {
            return allMatch;
        } else {
            return anyMatch;
        }
    },
    /**
     * Display the load molecule dialogue.
     *
     * @method _displayDialogue
     * @private
     */
    _displayDialogue: function () {
        // Store the current selection.
        this._currentSelection = this.get('host').getSelection();

        if (this._currentSelection === false || this._currentSelection.collapsed) {
            return;
        }

        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('pluginname', COMPONENTNAME),
            focusAfterHide: true,
            width: 'auto',
            focusOnShowSelector: SELECTORS.URL_INPUT
        });

        // Set the dialogue content, and then show the dialogue.
        dialogue.set('bodyContent', this._getDialogueContent());

        // Resolve anchors in the selected text.
        this._resolveAnchors();
        this._updateOptions();
        dialogue.show();
    },
    /**
     * Display the ChemDoodle Sketcher dialogue.
     *
     * @method _displaySketcherDialogue
     * @private
     */
    _displaySketcherDialogue: function () {
        // Store the current selection.
        this._currentSelection = this.get('host').getSelection();
        if (this._currentSelection === false || this._currentSelection.collapsed) {
            return;
        }

        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('pluginname', COMPONENTNAME),
            focusAfterHide: true,
            width: 'auto',
            focusOnShowSelector: SELECTORS.URL_INPUT
        });

        // Set the dialogue content, and then show the dialogue.
        dialogue.set('bodyContent', this._getSketcherDialogueContent());

        // Resolve anchors in the selected text.
        this._resolveAnchorsSketcher();
        this._updateOptions();
        dialogue.show();
    },
    /**
     * Set the display option fields in the form based on those in the loaded url.
     *
     * @method _setProperties
     * @private
     */
    _setProperties: function (url, result) {
        if (url !== '') {
            this._content.one(SELECTORS.URL_INPUT).setAttribute('value', url);
            this._updateOptions(this);
        }
        if (result.hasOwnProperty("width") && result['width'] > 0) {
            this._content.one(SELECTORS.OPTION_WIDTH).setAttribute('value', result['width']);
        } else {
            this._content.one(SELECTORS.OPTION_WIDTH).setAttribute('value', '325');
        }
        if (result.hasOwnProperty("height") && result['height'] > 0) {
            this._content.one(SELECTORS.OPTION_HEIGHT).setAttribute('value', result['height']);
        } else {
            this._content.one(SELECTORS.OPTION_HEIGHT).setAttribute('value', '325');
        }
        if (result.hasOwnProperty('download-link')) {
            if (result['download-link'] == 1) {
                this._content.one(SELECTORS.OPTION_DOWNLOAD_LINK).set("checked", true);
            } else {
                this._content.one(SELECTORS.OPTION_DOWNLOAD_LINK).set("checked", false);
            }
        }
        if (result.hasOwnProperty('help-link')) {
            if (result['help-link'] == 1) {
                this._content.one(SELECTORS.OPTION_HELP_LINK).set("checked", true);
            } else {
                this._content.one(SELECTORS.OPTION_HELP_LINK).set("checked", false);
            }
        }
        if (result.hasOwnProperty('styleselect')) {
            if (result['styleselect'] == 1) {
                this._content.one(SELECTORS.OPTION_STYLESELECT).set("checked", true);
            } else {
                this._content.one(SELECTORS.OPTION_STYLESELECT).set("checked", false);
            }
        }
        if (result.hasOwnProperty('spin')) {
            if (result['spin'] == 1) {
                this._content.one(SELECTORS.OPTION_SPIN).set("checked", true);
            } else {
                this._content.one(SELECTORS.OPTION_SPIN).set("checked", false);
            }
        }
        if (result.hasOwnProperty('label') && result['label'] == 1) {

            this._content.one(SELECTORS.OPTION_LABEL).set("checked", true);
        } else {
            this._content.one(SELECTORS.OPTION_LABEL).set("checked", false);
        }
        if (result.hasOwnProperty('xaxis') && result['xaxis'] !== "") {
            this._content.one(SELECTORS.OPTION_XAXIS).setAttribute('value', result['xaxis']);
        }
        if (result.hasOwnProperty('yaxis') && result['yaxis'] !== "") {
            this._content.one(SELECTORS.OPTION_YAXIS).setAttribute('value', result['yaxis']);
        }
        if (result.hasOwnProperty("renderer") && result['renderer'] !== "") {
            this._content.one(SELECTORS.RENDERER_SELECTION).set('value', result['renderer']);
        }
        if (result.hasOwnProperty('custom') && result['custom'] !== "") {
            document.getElementById(ELEMENTID + "_" + CSS.OPTION_CONSOLE_COMMANDS).value = result['custom'];
        }
    },
    /**
     * Resolving anchores for the Load Molecule form. If there is selected text and
     * it is part of an anchor link, then extract the url (and target) from the
     * link (and set them in the form).
     *
     * @method _resolveAnchors
     * @private
     */
    _resolveAnchors: function () {
        // Find the first anchor tag in the selection.
        var anchorNode,
                anchorNodes,
                selectedNode = this.get('host').getSelectionParentNode(),
                target,
                url;

        if (!selectedNode) {
            return;
        }

        anchorNodes = this._findSelectedAnchors(Y.one(selectedNode));

        if (anchorNodes.length > 0) {
            anchorNode = anchorNodes[0];
            this._currentSelection = this.get('host').getSelectionFromNode(anchorNode);
            target = anchorNode.getAttribute('target');

            var query = anchorNode.getAttribute('href');
            var result = {};
            var query = query.split("?");
            var url = query[0];
            var parameters = query[1];

            if (query.length > 1) {
                parameters.split("&").forEach(function (part) {
                    var item = part.split("=");
                    result[item[0]] = decodeURIComponent(item[1]);
                });
            }

            this._setProperties(url, result);
        }
    },
    /**
     * Resolving anchors for the ChemDoole Sketcher form. If there is selected text and
     * it is part of an anchor link, then extract the url (and target) from the
     * link (and set them in the form).
     *
     * @method _resolveAnchorsSketcher
     * @private
     */
    _resolveAnchorsSketcher: function () {
        // Find the first anchor tag in the selection.
        var anchorNode,
                anchorNodes,
                target,
                url,
                selectedNode;

        selectedNode = this.get('host').getSelectionParentNode();

        if (!selectedNode) {
            return;
        }

        anchorNodes = this._findSelectedAnchors(Y.one(selectedNode));

        if (anchorNodes.length > 0) {
            anchorNode = anchorNodes[0];
            this._currentSelection = this.get('host').getSelectionFromNode(anchorNode);
            target = anchorNode.getAttribute('target');

            var query = anchorNode.getAttribute('href');
            var result = {};
            var query = query.split("?");
            var url = query[0];
            var parameters = query[1];

            if (query.length > 1) {
                parameters.split("&").forEach(function (part) {
                    var item = part.split("=");
                    result[item[0]] = decodeURIComponent(item[1]);
                });
            }

            if (url !== '') {
                var filename = url.replace(/\\/g, '/');

                filename = filename.substring(filename.lastIndexOf('/') + 1, filename.lastIndexOf('.'));
                filename = decodeURIComponent(filename);
                this._content.one(SELECTORS.FILENAME_INPUT).setAttribute('value', filename);

                var doodleIFrame = document.getElementById(ELEMENTID + "_" + CSS.CHEMDOODLE_SKETCHER);
                doodleIFrame.onload = function () {
                    var iframeId = "#" + ELEMENTID + "_" + CSS.CHEMDOODLE_SKETCHER;
                    var frame = Y.one(iframeId);
                    var win = Y.Node.getDOMNode(frame.get('contentWindow'));
                    var doc = win.document;

                    YUI({frame: frame, win: win, doc: doc}).use('node', function (innerY) {
                        win.clearCanvas();
                        win.setMOL(url);
                    });
                };
            }
            if (result.hasOwnProperty("width")) {

                if (result['width'] > 0) {
                    this._content.one(SELECTORS.OPTION_WIDTH).setAttribute('value', result['width']);
                }
            }
            if (result.hasOwnProperty("height")) {

                if (result['height'] > 0) {
                    this._content.one(SELECTORS.OPTION_HEIGHT).setAttribute('value', result['height']);
                }
            }
            this._setProperties(url, result);
        }
    },
    /**
     * Update the dialogue after a file is selected in the File Picker.
     *
     * @method _filepickerCallback
     * @param {object} params The parameters provided by the filepicker
     * containing information about the image.
     * @private
     */
    _filepickerCallback: function (params) {
        if (params.url !== '') {
            this._content.one(SELECTORS.URL_INPUT).set('value', params.url);
            this._updateOptions(this);
            this.markUpdated();
        }
    },
    /**
     * Set the file link for the loaded molecule or spectrum in the editor source.
     *
     * @method _setLink
     * @param {EventFacade} e
     * @private
     */
    _setLink: function (e) {
        var anchorNodes,
                host = this.get('host'),
                input,
                selectedNode,
                value;

        e.preventDefault();
        this.getDialogue({
            focusAfterHide: null
        }).hide();

        input = this._content.one(SELECTORS.URL_INPUT);

        value = input.get('value');
        if (value !== '') {
            this.editor.focus();
            host.setSelection(this._currentSelection);
            document.execCommand('unlink', false, null);
            document.execCommand('createLink', false, value);

            // Now set the target.
            selectedNode = host.getSelectionParentNode();

            // Note this is a document fragment and YUI doesn't like them.
            if (!selectedNode) {
                return;
            }

            anchorNodes = this._findSelectedAnchors(Y.one(selectedNode));
            var param = [], paramString;

            var serialize = function (obj) {
                var str = [];
                for (var p in obj) {
                    if (obj.hasOwnProperty(p)) {
                        str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                    }
                }
                return str.join("&");
            };

            Y.Array.each(anchorNodes, function (anchorNode) {

                param = this._getParam();
                paramString = value + "?" + serialize(param);
                anchorNode.setAttribute('href', paramString);
                anchorNode.set('innerHTML', decodeURIComponent(value.split("/").pop()));

            }, this);

            this.markUpdated();
        }
    },
    /**
     * Set the file link for the Sketcher molecule in the editor source.
     *
     * @method _setSketcherLink
     * @param {EventFacade} e
     * @private
     */
    _setSketcherLink: function (e) {
        var anchorNodes,
                molFile,
                selectedNode,
                value;

        var iframeId = "#" + ELEMENTID + "_" + CSS.CHEMDOODLE_SKETCHER;
        var frame = Y.one(iframeId);
        var win = Y.Node.getDOMNode(frame.get('contentWindow'));
        var doc = win.document;

        YUI({frame: frame, win: win, doc: doc}).use('node', function (innerY) {
            molFile = win.getMOL();
        });

        var blob = new Blob([molFile], {type: "plain/text"});
        var filenameInput = this._content.one(SELECTORS.FILENAME_INPUT);
        var filename = filenameInput.get('value');

        if (filename == '') {
            alert('Filename required');
        } else {
            var host = this.get('host');
            var options = host.get('filepickeroptions').image;
            var savepath = (options.savepath === undefined) ? '/' : options.savepath;
            var formData = new FormData();
            formData.append('repo_upload_file', blob, filename + '.mol');
            formData.append('itemid', options.itemid);
            // List of repositories is an object rather than an array.  This makes iteration more awkward.
            var i = 0;
            while (true) {
                if (options.repositories[++i] === undefined) {
                    // No more repos in list.  This is a problem, but we'll get an error back anyway so we'll handle it later.
                    break;
                }
                if (options.repositories[i].type === 'upload') {
                    formData.append('repo_id', options.repositories[i].id);
                    break;
                }
            }
            formData.append('env', options.env);
            formData.append('sesskey', M.cfg.sesskey);
            formData.append('client_id', options.client_id);
            formData.append('savepath', savepath);
            formData.append('ctx_id', options.context.id);

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        var result = JSON.parse(xhr.responseText);
                        if (result) {
                            if (result.error) {
                                return new M.core.ajaxException(result);
                            }
                            var file = result;
                            if (result.event && result.event === 'fileexists') {
                                // A file with this name is already in use here - rename to avoid conflict.
                                file = result.newfile;
                            }
                            // Set the link in the Sketcher dialogue.
                            value = file.url;
                            if (value !== '') {
                                e.preventDefault();
                                this.getDialogue({
                                    focusAfterHide: null
                                }).hide();
                                var host = this.get('host');
                                this.editor.focus();
                                host.setSelection(this._currentSelection);
                                document.execCommand('unlink', false, null);
                                document.execCommand('createLink', false, value);
                                selectedNode = host.getSelectionParentNode();
                                if (!selectedNode) {
                                    return;
                                }
                                anchorNodes = this._findSelectedAnchors(Y.one(selectedNode));
                                var param = [], paramString;
                                serialize = function (obj) {
                                    var str = [];
                                    for (var p in obj) {
                                        if (obj.hasOwnProperty(p)) {
                                            str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                                        }
                                    }
                                    return str.join("&");
                                };
                                Y.Array.each(anchorNodes, function (anchorNode) {
                                    param = this._getParam();
                                    paramString = value + "?" + serialize(param);
                                    anchorNode.setAttribute('href', paramString);
                                    anchorNode.set('innerHTML', decodeURIComponent(value.split("/").pop()));
                                }, this);
                                this.markUpdated();
                            }
                        }
                    } else {
                        alert(M.util.get_string('servererror', 'moodle'));
                    }
                }
            }.bind(this);
            // Send the AJAX call.
            xhr.open("POST", M.cfg.wwwroot + '/repository/repository_ajax.php?action=upload', false);
            xhr.send(formData);
            return false;
        }
    },
    /*
     * Return associative array of parameters
     *
     * @method _getParam
     * @return param[]
     * @private
     */
    _getParam: function () {
        var param = [],
                target,
                value;

        target = this._content.one(SELECTORS.OPTION_WIDTH);
        if (target.get('value')) {
            param['width'] = target.get('value');
        }
        target = this._content.one(SELECTORS.OPTION_HEIGHT);
        if (target.get('value')) {
            param['height'] = target.get('value');
        }
        target = this._content.one(SELECTORS.OPTION_DOWNLOAD_LINK);
        if (target && target.get('checked')) {
            param['download-link'] = 1;
        }
        target = this._content.one(SELECTORS.OPTION_HELP_LINK);
        if (target && target.get('checked')) {
            param['help-link'] = 1;
        }
        target = this._content.one(SELECTORS.OPTION_SPIN);
        if (target && target.get('checked')) {
            param['spin'] = 1;
        }
        target = this._content.one(SELECTORS.OPTION_STYLESELECT);
        if (target && target.get('checked')) {
            param['styleselect'] = 1;
        }
        target = this._content.one(SELECTORS.OPTION_LABEL);
        if (target && target.get('checked')) {
            param['label'] = 1;
        }
        target = this._content.one(SELECTORS.OPTION_XAXIS);
        if (target && target.get('value')) {
            value = target.get('value');
            param['xaxis'] = value;
        }
        target = this._content.one(SELECTORS.OPTION_YAXIS);
        if (target && target.get('value')) {
            value = target.get('value');
            param['yaxis'] = value;
        }
        target = this._content.one(SELECTORS.RENDERER_SELECTION);
        if (target.get('value')) {
            param['renderer'] = target.get('value');
        }
        target = this._content.one(SELECTORS.OPTION_CONSOLE_COMMANDS);
        if (target.get('value')) {
            // Allowed letters (greeks + greek accented), digits, space, and ( ) [ ] \ / . , ; { } :.
            value = target.get('value');
            value = value.replace(/[^a-zA-Z0-9ά-ωΑ-ώ\ \(\)\[\]\/\\\.\,\;\{\}\:]/gi, '');
            param['custom'] = value;
        }
        return param;
    },
    /**
     * Update the preview div to match the Load Molecule Dialogue
     *
     * @param {EventFacade} e
     * @method _updatePreview
     * @private
     */
    _updatePreview: function (e) {
        var ajaxUrl,
                input,
                linkHTML,
                param,
                params,
                paramString,
                value;

        input = this._content.one(SELECTORS.URL_INPUT);
        value = input.get('value');

        if (value !== '') {
            var serialize = function (obj) {
                var str = [];
                for (var p in obj) {
                    if (obj.hasOwnProperty(p)) {
                        str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                    }
                }
                return str.join("&");
            };

            param = this._getParam();
            paramString = value + "?" + serialize(param);
            linkHTML = '<a href="' + paramString + '">test</a>';

            // Make an ajax request to the filter.
            ajaxUrl = M.cfg.wwwroot + '/lib/editor/atto/plugins/chemrender/ajax.php';
            params = {
                sesskey: M.cfg.sesskey,
                contextid: this.get('contextid'),
                action: 'filtertext',
                text: linkHTML
            };

            Y.io(ajaxUrl, {
                context: this,
                data: params,
                timeout: 500,
                on: {
                    complete: this._loadPreview
                }
            });
        }
    },
    /**
     * Update the preview div in the ChemDoodle Sketcher dialogue based on the stored ChemDoodle Sketcher output.
     *
     * @param {EventFacade} e
     * @method _updateSketcherPreview
     * @private
     */
    _updateSketcherPreview: function (e) {
        var ajaxUrl,
                input,
                param,
                params,
                paramString,
                sketcherOutput,
                url,
                value;

        input = this._content.one(SELECTORS.CHEMDOODLE_SKETCHER_OUTPUT);
        value = input.get('value');

        if (value !== '') {

            var serialize = function (obj) {
                var str = [];
                for (var p in obj) {
                    if (obj.hasOwnProperty(p)) {
                        str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                    }
                }
                return str.join("&");
            };

            param = this._getParam();
            sketcherOutput = value;

            // Create a dummy url for the chemistry filter to process.
            url = window.location.origin;
            paramString = url + "/chemdoodle/preview.mol?" + serialize(param);

            // Make an ajax request to the filter.
            ajaxUrl = M.cfg.wwwroot + '/lib/editor/atto/plugins/chemrender/ajax_sketcher.php';
            params = {
                sesskey: M.cfg.sesskey,
                contextid: this.get('contextid'),
                action: 'filtertext',
                paramstring: paramString,
                sketcheroutput: sketcherOutput
            };

            Y.io(ajaxUrl, {
                method: 'POST',
                context: this,
                data: params,
                timeout: 500,
                on: {
                    complete: this._loadPreview
                }
            });
        }
    },
    /**
     * Update the displayed options based on the selected molecule file type.
     *
     * @param {EventFacade} e
     * @method _updateOptions
     * @private
     */
    _updateOptions: function (e) {
        var input,
                value;

        input = this._content.one(SELECTORS.URL_INPUT);
        value = input.get('value').toLowerCase();

        this._content.all(SELECTORS.SPECTRUM_OPTIONS).each(function (taskNode) {
            taskNode.hide();
        });
        this._content.all(SELECTORS.JMOL_OPTIONS).each(function (taskNode) {
            taskNode.hide();
        });
        this._content.all(SELECTORS.SIZE_OPTIONS).each(function (taskNode) {
            taskNode.hide();
        });
        /*
         * Possible File types: cif|cml|csmol|mol|mol2|pdb\.gz|pdb|pse|sdf|xyz
         *
         * Option divs:
         *  .option-mol -> mol, mol2
         *  .option-spectrum -> jdx
         *  .option-jmol -> cif, cml, csmol, pdb, pdb.gz, pse, sdf, xyz
         */
        if (value.indexOf('.jdx') != -1) {
            // Display options for spectrum display.
            this._content.all(SELECTORS.SPECTRUM_OPTIONS).each(function (taskNode) {
                taskNode.show();
            });
        } else if (value.indexOf('.pdb') != -1 || value.indexOf('.cif') != -1) {
            // Display options for PDB molecule display.
            this._content.all(SELECTORS.JMOL_OPTIONS).each(function (taskNode) {
                taskNode.show();
            });
        } else if (value.indexOf('.mol') != -1) {
            // Display options for MOL display and renderer selection.
            this._content.all(SELECTORS.SIZE_OPTIONS).each(function (taskNode) {
                taskNode.show();
            });
        } else {
            // No URL loaded or ChemDoole Sketcher is open.
            this._content.all(SELECTORS.SIZE_OPTIONS).each(function (taskNode) {
                taskNode.show();
            });
        }

        var renderer = this._content.one(SELECTORS.RENDERER_SELECTION);

        if (renderer.get('value')) {
            if (renderer.get('value') == 'jmol') {
                this._content.all(SELECTORS.JMOL_OPTIONS).each(function (taskNode) {
                    taskNode.show();
                });
            }
        }
    },
    /**
     * Load returned preview output into the preview div.
     *
     * @param {String} id
     * @param {EventFacade} e
     * @method _loadPreview
     * @private
     */
    _loadPreview: function (id, preview) {
        var previewNode = this._content.one(SELECTORS.PREVIEW_JMOL);
        var renderer = this._content.one(SELECTORS.RENDERER_SELECTION).get('value');
        var fileType = this._content.one(SELECTORS.URL_INPUT).get("value").split('.').pop().toLowerCase();

        if (fileType == "pdb" || renderer == 'jmol') {
            // For JSmol preview.
            previewNode.setHTML(preview.responseText);
            var script = previewNode.getElementsByTagName('script');
            var newScript = document.createElement('script');
            newScript.type = 'text/javascript';
            var scriptArray = [];
            for (var n = 0; n < script._nodes.length; n++) {
                if (script._nodes[n].src != "") {
                    // If it is a script with a src link.
                    scriptArray.push(script._nodes[n].src);
                } else {
                    newScript.text = newScript.text + "\n" + script._nodes[n].innerHTML;
                }
                // Remove script from the node.
                script._nodes[n].parentNode.removeChild(script._nodes[n]);
            }

            Y.Get.js(scriptArray, function (err) {
                // Wait for JS to load.
                if (err) {
                    console.log('Error loading JS: ' + err[0].error, 'error');
                    return;
                }
                previewNode.appendChild(newScript);
            });

            this._content.all(SELECTORS.PREVIEW_CONTAINER).each(function (taskNode) {
                taskNode.show();
            });
            this._content.all(SELECTORS.PREVIEW_CHEMDOODLE).each(function (taskNode) {
                taskNode.hide();
            });
            this._content.all(SELECTORS.PREVIEW_JMOL).each(function (taskNode) {
                taskNode.show();
            });
        } else {
            // For ChemDoodle preview, but not JSMol.
            previewNode.setHTML(preview.responseText);

            var script = previewNode.getElementsByTagName('script');
            var newScript = document.createElement('script');
            newScript.type = 'text/javascript';
            var scriptArray = [];

            for (var n = 0; n < script._nodes.length; n++) {
                // Loop through all <script> tags.
                if (script._nodes[n].src != "") {
                    // If it is a script with a src link.
                    scriptArray.push(script._nodes[n].src);
                } else {
                    newScript.text = newScript.text + "\n" + script._nodes[n].innerHTML;
                }
                // Remove script from the node.
                script._nodes[n].parentNode.removeChild(script._nodes[n]);
            }

            // HTML remaining after striping <script>.
            var remainingHtml = previewNode.get('innerHTML');
            previewNode.setHTML('');

            // Load JS into iframe (for JSmol).
            scriptArray.push(M.cfg.wwwroot + '/theme/yui_combo.php?rollup/3.15.0_1/yui-moodlesimple.js');

            var iframeId = "#" + ELEMENTID + "_" + CSS.PREVIEW_CHEMDOODLE;
            var frame = Y.one(iframeId);
            var win = Y.Node.getDOMNode(frame.get('contentWindow'));
            var doc = win.document;

            YUI({frame: frame, win: win, doc: doc}).use('node', function (innerY) {
                var innerBody = innerY.one('body');
                var innerHead = innerY.one('head');

                // Delete any prior preview content.
                innerBody.get('childNodes').remove();
                innerBody.append(remainingHtml);
                scriptArray.forEach(function (entry) {
                    var newScript = document.createElement('script');
                    newScript.type = 'text/javascript';
                    newScript.src = entry;
                    newScript.defer = 'defer';
                    innerHead.appendChild(newScript);
                });
                setTimeout(function () {
                    innerBody.appendChild(newScript);
                    // Set the iFrame height and width to display all of the content.
                    var newheight;
                    var newwidth;
                    newheight = doc.body.scrollHeight;
                    newwidth = doc.body.scrollWidth;
                    frame.set('height', (newheight) + "px");
                    frame.set('width', (newwidth) + "px");
                }, 500);
            });

            this._content.all(SELECTORS.PREVIEW_CONTAINER).each(function (taskNode) {
                taskNode.show();
            });
            this._content.all(SELECTORS.PREVIEW_JMOL).each(function (taskNode) {
                taskNode.hide();
            });
            this._content.all(SELECTORS.PREVIEW_CHEMDOODLE).each(function (taskNode) {
                taskNode.show();
            });
        }
        Y.fire(M.core.event.FILTER_CONTENT_UPDATED, {nodes: (new Y.NodeList(previewNode))});

        // Reposition dialog.
        var y = document.getElementsByClassName('moodle-dialogue-focused');
        var dialogNode = y[0];
        dialogNode.style.left = ((window.innerWidth / 2) - (dialogNode.offsetWidth / 2)) + 'px';
    },
    /**
     * Store the ChemDoodle structure data from the Sketcher and initiate the update of sketcher preview
     *
     * @param {EventFacade} e
     * @method _sketcherPreview
     * @private
     */
    _sketcherPreview: function (e) {
        var molFile;
        var iframeId = "#" + ELEMENTID + "_" + CSS.CHEMDOODLE_SKETCHER;
        var frame = Y.one(iframeId);
        var win = Y.Node.getDOMNode(frame.get('contentWindow'));
        var doc = win.document;

        YUI({frame: frame, win: win, doc: doc}).use('node', function (innerY) {
            molFile = win.getMOL();
        });

        var input = Y.one("#" + ELEMENTID + "_" + CSS.CHEMDOODLE_SKETCHER_OUTPUT);
        input.set('value', molFile);
        this._updateSketcherPreview(this);

        // Do not upload file.
        return false;
    },
    /**
     * Look for the nearest anchor tags that are least partly contained in the selection.
     *
     * @method _findSelectedAnchors
     * @param {Node} node The node to search under for the selected anchor.
     * @return {Node|Boolean} The Node, or false if not found.
     * @private
     */
    _findSelectedAnchors: function (node) {
        var hit,
                hits,
                tagName = node.get('tagName');

        // Direct hit.
        if (tagName && tagName.toLowerCase() === 'a') {
            return [node];
        }
        // Search down but check that each node is part of the selection.
        hits = [];
        node.all('a').each(function (n) {
            if (!hit && this.get('host').selectionContainsNode(n)) {
                hits.push(n);
            }
        }, this);
        if (hits.length > 0) {
            return hits;
        }
        // Search up.
        hit = node.ancestor('a');
        if (hit) {
            return [hit];
        }
        return [];
    },
    /**
     * Generates the Load Molecule dialogue content.
     *
     * @method _getDialogueContent
     * @return {Node} Node containing the dialogue content
     * @private
     */
    _getDialogueContent: function () {
        var canShowFilepicker = this.get('host').canShowFilepicker('link'),
                template = Y.Handlebars.compile(TEMPLATE);

        ELEMENTID = Math.round(Math.random() * 1000000000);

        this._content = Y.Node.create(template({
            elementid: ELEMENTID,
            showFilepicker: canShowFilepicker,
            component: COMPONENTNAME,
            CSS: CSS,
            wwwroot: M.cfg.wwwroot
        }));

        this._content.one(SELECTORS.PREVIEW_BUTTON).on('click', this._updatePreview, this);
        this._content.one(SELECTORS.URL_INPUT).on('valuechange', this._updateOptions, this);
        this._content.one(SELECTORS.RENDERER_SELECTION).on('valuechange', this._updateOptions, this);
        this._content.one(SELECTORS.SUBMIT_BUTTON).on('click', this._setLink, this);

        if (canShowFilepicker) {
            this._content.one(SELECTORS.FILE_BROWSER).on('click', function (e) {
                e.preventDefault();
                this.get('host').showFilepicker('link', this._filepickerCallback, this);
            }, this);
        }

        this._content.all(SELECTORS.PREVIEW_CONTAINER).each(function (taskNode) {
            taskNode.hide();
        });
        return this._content;
    },
    /**
     * Generates the ChemDoodle Sketcher dialogue content.
     *
     * @method _getSketcherDialogueContent
     * @return {Node} Node containing the dialogue content
     * @private
     */
    _getSketcherDialogueContent: function () {
        var canShowFilepicker = this.get('host').canShowFilepicker('link'),
                template = Y.Handlebars.compile(TEMPLATE_SKETCHER);

        ELEMENTID = Math.round(Math.random() * 1000000000);

        this._content = Y.Node.create(template({
            elementid: ELEMENTID,
            showFilepicker: canShowFilepicker,
            component: COMPONENTNAME,
            CSS: CSS,
            wwwroot: M.cfg.wwwroot
        }));

        this._content.one(SELECTORS.PREVIEW_SKETCHER_BUTTON).on('click', this._sketcherPreview, this);
        this._content.one(SELECTORS.RENDERER_SELECTION).on('valuechange', this._updateOptions, this);
        this._content.one(SELECTORS.SUBMIT_BUTTON).on('click', this._setSketcherLink, this);
        this._content.all(SELECTORS.PREVIEW_CONTAINER).each(function (taskNode) {
            taskNode.hide();
        });

        return this._content;
    }
}, {
    ATTRS: {
        /**
         * Whether the TeX filter is currently active.
         *
         * @attribute texfilteractive
         * @type Boolean
         */
        chemrenderfilteractive: {
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
        }
    }
});
