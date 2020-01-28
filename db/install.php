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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     local_cohort_users
 * @category    upgrade
 * @copyright   Shubhendra Doiphode 2019
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_local_cohort_users_install() {
    global $CFG, $DB;
    $dbman = $DB->get_manager();
    $table = new xmldb_table('assign_grades');
    $field = new xmldb_field('marking2nd', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, '0.0000', 'attemptnumber');

    // Conditionally launch add field marking2nd.
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }


    return true;
}
