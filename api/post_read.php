<?php
	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');

	$token=$_REQUEST['token'];
	$notification_id=$_REQUEST['notification_id'] ? $_REQUEST['notification_id'] : '';
	$type=$_REQUEST['type'] ? $_REQUEST['type'] : '';
	
	if (!empty($token) && !empty($notification_id) && !empty($type)) {
		$users_id=getUsersId($token);
		if (!empty($users_id)) {
				if( $type == 1){
					$sql1="SELECT is_red FROM `likes` WHERE `id`=:id";
					$stmt1=$conn->prepare($sql1);
					$stmt1->bindValue(':id', $notification_id);
					try{ $stmt1->execute();	}
					catch(PDOException $e){ echo $e->getMessage(); }
					$res1= $stmt1->FetchAll(PDO::FETCH_ASSOC);
					$status=$res1[0]['is_red'];
					if($status == "y"){
						$success="1";
						$msg="marked read!";
						$sql="UPDATE `likes` SET `is_red`='y' WHERE `id`=:id";
						$stmt=$conn->prepare($sql);
						$stmt->bindValue(':id', $notification_id);
						try{ $stmt->execute();	}
						catch(PDOException $e){ echo $e->getMessage(); }
					}
					else{
					$success="0";
					$msg="Already read!";
					}		
				}
				elseif( $type == 2){
					$sql1="SELECT is_red FROM `share` WHERE `id`=:id";
					$stmt1=$conn->prepare($sql1);
					$stmt1->bindValue(':id', $notification_id);
					try{ $stmt1->execute();	}
					catch(PDOException $e){ echo $e->getMessage(); }
					$res1= $stmt1->FetchAll(PDO::FETCH_ASSOC);
					$status=$res1[0]['is_red'];
					if($status == "y"){
						$success="1";
						$msg="marked read!";
						$sql="UPDATE `share` SET `is_red`='y' WHERE `id`=:id";
						$stmt=$conn->prepare($sql);
						$stmt->bindValue(':id', $notification_id);
						try{ $stmt->execute();	}
						catch(PDOException $e){ echo $e->getMessage(); }
					}
					else{
					$success="0";
					$msg="Already read!";
					}
				}
				elseif( $type == 3){
					$sql1="SELECT is_red FROM `comment` WHERE id=:id";
					$stmt1=$conn->prepare($sql1);
					$stmt1->bindValue(':id', $notification_id);
					try{ $stmt1->execute();	}
					catch(PDOException $e){ echo $e->getMessage(); }
					$res1= $stmt1->FetchAll(PDO::FETCH_ASSOC);
					$status=$res1[0]['is_red'];
					if($status == "y"){
						$success="1";
						$msg="marked read!";
						$sql="UPDATE `comment` SET `is_red`='y' WHERE `id`=:id";
						$stmt=$conn->prepare($sql);
						$stmt->bindValue(':id', $notification_id);
						try{ $stmt->execute();	}
						catch(PDOException $e){ echo $e->getMessage(); }
					}
					else{
					$success="0";
					$msg="Already read!";
					}
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
	echo json_encode(array("success"=> $success, "msg"=>$msg));
?>