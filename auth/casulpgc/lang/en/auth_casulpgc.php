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
 * Strings for component 'auth_casulpgc', language 'en'.
 *
 * @package   auth_casulpgc
 * @copyright 2014 Víctor Déniz (based on CAS plugin by Martin Dougiamas)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['accesCAS'] = 'CAS users';
$string['accesNOCAS'] = 'other users';
$string['auth_casulpgc_auth_user_create'] = 'Create users externally';
$string['auth_casulpgc_baseuri'] = 'URI of the server (nothing if no baseUri)<br />For example, if the CAS server responds to host.domaine.fr/CAS/ then<br />cas_baseuri = CAS/';
$string['auth_casulpgc_baseuri_key'] = 'Base URI';
$string['auth_casulpgc_broken_password'] = 'You cannot proceed without changing your password, however there is no available page for changing it. Please contact your Moodle Administrator.';
$string['auth_casulpgc_cantconnect'] = 'LDAP part of CAS-module cannot connect to server: {$a}';
$string['auth_casulpgc_casversion'] = 'CAS protocol version';
$string['auth_casulpgc_certificate_check'] = 'Select \'yes\' if you want to validate the server certificate';
$string['auth_casulpgc_certificate_path_empty'] = 'If you turn on Server validation, you need to specify a certificate path';
$string['auth_casulpgc_certificate_check_key'] = 'Server validation';
$string['auth_casulpgc_certificate_path'] = 'Path of the CA chain file (PEM Format) to validate the server certificate';
$string['auth_casulpgc_certificate_path_key'] = 'Certificate path';
$string['auth_casulpgc_create_user'] = 'Turn this on if you want to insert CAS-authenticated users in Moodle database. If not then only users who already exist in the Moodle database can log in.';
$string['auth_casulpgc_create_user_key'] = 'Create user';
$string['auth_casulpgcdescription'] = 'This method uses a CAS server (Central Authentication Service) to authenticate users in a Single Sign On environment (SSO).';
$string['auth_casulpgc_enabled'] = 'Turn this on if you want to use CAS authentication.';
$string['auth_casulpgc_hostname'] = 'Hostname of the CAS server <br />eg: host.domain.fr';
$string['auth_casulpgc_hostname_key'] = 'Hostname';
$string['auth_casulpgc_changepasswordurl'] = 'Password-change URL';
$string['auth_casulpgc_invalidcaslogin'] = 'Sorry, your login has failed - you could not be authorised';
$string['auth_casulpgc_language'] = 'Select language for authentication pages';
$string['auth_casulpgc_language_key'] = 'Language';
$string['auth_casulpgc_logincas'] = 'Secure connection access';
$string['auth_casulpgc_logout_return_url_key'] = 'Alternative logout return URL';
$string['auth_casulpgc_logout_return_url'] = 'Provide the URL that CAS users shall be redirected to after logging out.<br />If left empty, users will be redirected to the location that moodle will redirect users to';
$string['auth_casulpgc_logoutcas'] = 'Select \'yes\' if you want to logout from CAS when you disconnect from Moodle';
$string['auth_casulpgc_logoutcas_key'] = 'CAS logout option';
$string['auth_casulpgc_multiauth'] = 'Select \'yes\' if you want to have multi-authentication (CAS + other authentication)';
$string['auth_casulpgc_multiauth_key'] = 'Multi-authentication';
$string['auth_casnotinstalled'] = 'Cannot use CAS authentication. The PHP LDAP module is not installed.';
$string['auth_casulpgc_port'] = 'Port of the CAS server';
$string['auth_casulpgc_port_key'] = 'Port';
$string['auth_casulpgc_proxycas'] = 'Select \'yes\' if you use CAS in proxy-mode';
$string['auth_casulpgc_proxycas_key'] = 'Proxy mode';
$string['auth_casulpgc_server_settings'] = 'CAS server configuration';
$string['auth_casulpgc_text'] = 'Secure connection';
$string['auth_casulpgc_use_cas'] = 'Use CAS';
$string['auth_casulpgc_version'] = 'CAS protocol version to use';
$string['CASform'] = 'Authentication choice';
$string['noldapserver'] = 'No LDAP server configured for CAS! Syncing disabled.';
$string['pluginname'] = 'CAS ULPGC (SSO)';
$string['location'] = 'File location';
$string['locationhelp'] = 'Path to auth file location. This must be subdirectory of moodledata. Give de path from <i>dataroot/</i>. Please, ensure that this directory is writable by moodle. ';
$string['pattern'] = 'File pattern to match';
$string['patternhelp'] = 'Files to look for in the directory, e.g. *.txt or *.csv ';
$string['auth_casulpgc_userstoremove'] = '{$a} Users to remove ';
$string['auth_casulpgc_deleteuser'] = 'Delete user {$a} ';
$string['auth_casulpgc_deleteusererror'] = 'Error when deleting user {$a} ';
$string['auth_casulpgc_suspenduser'] = 'Suspend account of user {$a}';
$string['auth_casulpgc_suspendusererror'] = 'Error when suspending user {$a} ';
$string['updateusersto'] = '{$a} Users to update';
$string['auth_casulpgc_userstoadd'] = '{$a} Users to add';
$string['auth_casulpgc_reviveduser'] = 'Reviven user {$a} ';
$string['auth_casulpgc_insertuser'] = 'Added user {$a}';
$string['auth_casulpgc_insertusererror'] = 'Error when adding user {$a}';
$string['auth_casulpgc_updatinguser'] = 'Updating user {$a}';
