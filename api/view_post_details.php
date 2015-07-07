<?php
	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');
	
	$share_id=$_REQUEST['share_id'];
	$token=$_REQUEST['token'];
	$type=$_REQUEST['type'] ? $_REQUEST['type'] : '';
	$notification_id=$_REQUEST['notification_id'] ? $_REQUEST['notification_id'] : '';
	$data=array();
	
	if (!empty($token) && !empty($share_id)) {
		$users_id=getUsersId($token);
		if (!empty($users_id)) {
			$post_read = postRead($users_id, $share_id);
				if($post_read)
					$post_details=getPostDetails($share_id);
			
			if(!empty($post_details)){
				$success = "1";
				$msg="Following are the details";
				$data=$post_details;
				if($type){
					if( $type == 1){
						$sql="UPDATE `likes` SET `is_red`='y' WHERE `id`=:notification_id";
					}
					elseif( $type == 2){
						$sql="UPDATE `share` SET `is_red`='y' WHERE `id`=:notification_id";
					}
					elseif( $type == 3){
						$sql="UPDATE `comment` SET `is_red`='y' WHERE `id`=:notification_id";
					}
					elseif( $type == 4){
						$sql="UPDATE `follow` SET `is_red`='y' WHERE `id`=:notification_id";
					}
						
					$stmt=$conn->prepare($sql);
					$stmt->bindValue(':notification_id', $notification_id);
					try{ 
						$stmt->execute();
					}
					catch(PDOException $e){ 
						echo $e->getMessage(); 
					}
				}
			}	
			else{
				$success="0";
				$msg="No details to post";
			}
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

		echo json_encode(array("success"=>$success, "msg"=>$msg, "data"=>$data));
?>