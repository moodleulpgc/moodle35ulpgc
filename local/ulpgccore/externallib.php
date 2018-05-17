<?php

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
 * External Web Service Template
 *
 * @package ulpgccore
 * @copyright 2013 Víctor Déniz
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once ($CFG->libdir . "/externallib.php");

class local_ulpgccore_external extends external_api {

	/**
	 * Actualizar cuatrimestre de un curso
	 *
	 * @return external_function_parameters
	 * @since Moodle 2.5
	 */
	public static function update_course_term_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'ID del curso' ),
				'term' => new external_value ( PARAM_INT, 'Cuatrimestre actual' )
		) );
	}

	/**
	 * Actualiza el cuatrimestre de un curso
	 *
	 * @param int $courseid
	 * @param int $term
	 * @since Moodle 2.5
	 */
	public static function update_course_term($courseid, $term) {
		global $CFG, $DB;
		require_once ($CFG->dirroot . "/course/lib.php");

		$params = self::validate_parameters ( self::update_course_term_parameters (), array (
				'courseid' => $courseid,
				'term' => $term
		) );

		// Catch any exception while updating course and return as warning to
		// user.
		try {
			// Ensure the current user is allowed to run this function.
			$context = context_course::instance ( $courseid, MUST_EXIST );
			// Comentado porque el usuario no tiene permiso
			self::validate_context ( $context );

			// Capability checking
			// OPTIONAL but in most web service it should present
			if (! has_capability ( 'moodle/course:update', $context )) {
				throw new moodle_exception ( 'requireloginerror' );
			}

			// Actualizar cuatrimestre del curso, en la nueva tabla
			$DB->set_field('local_ulpgccore_course', 'term', $term, array('courseid'=>$courseid));
			
			
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return 1;
	}

	/**
	 * Devuelve 1 si se modificó correctamente
	 *
	 * @return external_description
	 * @since Moodle 2.5
	 */
	public static function update_course_term_returns() {
		return new external_value ( PARAM_TEXT, 'Devuelve 1 si se modifica correctamente el cuatrimestre' );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function get_id_curso_parameters() {
		return new external_function_parameters ( array (
				'idnumber' => new external_value ( PARAM_TEXT, 'Idnumber del curso del que se quiere obtener el id' )
		) );
	}

	/**
	 * Returns el id del curso $idnumber
	 *
	 * @return string welcome message
	 */
	public static function get_id_curso($idnumber) {
		global $USER, $DB;

		// Parameter validation
		// REQUIRED
		$params = self::validate_parameters ( self::get_id_curso_parameters (), array (
				'idnumber' => $idnumber
		) );

		// Context validation
		// OPTIONAL but in most web service it should present
		$context = context_user::instance ( $USER->id );
		self::validate_context ( $context );

		// Capability checking
		// OPTIONAL but in most web service it should present
		if (! has_capability ( 'moodle/course:view', $context )) {
			throw new moodle_exception ( 'cannotviewprofile' );
		}

		$id_curso = $DB->get_field ( 'course', 'id', array (
				'idnumber' => $idnumber
		), $strictness = IGNORE_MISSING );

		return $id_curso;
	}

	/**
	 * Returns description of method result value
	 *
	 * @return external_description
	 */
	public static function get_id_curso_returns() {
		return new external_value ( PARAM_TEXT, 'Id del curso con el idnumber especificado' );
	}

}
