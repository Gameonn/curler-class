<?php

	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');	
	require_once ('../easyapns/apns.php');
	require_once('../easyapns/classes/class_DbConnect.php');
	$db = new DbConnect('localhost', 'codebrew_super', 'core2duo', 'codebrew_nusit');
	$db->show_errors();
	error_reporting(E_ALL);
	
	$token=$_REQUEST['token'];   
	$users_id1=$_REQUEST['users_id1'];
	$post_id=$_REQUEST['post_id'];
	$action = $_REQUEST['action'] ? $_REQUEST['action'] : "";
	$data = array();
	$path=BASE_PATH . "/timthumb.php?src=uploads/";
	
	if (!empty($token) && !empty($post_id)) {
		$users_id2=getUsersId($token);
			if (!empty($users_id2)) {
				
				$sql1="SELECT * FROM `post` WHERE id=:post_id";
				$stmt1=$conn->prepare($sql1);
				$stmt1->bindValue(':post_id', $post_id);
				try{
					$stmt1->execute();
				}
				catch(PDOException $e){
					echo $e->getMessage();
				}
				$result = $stmt1->FetchAll(PDO::FETCH_ASSOC);
				
			//$id = postBy($post_id);
			//if($id == $users_id){
			//	$success = "0";
			//	$msg = "You cannot share your own post!";
			//}
			//else{
				if($result){
					$repost_id=repost($users_id1, $users_id2, $post_id, $action);
					if(!empty($repost_id)){
						$success = "1";
						$msg="post is shared successfully!";
						$data = $repost_id;
						$sql="SELECT SN.users_id1, SN.users_id2, SN.share_id, P.id as pid, P.url, U.id as uid, U.name, CONCAT(  '$path', profile_pic ) AS profile_pic,  
							CASE 
								WHEN DATEDIFF( NOW( ) , S.created_on ) !=0
								THEN CONCAT( DATEDIFF( NOW( ) , S.created_on ) ,  'd ago' ) 
								WHEN HOUR( TIMEDIFF( NOW( ) , S.created_on ) ) !=0
								THEN CONCAT( HOUR( TIMEDIFF( NOW( ) , S.created_on ) ) ,  'h ago' ) 
								WHEN MINUTE( TIMEDIFF( NOW( ) , S.created_on ) ) !=0
								THEN CONCAT( MINUTE( TIMEDIFF( NOW( ) , S.created_on ) ) ,  'm ago' ) 
								ELSE CONCAT( SECOND( TIMEDIFF( NOW( ) , S.created_on ) ) ,  's ago' ) 
							END AS time_since
								FROM  `shared_noti` AS SN
								JOIN `share` AS S ON S.id = SN.share_id
								JOIN `post` AS P ON P.id=S.post_id
								JOIN `users` AS U ON U.id = SN.users_id2
								WHERE SN.share_id =:repost_id";
						$sth=$conn->prepare($sql);
						$sth->bindValue(":repost_id",$repost_id);
						try{$sth->execute();}
						catch(Exception $e){}
						$result=$sth->fetchAll(PDO::FETCH_ASSOC);
						
						$sql2="SELECT SN.users_id1, SN.users_id2, SN.share_id, P.url, U.name, CONCAT(  '$path', profile_pic ) AS profile_pic, U.apn_id, 
							CASE 
								WHEN DATEDIFF( NOW( ) , S.created_on ) !=0
								THEN CONCAT( DATEDIFF( NOW( ) , S.created_on ) ,  'd ago' ) 
								WHEN HOUR( TIMEDIFF( NOW( ) , S.created_on ) ) !=0
								THEN CONCAT( HOUR( TIMEDIFF( NOW( ) , S.created_on ) ) ,  'h ago' ) 
								WHEN MINUTE( TIMEDIFF( NOW( ) , S.created_on ) ) !=0
								THEN CONCAT( MINUTE( TIMEDIFF( NOW( ) , S.created_on ) ) ,  'm ago' ) 
								ELSE CONCAT( SECOND( TIMEDIFF( NOW( ) , S.created_on ) ) ,  's ago' ) 
							END AS time_since
								FROM  `shared_noti` AS SN
								JOIN `share` AS S ON S.id = SN.share_id
								JOIN `post` AS P ON P.id=S.post_id
								JOIN `users` AS U ON U.id = SN.users_id1
								WHERE SN.share_id =:repost_id";
						$sth2 = $conn->prepare($sql2);
						$sth2->bindValue(":repost_id",$repost_id);
						try{$sth2->execute();}
						catch(Exception $e){}
						$result2 = $sth2->fetchAll(PDO::FETCH_ASSOC);
						$uname=$result[0]['name'];
						$message=array();
						
						if($result){
							$domain_name = str_ireplace('www.', '', parse_url($result[0]['url'], PHP_URL_HOST));
							$message['msg'] = $uname." shared your post from ".$domain_name;
						}
						//print_r($message['msg']);die;
						//$name=$result[0]['name'];
						//$pic=$result[0]['profile_pic'];
						
						$message['type']=2;
						$message['uid']=$result[0]['uid'];
						$message['pid']=$result[0]['pid'];
					
						foreach($result2 as $key=>$value){
							if(!empty($value['apn_id'])){
								
								$apns->newMessage($value['apn_id']);
								$apns->addMessageAlert($message['msg']);
								$apns->addMessageSound('x.wav');
								$apns->addMessageCustom('t', $message['type']);
								$apns->addMessageCustom('i',$message['uid'] );
								$apns->addMessageCustom('p',$message['pid'] );
								$apns->queueMessage();
								$apns->processQueue();
							}
							else{}
						}
					}				
				}
				else{
					$success="0";
					$msg="No such post exist!";
				}
			}
		
		else{
			$success="0";
			$msg="No such user exist!";
		}

	}	
	else{
		$success="0";
		$msg="Incomplete parameters";
	}
		echo json_encode(array("success"=>$success, "msg"=>$msg, "data"=>$data));

?>