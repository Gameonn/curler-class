<?php
	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');

	$share_id=$_REQUEST['share_id'];
	
	if (!empty($share_id)) {
			deletePost($share_id);
			$success = "1";
			$msg="post deleted!";
		}
	else{
		$success="0";
		$msg="Incomplete Parameters!";
	}
		echo json_encode(array("success"=>$success, "msg"=>$msg));

?>