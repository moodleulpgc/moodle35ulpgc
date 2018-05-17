<?php

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
 * Web services con utilidades necesarias para la ULPGC
 *
 * @package    ulpgccore
 * @copyright  2013 Víctor Déniz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
        'local_ulpgccore_get_id_curso' => array(
                'classname'   => 'local_ulpgccore_external',
                'methodname'  => 'get_id_curso',
                'classpath'   => 'local/ulpgccore/externallib.php',
                'description' => 'Devuelve el id de un curso a partir del idnumber',
                'capabilities' => 'moodle/course:view',
                'type'        => 'read',
        ),
        'local_ulpgccore_update_course_term' => array(
        		'classname'   => 'local_ulpgccore_external',
        		'methodname'  => 'update_course_term',
        		'classpath'   => 'local/ulpgccore/externallib.php',
        		'description' => 'Modifica el cuatrimestre de un curso',
        		'capabilities' => array('moodle/course:update','moodle/course:viewhiddencourses'),        		
        		'type'        => 'write',
        )        
);
