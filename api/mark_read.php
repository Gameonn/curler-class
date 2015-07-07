<?php
	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');

	$token=$_REQUEST['token'];
	$notification_id=$_REQUEST['notification_id'] ? $_REQUEST['notification_id'] : '';
	$type=$_REQUEST['type'] ? $_REQUEST['type'] : '';
	
	if (!empty($token) && !empty($notification_id) && !empty($type)) {
		$users_id=getUsersId($token);
		if (!empty($users_id)) {
			
				if($type){
					$success="1";
					$msg="marked read!";
					if( $type == 1){
						$sql="UPDATE `likes` SET `is_red`='y' WHERE `id`=:id";
					}
					elseif( $type == 2){
						$sql="UPDATE `share` SET `is_red`='y'  WHERE `id`=:id";
					}
					elseif( $type == 3){
						$sql="UPDATE `comment` SET `is_red`='y'  WHERE `id`=:id";
					}
					elseif( $type == 4){
						$sql="UPDATE `follow` SET `is_red`='y'  WHERE `id`=:id";
					}
					
					$stmt=$conn->prepare($sql);
					$stmt->bindValue(':id', $notification_id);
					try{ 
						$stmt->execute();
					}
					catch(PDOException $e){ 
						echo $e->getMessage(); 
					}
				}
				else{
					$success="0";
					$msg="Not marked read!";
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
	echo json_encode(array("success"=> $success, "msg"=>$msg));
?>