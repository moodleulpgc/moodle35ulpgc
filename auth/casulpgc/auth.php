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
 * Authentication Plugin: CAS Authentication
 *
 * Authentication using CAS (Central Authentication Server).
 *
 * @author Victor Deniz
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package auth_casulpgc
 */
defined('MOODLE_INTERNAL') || die();

require_once ($CFG->libdir . '/authlib.php');
require_once ($CFG->dirroot . '/auth/cas/CAS/CAS.php');
// Conexión a base de datos externa
require_once ($CFG->dirroot . '/local/sinculpgc/locallib.php');


/*
 * Proceso los comandos single sign-out enviados por CAS.
 * Busca en los archivos de sesión en disco el que coincide con el ticket
 * recibido desde CAS.
 */
function casSingleSignOut($ticket2logout)
{
    global $CFG;

    // File session
    $dir = $CFG->dataroot . '/sessions';
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            // Read all session files
            while (($file = readdir($dh)) !== false) {
                // Check if it is a file
                if (is_file($dir . '/' . $file)) {
                    $content = file($dir . '/' . $file);
                    if (preg_match('/'.$ticket2logout.'/', $content[0])) {
                        unlink($dir . '/' . $file);
                    }
                }
            }
        }
        closedir($dh);
    }
}

/**
 * CAS authentication plugin.
 */
