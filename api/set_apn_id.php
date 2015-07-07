<?php
	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');

	$token=$_REQUEST['token']; 
	$apn_id = $_REQUEST['apn_id'];
	
	if (!empty($token) && !empty($apn_id)) {
		$users_id=getUsersId($token);
		if (!empty($users_id)) {
			
			$sql="UPDATE `users` SET `apn_id`=:apn_id WHERE token=:token";
			$stmt=$conn->prepare($sql);
			$stmt->bindValue(':apn_id', $apn_id);
			$stmt->bindValue(':token', $token);
			try{ 
				$count=$stmt->execute();
			}
			catch(PDOException $e){ 
				echo $e->getMessage(); 
			}
			$success="1";
			$msg="apn_id is updated successfully!";
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
		echo json_encode(array("success" => $success, "msg" => $msg));
?>