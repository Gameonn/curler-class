<?php
	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');

	$token=$_REQUEST['token'];
	$data=array();
	$followers=array();
	$followings=array();
	if (!empty($token)) {
		$users_id=getUsersId($token);
		if (!empty($users_id)) {
			$result=getAllFollowRequests($users_id);
			$result1=getAllFollowers($users_id);
			$result2=getAllFollowing($users_id);
			$success = "1";
			$msg="Request exist!";
			if(!empty($result))
				$data=$result;
			if(!empty($result1))
				$followers=$result1;
			if(!empty($result2))
				$followings=$result2;
			
		}
		else{
			$success="0";
			$msg="No such user exist!";
		}
	}
	else{
		$success="0";
		$msg="Incomplete parameters!";
	}

		echo json_encode(array("success"=>$success, "msg"=>$msg, "data"=>$data, "followers"=>$followers, "followings"=>$followings));


?>