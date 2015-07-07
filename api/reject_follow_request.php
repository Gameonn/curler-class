<?php
	require_once('../phpInclude/dbconn.php');
    require_once('../phpInclude/AdminClass.php');

	$token=$_REQUEST['token'];
    //$follow_id=$_REQUEST['follow_id'];
	$users_id2 = $_REQUEST['users_id2'];
	
	$data=array();
	$followers=array();
	$followings=array();
	if (!empty($token) && !empty($users_id2)){
		$users_id1=getUsersId($token);
		if (!empty($users_id1)) {

		$sql="DELETE FROM `follow` WHERE users_id1 = :users_id2 AND users_id2 = :users_id1";
		$stmt=$conn->prepare($sql);
		$stmt->bindParam(':users_id2',$users_id2);
		$stmt->bindParam(':users_id1',$users_id1);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			$e->getMessage();
		}
		$success = "1";
		$msg="Request Rejected!";
		
		$result=getAllFollowRequests($users_id1);
		$result1=getAllFollowers($users_id1);
		$result2=getAllFollowing($users_id1);
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
		$msg="Incomplete Parameters!";
	}
		echo json_encode(array("success"=>$success, "msg"=>$msg, "data"=>$data, "followers"=>$followers, "followings"=>$followings));


?>