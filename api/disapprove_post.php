<?php
	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');

	$share_id=$_REQUEST['share_id'];
	$data=array();
	
	if (!empty($share_id)) {
		
		$sql = "SELECT approved FROM share WHERE id=:share_id ";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':share_id', $share_id);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		
		if($result[0]['approved']=="n"){
			$success="0";
			$msg="Post is already disapproved!";
		}
		else{
			$data=disapprovePost($share_id);
			$success="1";
			$msg="Post is disapproved successfully!";
		}
	}
	else{
		$success="0";
		$msg="Incomplete Parameters!";
	}
		echo json_encode(array("success" => $success, "msg" => $msg));
?>
