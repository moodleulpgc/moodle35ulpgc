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
 * masks module version upgrade code utility lib
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masks;

defined('MOODLE_INTERNAL') || die;

function fix_id_field( $table, $field ) {
    global $DB, $CFG;
    $sql = 'ALTER TABLE ' . $CFG->prefix . $table . ' MODIFY COLUMN ' . $field . ' BIGINT( 10 ) NOT NULL AUTO_INCREMENT';
    $DB->change_database_structure( $sql );
}

function add_db_id_field( $tbl, $field ) {
    $tbl->add_field( $field, XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL );
    $tbl->add_key( 'primary', XMLDB_KEY_PRIMARY, array($field) );
}

function add_db_int_field( $tbl, $field ) {
    $tbl->add_field( $field, XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, 0 );
}

function add_db_txt_field( $tbl, $field ) {
    $tbl->add_field( $field, XMLDB_TYPE_TEXT, null, null, null, null, null );
}

function add_db_date_field( $tbl, $field ) {
    $tbl->add_field( $field, XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, 0 );
}

function add_db_chr_field( $tbl, $field, $size ) {
    $tbl->add_field( $field, XMLDB_TYPE_CHAR, $size, null, null, null, '' );
}

