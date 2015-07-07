<?php
session_start();
require_once('../phpInclude/dbconn.php');
require_once('../phpInclude/GeneralFunctions.php');
require_once('../phpInclude/AdminClass.php');
require_once('../phpass-0.3/PasswordHash.php');   

$name = $_REQUEST['name'];
$profile_pic = $_FILES['profile_pic'];
$cover_pic = $_FILES['cover_pic'];
$email = $_REQUEST['email'];
$password = $_REQUEST['password'];
$dob = $_REQUEST['dob'] ? $_REQUEST['dob'] : '';
$gender = $_REQUEST['gender'] ? $_REQUEST['gender'] : '';
$token = md5(mt_rand());
$token=md5($token);
$users_id=array();
$data = array();

if (!empty($email) && !empty($password) && !empty($name)) {

	$sql="SELECT `email` FROM `users` WHERE `email`='$email'";
	$stmt=$conn->prepare($sql);
	try{ 
		$stmt->execute(); 
	}
	catch(PDOException $e){ 
		$e->getMessage(); 
	}

	$result=$stmt->fetch(PDO::FETCH_ASSOC);
	if(!empty($result['email'])){
		$success="0";
		$msg="User already exist!"; 
	}
	else{
		if(!empty($profile_pic)){
		$randomFileName=GeneralFunctions::randomFileNameGenerator("Img_").".".end(explode(".",$profile_pic['name']));
		  if(@move_uploaded_file($profile_pic['tmp_name'], "../uploads/$randomFileName")){
			$success="1";
			$url=$randomFileName;
		  }
		}
		   if(!$url){
			 $url="default_user.png";
		   }
		if(!empty($cover_pic['name'])){
		$randomFileName=GeneralFunctions::randomFileNameGenerator("Img_").".".end(explode(".",$cover_pic['name']));
		  if(@move_uploaded_file($cover_pic['tmp_name'], "../uploads/$randomFileName")){
			$success="1";
			$url1=$randomFileName;
		  }
		}
		   if(!$url1){
			 $url1="default_user.png";
		   }

		// Initialize the hasher without portable hashes (this is more secure)
		$hasher = new PasswordHash(8, false);
		 
		// Hash the password.  $hashedPassword will be a 60-character string.
		$hashedPassword = $hasher->HashPassword($password);

	    $sql = "insert into `users` values(DEFAULT,:name,:profile_pic,:cover_pic,:email,:password,'',:dob,:gender,:token,now(),'','')";

	    $stmt = $conn->prepare($sql);

		$stmt->bindParam(':name',$name);
		$stmt->bindParam(':profile_pic',$url);
		$stmt->bindParam(':cover_pic',$url1);
	    $stmt->bindParam(':email', $email);
	    $stmt->bindParam(':password', $hashedPassword);
		$stmt->bindParam(':dob',$dob);
	    $stmt->bindParam(':gender', $gender);	
	    $stmt->bindParam(':token', $token);	
		
		try{
	    $result = $stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$users_id = $conn->lastInsertId();
		$success="1";
		$msg="Signup Successfully!";
		$result1 = getUserDetails($users_id);
		}
	} 
	else{
		$success="0";
		$msg="Incomplete parameters!";
	}
		
		echo json_encode(array("success" => $success, "msg" => $msg, "users_id" => $users_id, "data" => $result1));
?>