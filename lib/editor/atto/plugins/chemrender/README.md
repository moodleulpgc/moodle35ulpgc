Description
-----------
Chemical structure editor and molecular data file import plugin for the Atto HTML Editor.

A chemistry molecule or spectrum can be inserted into a text area and rendered with the ChemRender filter using either the JSmol or the ChemDoodle library.

Specific display options are available for .mol, .jdx, and .pdb file types.

Pre-requisites
--------------
1. Designed to run on Moodle 2.7.
2. Atto HTML editor.
3. Requires the ChemRender filter, which can be downloaded from the following location:

- GIT: **https://github.com/ccle/moodle-filter-chemrender.git**
 

Installation
------------
1. Download the files for this plugin
    - Direct download
        - GIT: **https://github.com/ucla/moodle-atto_chemrender.git**
        - Copy the 'chemrender' folder to the lib/editor/atto/plugins folder of your Moodle installation to give lib/editor/atto/plugins/chemrender
    - Git repository clone
        - Navigate to the lib/editor/atto/plugins directory of your Moodle installation.
        - Issue the command: git clone https://github.com/ucla/moodle-atto_chemrender.git chemrender

2. Enable the ChemRender Atto editor button by either method:
    - If using forced plugin settings: In the config.php, add 'chemrender' to the Atto editor toolbar variable $CFG->forced_plugin_settings['editor_atto']['toolbar']
    - Add 'chemrender' to Site administration > Plugins > Text editors > Atto HTML editor > Atto toolbar settings

    ```sh
        insert = chemrender, equation, charmap, clear, pastespecial
    ```