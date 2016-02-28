<?php

//{BANNER}

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
