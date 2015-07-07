<?php
	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');

	$token=$_REQUEST['token']; 
	$paging_enabled=$_REQUEST['paging_enabled'] ? $_REQUEST['paging_enabled'] : 0;
	$pageno = (int) (!isset($_REQUEST['pageno']) ? 1 : $_REQUEST['pageno']);
	$row_count = 30;
	$offset = ($pageno * $row_count) - $row_count;
	$data=array();
	if (!empty($token)) {
		$users_id1=getUsersId($token);
		if (!empty($users_id1)) {
			if($paging_enabled)
				$result = getPageDetailsWthPaging($users_id1, $offset, $row_count); 
			else
				$result = getPageDetailsWthoutPaging($users_id1);
			if ($result){
				$success="1";
				$msg="Posts exist!";
				$data=$result;
			}
			else{
				$success="1";
				$msg="No post exist!";
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
		echo json_encode(array("success" => $success, "msg" => $msg, "data"=>$data));
?>