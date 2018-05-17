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
 * Database enrolment plugin.
 *
 * This plugin synchronises enrolment and roles with external database table.
 *
 * @package enrol
 * @subpackage sinculpgc
 * @copyright 2015 Victor Deniz based on code by Petr Skoda {@link http://skodak.org}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot . '/local/sinculpgc/locallib.php');
require_once ($CFG->dirroot . '/blocks/usermanagement/manageusers_actions/managedefaulter/lib.php');

/**
 * sinculpgc enrolment plugin implementation.
 *
 * @author Victor Deniz - based on Petr Skoda, Martin Dougiamas, Martin Langhoff and
 *         others
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_sinculpgc_plugin extends enrol_plugin
{

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param object $instance
     * @return bool
     */
    public function instance_deleteable($instance)
    {
        if (! enrol_is_enabled('sinculpgc')) {
            return true;
        }

        return false;
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param object $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        return false;
    }
    
    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/sinculpgc:config', $context);
    }
    
    /**
     * Does this plugin allow manual unenrolment of a specific user?
     * Yes, but only if user suspended
     *
     * @param stdClass $instance
     *            course enrol instance
     * @param stdClass $ue
     *            record from user_enrolments table
     *
     * @return bool - true means user with 'enrol/xxx:unenrol' may unenrol this
     *         user, false means nobody may touch this user enrolment
     */
    public function allow_unenrol_user(stdClass $instance, stdClass $ue)
    {
        if ($ue->status == ENROL_USER_SUSPENDED) {
            return true;
        }

        return false;
    }

    /**
     * Gets an array of the user enrolment actions
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue
     *            A user enrolment object
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue)
    {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol_user($instance, $ue) && has_capability('enrol/sinculpgc:unenrol', $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array(
                'class' => 'unenrollink',
                'rel' => $ue->id
            ));
        }
        return $actions;
    }

    public function enrol_sinculpgc_allow_group_member_remove($itemid, $groupid, $userid)
    {
        global $USER;
        $admin = get_admin();
        if ($USER->id === $admin->id) { // ecastro allo delete id main admin, used un cronjobs and cli
            return true;
        }
        return false;
    }

    /**
     * Sincroniza las matrículas con base de datos externa
     *
     * @param bool $verbose
     * @return int 0 en caso de éxito, 1 si falla la conexión a la bbdd externa, 2 si falla la lectura de la bbdd externa
     */
    public function sync_enrolments($verbose = false)
    {
        global $CFG, $DB, $USER;
        require_once ($CFG->dirroot . '/group/lib.php');

        if ($verbose) {
            mtrace('Inicio del proceso de sincronización...');
        }

        if (! $extdb = db_init()) {
            mtrace('Error conectando con la base de datos externa');
            return 1;
        }

        // we may need a lot of memory here
        @set_time_limit(0);
        raise_memory_limit(MEMORY_HUGE);

        $defaultrole = $this->get_config('defaultrole');

        // create roles mapping
        $allroles = get_all_roles();
        if (! isset($allroles[$defaultrole])) {
            $defaultrole = 0;
        }
        $roles = array();
        foreach ($allroles as $role) {
            $roles[$role->shortname] = $role->id;
        }

        // Registros en base de datos externa (A efectos de demostración se restrigen a la categoría 111_4036)
        $sqlenrolsulpgc = "SELECT lower(u.username || '|' || c.idnumber || '|' || m.rol) as enrol, u.username, c.idnumber, m.rol, m.estado
		                    FROM tmomatriculas m, tmocursos c, tmoplataformasactivas p, tmocategorias ca, tmousuarios u
		                   WHERE p.plataforma = '{$CFG->plataforma}'
		                         AND p.aacada = '{$CFG->aacada}'
                                         AND ca.plataformaid = p.id
                                         AND c.categoriaid = ca.id
                                         AND m.cursoid = c.id
                                         AND u.id = m.usuarioid
                                ORDER BY m.estado desc";

        $enrolsulpgc = get_rows_external_db($extdb, $sqlenrolsulpgc, 'enrol');
        db_close($extdb);

        // Registros en Moodle
        $enrolsmoodlesql = "SELECT lower(concat(u.username,'|',c.idnumber,'|',r.shortname))
                              FROM {role_assignments} ra,
    							   {role} r,
                                   {context} ct,
                                   {course} c,
                                   {user} u
                             WHERE r.id = ra.roleid
                                   AND ct.id = ra.contextid
                                   AND ct.contextlevel = 50
                                   AND c.id = ct.instanceid
                                   AND u.id = ra.userid
                                   AND ra.component = 'enrol_sinculpgc'";
        $enrolsmoodle = $DB->get_fieldset_sql($enrolsmoodlesql);

        $enrolsadd = array_diff(array_keys(array_filter($enrolsulpgc, function ($obj) {

            if ($obj->estado == 'I')
                return true;
        })), $enrolsmoodle);

        $enrolsdel = array_intersect(array_keys(array_filter($enrolsulpgc, function ($obj) {
            if ($obj->estado == 'D')
                return true;
        })), $enrolsmoodle);

        $enrolskeys = array_merge($enrolsadd, $enrolsdel);
        $enrols = array_intersect_key($enrolsulpgc, array_flip($enrolskeys));

        if ((! isset($enrols)) or (count($enrols) == 0)) {
            return 0;
        } else {

            foreach ($enrols as $enrol) {

                // Verificar existencia de alumno, morosidad y curso
                if (! $user = $DB->get_record("user", array(
                    "username" => $enrol->username
                ))) {
                    continue;
                }

                // Si el alumno es moroso no se enrola en los cursos
                if (isset($CFG->morosos)) {
                    if (defaulter($user->id)) {
                        // add_to_log ( SITEID, 'enrol', 'add', '', 'El usuario ' . $enrol->username . ' es moroso', 0, $USER->id );
                        continue;
                    }
                }

                if (! $course = $DB->get_record("course", array(
                    "idnumber" => $enrol->idnumber
                ))) {
                    continue;
                }

                // Crea/recupera un objeto de contexto
                $context = context_course::instance($course->id);
                if (! $context) {
                    continue;
                }

                // Get/creates the enrol_sinculpgc instance for this course
                $enrol_instance = $DB->get_record('enrol', array(
                    'courseid' => $course->id,
                    'enrol' => 'sinculpgc'
                ));
                if (empty($enrol_instance)) {
                    // Only add an enrol instance to the course if non-existent
                    $enrolid = $this->add_instance($course);
                    if ($enrolid) {
                        $enrol_instance = $DB->get_record('enrol', array(
                            'id' => $enrolid
                        ));
                    } else {
                        continue;
                    }
                }

                $roleid = isset($roles[$enrol->rol]) ? $roles[$enrol->rol] : $defaultrole;

                // Matricular usuario
                if ($enrol->estado == 'I') {
                    // Enrol the user with this plugin instance
                    $this->enrol_user($enrol_instance, $user->id, $roleid, 0, 0, null, false);

                    // Si tiene enrol manual, se elimina, prevalece sinculpgc
                    // Se respetan otros tipos de enrol

                    $sql = "SELECT e.id
                        FROM {enrol} e
                        join
                        {context} c ON c.instanceid = e.courseid
                        AND c.contextlevel = 50
                        join
                        {role_assignments} ra ON ra.contextid = c.id
                        WHERE e.courseid = :courseid
                        AND enrol = 'manual'
                        AND ra.userid = :userid
                        AND ra.roleid = :roleid";
                    $manual_enroled = $DB->get_record_sql($sql, array(
                        'userid' => $user->id,
                        'courseid' => $course->id,
                        'roleid' => $roleid
                    ));
                    if ($manual_enroled) {
                        role_unassign($roleid, $user->id, $context->id, '');
                    }
                } else
                    if ($enrol->estado == 'D') {
                        role_unassign($roleid, $user->id, $context->id, 'enrol_sinculpgc', $enrol_instance->id);

                        // Si no tiene ningún rol asignado, se elimina el enrol
                        $sql = "SELECT id
                            FROM {role_assignments}
                        WHERE userid = :userid
                            AND itemid = :enrolid
                            AND component = 'enrol_sinculpgc'";
                        $hasmiulpgcroles = $DB->get_record_sql($sql, array(
                            'userid' => $user->id,
                            'enrolid' => $enrol_instance->id
                        ));

                        if (! $hasmiulpgcroles) {
                            // Unenrol the user from sinculpgc enrolment instance
                            $this->unenrol_user($enrol_instance, $user->id);
                        }
                    }
            }
            return count($enrols);
        }
    }
}
