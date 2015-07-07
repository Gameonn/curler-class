<?php
    require_once('../phpInclude/dbconn.php');
    require_once('../phpInclude/AdminClass.php');
	require_once ('../easyapns/apns.php');
	require_once('../easyapns/classes/class_DbConnect.php');
	$db = new DbConnect('localhost', 'codebrew_super', 'core2duo', 'codebrew_nusit');
	$db->show_errors();
	//error_reporting(E_ALL);

    $token=$_REQUEST['token'];
    $share_id=$_REQUEST['share_id'];
    $comment=$_REQUEST['comment'];
	$path=BASE_PATH . "/timthumb.php?src=uploads/";
	
    $comment_id=array();
	$all_comments=array();
    if (!empty($token) && !empty($share_id) && !empty($comment)) {
        $users_id=getUsersId($token);
        if (!empty($users_id)) {
                $comment_id=insertComment($users_id, $share_id, $comment);
                $res=getAllComments($share_id, $users_id);
                if(!empty($comment_id)){
                    $success = "1";
                    $msg="comment added!";
					if(!empty($res))
						$all_comments=$res;
					
						$sql="SELECT C.share_id as sid, C.users_id, P.id as pid, P.url, U.id as uid, U.name, CONCAT(  '$path', profile_pic ) AS profile_pic, C.created_on,
							CASE 
								WHEN DATEDIFF( NOW( ) , C.created_on ) !=0
								THEN CONCAT( DATEDIFF( NOW( ) , C.created_on ) ,  'd ago' ) 
								WHEN HOUR( TIMEDIFF( NOW( ) , C.created_on ) ) !=0
								THEN CONCAT( HOUR( TIMEDIFF( NOW( ) , C.created_on ) ) ,  'h ago' ) 
								WHEN MINUTE( TIMEDIFF( NOW( ) , C.created_on ) ) !=0
								THEN CONCAT( MINUTE( TIMEDIFF( NOW( ) , C.created_on ) ) ,  'm ago' ) 
								ELSE CONCAT( SECOND( TIMEDIFF( NOW( ) , C.created_on ) ) ,  's ago' ) 
							END AS time_since
								FROM  `comment` AS C
								JOIN `share` AS S ON S.id=C.share_id
 								JOIN `post` AS P ON P.id = S.post_id
								JOIN `users` AS U ON U.id = C.users_id
								WHERE C.id =:comment_id";
						$sth=$conn->prepare($sql);
						$sth->bindValue(":comment_id",$comment_id);
						try{$sth->execute();}
						catch(Exception $e){}
						$result=$sth->fetchAll(PDO::FETCH_ASSOC);
						
						$sql2="SELECT C.share_id, S.users_id, P.url, CONCAT(  '$path', profile_pic ) AS profile_pic, U.apn_id, C.created_on,
							CASE 
								WHEN DATEDIFF( NOW( ) , C.created_on ) !=0
								THEN CONCAT( DATEDIFF( NOW( ) , C.created_on ) ,  'd ago' ) 
								WHEN HOUR( TIMEDIFF( NOW( ) , C.created_on ) ) !=0
								THEN CONCAT( HOUR( TIMEDIFF( NOW( ) , C.created_on ) ) ,  'h ago' ) 
								WHEN MINUTE( TIMEDIFF( NOW( ) , C.created_on ) ) !=0
								THEN CONCAT( MINUTE( TIMEDIFF( NOW( ) , C.created_on ) ) ,  'm ago' ) 
								ELSE CONCAT( SECOND( TIMEDIFF( NOW( ) , C.created_on ) ) ,  's ago' ) 
							END AS time_since
								FROM  `comment` AS C
								JOIN `share` AS S ON S.id=C.share_id
 								JOIN `post` AS P ON P.id = S.post_id
								JOIN `users` AS U ON U.id = S.users_id
								WHERE C.id =:comment_id";
						$sth2=$conn->prepare($sql2);
						$sth2->bindValue(":comment_id",$comment_id);
						try{$sth2->execute();}
						catch(Exception $e){}
						$result2=$sth2->fetchAll(PDO::FETCH_ASSOC);
						$uname=$result[0]['name'];
						$message=array();
						//print_r($result2);die;
						if($result){
							$domain_name = str_ireplace('www.', '', parse_url($result[0]['url'], PHP_URL_HOST));
							$message['msg'] = $uname." commented on your post from ".$domain_name;
						}
						//print_r($message['msg']);die;
						$message['type']=3;
						$message['uid']=$result[0]['uid'];
						$message['pid']=$result[0]['pid'];
						$message['sid']=$result[0]['sid'];
												
						foreach($result2 as $key=>$value){
							if(!empty($value['apn_id'])){
								$all_apns[]=$value['apn_id'];
							}
						}
						if(!empty($value['apn_id'])){
							$apns->newMessage($all_apns);
							$apns->addMessageAlert($message['msg']);
							$apns->addMessageSound('x.wav');
							$apns->addMessageCustom('t', $message['type']);
							$apns->addMessageCustom('i',$message['uid'] );
							$apns->addMessageCustom('s',$message['sid'] );
							$apns->queueMessage();
							$apns->processQueue();
						}
						else{}
					}
	
                else{
                    $success="0";
                    $msg="Database error!";
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

        echo json_encode(array("success"=>$success, "msg"=>$msg, "comment_id"=>$comment_id, "all_comments"=>$all_comments));


?>