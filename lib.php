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
 * Code for adding a link to category menu
 * @copyright   Shubhendra Doiphode 2019
 * @package 	local_cohort_users
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * Hook to insert a link in settings navigation menu block
 */
function local_cohort_users_extend_settings_navigation(settings_navigation $navigation, $context) {

    global $CFG, $PAGE;

    
    $url = $CFG->wwwroot . "/local/cohort_users/view.php";

    $node = navigation_node::create(get_string("pluginname", "local_cohort_users"), $url, '', '', '', new pix_icon('i/navigationitem', get_string("pluginname", "local_cohort_users")));
    $node->showinflatnavigation = true;
    $PAGE->navigation->add_node($node);

}

