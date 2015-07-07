<?php
	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');

	$token=$_REQUEST['token'] ? $_REQUEST['token'] : '';
	$others_id=$_REQUEST['others_id'] ? $_REQUEST['others_id'] : '';
	
	$paging_enabled=$_REQUEST['paging_enabled'] ? $_REQUEST['paging_enabled'] : 0;
	$pageno = (int) (!isset($_REQUEST['pageno']) ? 1 : $_REQUEST['pageno']);
	$row_count = 10;
	$offset = ($pageno * $row_count) - $row_count;

	$users_details=array();
	$all_posts=array();
	$is_following=array();
	if (!empty($token) && empty($others_id)) {
		$users_id=getUsersId($token);
		if (!empty($users_id)) {
			$result=getUserDetails($users_id);
			if($paging_enabled)
				$result1=getAllPostsByUserwthpaging($users_id, $offset, $row_count); 
			else
				$result1=getAllPostsByUserwthoutpaging($users_id);

			if(!empty($result)){
				$success = "1";
				$msg="This is Users Profile";
				$users_details=$result;
				if(!empty($result1))
					$all_posts=$result1;
			}
				
			else{
				$success="0";
				$msg="Nothing to show";
			}
		}
		else{
			$success="0";
			$msg="No such user exist!";	}
	}
	
	elseif(!empty($token) && !empty($others_id)){
		$users_id=getUsersId($token);
		if (!empty($users_id)) {
			$result2=getOtherUserDetails($users_id, $others_id);
			
			if($paging_enabled)
				$result3=getAllPostsByUserwthpaging($others_id, $offset, $row_count); 
			else
				$result3=getAllPostsByUserwthoutpaging($others_id);

			if(!empty($result2)){
				$success = "1";
				$msg="This is Users Profile";
				$users_details=$result2;
				if(!empty($result3))
					$all_posts=$result3;
			}
				
			else{
				$success="0";
				$msg="Nothing to show";
			}
		}
		else{
			$success="0";
			$msg="No such user exist!";	}
	}
	
	else{
		$success="0";
		$msg="Incomplete parameters!";	}

		echo json_encode(array("success"=>$success, "msg"=>$msg, "user details"=>$users_details, "all posts by user"=>$all_posts));
?>