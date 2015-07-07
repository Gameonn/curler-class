<?php

	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');

	$token=$_REQUEST['token']; 
	$data=array();

	if (!empty($token)) {
	$users_id=getUsersId($token);
		if (!empty($users_id)) {
			$data=logout($users_id);
				$success="1";
				$msg="Logout successful";
		}
		else{
			$success="0";
			$msg="No such user exist!";
		}
	}
	else{
		$success="0";
		$msg="Incomplete Parameters!";
	}
	echo json_encode(array("success"=> $success, "msg"=>$msg));
?>