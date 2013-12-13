<?php
// This script is released in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
// For any questions contact with DOMINGO FERNANDEZ, dfernandezgo@upsa.es

// This script will empty a bunch of courses of user data.
// It will retain the activities and the structure of the courses.

// unenrol_users, an array containing the roles to be processed
// $data, an object containing all the settings including courseid (without magic quotes)
// $cond, establishes a condition to indetify the courses to be emptied


	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	
	define('CLI_SCRIPT', true);				// To run from the command line. Delete it if you want to run from a browser
	require('../config.php');
    global $USER, $DB;
	$USER = get_admin();	
	$security_number = 4000; 					// Max number of courses permited to be procesed in an execution. It is a security feature.
	$number = 0;							// Counts the number of courses that we actually clean	
 
	/////////////////////////////////////////// Set general parameters for all courses to be cleaned ///////////////////////////////////////////////
	//////// These parameters have been obtained after analyzing  moodlelib.php and they correspond to general parameters in reset_form.php ////////
	$data->MAX_FILE_SIZE = 900000000;
	// General
	$fecha = date_create();				
	$data->reset_start_date = date_timestamp_get($fecha);
	$data->reset_events = 1;
	$data->reset_logs = 1;
	$data->reset_notes = 1;
	$data->reset_comments = 1;
	$data->delete_blog_associations = 1;
	$data->reset_course_completion = 1;		

	// Roles
	// 1 manager, 2 coursecreator, 3 editingteacher, 4 teacher, 5 student, 6 guest, 7 user, 8 frontpage
	// It is possible to expecify several: $p_data->unenrol_users = array(3,5); 
	$data->unenrol_users = array(5); 	
	$data->reset_roles_overrides = 1;
	$data->reset_roles_local = 1;
	
	// Gradebook
	$data->reset_gradebook_items = 1;		//If we include this we don't need to include $data->reset_gradebook_grades
	// $data->reset_gradebook_grades = 1;	//Not necessary if $data->reset_gradebook_items is present
	
	// Groups
	$data->reset_groups_remove = 1;			//If we include this we don't need to include $data->reset_groups_members
	// $data->reset_groups_members = 1;			//Not necessary if $data->reset_groups_remove is present	
	$data->reset_groupings_remove = 1;		//If we include this we don't need to include $data->reset_groupings_members
	// $data->reset_groupings_members = 1;		//Not necessary if $data->reset_groupings_remove is present		
	
	//////////////////////////////////////////////// Select course/courses that you want to clean ////////////////////////////////////////////////////////	
	//$cond = array("id"=>3825);
	$cond = array("tipo"=>"O");
	$course = $DB->get_records('course', $cond);	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$countcourses = $DB->count_records('course', $cond);
	
																			echo "Courses to clean: ".$countcourses."\n";	
	foreach ($course as $co) 
	{
		if($number > $security_number)
		{
									echo "The script has been stopped due to the fact that the the number of operations allowed it has been exceeded.\n";
			exit;
		}
																			echo "\nCleaning Id: ".$co->id." Name: ".$co->fullname."\n";
		unset_especific_parameters($data);

        if ($allmods = $DB->get_records('modules')) 
		{
			foreach ($allmods as $mod) 
			{	
																			echo "\nModule: ".$mod->name."\n";
                if (!$DB->count_records($mod->name, array('course'=>$co->id))) 
				{
																			echo "Module with no instances \n";
                    continue; // skip mods with no instances
                }
																			echo "This module has instances: ".$mod->name."\n";
                $modfile = $CFG->dirroot."/mod/$mod->name/lib.php";
                $mod_reset_course_form_definition = $mod->name.'_reset_course_form_definition';
                $mod_reset__userdata = $mod->name.'_reset_userdata';
                if (file_exists($modfile)) 
				{
                    include_once($modfile);
																			echo "lib.php located for module: ".$mod->name."\n";
                    if (function_exists($mod_reset_course_form_definition)) 
					{
																			
												echo "Module ".$mod->name." will use the parameters present in function _reset_course_form_definition  "."\n";
						set_especific_parameters($data, $mod->name); // Set the parameters to clean the activity
                    } 
					else
					{ 
						if (function_exists($mod_reset__userdata)) 
						{
																			echo "Module ".$mod->name." will use _reset_userdata "."\n";
						}
						else
						{
										// unsupported mods
										echo "Neither _reset_course_form_definition nor _reset_userdata are defined for this module:  ".$mod->name."\n";																			
						}
					}
                } 
				else 
				{
																			echo "Missing lib.php in ".$mod->name." module\n";
                }
            }

        }
		
		$data->reset_start_date_old = $co->startdate;		
		$data->id = $co->id;	
		
		$status = reset_course_userdata($data);	//////////////////+++++++++++++++++++ Invokes the cleaning function	//////////////////+++++++++++++++++++
			
		// enrol_course_delete($data); That's not necessary. Deletes all registered users of the course indicated by $data->id
		
		echo "\n";
		foreach($status as $st)
		{
																			echo "Componet: ". $st['component']. " Item: ". $st['item']."\n";
		}
		$number++;		
																			echo "\nCleaned: ".$number." of ".$countcourses."\n";
	}
																			echo "\nCourses cleaned: ". $number."\n";
	
	function unset_especific_parameters(&$p_data)
	{
		unset($p_data->reset_assignment_submissions);
		unset($p_data->reset_chat);
		unset($p_data->reset_choice);
		unset($p_data->reset_data); 			// If we include this we don't need to include the next ones
		// unset($p_data->reset_data_notenrolled = 1);
		// unset($p_data->reset_data_ratings = 1);
		// unset($p_data->reset_data_comments = 1);

		unset($p_data->reset_forum_all);
		unset($p_data->reset_glossary_all); 	// This englobles all the possible options
		unset($p_data->reset_lesson);
		unset($p_data->reset_quiz_attempts);
		unset($p_data->reset_scorm);
		unset($p_data->reset_survey_answers); 	// If we include this we don't need to include the next one
		// unset(p_$data->reset_survey_analysis = 1);

		unset($p_data->reset_wiki_tags);								
		unset($p_data->reset_wiki_comments);		
	}
	
	function set_especific_parameters(&$p_data, $modulo)
	{
	// These parameters have been obtained from 'mod\'.$modulo.'\lib.php', function $modulo.'_reset_course_form_definition'
	// after analyzing course/reset_form.php  They depend on the activities present in each course 		
		switch ($modulo) 
		{
			case "assignment":
				$p_data->reset_assignment_submissions = 1;
				break;
			case "chat":
				$p_data->reset_chat = 1;
				break;								
			case "choice":
				$p_data->reset_choice = 1;
				break;	
			case "data":
				$p_data->reset_data = 1; 			// If we include this we don't need to include the next ones
				// $p_data->reset_data_notenrolled = 1;
				// $p_data->reset_data_ratings = 1;
				// $p_data->reset_data_comments = 1;
				break;									
			case "feedback":							
				if ($feedbacks = $DB->get_records('feedback', array('course'=>$co->id), 'name')) 
				{
										echo "\nWARNING: Feedbacks Present will not be deleted \n";
				}
				break;	
			case "folder":
				// folder_reset_course_form_definition doesn't exist.
				break;	
			case "forum":
				$p_data->reset_forum_all = 1;
				$p_data->reset_forum_subscriptions =1;
				break;								
			case "glossary":
				$p_data->reset_glossary_all = 1; 	// This englobles all the possible options
				break;	
			case "imscp":
				// imscp_reset_course_form_definition doesn't exist.
				break;	
			case "label":
				// label_reset_course_form_definition doesn't exist.
				break;	
			case "lesson":
				$p_data->reset_lesson = 1;
				break;	
			case "lti":
				// lti_reset_course_form_definition doesn't exist.
				break;									
			case "page":
				//page_reset_course_form_definition doesn't exist.
				break;						
			case "quiz":
				$p_data->reset_quiz_attempts = 1;	
				break;
			case "resource":
				// resource_reset_course_form_definition doesn't exist.
				break;
			case "scorm":
				$p_data->reset_scorm = 1;
				break;	
			case "survey":
				$p_data->reset_survey_answers = 1; 	// If we include this we don't need to include the next one
				//$data->reset_survey_analysis = 1;
				break;	
			case "url":
				//url_reset_course_form_definition doesn't exist.
				break;	
			case "wiki":
				$p_data->reset_wiki_tags = 1;
				$p_data->reset_wiki_comments = 1;
				break;		
			case "workshop":
				//workshop_reset_course_form_definition doesn't exist.
				break;									
		}
	}
	?>