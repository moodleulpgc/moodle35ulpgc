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
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

global $DB;

// Determina si se intenta aplicar plantilla / restaurar curso (0:no; 1:si)
$restore = 1;

// Variables que pueden variar al inicio de curso
// TODO Ofrecer interfaz de configuración
$backupspath = '/telepresencial_2015_replica/repository/backups1415';
$templatespath = $CFG->dataroot . '/repository/plantillas/';
$templatename = 'PlantillaTP-seccionO-all-20150710-1016-nu.mbz';

function restore_curso($file, $newcourse, $now, $sourcedir, $mode) {

    global $CFG;

    $tempdir = 'backuprestore-' . strstr($file, '.', 1) . $now;
    $restore_settings = get_config('tool_backuprestore');
    $fb = get_file_packer('application/vnd.moodle.backup');
    $result = $fb->extract_to_pathname($sourcedir.'/'.$file,
            $CFG->dataroot.'/temp/backup/'.$tempdir);
    $coursename = $newcourse->shortname;
    $strcourse = get_string('course');

    $controller = new restore_controller($tempdir, $newcourse->id,
            backup::INTERACTIVE_NO, $restore_settings->restore_target, 2,
            $restore_settings->restore_target);
    try {
        $controller->get_logger()->set_next(new output_indented_logger(backup::LOG_INFO, false, true));
        $controller->execute_precheck();
        $plan = $controller->get_plan();
        $plan->get_setting('users')->set_value($restore_settings->restore_users);
        $plan->get_setting('role_assignments')->set_value($restore_settings->restore_role_assignments);
        $plan->get_setting('activities')->set_value($restore_settings->restore_activities);
        $plan->get_setting('blocks')->set_value($restore_settings->restore_blocks);
        $plan->get_setting('filters')->set_value($restore_settings->restore_filters);
        $plan->get_setting('comments')->set_value($restore_settings->restore_comments);
        $plan->get_setting('userscompletion')->set_value($restore_settings->restore_userscompletion);
        $plan->get_setting('logs')->set_value($restore_settings->restore_logs);
        $plan->get_setting('grade_histories')->set_value($restore_settings->restore_histories);
        $plan->get_setting('groups')->set_value($restore_settings->restore_groups);
        $plan->get_setting('groupings')->set_value($restore_settings->restore_groupings);
        $plan->get_setting('adminmods')->set_value($restore_settings->restore_adminmods);

        $restore_settings->restore_target = constant("backup::$mode");

        if($restore_settings->restore_target == backup::TARGET_EXISTING_DELETING) {
            $options = array('keep_roles_and_enrolments'=>$restore_settings->restore_keeproles ,'keep_groups_and_groupings'=>$restore_settings->restore_keeproles);
            restore_dbops::delete_course_content($controller->get_courseid(), $options);
        }

        $controller->execute_plan();
        $controller->log($strcourse.': '.$coursename, backup::LOG_INFO, ' OK');
    } catch (backup_exception $e) {
        $error = '  <<< ERROR '.$e->errorcode;
        $controller->log($strcourse.': '.$coursename, backup::LOG_WARNING, $error);
    }
    $controller->destroy();
    unset($controller);
}

// Obtención de registros en BBDD externa
$extdb = db_init();
$sqlcursosulpgc = "SELECT c.shortname, c.aadenc AS fullname, c.ccuatrim AS term, ca.idnumber AS category, c.idnumber, c.creditos AS credits, u.codigo AS departamento, c.estado
                     FROM tmocursos c, tmocategorias ca, tmoplataformasactivas p, tmounidades u
                    WHERE ca.id = c.categoriaid
                      AND p.id = ca.plataformaid
                      AND u.id(+) = c.departamentoid
                      AND u.tipo(+) = 'departamento'
                      AND p.plataforma = '{$CFG->plataforma}'
                      AND vinculo IS NULL";
$cursosulpgc = get_rows_external_db($extdb, $sqlcursosulpgc, 'idnumber');
db_close($extdb);

// Obtención de registros en Campus Virtual
$sqlcursoscv = "SELECT idnumber
                  FROM {course}
                 WHERE idnumber != ''";
$cursoscv = $DB->get_fieldset_sql($sqlcursoscv);

/* Registros a tratar (Insertar o Eliminar) */
// Registros que están en la bbdd externa y no en Moodle
$cursosadd = array_diff(array_keys(array_filter($cursosulpgc, function ($obj)
			{
				if ($obj->estado == 'I')
				return true;
			})), $cursoscv);

// Registros a eliminar en la bbdd externa que están en Moodle
$cursosdel = array_intersect(array_keys(array_filter($cursosulpgc, function ($obj)
			{
				if ($obj->estado == 'D')
				return true;
			})), $cursoscv);

// Combinación de registros
$cursoskeys = array_merge($cursosadd, $cursosdel);
$cursos = array_intersect_key($cursosulpgc, array_flip($cursoskeys));

