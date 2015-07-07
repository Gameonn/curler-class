<?php
    require_once('../phpInclude/dbconn.php');
    require_once('../phpInclude/AdminClass.php');

	$token=$_REQUEST['token'];
    //$follow_id=$_REQUEST['follow_id'];
	$users_id2 = $_REQUEST['users_id2'];
	
	$data=array();
	$followers=array();
	$followings=array();
	if (!empty($token) && !empty($users_id2)){
		$users_id1=getUsersId($token);
		if (!empty($users_id1)) {

			$sql="DELETE FROM `follow` WHERE users_id1 = :users_id1 AND users_id2 = :users_id2";
			$stmt=$conn->prepare($sql);
			$stmt->bindParam(':users_id2',$users_id2);
			$stmt->bindParam(':users_id1',$users_id1);
			try{
				$stmt->execute();
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
			$success = "1";
			$msg="Successfully unfollowed!";
			
			$result=getAllFollowRequests($users_id1);
			$result1=getAllFollowers($users_id1);
			$result2=getAllFollowing($users_id1);
			if(!empty($result))
				$data=$result;
			if(!empty($result1))
				$followers=$result1;
			if(!empty($result2))
				$followings=$result2;
				
		  // $sql1="SELECT U.id, U.name AS username, CONCAT(  '$path', profile_pic )AS profile_pic, U.apn_id 
			//         FROM  `follow` AS F
			//         JOIN users AS U ON F.users_id1 = U.id
			//         WHERE F.id =:follow_id";
			// $sth1=$conn->prepare($sql1);
			// $sth1->bindValue(":follow_id",$follow_id);
			// try{$sth1->execute();}
			// catch(Exception $e){}
			// $result1=$sth1->fetchAll(PDO::FETCH_ASSOC);
			
			// if($result1){
			//      $message = $result1[0]['username']."has accepted your request";
			// }
			
			// foreach($result as $key=>$value){
			//     if(!empty($value['apn_id'])){
			
			//         $apns->newMessage($value['apn_id']);
			//         $apns->addMessageAlert($message);
			//         $apns->addMessageSound('x.wav');
			//         $apns->addMessageCustom('username', $value['name']);
			//         $apns->addMessageCustom('profile_pic', $value['profile_pic']);
			//         $apns->addMessageCustom('apn_id', $value['apn_id']);
			//         $apns->queueMessage();
			//         $apns->processQueue();
			//     }
			//     else{}
			// }

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
        echo json_encode(array("success"=>$success, "msg"=>$msg, "data"=>$data, "followers"=>$followers, "followings"=>$followings));


?>