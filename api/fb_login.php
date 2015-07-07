<?php

	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/GeneralFunctions.php');
	require_once('../phpInclude/AdminClass.php');
	require_once('../phpass-0.3/PasswordHash.php');   

//$name = $_REQUEST['name'] ? $_REQUEST['name'] : '';
//$profile_pic = $_FILES['profile_pic'] ? $_FILES['profile_pic'] : '' ;
//$cover_pic = $_FILES['cover_pic'] ? $_FILES['cover_pic'] : '' ;
//$email = $_REQUEST['email'] ? $_REQUEST['email'] : '';
//$gender = $_REQUEST['gender'] ? $_REQUEST['gender'] : '';
$data=array();
$path=BASE_PATH . "/timthumb.php?src=uploads/";

$fb_id = $_REQUEST['fb_id'];
$dob = $_REQUEST['dob'] ? $_REQUEST['dob'] : '';

if(empty($fb_id)){
	$details = $_REQUEST['details'];
	$details = json_decode($details);

	$fb_id = $details->id;
}



global $conn;
if (!empty($fb_id)) {
	
	$sql="SELECT id FROM `users` WHERE fb_id=:fb_id";
	$stmt=$conn->prepare($sql);
	$stmt->bindValue(':fb_id', $fb_id);
	try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	$result=$stmt->fetch(PDO::FETCH_ASSOC);	
	$users_id = $result['id'];
	if (!empty($users_id)) {
			$token = md5(mt_rand());
			$token=md5($token);
			$sql1="UPDATE `users` SET `token`='$token' WHERE `id`=:users_id";
			$stmt1=$conn->prepare($sql1);
			$stmt1->bindValue(':users_id', $users_id);
			$count=0;
			try{ 
				$count=$stmt1->execute();
			}
			catch(PDOException $e){ 
				echo $e->getMessage(); 
			}
			if($count){
			$result1=getUserDetails($users_id);
			$success= "1";
			$msg = "login successful";
			if($result1)
				$data=$result1;
		}
	}
	else{
		$token = md5(mt_rand());
		$token = md5($token);
		
		$name = $details->name;
		$gender = $details->gender;
		$email = $details->email;
		$cover = $details->cover;
		$cover_pic_id = $cover->id;
		$cover_pic = $cover->source;
		$cover_pic = $cover_pic;
		$picture = $details->picture;
		$data = $picture->data;
		$profile_pic = $data->url;
		
		//uploading cover_pic
		$cover_pic = file_get_contents($cover_pic);
		$cover_pic_name = 'IMG_'.$cover_pic_id.'.jpg';
		file_put_contents("../uploads/".$cover_pic_name, $cover_pic);
		
		//uploading profile_pic
		$profile_pic = file_get_contents($profile_pic);
		$profile_pic_name = 'IMG_'.$fb_id.'.jpg';
		file_put_contents("../uploads/".$profile_pic_name, $profile_pic);
		
		$sql="INSERT INTO `users` VALUES(DEFAULT,:name,:profile_pic,:cover_pic,:email,'',:fb_id,:dob,:gender,:token,now(),'','')";
		$stmt = $conn->prepare($sql);

		$stmt->bindParam(':name',$name);
		$stmt->bindParam(':profile_pic',$profile_pic_name);
		$stmt->bindParam(':cover_pic',$cover_pic_name);
	    $stmt->bindParam(':email', $email);
		$stmt->bindParam(':fb_id', $fb_id);
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
		if(!empty($users_id)){
			$success="1";
			$msg="user registered successfully!";
			
			$result_q=getUserDetails($users_id);
			$data=$result_q;
		}
		else{
			$success="0";
			$msg="No new user registered!";
		}
	}
}
else{
	$success = "0";
	$msg = "Incomplete Parameters!";
}
	echo json_encode(array("success" => $success, "msg" => $msg, "data" => $data));
?>