if ((! isset($cursos)) or (count($cursos) == 0)) {

} else {

	foreach ($cursos as $curso) {

		$cursoencv = $DB->get_record('course', array(
				'idnumber' => $curso->idnumber
			));

		// Curso a añadir
		if ($curso->estado == 'I') {
			// Si ya existe el curso se pasa al siguiente
			if ($cursoencv) {
				/* De inicio todos los cursos están ocultos */
                                if (! $cursoencv->visible == 1) {
					if (! $DB->set_field('course', 'visible', '1', array(
								'id' => $cursoencv->id
							))) {
					}
				}
				continue;
			} else {
				// Asignarle id de la categoría al curso
				$categoria = $DB->get_field_select('course_categories', 'id', ' idnumber = ? ', array(
						$curso->category
					));

				if (! $categoria) { // Si no existe la categoría no se crea el curso
					continue;
				}
				$curso->category = $categoria;
				// Normalizar nombre del curso
				$romanos = array(
					'I',
					'II',
					'III',
					'IV',
					'V',
					'VI',
					'VII',
					'VIII',
					'IX',
					'X',
					'XI',
					'XII',
					'XII',
					'XIV',
					'XV',
					'XVI',
					'XVII',
					'XVIII',
					'XIX',
					'XX'
				);
				$siglas = array(
					'RRHH'
				);
				$acronimos = array_merge($romanos, $siglas);
				$preposiciones = array(
					'a',
					'ante',
					'bajo',
					'cabe',
					'con',
					'contra',
					'de',
					'desde',
					'en',
					'entre',
					'hacia',
					'hasta',
					'para',
					'por',
					'según',
					'sin',
					'so',
					'sobre',
					'tras', /* no es prepo */ 'del',
					'al'
				);
				$articulos = array(
					'el',
					'la',
					'las',
					'los',
					'un',
					'unos',
					'un',
					'una',
					'unas'
				);
				$nexos = array(
					'y',
					'u',
					'o',
					'e'
				);
				$conectores = array_merge($preposiciones, $articulos, $nexos);
				$curso->fullname = ucwords(mb_strtolower($curso->fullname, 'UTF-8'));
				$palabras = explode(" ", $curso->fullname);
				$nombre = '';
				foreach ($palabras as $key => $palabra) {
					if (in_array(strtoupper($palabra), $acronimos))
						$palabras[$key] = strtoupper($palabra);
					if (in_array(strtolower($palabra), $conectores) && $key != 0) {
						$palabras[$key] = strtolower($palabra);
					}
				}
				$nombre = implode(" ", $palabras);
				$curso->fullname = $nombre;

				// Asignar id del departamento al curso
				$departamento = $DB->get_field_select('local_sinculpgc_units', 'id', 'idnumber = ? AND type = ?', array(
                $curso->departamento, 'departamento'
            ));
            if (! $departamento) { // Si no existe la categoría se asigna
                                   // departamento
                                   // 0
                $departamento = 0;
            }
				$curso->department = $departamento;

				// Opciones por defecto
				// Si la fecha de inicio no está definida se asigna el día actual
				$curso->startdate = time();

				// Si no hay cuatrimestre definido, se asigna 0
				if (! $curso->term) {
					$curso->term = 0;
				}

				// convierte los créditos a decimal, sustituyendo la ',' por un '.'
				$curso->credits = str_replace(',', '.', $curso->credits);
				$curso->teacher = 'Profesor';
				$curso->teachers = 'Profesores';
				$curso->student = 'Estudiante';
				$curso->students = 'Estudiantes';
				$curso->format = 'topics';
				$curso->numsections = 10;

				$curso->hiddensections = 1;
				$curso->newsitems = 5;
				$curso->maxbytes = 0;
				$curso->metacourse = 0;
				$curso->enrollable = 1;
				$curso->visible = 0;
				$curso->password = md5(time());
				$curso->guest = 0;
				// Configuración por defecto de cursos de centros
				if (substr($curso->shortname, 0, 3) == '555') {
					$curso->showreports = 0;
					$curso->showgrades = 0;
					$curso->groupmode = 2;
					$curso->guest = 1;
				} else {
					$curso->showreports = 1;
					$curso->showgrades = 1;
				}
				try {
					$newcourse = create_course($curso);
					$newcourseulpgc = new stdClass();
					$newcourseulpgc->courseid = $newcourse->id;
					$newcourseulpgc->term = $curso->term;
					$newcourseulpgc->credits = $curso->credits;
					$newcourseulpgc->department = $curso->department;

					$newcourseulpgc->id = $DB->insert_record('local_ulpgccore_course', $newcourseulpgc);

					// After course creation, try to restore course backup or apply basic template
					// According to 201415 restore procedure (first restore backup course, then basic template)

                    if ($CFG->plataforma == 'tp' && $restore) {
                        $now = time();
                        if ($handle = opendir($backupspath)) {
                            while (false !== ($fichero = readdir($handle))) {
                                if ((substr(strrchr(substr($fichero, 1, - 7), '-'), 1)) == $curso->idnumber) {
                                    // Restore backup into course
                                    restore_curso($fichero, $newcourse, $now, $backupspath, 'TARGET_EXISTING_DELETING');
                                    rename($backupspath . $fichero, $backupspath . 'restored/' . $fichero);
                                    break;
                                }
                            }
                            closedir($handle);
                        }
                        restore_curso($templatename, $newcourse, $now, $templatespath, 'TARGET_EXISTING_ADDING');
                    }
                } catch (Exception $e) {
                    print_object($e);
                }
            }
		} else
			// Curso a eliminar (se oculta para evitar pérdidas de información)
			if ($curso->estado == 'D') {
				if ($cursoencv) {
					if (! $DB->set_field('course', 'visible', '0', array(
								'id' => $cursoencv->id
							))) {
					}
				}
			}
	}
}
?>
