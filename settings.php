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
 * @package     local_cohort_users
 * @copyright   Shubhendra Doiphode 2019
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('root', new admin_category('local_cohort_users',
    get_string('pluginname', 'local_cohort_users', null, true)));

$page = new admin_externalpage('local_cohort_users_pagelist',
    get_string('cohortusers', 'local_cohort_users', null, true),
    new moodle_url('/local/cohort_users/view.php'),
    'moodle/site:config');


// Add documents page to navigation category.
$ADMIN->add('local_cohort_users', $page);

