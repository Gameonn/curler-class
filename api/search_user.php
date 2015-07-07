<?php
	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');

	$token=$_REQUEST['token'];
	$searchkey=$_REQUEST['searchkey'];
	$data=array();
	if (!empty($token) && !empty($searchkey)) {
		$users_id1=getUsersId($token);
			if (!empty($users_id1)) {
			$result=getAllUsers($searchkey, $users_id1);
				if (!empty($result)) {
					$success = "1";
					$msg="users exist!";
					$data=$result;
				}
				else{
					$success="0";
					$msg="No user exist with this name!";
				}
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
			echo json_encode(array("success"=>$success, "msg"=>$msg, "data"=>$data));
?>