<?php

//{BANNER}

	require_once 'class.usersimporter.php';

    /* echo "<pre>";
    print_r($_REQUEST);
    echo "</pre>"; */
    

    if ( isset($_REQUEST['dbconfigfile']) && $_REQUEST['dbconfigfile'] == "1" ) {
        require_once 'config.php';
        $db['host'] = $config['host'];
        $db['name'] = $config['name'];
        $db['username'] = $config['username'];
        $db['password'] = $config['password'];
        $db['prefix'] = $config['prefix'];
    }else{
        $db['host'] = $_REQUEST['dbhost'];
        $db['name'] = $_REQUEST['dbname'];
        $db['username'] = $_REQUEST['dbusername'];
        $db['password'] = $_REQUEST['dbpassword'];
        $db['prefix'] = $_REQUEST['dbprefix'];
    }
	
	$db['userstable'] = $db['prefix']."users";
	$db['usergrouptable'] = $db['prefix']."user_usergroup_map";
	
	$db['usersgroup'] = $_REQUEST['usersgroup']; // groupID 2 = registred
	$db['usersblocked'] = $_REQUEST['usersblocked']; 
	$db['usersactivation'] = $_REQUEST['usersactivation']; 
	$db['userssendmail'] = $_REQUEST['userssendmail']; 
	$db['usersreset'] = $_REQUEST['usersreset']; 

    $db['usersmoregroups'] = $_REQUEST['usersmoregroups'];  
    $db['userautopassword'] = $_REQUEST['userautopassword'];  
    
	
	$usersfile = $_REQUEST['usersfile']; 

    /* echo "<pre>";
    print_r($db);
    echo "</pre>";
    return; */
	
	$upload = new UploadJoomlaUsers($db,false);
	$data = $upload->insertUsers($usersfile);
	
	echo json_encode($data);
	
	
?>
