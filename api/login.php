<?php

	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');
	require_once('../phpass-0.3/PasswordHash.php');   

$email = $_REQUEST['email'];
$password = $_REQUEST['password'];
$data=array();
$path=BASE_PATH . "/timthumb.php?src=uploads/";
	$hasher = new PasswordHash(8, false);

if (!empty($email)) {

	$sql="SELECT id, password FROM `users` WHERE `email`=:email";
	$stmt=$conn->prepare($sql);
	$stmt->bindValue(':email', $email);
	try{ 
		$stmt->execute();
	}
	catch(PDOException $e){ 
		echo $e->getMessage(); 
	}
	$result=$stmt->fetch(PDO::FETCH_ASSOC); 
	$old_pass=$result['password'];
	$users_id = $result['id'];
	
	if ($old_pass) {
		$check = $hasher->CheckPassword($password, $old_pass);
		if($check) {
			$token = md5(mt_rand());
			$token=md5($token);
			$sql1="UPDATE `users` SET `token`='$token' WHERE `email`=:email";
			$stmt1=$conn->prepare($sql1);
			$stmt1->bindValue(':email', $email);
			$count=0;
			try{ 
				$count=$stmt1->execute();
			}
			catch(PDOException $e){ 
				echo $e->getMessage(); 
			}
			if($count){
				$result1 = getUserDetails($users_id);
				$success= "1";
				$msg = "Login Successful";
				if(!empty($result1))
					$data=$result1;
			}
			else{
				$success="0";
				$msg = "No data";
			}
		}
		else{
			$success = "0";
			$msg = "Incorrect Password!";		
	 	}
	}
	else{
		$success = "0";
		$msg = "Incorrect Email!";		
	}
}
else{
	$success = "0";
	$msg = "Incomplete Parameters";	
}
		echo json_encode(array("success" => $success, "msg" => $msg, "data" => $data));

?>
