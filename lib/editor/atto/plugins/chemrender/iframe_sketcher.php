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
 * IFrame container for the ChemDoodle sketcher.
 *
 * @package    atto_chemrender
 * @copyright  2016 UC Regents
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../../../../config.php');

global $CFG;
$wwwroot = $CFG->wwwroot;

echo <<< EOT
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="chrome=1">
        <link rel="stylesheet" href="$wwwroot/filter/chemrender/lib/chemdoodle/ChemDoodleWeb.css" type="text/css">
        <link rel="stylesheet" href="$wwwroot/filter/chemrender/lib/chemdoodle/uis/jquery-ui-1.10.3.custom.css" type="text/css">
        <script type="text/javascript" src="$wwwroot/filter/chemrender/lib/chemdoodle/ChemDoodleWeb.js"></script>
        <script type="text/javascript" src="$wwwroot/filter/chemrender/lib/chemdoodle/uis/ChemDoodleWeb-uis.js"></script>
        <title>ChemDoodle Web Component Sketcher (Full Sketcher)</title>
    </head>
    <body>
    <center>
        <script>
            ChemDoodle.ELEMENT['H'].jmolColor = 'black';
            ChemDoodle.ELEMENT['S'].jmolColor = '#B9A130';
            var sketcher = new ChemDoodle.SketcherCanvas('sketcher', 500, 300, {useServices: false, oneMolecule: true});
            sketcher.specs.atoms_displayTerminalCarbonLabels_2D = true;
            sketcher.specs.atoms_useJMOLColors = true;
            sketcher.specs.bonds_clearOverlaps_2D = true;
            sketcher.specs.shapes_color = '#c10000';
        </script>
    </center>
    <br>

    <script type="text/javascript">
        function getMOL()
        {
            var mol = sketcher.getMolecule();
            var molFile = ChemDoodle.writeMOL(mol);
            return molFile;
        }

        function clearCanvas()
        {
            sketcher.clear();
            sketcher.repaint();
        }

        // the url is the local path to the chemical file
        function setMOL(fileurl)
        {
            ChemDoodle.io.file.content(fileurl, function (fileContent) {
                var mol = ChemDoodle.readMOL(fileContent);
                sketcher.addMolecule(mol);
                sketcher.center();
                sketcher.repaint();
            });
        }
    </script>

</body>
</html>
EOT;
