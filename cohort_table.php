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
global $CFG,$USER, $DB;

require "$CFG->libdir/tablelib.php";
//
class report_table extends table_sql {

    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    function __construct($uniqueid) {
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = array('username','email', 'awardingbodyid',"campus",'cohort_name','lastaccess');
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = array(get_string('firstname/surname','local_cohort_users'),get_string('email'), get_string('awardingbodyid','local_cohort_users'), get_string('campus','local_cohort_users'), get_string('cohort','local_cohort_users'), get_string('lastaccess'));
        
        $this->define_headers($headers);

        $this->no_sorting('awardingbodyid');
        $this->no_sorting('campus');
        $this->no_sorting('cohort_name');

    }



    function col_username($values){
        $username = format_string($values->username);
        return $username;
    }

    function col_lastaccess($values){
		$strlastaccess = format_time(time() - $values->lastaccess);
		if ($values->lastaccess==0) {
			$strlastaccess = "Never";
			}
		return $strlastaccess;
	}

    function col_awardingbodyid($values){
        global $DB;
        $sql_abid = 'SELECT id, name FROM {user_info_field} WHERE shortname="abid"';
        $arr_abid = $DB->get_record_sql($sql_abid);
        $abid = $arr_abid->id;
        $abid_fieldname = $arr_abid->name;

        $sql_usersabid = 'SELECT * FROM {user_info_data} WHERE userid='. $values->id .' && fieldid=' . $abid;
        if ($arr_usersabid = $DB->get_record_sql($sql_usersabid)) {
            $awardingbodyid = $arr_usersabid->data;
        } else {
            $awardingbodyid = "";
        }

        $awardingbodyid = format_string($awardingbodyid);
        return $awardingbodyid;
    }

    function col_campus($values){
        global $DB;
        $sql_uinfo = 'SELECT id, name FROM {user_info_field} WHERE upper(shortname)="CAMPUS"';
        $arr_uinfo = $DB->get_record_sql($sql_uinfo);
        $uinfoid = $arr_uinfo->id;
        $uinfoid_fieldname = $arr_uinfo->name;

        $sql_userscampus = 'SELECT data FROM {user_info_data} WHERE userid='. $values->id .' && fieldid=' . $uinfoid;
        if ($arr_userscampus = $DB->get_record_sql($sql_userscampus)) {
            $campus = $arr_userscampus->data;
        } else {
            $campus = "";
        }

        $campus = format_string($campus);
        return $campus;
    }

    function col_cohort_name($values){
        global $SESSION;
        $cohortid = "";
        if(!empty($SESSION->filter["sel_cohort"])) {
            $sel_cohort = $SESSION->filter["sel_cohort"];
            $str_cohorts = implode(",", $sel_cohort);

            $cohortid = "&& cohortid in ($str_cohorts)";
        }

        global $DB;
        $sql_cohortmembers = "SELECT * FROM {cohort_members} WHERE userid=  $values->id  $cohortid";
        if ($arr_cohortmembers = $DB->get_records_sql($sql_cohortmembers)) {
            $cohort_namebr = "";
            $cohort_namechr = "";
            $count =0;
            foreach($arr_cohortmembers as $key_cohortmembers) {

                $sql_nameofcohort = 'SELECT * FROM {cohort} WHERE id=' . $key_cohortmembers->cohortid;
                if ($arr_nameofcohort = $DB->get_record_sql($sql_nameofcohort)) {
                        $cohort_namebr .= $arr_nameofcohort->name . "<br/>";
                        if($count >0){
                          $cohort_namechr .= chr(10) . chr(13).$arr_nameofcohort->name ;
                        }else{
                            $cohort_namechr .= $arr_nameofcohort->name ;
                        }


                }


            }

        } else {
            $cohort_namebr = "";
            $cohort_namechr = "";
        }

        if (!$this->is_downloading()) {
            $cohort_name = $cohort_namebr;
        } else {
            $cohort_name = $cohort_namechr;
        }

//        $cohort_name = format_string($values->cohort_name);
//        $cohort_name = "ABC" . chr(10) . chr(13) . "DEF";
        return $cohort_name;
    }


}