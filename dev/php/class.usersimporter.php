<?php

//{BANNER}

class UploadJoomlaUsers  {
	
	function __construct($db,$debug) {

        $this->debug = $debug;

        if($this->debug == true) {
            echo "<pre>";
            print_r($db);
            echo "</pre>";
        }
		
		// DB settings
		$this->dbHost = $db['host'];           
		$this->dbName = $db['name'];          
		$this->dbUser = $db['username'];      
		$this->dbPass = $db['password'];
		$this->dbUsersTable = $db['userstable'];
		$this->dbGroupTable = $db['usergrouptable'];
		$this->db = $this->dbConnect();
		
		// User settings
		$this->dbGroupId = intval($db['usersgroup']);
        $this->dbGroupIds = ( isset($db['usersmoregroups']) && !empty($db['usersmoregroups']) ) ? explode(',',$db['usersmoregroups']) : false;
		$this->usersActivation = intval($db['usersactivation']); 
		$this->usersBlocked = intval($db['usersblocked']);
		$this->usersSendEmail = intval($db['userssendmail']);
		$this->userPswdReset = intval($db['usersreset']);
        $this->autoPassword = intval($db['userautopassword']);

        /* $this->dbGroupId = $db['usersgroup'];
		$this->usersActivation = $db['usersactivation']; 
		$this->usersBlocked = $db['usersblocked'];
		$this->usersSendEmail = $db['userssendmail'];
		$this->userPswdReset = $db['usersreset']; */
				
		// Upload Results
		$this->result = array();
		$this->result['result'] = "";
		$this->result['message'] = "";
		$this->result['logs'] = null;
		
		// Logs
		$this->LogFile = "logs/log__".date('dFY')."_".date('His').".txt";
        $this->Logs = new stdClass();
		$this->Logs->UsersInsert = array();
		$this->Logs->UsersGroupInsert = array();
		$this->LogFileIntro = "\n";
		$this->LogFileIntro .= "+-----------------------------------------------------+\n";
		$this->LogFileIntro .= "	UPLOAD JOOMLA USER LOG\n";
		$this->LogFileIntro .= "	date: ".date('dFY')." time: ".date('H.i.s')."\n";
		$this->LogFileIntro .= "+-----------------------------------------------------+\n";
	
	}
	
