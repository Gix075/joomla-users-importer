<?php

/*! 
 * ************************************************************************************ 
 *  Joomla Users Importer | Import users from CSV file into Joomla 3 database 
 *  Version 2.0.1 - Date: 28/02/2016 
 *  HomePage: https://github.com/Gix075/joomla-users-importer#readme 
 * ************************************************************************************ 
*/ 


	require 'class.usersimporter.php';

	$db['host'] = $_REQUEST['dbhost'];
	$db['name'] = $_REQUEST['dbname'];
	$db['username'] = $_REQUEST['dbusername'];
	$db['password'] = $_REQUEST['dbpassword'];
	$db['userstable'] = str_replace("_", "", $_REQUEST['dbprefix'])."_users";
	$db['usergrouptable'] = $_REQUEST['dbprefix']."_user_usergroup_map";
	
	$db['usersgroup'] = $_REQUEST['usersgroup']; // groupID 2 = registred
	$db['usersblocked'] = $_REQUEST['usersblocked']; 
	$db['usersactivation'] = $_REQUEST['usersactivation']; 
	$db['userssendmail'] = $_REQUEST['userssendmail']; 
	$db['usersreset'] = $_REQUEST['usersreset']; 
	
	$usersfile = $_REQUEST['usersfile']; 
	
	$upload = new UploadJoomlaUsers($db);
	$data = $upload->insertUsers($usersfile);
	
	echo json_encode($data);
	
	
?>
