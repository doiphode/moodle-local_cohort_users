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
global $CFG,$USER,$DB,$COURSE;


require_once($CFG->dirroot.'/lib/excellib.class.php');

$cid = required_param('cid',PARAM_INT);
$cmid = required_param('cmm',PARAM_INT);

$sql_campusfield = 'SELECT id, name FROM {user_info_field} WHERE shortname="campus"';
$arr_campusfield = $DB->get_record_sql($sql_campusfield);
$campusfieldid = $arr_campusfield->id;

// Print names of all the fields
$no = get_string("no", 'local_cohort_users');
$studentid = get_string("studentid", 'local_cohort_users');
$username = get_string('username');
$awardingbody = get_string("awardingbodyid", 'local_cohort_users');
$hdcampus = get_string("campus", 'local_cohort_users');
$firstname = get_string("firstname");
$lastname = get_string("lastname");
$marker1st = get_string("marker1st", 'local_cohort_users');
$marker2nd = get_string("marker2nd", 'local_cohort_users');
$agreedmark = get_string("agreedmark", 'local_cohort_users');
$comment = get_string("comment", 'local_cohort_users');

$sql_mod = 'SELECT *  FROM {modules} WHERE name="assign"';
$arr_mod = $DB->get_record_sql($sql_mod);
$modid = $arr_mod->id;

$sql_cm = 'SELECT *  FROM {course_modules} WHERE course='. $cid .' AND module=' . $modid . ' AND id=' . $cmid;
$arr_cm = $DB->get_record_sql($sql_cm);
$asid = $arr_cm->instance;

$sql_gradeitem = 'SELECT id, itemname, grademax  FROM {grade_items} WHERE courseid='. $cid .' AND iteminstance=' . $asid . ' AND itemtype="mod" AND itemmodule="assign"';
$arr_gradeitem = $DB->get_record_sql($sql_gradeitem);
$giid = $arr_gradeitem->id;
$itemname = $arr_gradeitem->itemname;
$grademax = $arr_gradeitem->grademax;

$sql_role = 'SELECT id FROM {role} WHERE shortname="student"';
$arr_role = $DB->get_record_sql($sql_role);
$rid = $arr_role->id;

/*
$sql_assignsubmitted = 'SELECT ue.userid, u.id, ag.id, ag.assignment, u.firstname, u.lastname, ag.grade, ag.grader, ag.marking2nd 
FROM {user} u
 JOIN {user_enrolments} ue ON ue.userid = u.id
 JOIN {enrol} e ON e.id = ue.enrolid
 JOIN {course} c ON e.courseid = c.id 
 left JOIN {assign_grades} ag ON (ue.userid = ag.userid AND (ag.assignment=null OR ag.assignment=' . $asid . ')) 
WHERE c.id = ' . $cid . ' ORDER BY ue.userid';
*/

$campus = optional_param('campus',null,PARAM_RAW);
$where = '';
if ($campus != "") {
    $where = ' AND uii.data="' . $campus . '" ';
}

$sql_assignsubmitted = 'SELECT CONCAT(u.id, rand(1000)), ra.userid, c.id, ag.id, ag.assignment, u.username, u.firstname, u.lastname, ag.grade, ag.grader, ag.marking2nd, uii.data as vcampus  
FROM {user} u
INNER JOIN {role_assignments} ra ON ra.userid = u.id
INNER JOIN {context} ct ON ct.id = ra.contextid
INNER JOIN {course} c ON c.id = ct.instanceid
INNER JOIN {role} r ON r.id = ra.roleid
  left join {user_info_data} uii on (u.id = uii.userid AND uii.fieldid = '.$campusfieldid.')
left JOIN {assign_grades} ag ON (u.id = ag.userid AND (ag.assignment=null OR ag.assignment=' . $asid . ')) 
WHERE r.id = ' . $rid . ' AND c.id = ' . $cid . $where . ' ORDER BY ra.userid';


