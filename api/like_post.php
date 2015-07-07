<?php
	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');
	
	$token=$_REQUEST['token'];
	$share_id=$_REQUEST['share_id'];
	$like_id=array();

	if (!empty($token) && !empty($share_id)) {
		$users_id=getUsersId($token);
		if (!empty($users_id)) {
			$sql="SELECT  `id` FROM  `likes` WHERE users_id =:users_id	AND share_id =:share_id";
			$stmt=$conn->prepare($sql);
			$stmt->bindValue(':users_id', $users_id);
			$stmt->bindValue(':share_id', $share_id);
			try{
				$stmt->execute();
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
			$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
			if ($result) {
				$success="0";
				$msg="Post is already liked";	
			}
			else{
				$res = likePost($users_id, $share_id);
				$success = "1";
				$msg="post liked!";	
				if(!empty($res))				
					$like_id=$res;			
				// $sql1="SELECT P.url, U.name, CONCAT('$path',profile_pic) as profile_pic, U.apn_id,
				// CASE 
				// 	WHEN DATEDIFF(NOW(),L.created_on) != 0 THEN CONCAT(DATEDIFF(NOW(),L.created_on) ,'d ago')
				// 	WHEN HOUR(TIMEDIFF(NOW(),L.created_on)) != 0 THEN CONCAT(HOUR(TIMEDIFF(NOW(),L.created_on)) ,'h ago')
				// 	WHEN MINUTE(TIMEDIFF(NOW(),L.created_on)) != 0 THEN CONCAT(MINUTE(TIMEDIFF(NOW(),L.created_on)) ,'m ago')
				// ELSE
				// 	CONCAT(SECOND(TIMEDIFF(NOW(),L.created_on)) ,'s ago')
				// END as time_since
				// 	FROM  `post` P
				// 	JOIN likes L ON P.id = L.share_id
				// 	JOIN users U ON L.users_id = U.id
				// 	WHERE L.id =:like_id";
				// $sth1=$conn->prepare($sql1);
	   //  		$sth1->bindValue(":like_id",$like_id);
	   //  		try{$sth1->execute();}
	   //  		catch(Exception $e){}
	   //  		$result1=$sth1->fetchAll(PDO::FETCH_ASSOC);
				
				// if($result1){
				// 	 $domain_name = str_ireplace('www.', '', parse_url($result1[0]['url'], PHP_URL_HOST));
    //                  $message = $result1[0]['username']."likes your post from".$domain_name;
				// }
				
				// foreach($result as $key=>$value){
    // 			if(!empty($value['apn_id'])){
    			
	   //  			$apns->newMessage($value['apn_id']);
				// 	$apns->addMessageAlert($message);
				// 	$apns->addMessageSound('x.wav');
				// 	$apns->addMessageCustom('username', $value['name']);
				// 	$apns->addMessageCustom('profile_pic', $value['profile_pic']);
				// 	$apns->addMessageCustom('apn_id', $value['apn_id']);
				// 	$apns->addMessageCustom('time_since', $value['time_since']);
				// 	$apns->queueMessage();
				// 	$apns->processQueue();
    // 			}
    // 			else{}
    // 			}				
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

		echo json_encode(array("success"=>$success, "msg"=>$msg, "like_id"=>$like_id));	
?>