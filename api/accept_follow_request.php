<?php
    require_once('../phpInclude/dbconn.php');
    require_once('../phpInclude/AdminClass.php');
	require_once ('../easyapns/apns.php');
	require_once('../easyapns/classes/class_DbConnect.php');
	$db = new DbConnect('localhost', 'codebrew_super', 'core2duo', 'codebrew_nusit');

	$token=$_REQUEST['token'];
	$users_id2 = $_REQUEST['users_id2'];
	$path=BASE_PATH . "/timthumb.php?src=uploads/";
	
	$data=array();
	$followers=array();
	$followings=array();
	if (!empty($token) && !empty($users_id2)){
		$users_id1=getUsersId($token);
		if (!empty($users_id1)) {
			$follow_id=get_follow_id($users_id1, $users_id2);
			
			$sql = "UPDATE `follow` SET `status`=2,`created_on`=now() WHERE users_id1 = :users_id2 AND users_id2 = :users_id1";
			$stmt=$conn->prepare($sql);
			$stmt->bindParam(':users_id2',$users_id2);
			$stmt->bindParam(':users_id1',$users_id1);
			try{$stmt->execute();}
			catch(PDOException $e){}
			$success = "1";
			$msg="Request Accepted!";
			
			$result=getAllFollowRequests($users_id1);
			$result1=getAllFollowers($users_id1);
			$result2=getAllFollowing($users_id1);
			
			if(!empty($result))
				$data=$result;
			if(!empty($result1))
				$followers=$result1;
			if(!empty($result2))
				$followings=$result2;
				
			$sql="SELECT U.id, U.name AS username, CONCAT(  '$path', profile_pic )AS profile_pic, U.apn_id FROM  `follow` AS F JOIN users AS U ON F.users_id1 = U.id
			WHERE F.id =:follow_id";
			
			$sth=$conn->prepare($sql);
			$sth->bindValue(":follow_id",$follow_id);
			try{$sth->execute();}
			catch(Exception $e){}
			$res=$sth->fetchAll(PDO::FETCH_ASSOC);
			$apnid=$res[0]['apn_id'];
			
			$sql="SELECT U.id as uid, U.name AS username, CONCAT(  '$path', profile_pic )AS profile_pic
			FROM  `follow` AS F
			JOIN users AS U ON F.users_id2 = U.id
			WHERE F.id =:follow_id";
			$sth=$conn->prepare($sql);
			$sth->bindValue(":follow_id",$follow_id);
			try{$sth->execute();}
			catch(Exception $e){}
			$res1=$sth->fetchAll(PDO::FETCH_ASSOC);
			$uname=$res1[0]['username'];
			$message=array();
			
			$message['msg'] = $uname." wants to follow you on nusit";
			
			$message['type']=4;
			$message['uid']=$res1[0]['uid'];
			
			if(!empty($apnid)){
			$apns->newMessage($apnid);
			$apns->addMessageAlert($message['msg']);
			$apns->addMessageSound('x.wav');
			$apns->addMessageCustom('username', $uname);
			$apns->addMessageCustom('t', $message['type']);
			$apns->addMessageCustom('i',$uid );
			$apns->queueMessage();
			$apns->processQueue();
			 }
			else{}
		 

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