class auth_plugin_casulpgc extends auth_plugin_base
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->authtype = 'casulpgc';
        $this->roleauth = 'auth_casulpgc';
        $this->errorlogtag = '[AUTH CAS] ';
        $this->pluginconfig = 'auth/' . $this->authtype;
        $this->config = get_config($this->pluginconfig);
    }

    /**
     * Old syntax of class constructor.
     * Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function auth_plugin_casulpgc()
    {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    function prevent_local_passwords()
    {
        return true;
    }

    /**
     * Authenticates user against CAS
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username
     *            The username (with system magic quotes)
     * @param string $password
     *            The password (with system magic quotes)
     * @return bool Authentication success or failure.
     */
    function user_login($username, $password)
    {
        $this->connectCAS();
        return phpCAS::isAuthenticated() && (trim(core_text::strtolower(phpCAS::getUser())) == $username);
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal()
    {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password()
    {
        return false;
    }

    /**
     * Authentication choice (CAS or other)
     * Redirection to the CAS form or to login/index.php
     * for other authentication
     */
    function loginpage_hook()
    {
        global $frm;
        global $user;
        global $CFG;
        global $SESSION, $OUTPUT, $PAGE;

        $site = get_site();
        $username = optional_param('username', '', PARAM_RAW);
        $courseid = optional_param('courseid', 0, PARAM_INT);

        if (! empty($username)) {
            if (isset($SESSION->wantsurl) && (strstr($SESSION->wantsurl, 'ticket') || strstr($SESSION->wantsurl, 'NOCAS'))) {
                unset($SESSION->wantsurl);
            }
            return;
        }

        // Return if CAS enabled and settings not specified yet
        if (empty($this->config->hostname)) {
            return;
        }

        // Connection to CAS server
        $this->connectCAS();

        if (phpCAS::checkAuthentication()) {
            $frm = new stdClass();
            $frm->username = phpCAS::getUser();
            $user = get_complete_user_data('username', $frm->username, $CFG->mnet_localhost_id);
            if ((! $user) || (($user->auth != 'casulpgc') && ($user->auth != 'manual'))) {
                redirect($CFG->wwwroot . '/auth/casulpgc/noexiste.php');
            }

            return;
        }

        // Force CAS authentication (if needed).
        /*
         * if (!phpCAS::isAuthenticated()) { phpCAS::setLang($this->config->language); phpCAS::forceAuthentication(); }
         */
    }

    /**
     * Connect to the CAS (clientcas connection or proxycas connection)
     */
    function connectCAS()
    {
        global $CFG;
        static $connected = false;

        if (! $connected) {
            // Make sure phpCAS doesn't try to start a new PHP session when connecting to the CAS server.
            if ($this->config->proxycas) {
                phpCAS::proxy($this->config->casversion, $this->config->hostname, (int) $this->config->port, $this->config->baseuri, false);
            } else {
                //phpCAS::setDebug('debug.log');
                phpCAS::client($this->config->casversion, $this->config->hostname, (int) $this->config->port, $this->config->baseuri, false);
            }
            $connected = true;
        }

        // Función invocada para tratar un petición de Single Sign Out
        phpCAS::setSingleSignoutCallback('casSingleSignOut', array(session_id()));

        // If Moodle is configured to use a proxy, phpCAS needs some curl options set.
        if (! empty($CFG->proxyhost) && ! is_proxybypass($this->config->hostname)) {
            phpCAS::setExtraCurlOption(CURLOPT_PROXY, $CFG->proxyhost);
            if (! empty($CFG->proxyport)) {
                phpCAS::setExtraCurlOption(CURLOPT_PROXYPORT, $CFG->proxyport);
            }
            if (! empty($CFG->proxytype)) {
                // Only set CURLOPT_PROXYTYPE if it's something other than the curl-default http
                if ($CFG->proxytype == 'SOCKS5') {
                    phpCAS::setExtraCurlOption(CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                }
            }
            if (! empty($CFG->proxyuser) and ! empty($CFG->proxypassword)) {
                phpCAS::setExtraCurlOption(CURLOPT_PROXYUSERPWD, $CFG->proxyuser . ':' . $CFG->proxypassword);
                if (defined('CURLOPT_PROXYAUTH')) {
                    // any proxy authentication if PHP 5.1
                    phpCAS::setExtraCurlOption(CURLOPT_PROXYAUTH, CURLAUTH_BASIC | CURLAUTH_NTLM);
                }
            }
        }

        if ($this->config->certificate_check && $this->config->certificate_path) {
            phpCAS::setCasServerCACert($this->config->certificate_path);
        } else {
            // Don't try to validate the server SSL credentials
            phpCAS::setNoCasServerValidation();
        }

        // Recoge las peticiones Single Sign Out, invocando la función especificada en setSingleSignoutCallback
        phpCAS::handleLogoutRequests(false);
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page
     *            An object containing all the data for this page.
     */
    function config_form($config, $err, $user_fields)
    {
        global $CFG, $OUTPUT;

        include ($CFG->dirroot . '/auth/casulpgc/config.html');
    }

    /**
     * A chance to validate form data, and last chance to
     * do stuff before it is inserted in config_plugin
     *
     * @param
     *            object object with submitted configuration settings (without system magic quotes)
     * @param array $err
     *            array of error messages
     */
    function validate_form($form, &$err)
    {
        $certificate_path = trim($form->certificate_path);
        if ($form->certificate_check && empty($certificate_path)) {
            $err['certificate_path'] = get_string('auth_cas_certificate_path_empty', 'auth_casulpgc');
        }
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url()
    {
        return null;
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    function process_config($config)
    {

        // CAS settings
        if (! isset($config->hostname)) {
            $config->hostname = '';
        }
        if (! isset($config->port)) {
            $config->port = '';
        }
        if (! isset($config->casversion)) {
            $config->casversion = '';
        }
        if (! isset($config->baseuri)) {
            $config->baseuri = '';
        }
        if (! isset($config->language)) {
            $config->language = '';
        }
        if (! isset($config->proxycas)) {
            $config->proxycas = '';
        }
        if (! isset($config->logoutcas)) {
            $config->logoutcas = '';
        }
        if (! isset($config->certificate_check)) {
            $config->certificate_check = '';
        }
        if (! isset($config->certificate_path)) {
            $config->certificate_path = '';
        }
        if (! isset($config->logout_return_url)) {
            $config->logout_return_url = '';
        }

        if (! isset($config->removeuser)) {
            $config->removeuser = AUTH_REMOVEUSER_KEEP;
        }

        // save CAS settings
        set_config('hostname', trim($config->hostname), $this->pluginconfig);
        set_config('port', trim($config->port), $this->pluginconfig);
        set_config('casversion', $config->casversion, $this->pluginconfig);
        set_config('baseuri', trim($config->baseuri), $this->pluginconfig);
        set_config('language', $config->language, $this->pluginconfig);
        set_config('proxycas', $config->proxycas, $this->pluginconfig);
        set_config('logoutcas', $config->logoutcas, $this->pluginconfig);
        set_config('certificate_check', $config->certificate_check, $this->pluginconfig);
        set_config('certificate_path', $config->certificate_path, $this->pluginconfig);
        set_config('logout_return_url', $config->logout_return_url, $this->pluginconfig);

        set_config('removeuser', $config->removeuser, $this->pluginconfig);

        return true;
    }

    /**
     * Devuelve los datos personales de un usuario
     *
     * @param string $username
     *            Identificador único del usuario en la base de datos externa
     * @return StdClass
     */
    function get_personaldata($db, $username)
    {

        // fetch userdata
        $rs = external_db($db, "SELECT *
                                  FROM vmodatospersonales
                                 WHERE username='$username'");

        if (! $rs) {
            print_error('auth_dbcantconnect', 'auth_db');
        } else
            if (! $rs->EOF) {
                $rec = $rs->FetchRow();
                $rec = (object) array_change_key_case((array) $rec, CASE_LOWER);
            }
        utf8_encode_deep($rec);

        return $rec;
    }

    /**
     * Sincroniza los usuarios de la BBDD de moodle con los de la BBDD
     * corporativa de la ULPGC
     *
     * La sincronización se realiza en base al campo username
     *
     * La sincronización supende los usuarios que se han dado de baja en la BBDD
     * corporativa
     *
     * @param bool $do_updates
     *            Determina si se actualizan los dato de usuario
     * @param bool $verbose
     *            Muestra información de depuración
     *
     */
    function sync_users($do_updates = false, $verbose = false)
    {
        global $CFG, $DB, $USER;

        // Obtención de registros en BBDD externa (A efectos de demostración se restrigen a la categoría 111_4036)
        $extdb = db_init();
        $sqlusuariosulpgc = "SELECT lower(u.username) AS username, MAX(up.estado) AS estado
		                       FROM tmousuariosplataforma up, tmousuarios u, tmoplataformasactivas p
		                      WHERE p.plataforma = '{$CFG->plataforma}'
		                            AND p.aacada = '{$CFG->aacada}'
                                    AND up.plataformaid = p.id
		                            AND u.id = up.usuarioid";

        $sqlusuariosulpgc = "SELECT lower(u.username) AS username, MAX(up.estado) AS estado, min(m.rol) AS rol
                               FROM tmousuariosplataforma up, tmousuarios u, tmoplataformasactivas p, tmomatriculas m
                              WHERE p.plataforma = '{$CFG->plataforma}'
                                    AND p.aacada = '{$CFG->aacada}'
                                    AND up.plataformaid = p.id
                                    AND u.id = up.usuarioid
                                    AND m.usuarioid = u.id";

        /* Solo se crean las cuentas de los usuarios cuando no está habilitada la carga de alumnos */
        if (! $CFG->cargaalumnos) {
            $sqlusuariosulpgc .= " AND EXISTS (select id from tmomatriculas m where m.usuarioid = u.id AND m.rol like '%teacher%' and estado='I')";
        }

        $sqlusuariosulpgc .= " GROUP BY lower(u.username)";

        $usuariosulpgc = get_rows_external_db($extdb, $sqlusuariosulpgc, 'username');

        // Obtención de registros en Campus Virtual
        $usuarioscv = $DB->get_fieldset_select('user', 'username', 'auth="casulpgc"');

        /* Registros a tratar (Insertar o Eliminar) */
        // Registros que están en la bbdd externa y no en Moodle
        $usuariosadd = array_diff(array_keys(array_filter($usuariosulpgc, function ($obj) {
            if ($obj->estado == 'I')
                return true;
        })), $usuarioscv);

        // Registros a eliminar en la bbdd externa que están en Moodle
        $usuariosdel = array_intersect(array_keys(array_filter($usuariosulpgc, function ($obj) {
            if ($obj->estado == 'D')
                return true;
        })), $usuarioscv);

        // Combinación de registros
        $usuarioskeys = array_merge($usuariosadd, $usuariosdel);
        $userlist = array_intersect_key($usuariosulpgc, array_flip($usuarioskeys));

        if ((! isset($userlist)) or (count($userlist) == 0)) {
            // add_to_log(SITEID, 'grupos', 'sync', '', 'No hay usuarios a tratar', 0, $USER->id);
            return;
        } else {
            // add_to_log(SITEID, 'grupos', 'sync', '', 'Usuarios a tratar: ' . count($userlist), 0, $USER->id);

            /*
             * Preferencias de usuario para enviar envío de notificaciones a los profesores
             */

            /*$teacher_preferences = array();
            $user_preferences = new stdclass();
            $user_preferences->name = 'message_provider_mod_quiz_submission_loggedin';
            $user_preferences->value = 'none';
            $teacher_preferences[] = $user_preferences;
            unset($user_preferences);
            $user_preferences = new stdclass();
            $user_preferences->name = 'message_provider_mod_quiz_submission_loggedoff';
            $user_preferences->value = 'none';
            $teacher_preferences[] = $user_preferences;
            unset($user_preferences);
            $user_preferences = new stdclass();
            $user_preferences->name = 'message_provider_moodle_badgecreatornotice_loggedin';
            $user_preferences->value = 'none';
            $teacher_preferences[] = $user_preferences;
            unset($user_preferences);
            $user_preferences = new stdclass();
            $user_preferences->name = 'message_provider_moodle_instantmessage_loggedoff';
            $user_preferences->value = 'email';
            $teacher_preferences[] = $user_preferences;
            unset($user_preferences);*/

            foreach ($userlist as $user) {
                // Verificar existencia de la cuenta del usuario
                $existing_user = $DB->get_record('user', array(
                    'username' => $user->username
                ));

                // Añadir usuario
                if ($user->estado == 'I') {

                    if ($existing_user) {
                        if ($existing_user->auth != 'casulpgc') {
                            /* Si no está abierto el acceso a alumnos y es un estudiante no se modifica el registro */
                            if (! $CFG->accesoalumnos && $user->rol == 'student') {
                                continue;
                            }
                            $existing_user->auth = 'casulpgc';
                            $existing_user->deleted = 0;
                            $existing_user->timemodified = time();
                            if ($DB->update_record('user', $existing_user)) {
                                if ($verbose) {
                                    mtrace(print_string('auth_casulpgc_reviveduser', 'auth_casulpgc', $existing_user->username));
                                }
                            } else {
                                if ($verbose) {
                                    mtrace(print_string('auth_casulpgc_revivedusererror', 'auth_casulpgc', $existing_user->username));
                                }
                            }
                        }
                    } else {
                        $sqldatos = "SELECT *
                                   FROM vmodatospersonales v
                                  WHERE lower(username) = '{$user->username}'";
                        $newuser = get_rows_external_db($extdb, $sqldatos, 'username');

                        $newuser = array_shift($newuser);
                        if (! isset($newuser->username)) {
                            continue;
                        }

                        $newuser->username = strtolower($newuser->username);

                        if (is_null($newuser->phone1)) {
                            $newuser->phone1 = '';
                        }
                        if (is_null($newuser->phone2)) {
                            $newuser->phone2 = '';
                        }
                        if (is_null($newuser->email)) {
                            $newuser->email = '';
                        }
                        $newuser->timemodified = time();
                        $newuser->confirmed = 1;
                        /* Si no está abierto el acceso a los alumnos, se le deniega el acceso */
                        if (! $CFG->accesoalumnos && $user->rol == 'student') {
                            $newuser->auth = 'nologin';
                        } else
                            $newuser->auth = 'casulpgc';
                        $newuser->mnethostid = $CFG->mnet_localhost_id;
                        $newuser->password = 'd41d8cd98f00b204e9800998ecf8427e';
                        $newuser->lang = 'es';
                        $newuser->country = 'ES';
                        $newuser->timezone = 'Atlantic/Canary';
                        $newuser->emailstop = 0;
                        $newuser->maildigest = 0;
                        $newuser->maildisplay = 1;
                        $newuser->htmleditor = 1;
                        $newuser->ajax = 1;
                        $newuser->autosubscribe = 0;
                        $newuser->trackforums = 1;
                        if ($user->rol != 'student') {
                            $newuser->institution = 'ULPGC';
                            if (is_numeric($newuser->department)) {
                                $departamento = $DB->get_record('local_sinculpgc_units', array(
                                    'idnumber' => $newuser->department,
                                    'type' => 'departamento'
                                ));
                                if ($departamento) {
                                    $newuser->department = $departamento->name;
                                }
                            }
                        } else {
                            $newuser->institution = '';
                            $newuser->department = '';
                        }
                        $newuser->url = ! ($newuser->url) ? '' : $newuser->url;

                        if ($id = $DB->insert_record('user', $newuser)) {
                            if ($verbose) {
                                mtrace(print_string('auth_casulpgc_insertuser', 'auth_casulpgc', $newuser->username));
                            }
                            /*if ($user->rol != 'student') {
                                foreach ($teacher_preferences as $preference) {
                                    $preference->userid = $id;
                                    try {
                                    $DB->insert_record('user_preferences', $preference);
                                    } catch (Exception $e) {
                                        if ($verbose) {
                                            mtrace("Error inserting $id user preferences:". $e->getMessage());
                                        }
                                    }
                                }
                            }*/
                        } else {
                            if ($verbose) {
                                mtrace(print_string('auth_casulpgc_insertusererror', 'auth_casulpgc', $newuser->username));
                            }
                       }
                    }
                } else
            /*
             *
             * El valor del campo "Usuario externo eliminado", en la pantalla de configuración de la extensión,
             * determina que sucede cuando un alumno no está matriculado en ningún curso de una plataforma:
             * - Mantener interna (0): el alumno conserva el acceso a la plataforma
             * - Suspender interna(1): el alumno se mantiene en la plataforma pero se le niega el acceso
             * - Borrado completo (2): el alumno se elimina de la plataforma
             *
             */
            if ($user->estado == 'D' && (! empty($this->config->removeuser))) {
                        if (isset($existing_user) && $existing_user->auth == 'casulpgc') {
                            // la opción 2 es borrado completo
                            if ($this->config->removeuser == 2) {
                                if (delete_user($existing_user)) {
                                    if ($verbose) {
                                        mtrace(print_string('auth_casulpgc_deleteuser', 'auth_casulpgc', $existing_user->username));
                                    }
                                    // add_to_log(SITEID, 'user', 'del', '', get_string('auth_casulpgc_deleteuser', 'auth_casulpgc', $existing_user->username));
                                } else {
                                    if ($verbose) {
                                        mtrace(print_string('auth_casulpgc_deleteusererror', 'auth_casulpgc', $existing_user->username));
                                    }
                                    // add_to_log(SITEID, 'user', 'del', '', get_string('auth_casulpgc_deleteusererror', 'auth_casulpgc', $existing_user->username));
                                }
                            } else
                                if ($this->config->removeuser == 1) {
                                    $existing_user->auth = 'nologin';
                                    $existing_user->timemodified = time();
                                    if ($DB->update_record('user', $existing_user)) {
                                        if ($verbose) {
                                            mtrace(print_string('auth_casulpgc_suspenduser', 'auth_casulpgc', $existing_user->username));
                                        }
                                        // add_to_log(SITEID, 'user', 'del', '', get_string('auth_casulpgc_suspenduser', 'auth_casulpgc', $existing_user->username));
                                    } else {
                                        if ($verbose) {
                                            mtrace(print_string('auth_casulpgc_suspendusererror', 'auth_casulpgc', $existing_user->username));
                                        }
                                        // add_to_log(SITEID, 'user', 'del', '', get_string('auth_casulpgc_suspendusererror', 'auth_casulpgc', $existing_user->username));
                                    }
                                }
                        }
                    }
            }
        }
        db_close($extdb);
        return count($userlist);
    }

    /**
     * Hook for logout page
     */
    function logoutpage_hook()
    {
        global $USER, $redirect;

        // Only do this if the user is actually logged in via CAS
        if (($USER->auth === $this->authtype) || ($USER->auth === 'manual')) {
            // Check if there is an alternative logout return url defined
            if (isset($this->config->logout_return_url) && ! empty($this->config->logout_return_url)) {
                // Set redirect to alternative return url
                $redirect = $this->config->logout_return_url;
            }
            if (! empty($this->config->logoutcas)) {
                $this->connectCAS();
                $redirect = phpCAS::getServerLogoutURL() . '?service=' . urlencode($this->config->logout_return_url);
            }
        }
    }
}
