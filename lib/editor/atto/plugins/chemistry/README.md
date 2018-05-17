moodle-editor_atto-chemistry
============================

Chemistry equation editor for Moodle using the mhchem extension in MathJax/TeX

Installation

Either

Download the zip file, unzip to give the moodle-atto_editor-chemistry folder. Rename this to chemistry and copy to the lib/editor/atto/plugins folder of your Moodle installation to give lib/editor/atto/plugins/chemistry

Or
Navigate to the lib/editor/atto/plugins directory of your Moodle installation. Then isue the command

git clone https://github.com/geoffrowland/moodle-editor_atto-chemistry.git chemistry.

Then visit the Admin notifications page of your Moodle to complete the installation.

After installation you need to complete the following steps:

Add chemistry to Administration > Site administration > Plugins > Text editors > Atto HTML editor > Atto toolbar settings > Toolbar config, to give, for example:

insert = chemistry, equation, charmap, table, clear

Add mhchem to the Moodle MathJax filter configuration

Edit Administration > Site administration > Plugins > Filters > MathJax > MathJax configuration to include:

TeX: {
  extensions: ["AMSmath.js","AMSsymbols.js","color.js","mhchem.js","noErrors.js","noUndefined.js"]
},

You may need to Purge all caches on your Moodle server

Administration > Site administration > Development > Purge all caches

and in your browser

Enjoy!






