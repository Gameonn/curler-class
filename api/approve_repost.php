<?php
	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');

	$post_id=$_REQUEST['post_id'];
	$token=$_REQUEST['token'];
	$data=array();
	
	if (!empty($token) && !empty($post_id)) {
		$users_id=getUsersId($token);
		if (!empty($users_id)) {
			$sql = "SELECT approved FROM share WHERE users_id=:users_id AND post_id=:post_id ";
			$stmt=$conn->prepare($sql);
			$stmt->bindValue(':users_id', $users_id);
			$stmt->bindValue(':post_id', $post_id);
			try{
				$stmt->execute();
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
			$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
			if($result[0]['approved']=="y"){
				$success="0";
				$msg="Post is already approved!";
			}
			else{
				$result=approveRePost($users_id, $post_id);
				$success="1";
				$msg="Post is approved successfully!";
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
		echo json_encode(array("success" => $success, "msg" => $msg));

?>