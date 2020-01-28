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
require_once(dirname(dirname(__FILE__)).'../../config.php');
global $PAGE,$CFG,$USER, $DB, $OUTPUT,$COURSE,$SESSION;

require  "cohort_table.php";

## Redirect if non-admin
if (!is_siteadmin()) {
    $url = $CFG->wwwroot;
    redirect($url);
}
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('cohortusers','local_cohort_users'));
$PAGE->set_heading(get_string('pluginname', 'local_cohort_users'));
$PAGE->navbar;


$PAGE->set_url('/local/cohort_users/view.php');
$PAGE->requires->jquery();

$download = optional_param('download', '', PARAM_ALPHA);

$table = new report_table('uniqueid' . time());

$filename = 'cohort_users_' . date("m-d-Y");

$table->is_downloading($download, $filename, 'sheet1');

## Parameters
$pageid = optional_param('page',0,PARAM_INT);
$search_activity = optional_param('activity',"",PARAM_TEXT);
$search_student = optional_param('student',"",PARAM_TEXT);
if (isset($_POST["btn_submit"]))  {
    $SESSION->filter = $_POST;
    $pageid=0;

}
$where = array();
$where[] = "u.id > 1";

$sel_cohort = array();
$search_text = "";
if (isset( $SESSION->filter["btn_submit"]))  {

    if(!empty($SESSION->filter["sel_cohort"])){
        $sel_cohort =  $SESSION->filter["sel_cohort"];
        $str_cohorts = implode(",", $sel_cohort);
        $arr_cohortmembers = $DB->get_records_sql('SELECT * FROM {cohort_members} WHERE cohortid in ('. $str_cohorts .')');
    }
    if(!empty($SESSION->filter["search_text"])){
        $search_text =  $SESSION->filter["search_text"];
    }


    $wheresearch = array();

    $serachfield =array('u.firstname', 'u.lastname','u.email','username');
    if(!empty($search_text)) {
        foreach ($serachfield as $fields) {
            $wheresearch[] = $fields . " like '%" . $search_text . "%'";
        }

        $searchstr = implode(" or ",$wheresearch);
    }

    $cohortmembers = array();

    if ($arr_cohortmembers) {
        foreach ($arr_cohortmembers as $key_cohortmembers) {
            $cohortmembers[] = $key_cohortmembers->userid;
        }
    }

    $str_cohortmembers = implode(",", $cohortmembers);


    if ($str_cohorts) {
        $where[] = "u.id in (".$str_cohortmembers.")";
    }
    if(!empty($searchstr)){
        $where[]  = '( ' .$searchstr . ')';
    }

}
if(!empty($where)){
    $wherestring =  $searchstr = implode(" && ",$where);
}


if (!$table->is_downloading()) {
    echo $OUTPUT->header();


    global $DB;

    $cohorts = $DB->get_records_sql('SELECT * FROM {cohort} WHERE visible=1');

    $cohort_select = "<select multiple style='padding: 5px;max-width: 250px;display: inline-block;' id='sel_cohort' name='sel_cohort[]' >";
    $cohort_select .= "<option value='0'  >".get_string('selectcohort','local_cohort_users')."</option>";

    $cohortarr =array();
    foreach($cohorts as $cohort){
        $cohortarr[$cohort->id] = $cohort->name;

        $selected = "";

        if (in_array($cohort->id,$sel_cohort)) {
            $selected = "selected";
        }
        $cohort_select .= "<option value='" . $cohort->id . "' " . $selected . " >" . $cohort->name . "</option>";

    }

    $cohort_select .= "</select>";

    $search_text = '<span style="float:right;text-align: right;">Search <input type="text" id="search_text" name="search_text" value="'.$search_text.'">';

    $search_text .= '&nbsp;&nbsp;<br><input type="submit" id="btn_submit" class="btn btn-primary" name="btn_submit" value="Filter" style="margin:10px; margin-top: 35px;"></span> ';

    echo '<form action="view.php" method="post">';
    echo $cohort_select . " " . $search_text;
    echo '</form>';

}


$table->set_sql("CONCAT(u.id, u.lastname), u.id, CONCAT(u.firstname ,' ', u.lastname) as username, u.lastaccess, u.email, 'c' as cohort_name, 'au' as awardingbodyid, 'campusname' as campus", "{user} u","{$wherestring}");


$table->define_baseurl("$CFG->wwwroot/local/cohort_users/view.php");

$table->out(10, true);

$pageurl = "$CFG->wwwroot/local/cohort_users/view.php";

if (!$table->is_downloading()) {

    echo '<style>
       
        #downloadtype_download {
        display:none;
        }
        
      
 label[for=downloadtype_download]
        {
            display:none;
        }

    </style>';

    echo $OUTPUT->footer();
}