$arr_assignsubmitted = $DB->get_records_sql($sql_assignsubmitted);

// Calculate file name
$shortname = $itemname;
$downloadfilename = $shortname . " - " . date("d M Y") . ".xls";
// Creating a workbook
$workbook = new MoodleExcelWorkbook("-");
// Sending HTTP headers
$workbook->send($downloadfilename);
// Adding the worksheet
$myxls = $workbook->add_worksheet("sheet1");

$rowno = 0;

$myxls->write_string($rowno, 0, $no);
$myxls->write_string($rowno, 1, $username);
$myxls->write_string($rowno, 2, $awardingbody);
$myxls->write_string($rowno, 3, $hdcampus);
$myxls->write_string($rowno, 4, $firstname);
$myxls->write_string($rowno, 5, $lastname);
$myxls->write_string($rowno, 6, $marker1st);
$myxls->write_string($rowno, 7, $marker2nd);
$myxls->write_string($rowno, 8, $agreedmark);
$myxls->write_string($rowno, 9, $comment);

foreach($arr_assignsubmitted as $key_assignsubmitted) {
    $rowno++;

    $agassignment = $key_assignsubmitted->assignment;

    $sql_fieldid = 'SELECT id FROM {user_info_field} WHERE shortname="abid"';
    $arr_fieldid = $DB->get_record_sql($sql_fieldid);
    $fieldid = $arr_fieldid->id;

    $userid = $key_assignsubmitted->userid;

    $sql_awardingbody = 'SELECT data FROM {user_info_data} WHERE userid=' . $userid . ' AND fieldid=' . $fieldid;
    $arr_awardingbody = $DB->get_record_sql($sql_awardingbody);
    $awardingbody = $arr_awardingbody->data;

    $vcampus = $key_assignsubmitted->vcampus;
    $username = $key_assignsubmitted->username;
    $firstname = $key_assignsubmitted->firstname;
    $lastname = $key_assignsubmitted->lastname;
    $marker1st = sprintf('%03.2f', $key_assignsubmitted->grade);
    $marking2nd = sprintf('%03.2f', $key_assignsubmitted->marking2nd);

    $sql_finalgrade = 'SELECT id, finalgrade FROM {grade_grades} WHERE itemid=' . $giid . ' AND userid=' . $userid ;
    $arr_finalgrade = $DB->get_record_sql($sql_finalgrade);
    $itemid = $arr_finalgrade->id;
    $agreedmark = sprintf('%03.2f', $arr_finalgrade->finalgrade);


        $sql_comments = 'SELECT * FROM {assign_commentdd} WHERE userid=' . $userid . ' AND assignid=' . $cmid;
        $arr_comments = $DB->get_record_sql($sql_comments);

        $commentdd = $arr_comments->commentdd;

        if ($commentdd=="other") {
            $comment = $arr_comments->commentother;
        } else {
            $comment = $commentdd;
        }

    //echo "<br/>" . $sql_finalgrade . " / " . $sql_comments  . " " . $firstname . " " . $agreedmark . " " . $commentcontent . "<br/>";

    if ($comment == "[[]]" || $comment == "") {
        $comment = "NS";
        if ($agreedmark>0) {
            $comment = "SB";
        }
    }

    $myxls->write_string($rowno, 0, $rowno);
    $myxls->write_string($rowno, 1, $username);
    $myxls->write_string($rowno, 2, $awardingbody);
    $myxls->write_string($rowno, 3, $vcampus);
    $myxls->write_string($rowno, 4, $firstname);
    $myxls->write_string($rowno, 5, $lastname);
    $myxls->write_string($rowno, 6, $marker1st);
    $myxls->write_string($rowno, 7, $marking2nd);
    $myxls->write_string($rowno, 8, $agreedmark);
    $myxls->write_string($rowno, 9, $comment);

}


$workbook->close();






