<?php
    require_once('../phpInclude/dbconn.php');
    require_once('../phpInclude/AdminClass.php');
	require_once ('../easyapns/apns.php');
	require_once('../easyapns/classes/class_DbConnect.php');
	$db = new DbConnect('localhost', 'codebrew_super', 'core2duo', 'codebrew_nusit');
	$db->show_errors();
	//error_reporting(E_ALL);
    $token=$_REQUEST['token'];
    $users_id2 = $_REQUEST['users_id2'];
	$path=BASE_PATH . "/timthumb.php?src=uploads/";
	
    if (!empty($token) && !empty($users_id2)) {
        $users_id1=getUsersId($token);
        if (!empty($users_id1)) {
			
			$sql = "SELECT id FROM `follow` WHERE users_id1=:users_id1 AND users_id2=:users_id2";
			$stmt=$conn->prepare($sql);
			$stmt->bindParam(':users_id1',$users_id1);
			$stmt->bindParam(':users_id2',$users_id2);
			try{
				$stmt->execute();
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
			$id = $stmt->FetchAll(PDO::FETCH_ASSOC);
			
			if(empty($id)){
				if($users_id1 != $users_id2){
					$follow_id=follow_user($users_id1, $users_id2);

					if(!empty($follow_id)){
						$success = "1";
						$msg="Successfully followed!";

						$sql1="SELECT U.id, U.name AS username, CONCAT(  '$path', profile_pic )AS profile_pic, U.apn_id 
						        FROM  `follow` AS F
						        JOIN users AS U ON F.users_id2 = U.id
						        WHERE F.id =:follow_id";
						$sth1=$conn->prepare($sql1);
						$sth1->bindValue(":follow_id",$follow_id);
						try{$sth1->execute();}
						catch(Exception $e){}
						$result1=$sth1->fetchAll(PDO::FETCH_ASSOC);
						$apnid=$result1[0]['apn_id'];
						
						$sql2="SELECT U.id as uid, U.name AS username, CONCAT(  '$path', profile_pic )AS profile_pic
						        FROM  `follow` AS F
						        JOIN users AS U ON F.users_id1 = U.id
						        WHERE F.id =:follow_id";
						$sth2=$conn->prepare($sql2);
						$sth2->bindValue(":follow_id",$follow_id);
						try{$sth2->execute();}
						catch(Exception $e){}
						$result2=$sth2->fetchAll(PDO::FETCH_ASSOC);
						$uname=$result2[0]['username'];
						$message=array();
						if($uname)						
							$message['msg'] = $uname." wants to follow you on nusit";
						
						$message['type']=4;
						$message['uid']=$result2[0]['uid'];
					
						
						   if(!empty($apnid)){
								
							$apns->newMessage($apnid);
							$apns->addMessageAlert($message['msg']);
							$apns->addMessageSound('x.wav');
							$apns->addMessageCustom('t', $message['type']);
							$apns->addMessageCustom('i',$message['uid'] );
							$apns->queueMessage();
							$apns->processQueue();
				
						}
						else{}
						}
					
					
				}
					
                else{
                    $success="0";
                    $msg="You cannot follow yourself!";
                }      
			}
            else{
				$success="0";
				$msg="User is already followed!";
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

        echo json_encode(array("success"=>$success, "msg"=>$msg, "users_id1"=>$users_id1, "users_id2"=>$users_id2));


?>