<?php

/*! 
 * ************************************************************************************ 
 *  Joomla Users Importer | Import users from CSV file into Joomla 3 database 
 *  Version 2.0.2 - Date: 28/02/2016 
 *  HomePage: https://github.com/Gix075/joomla-users-importer#readme 
 * ************************************************************************************ 
*/ 


class UploadJoomlaUsers  {
	
	function __construct($db) {
		
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
		$this->usersActivation = intval($db['usersactivation']); 
		$this->usersBlocked = intval($db['usersblocked']);
		$this->usersSendEmail = intval($db['userssendmail']);
		$this->userPswdReset = intval($db['usersreset']);
				
		// Upload Results
		$this->result = array();
		$this->result['result'] = "";
		$this->result['message'] = "";
		$this->result['logs'] = null;
		
		// Logs
		$this->LogFile = "logs/log__".date(dFY)."_".date(His).".txt";
		$this->Logs->UsersInsert = array();
		$this->Logs->UsersGroupInsert = array();
		$this->LogFileIntro = "\n";
		$this->LogFileIntro .= "+-----------------------------------------------------+\n";
		$this->LogFileIntro .= "	UPLOAD JOOMLA USER LOG\n";
		$this->LogFileIntro .= "	date: ".date(dFY)." time: ".date(H.i.s)."\n";
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
			return $this->result;
		} 
			
		$csv = file_get_contents("../csv/".$csv);
		$users = explode("\r", $csv);
		
		$users_count = count($users);
		$users_duplicated_error = 0;
		$users_isert_success = 0;
		$users_isert_error = 0;
		$users_group_success = 0;
		$users_group_error = 0;
		
		for($i=0; $i<$users_count; $i++) {
			
			$usersValues = explode(",", $users[$i]);
			$usersValues[0] = str_replace("\n", "", $usersValues[0]);
			$name = $usersValues[0]." ".$usersValues[1];
			$username = strtolower($usersValues[0])."_".strtolower($usersValues[1]);
			$email = $usersValues[2];
			$password = md5("password_".$username);
			
			$values = "'".$name."', "."'".$username."', "."'".$email."', "."'".$password."'";
			$values = $values.", ".$this->usersBlocked.", ".$this->usersSendEmail.", ".$this->usersActivation.", ".$this->userPswdReset;
			
			$queryInsert = "INSERT INTO ".$this->dbUsersTable." (name, username, email, password, block, sendEmail, activation, requireReset) VALUES (".$values.");";
			$querySelect = "SELECT * FROM ".$this->dbUsersTable." WHERE username='".$username."';";
			
			$userCheck = $this->db->query($querySelect);
			$userCheck = $userCheck->fetch_array();
			
			if (count($userCheck) > 0) {
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
					
					// Update Group
					// ************************
					if ($this->dbGroupId > 0) {
						$groupUpdate = $this->userGroupInsert($userId[1]);
						if ($groupUpdate['result'] === true) {
							$users_group_success++;
							$this->Logs->UsersGroupInsert['success'][$i] = "	> GROUP UPDATE OK | User: ".$name." - Username=".$username." - ID: ".$userId[1]." added to GroupId ".$this->dbGroupId."\n";
						}else{
							$users_group_error++;	
							$this->Logs->UsersGroupInsert['error'][$i] = "	> GROUP UPDATE ERROR | User: ".$name." - Username=".$username." - ID: ".$userId[1]." NOT added to GroupId ".$this->dbGroupId." | ".$groupUpdate['result']."\n";
						}
					}else{
						$users_group_success = "NOT Requested!";
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
		
		for ($i=0; $i < count($this->Logs->UsersInsert['success']) ; $i++) { 
			$logText .= $this->Logs->UsersInsert['success'][$i]."\n";
		}
		
		$logText .= "\n";	
		$logText .= "	*************************************** \n";	
		$logText .= "		Errors \n";
		$logText .= "	*************************************** \n";
		$logText .= "\n";
		
		for ($i=0; $i < count($this->Logs->UsersInsert['errors']) ; $i++) { 
			$logText .= $this->Logs->UsersInsert['errors'][$i]['msg']."\n";
		}

		$logText .= "\n";	
		$logText .= "	*************************************** \n";	
		$logText .= "		DUPLICATED \n";
		$logText .= "	*************************************** \n";
		$logText .= "	Following users are duplicated. Verify on your database \n";
		$logText .= "\n";
		
		for ($i=0; $i < count($this->Logs->UsersInsert['duplicated']) ; $i++) { 
			$logText .= $this->Logs->UsersInsert['duplicated'][$i]."\n";
		}
		
		$logText .= "\n";	
		$logText .= "	*************************************** \n";	
		$logText .= "		GROUP UPDATE \n";
		$logText .= "	*************************************** \n";
		$logText .= "\n";
		
		for ($i=0; $i < count($this->Logs->UsersGroupInsert['success']) ; $i++) { 
			$logText .= $this->Logs->UsersGroupInsert['success'][$i]."\n";
		}
		
		for ($i=0; $i < count($this->Logs->UsersGroupInsert['error']) ; $i++) { 
			$logText .= $this->Logs->UsersGroupInsert['error'][$i]."\n";
		}
			 
		$logcontents = $this->LogFileIntro.$logText;
		file_put_contents("../".$this->LogFile, $logcontents);
		$this->result['logs'] = $this->LogFile;	

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