	/* Database Connection */
	/* ************************************************ */
	private function dbConnect() {
		$mysql = new mysqli($this->dbHost, $this->dbUser, $this->dbPass, $this->dbName);
		
		if ($mysql->connect_error) {
		    $this->result['result'] = "fail";
			$this->result['message'] = "Ops, DB Error: ".$mysql->connect_error." <br>Verify your data!";
		}
	
		return $mysql;
	}

	
	/* Insert Users into Database */
	/* ************************************************ */
	public function insertUsers($csv) {
			
		// Test DB Connection
		if ($this->result['result'] == "fail") {
            if($this->debug == true) echo $this->result['message'];
			return $this->result;
		} 
			
		$csv = file_get_contents("../csv/".$csv);
		//$users = explode("\r", $csv);
		//$users = explode("\n", $csv);
		$users = explode(PHP_EOL, $csv);

        if($this->debug == true) {
            echo "<pre>";
            print_r($users);
            echo "</pre>";
            //return;
        }
		
		$users_count = count($users);
		$users_duplicated_error = 0;
		$users_isert_success = 0;
		$users_isert_error = 0;
		$users_group_success = 0;
		$users_group_error = 0;
		
		for($i=0; $i < $users_count; $i++) {
			
			$usersValues = explode(",", $users[$i]);
			//$usersValues[0] = str_replace("\n", "", $usersValues[0]);
			$name = $usersValues[0]." ".$usersValues[1];
			$username = strtolower($usersValues[0])."_".strtolower($usersValues[1]);
			$username = str_replace(" ", "", $username);
			$email = $usersValues[2];

            if( $this->autoPassword == 1 ) {
                $password = md5("password_".$username);
            }else{
                $password = md5($usersValues[3]);
            }
			
			
			$values = "'".$name."', "."'".$username."', "."'".$email."', "."'".$password."'";
			$values = $values.", ".$this->usersBlocked.", ".$this->usersSendEmail.", ".$this->usersActivation.", ".$this->userPswdReset;
			
			$queryInsert = "INSERT INTO ".$this->dbUsersTable." (name, username, email, password, block, sendEmail, activation, requireReset) VALUES (".$values.");";
			$querySelect = "SELECT * FROM ".$this->dbUsersTable." WHERE username='".$username."';";
			
			$userCheck = $this->db->query($querySelect);
			$userCheck = $userCheck->fetch_array();
			
			if (!empty($userCheck)) {

                if( $this->debug === true ) {
                    echo "<pre>";
                    print_r($userCheck);
                    echo "</pre>";
                }

				$users_duplicated_error++;
				$users_isert_error++;
				$this->Logs->UsersInsert['duplicated'][$i] = "	> DUPLICATED | User: ".$name." - Username: ".$username."\n";
				$this->Logs->UsersInsert['errors'][$i]['error'] = "duplicate";
				$this->Logs->UsersInsert['errors'][$i]['msg'] = "	> ERROR | User: ".$name." (username=".$username.") already exists inside database!\n";
			}else{
				if ($this->db->query($queryInsert) === TRUE) {
				    	
				    $users_isert_success++;
					$userId = $this->getUserID($username);
					$this->Logs->UsersInsert['success'][$i]= "	> OK | User: ".$name." - Username=".$username." - ID: ".$userId[1]." inserted into database!\n";
					
                    if( $this->debug === true ) {
                        echo $this->Logs->UsersInsert['success'][$i]."<br>";
                    }

					// Update Group
					// ************************
					if ($this->dbGroupId > 0) {
						$groupUpdate = $this->userGroupInsert($userId[1]);
						if ($groupUpdate['result'] === true) {
							$users_group_success++;
							$this->Logs->UsersGroupInsert['success'][$i] = "	> GROUP UPDATE OK | User: ".$name." - Username=".$username." - ID: ".$userId[1]." added to GroupId ".$this->dbGroupId."\n";
                            if( $this->debug === true ) {
                                echo $this->Logs->UsersGroupInsert['success'][$i]."<br>";
                            }
                        }else{
							$users_group_error++;	
							$this->Logs->UsersGroupInsert['error'][$i] = "	> GROUP UPDATE ERROR | User: ".$name." - Username=".$username." - ID: ".$userId[1]." NOT added to GroupId ".$this->dbGroupId." | ".$groupUpdate['result']."\n";
                            if( $this->debug === true ) {
                                echo $this->Logs->UsersGroupInsert['error'][$i]."<br>";
                            }
                        }
					}else{
						$users_group_success = "NOT Requested!";
                        if( $this->debug === true ) {
                            echo $users_group_success."<br>";
                        }
					}


                    /* Multiple group */
                    if( $this->dbGroupIds != false && is_array($this->dbGroupIds) && !empty($this->dbGroupIds) ) {
                        
                        $multiple_groups = $this->userMultipleGroupInsert($userId[1],$this->dbGroupIds);

                        if($this->debug == true) {
                            echo "Multiple groups requested<br>";
                            echo "<pre>";
                            print_r($multiple_groups);
                            echo "</pre>";
                            echo "<pre>";
                            print_r($multiple_groups);
                            echo "</pre>";
                        }

                        if($multiple_groups['result'] == true) {
                            $users_group_success++;
                            $this->Logs->UsersGroupInsert['success'][$i] = "	> MULTIPLE GROUP UPDATE OK | User: ".$name." - Username=".$username." - ID: ".$userId[1]." added to Groups ".implode(',',$this->dbGroupIds)."\n";
                        }else{
                            $users_group_error++;	
							$this->Logs->UsersGroupInsert['error'][$i] = "	> GROUP UPDATE ERROR | User: ".$name." - Username=".$username." - ID: ".$userId[1]." NOT added to GroupId ".$this->dbGroupIds." | ".$multiple_groups['msg']."\n";
                        }
                    }
                    
					
				
				} else {
					$users_isert_error++;
					$this->Logs->UsersInsert['errors'][$i]['error'] = "mysql";
					$this->Logs->UsersInsert['errors'][$i]['msg'] = "	> ERROR | User: ".$name." (username=".$username.") MySQL Error: ".$this->db->error."\n";
				}	
			}
						
		}// end for	
		
		$logText = "\n";
		$logText .= " 	Total Users: ".$users_count." \n";
		$logText .= " 	Users Inserted: ".$users_isert_success." \n";
		$logText .= " 	Users NOT Inserted: ".$users_isert_error." \n";
		$logText .= " 	Users Duplicated: ".$users_duplicated_error." \n";
		
		// FAIL: Insert 100% Failed
		if ($users_isert_success == 0) {
			$this->result['result'] = "fail";
			$this->result['message'] = "0 users added on database!";	
		}
		
		// WARNING: Some records are failed
		if ($users_isert_error > 0 && $users_isert_success > 0) {
			$this->result['result'] = "warning";
			$this->result['message'] = $users_isert_success." users added on database, but ".$users_isert_error." users insert are failed! See logs for mor info.";		
		}
		
		// SUCCESS: Insert 100% Success
		if ($users_isert_error == 0 && $users_isert_success != 0) {
			$this->result['result'] = "success";
			$this->result['message'] = $users_isert_success." users added on database!";
		}
			
		$logText .= "\n";	
		$logText .= "	*************************************** \n";	
		$logText .= "		Users Added \n";
		$logText .= "	*************************************** \n";
		$logText .= "\n";

        if( !empty($this->Logs->UsersInsert['success']) ) {
            foreach($this->Logs->UsersInsert['success'] as $single_log) {
                $logText .= $single_log;
            }
        }
		
		/* for ($i=0; $i < count($this->Logs->UsersInsert['success']) ; $i++) { 
			$logText .= $this->Logs->UsersInsert['success'][$i]."\n";
		} */
		
		$logText .= "\n";	
		$logText .= "	*************************************** \n";	
		$logText .= "		Errors \n";
		$logText .= "	*************************************** \n";
		$logText .= "\n";
		

        if( !empty($this->Logs->UsersInsert['errors']) ) {
            foreach($this->Logs->UsersInsert['errors'] as $single_log) {
                $logText .= $single_log['msg'];
            }
        }

		/* for ($i=0; $i < count($this->Logs->UsersInsert['errors']) ; $i++) { 
			$logText .= $this->Logs->UsersInsert['errors'][$i]['msg']."\n";
		} */

		$logText .= "\n";	
		$logText .= "	*************************************** \n";	
		$logText .= "		DUPLICATED \n";
		$logText .= "	*************************************** \n";
		$logText .= "	Following users are duplicated. Verify on your database \n";
		$logText .= "\n";
		
        if( !empty($this->Logs->UsersInsert['duplicated']) ) {
            foreach($this->Logs->UsersInsert['duplicated'] as $single_log) {
                $logText .= $single_log;
            }
        }

		/* for ($i=0; $i < count($this->Logs->UsersInsert['duplicated']) ; $i++) { 
			$logText .= $this->Logs->UsersInsert['duplicated'][$i]."\n";
		} */
		
		$logText .= "\n";	
		$logText .= "	*************************************** \n";	
		$logText .= "		GROUP UPDATE \n";
		$logText .= "	*************************************** \n";
		$logText .= "\n";

        if( !empty($this->Logs->UsersGroupInsert['success']) ) {
            foreach($this->Logs->UsersGroupInsert['success'] as $single_log) {
                $logText .= $single_log;
            }
        }

        if( !empty($this->Logs->UsersGroupInsert['error']) ) {
            foreach($this->Logs->UsersGroupInsert['error'] as $single_log) {
                $logText .= $single_log;
            }
        }
		
		/* for ($i=0; $i < count($this->Logs->UsersGroupInsert['success']) ; $i++) { 
			$logText .= $this->Logs->UsersGroupInsert['success'][$i]."\n";
		}
		
		for ($i=0; $i < count($this->Logs->UsersGroupInsert['error']) ; $i++) { 
			$logText .= $this->Logs->UsersGroupInsert['error'][$i]."\n";
		} */
			 
		$logcontents = $this->LogFileIntro.$logText;
		file_put_contents("../".$this->LogFile, $logcontents);
		$this->result['logs'] = $this->LogFile;	

        if( $this->debug === true ) {
            echo "<h3>LOGS</h3>";
            echo "<pre>";
            print_r($logcontents);
            echo "</pre>";
        }

        if( $this->debug === true ) {
            echo "<h3>Results</h3>";
            echo "<pre>";
            print_r($this->result);
            echo "</pre>";
        }

		return  $this->result;
		
	}
	
