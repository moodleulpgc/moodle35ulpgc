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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
* Sincronización de cursos en base de datos externa
*
* En base a los registros de una base de datos externa, se crean aquellos cursos
* que no existen en Moodle y se eliminan (ocultan) aquellos que ya no existan en la
* base de datos externa
*
* @package local_sinculpgc
* @copyright 2014 Victor Deniz
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*
*/

if (! defined('CLI_SCRIPT')) {
    define('CLI_SCRIPT', true);
}

require_once (__DIR__ . '/../../../config.php');
require_once ($CFG->dirroot . '/course/lib.php');
require_once ($CFG->dirroot . '/group/lib.php');
require_once ('../locallib.php');

global $DB;

// Determina si se intenta aplicar plantilla / restaurar curso (0:no; 1:si)
$restore = 0;

// Variables que pueden variar al inicio de curso
// TODO Ofrecer interfaz de configuración

        @set_time_limit(0);
        raise_memory_limit(MEMORY_HUGE);



// Obtención de registros en BBDD externa
$extdb = db_init();


$sqlenrolsulpgc = "SELECT lower(u.username || ';' || c.idnumber) as enrol,u.username, c.idnumber, m.rol, m.estado
                        FROM tmomatriculas m, tmocursos c, tmoplataformasactivas p, tmocategorias ca, tmousuarios u
                       WHERE p.plataforma = '{$CFG->plataforma}'
                             AND p.aacada = '{$CFG->aacada}'
                                         AND ca.plataformaid = p.id
                                         AND c.categoriaid = ca.id
                                         AND m.cursoid = c.id
                                         AND u.id = m.usuarioid
                                ORDER BY c.idnumber,m.estado desc";
        $consulta = get_rows_external_db($extdb, $sqlenrolsulpgc, 'enrol');

// print_r($consulta);

        db_close($extdb);


$sqlusuarioscv = "select lower(concat(u.username,';', c.idnumber))  from mdl_user u, mdl_user_enrolments e, mdl_enrol ep, mdl_course c  where u.id = e.userid and c.id=ep.courseid and ep.id = e.enrolid and c.idnumber = 'bib-arq_100' AND ep.enrol='sinculpgc'";

//$sqlusuarioscv = "select lower(concat(u.username,';', c.idnumber))  from mdl_user u, mdl_user_enrolments e, mdl_enrol ep, mdl_course c  where u.id = e.userid and c.id=ep.courseid and ep.id = e.enrolid and ep.enrol='sinculpgc'";

   //print_r ($usuarioscv);


$mysqli = new mysqli($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname); 
$result = $mysqli->query($sqlusuarioscv); 
// print_r($result);

while ($row = $result->fetch_row()){ 
       $valor=$row[0];
       
//       echo "$row[0]\n"; 
       if (isset($consulta[$valor]))
       { 
          $a=$consulta[$valor]->enrol; 
   //       echo "Encontrado en Oracle $a \n";
       }
       else
       {
          echo "NO ENCONTRADO EN ORACLE (DNI;CURSO): $row[0]\n";
          $dni=substr($row[0],0,strpos($row[0],";"));
          $curso=substr($row[0],strpos($row[0],";")+1);
          //echo "DNI:$dni , CURSO:$curso \n";

          $sql_datos_borrar="select ra.roleid as rid,u.id as uid,ct.id as ctid,e.id as eid,c.id as cid from mdl_user u, mdl_user_enrolments ue, mdl_enrol e, mdl_course c, mdl_context ct,mdl_role_assignments ra, mdl_user u2 where u.username='$dni' and u.id=ue.userid and ue.enrolid=e.id and  e.courseid = c.id and c.idnumber='$curso' and ct.instanceid=c.id and ct.id=ra.contextid and ra.userid=u2.id and u2.username='$dni' ";
 
          $result2 = $mysqli->query($sql_datos_borrar);
          $row2=$result2->fetch_row();
          echo "Borrando roles de contexto (RoleID $row2[0], UserID $row2[1], ContextID $row2[2], EnrolID $row2[3], Course ID $row2[4])\n";
          role_unassign($row2[0], $row2[1], $row2[2], 'enrol_sinculpgc', $row2[3]); 

          // Si no tiene ningún rol asignado, se elimina el enrol
          $sql = "SELECT id
                       FROM {role_assignments}
                       WHERE userid = $row2[1]
                       AND itemid = $row2[3]
                       AND component = 'enrol_sinculpgc'";

          $hasmiulpgcroles = $DB->get_record_sql($sql, array(
                            'userid' => $row2[1],
                            'enrolid' => $row2[3]
                        ));
          if (! $hasmiulpgcroles) {
                echo "Borrando enrol de curso (UserID $row2[1], CourseID $row2[4])\n";  
                $instances = $DB->get_records('enrol', array('courseid' => $row2[4]));
                foreach ($instances as $instance) {
                        $plugin = enrol_get_plugin($instance->enrol);
                        $plugin->unenrol_user($instance, $row2[1]);
                }
          }

       }
} 
$mysqli->close();
    


?>

