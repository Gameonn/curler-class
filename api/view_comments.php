<?php
	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');
	
	
	$token = $_REQUEST['token']; 
	$share_id = $_REQUEST['share_id'];
	$data=array();
    if (!empty($token) && !empty($share_id)) {
        $users_id=getUsersId($token);
        if (!empty($users_id)) {
			$result=getAllComments($share_id, $users_id);
			if(!empty($result)){
				$success = "1";
				$msg="Following are the comments";
				$data=$result;
			}
			else{
				$success="1";
				$msg="No comments to this post";
			}
		}
		else{
			$success="0";
			$msg="No such user exist!";
		}
	}
	else{
		$success="0";
		$msg="Incomplete Parameters";
	}
		echo json_encode(array("success"=>$success, "msg"=>$msg, "data"=>$data));


?>