	/* User Group Insert */
	/* ************************************************ */
	private function userGroupInsert($userid) {
		$groupUpdate = array();
		$sqlGroupInsert = "INSERT INTO ".$this->dbGroupTable." (user_id, group_id) VALUES (".$userid.",".$this->dbGroupId.");";
		if ($this->db->query($sqlGroupInsert) === TRUE) {
			$groupUpdate['result'] = true;
		}else{
			$groupUpdate['result'] = false;
			$groupUpdate['msg'] = $this->db->error;
		}
		
		return $groupUpdate;
	}

    private function userMultipleGroupInsert($userid,$groups) {
		
        $groupUpdate = array();
        $errors = array();
		
        if( is_array($groups) && !empty($groups) ) {
            
            foreach( $groups as $key => $group ) {
                $sqlGroupInsert = "INSERT INTO ".$this->dbGroupTable." (user_id, group_id) VALUES (".$userid.",".$group.");";
                if ($this->db->query($sqlGroupInsert) === false) {
                    $errors[$key]['id'] = $group;
                    $errors[$key]['msg'] = $this->db->error . " query: " . $sqlGroupInsert;
                }
            }

        }

        if( !empty($errors) ) {
            $groupUpdate['result'] = false;
            $groupUpdate['msg'] = "The are some errors during updating groups: ";
            foreach ($errors as $error) {
                $groupUpdate['msg'] .= "group_id: ". $error['id'] . "error: " . $error['msg'] . " -- ";
            }
        }else{
            $groupUpdate['result'] = true;
            $groupUpdate['msg'] = "Groups updated";
        }

		return $groupUpdate;
	}
	
	/* Get User ID from Database */
	/* ************************************************ */
	private function getUserID($username) {
		$selectResult = array();
		$userId = array();
		$query = "SELECT id FROM ".$this->dbUsersTable." WHERE username='".$username."';";
		$selectResult = $this->db->query($query);
		if ($selectResult > 0) {
			$selectResult = $selectResult->fetch_assoc();
			$userId[0] = true;
			$userId[1] = $selectResult['id'];
		}else{
			$userId[0] = false;
			$userId[1] = "";
		}
		return $userId;
	}
	
}

?>