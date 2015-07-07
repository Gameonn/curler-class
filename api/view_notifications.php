 <?php
	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');

	$token=$_REQUEST['token'];
	$all_notifications=array();
	$result=array();
	$ord = array();
	$like_notification=array();
	$share_notification=array();
	$comment_notification=array();
	$follow_req_notification=array();
	
	if (!empty($token)) {
		$users_id=getUsersId($token);
		if (!empty($users_id)) {
			//$like_notification=viewLikeNotifications($users_id);
			
			$share_notification=viewShareNotifications($users_id);
			$comment_notification=viewCommentNotifications($users_id);
			$follow_req_notification=viewFollowRequestNotifications($users_id);
			
			if(empty($like_notification)) $like_notification=array();
			if(empty($share_notification)) $share_notification=array();
			if(empty($comment_notification)) $comment_notification=array();
			if(empty($follow_req_notification)) $follow_req_notification=array();
			
			if (!empty($like_notification) || !empty($share_notification) || !empty($comment_notification) || !empty($follow_req_notification)) {
				$all_notifications=array_merge_recursive($like_notification, $share_notification, $comment_notification, $follow_req_notification);
				
				foreach ($all_notifications as $key => $value){
				    $ord[] = strtotime($value['created_on']);
				}
				array_multisort($ord, SORT_DESC, $all_notifications);
				$all_notifications= array_slice($all_notifications, 0, 10);
				$success="1";
				$msg="Following are the notifications";
				if($all_notifications){
					$result=$all_notifications;
				}
			 }
			 else{
			$success="1";
			$msg="No notifications exist!";	}
		}

		else{
			$success="0";
			$msg="No such user exist!";	}
	}
	else{
		$success="0";
		$msg="Incomplete parameters!";		
	}
	echo json_encode(array("success"=> $success, "msg"=>$msg, "notifications"=>$result));
?>