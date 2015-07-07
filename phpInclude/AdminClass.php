<?php
	require_once('GeneralFunctions.php');
	
	function getUsersId($token){
		global $conn;
		
		$sql="SELECT  `id` FROM  `users` WHERE token =:token";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue('token', $token);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		if ($result) {
			$user_id = $result[0]['id'];			
		}
		else{
			$user_id="";
		}
		return $user_id;
	}
	
	function checkUsersIdExist($users_id){
		global $conn;
		
		$sql="SELECT  `id` FROM  `users` WHERE id =:users_id";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		if ($result) {
			$user_id = $result[0]['id'];			
		}
		else{
			$user_id="";
		}
		return $user_id;
	}

	//for home page--> to show posts by followed users only
	function getPageDetailsWthPaging($users_id1, $offset, $row_count){
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";
		$data = array();
		
		$sql="SELECT temp.*,CASE 
				WHEN DATEDIFF(NOW(),temp.created_on) != 0 THEN CONCAT(DATEDIFF(NOW(),temp.created_on) ,'d ago')
				WHEN HOUR(TIMEDIFF(NOW(),temp.created_on)) != 0 THEN CONCAT(HOUR(TIMEDIFF(NOW(),temp.created_on)) ,'h ago')
				WHEN MINUTE(TIMEDIFF(NOW(),temp.created_on)) != 0 THEN CONCAT(MINUTE(TIMEDIFF(NOW(),temp.created_on)) ,'m ago')
				ELSE
				CONCAT(SECOND(TIMEDIFF(NOW(),temp.created_on)) ,'s ago')
			END as time_since FROM
			(
				SELECT S.post_type, S.id as share_id, P.id as post_id, P.title, P.images, P.url, S.approved as is_approved, S.created_on,
						U.id as users_id, U.name, CONCAT('$path',profile_pic) as profile_pic, 

						(SELECT COUNT(*) 
						FROM post_read AS PR
						WHERE PR.users_id=:users_id AND PR.post_id=S.id
						) AS post_status,
						
						(SELECT COUNT( C.id ) 
						FROM comment AS C
						WHERE C.share_id = S.id 
						) AS c_count FROM `share` AS S
						JOIN `post` as P ON P.id=S.post_id
						JOIN `follow` as F on S.users_id=F.users_id2 
						JOIN `users` as U on U.id=F.users_id2
						WHERE F.users_id1=:users_id AND F.status=2
			UNION
				SELECT S.post_type, S.id as share_id, P.id as post_id, P.title, P.images, P.url, S.approved as is_approved, S.created_on,
						U.id as users_id, U.name, CONCAT('$path',profile_pic) as profile_pic,
						(SELECT COUNT(*) 
						FROM post_read AS PR
						WHERE PR.users_id=:users_id AND PR.post_id=S.id
						) AS post_status,
						
						(SELECT COUNT( C.id ) 
						FROM comment AS C
						WHERE C.share_id = S.id
						) AS c_count FROM `share` AS S
						JOIN `post` as P ON P.id=S.post_id
						JOIN users as U ON U.id = S.users_id
						WHERE S.users_id=:users_id
			)
			AS temp	ORDER BY temp.created_on DESC LIMIT {$offset}, {$row_count}";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id1);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		
		foreach($result as $key => $value1){
			$domain_name = $result[$key]['url'];
		    $domain_name1[] = str_ireplace('www.', '', parse_url($domain_name, PHP_URL_HOST));
			//$domain_name = GeneralFunctions::get_domain($domain_name);
		}
		
		if(!empty($domain_name1)){
			foreach($domain_name1 as $key => $value2){
				if($domain_name1[$key] == "economist.com" || $domain_name1[$key] == "lemonde.fr" || $domain_name1[$key] == "theguardian.com" || $domain_name1[$key] == "nytimes.com" || $domain_name1[$key] == "lefigaro.fr" || $domain_name1[$key] == "bloomberg.com" || $domain_name1[$key] == "businessinsider.in" || $domain_name1[$key] == "techcrunch.com" || $domain_name1[$key] == "ft.com" || $domain_name1[$key] == "bbc.com" )
				{		
					$type[] = "1";		
				}
				else{ $type[] = "0";}
			}
		}
		$s=sizeof($result);
		for($i=0; $i<$s; $i++){
		
		$j=json_decode($result[$i]['images'],true);

		if(is_array($j)){
			$post_imgs="";
			foreach($j as $key=>$value){
						$m=$value;
						$post_imgs.=$m.',';
					}
			}
		$total_img= rtrim($post_imgs, ', ');
		
			$data[]=array(
				"post_type" => $result[$i]['post_type'],
				"post_status" => $result[$i]['post_status'] ? 1 :0,
				"type" => $type[$i],
				"share_id" => $result[$i]['share_id'],
				"post_id" => $result[$i]['post_id'],
				"post_title" => $result[$i]['title'],
				"post_images" => $total_img,
				"post_url" => $result[$i]['url'],
				"is_approved" => $result[$i]['is_approved'],
				"time_since" => $result[$i]['time_since'],
				"u_id" => $result[$i]['users_id'],
				"username" => $result[$i]['name'],
				"profile_pic" => $result[$i]['profile_pic'],
				"c_count" => $result[$i]['c_count'],
				"domain_name"=>$domain_name1[$i]
			);
		}
		return $data;
	}
	
	function getPageDetailsWthoutPaging($users_id1){
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";
		$data = array();
		
		$sql="SELECT temp.*,CASE 
				WHEN DATEDIFF(NOW(),temp.created_on) != 0 THEN CONCAT(DATEDIFF(NOW(),temp.created_on) ,'d ago')
				WHEN HOUR(TIMEDIFF(NOW(),temp.created_on)) != 0 THEN CONCAT(HOUR(TIMEDIFF(NOW(),temp.created_on)) ,'h ago')
				WHEN MINUTE(TIMEDIFF(NOW(),temp.created_on)) != 0 THEN CONCAT(MINUTE(TIMEDIFF(NOW(),temp.created_on)) ,'m ago')
				ELSE
				CONCAT(SECOND(TIMEDIFF(NOW(),temp.created_on)) ,'s ago')
			END as time_since FROM
			(
				SELECT S.post_type, S.id as share_id, P.id as post_id, P.title, P.images, P.url, S.approved as is_approved, P.created_on,
						U.id as users_id, U.name, CONCAT('$path',profile_pic) as profile_pic, 

						(SELECT COUNT(*) 
						FROM post_read AS PR
						WHERE PR.users_id=:users_id AND PR.post_id=S.id
						) AS post_status,
						
						(SELECT COUNT( C.id ) 
						FROM comment AS C
						WHERE C.share_id = S.id 
						) AS c_count FROM `share` AS S
						JOIN `post` as P ON P.id=S.post_id
						JOIN `follow` as F on S.users_id=F.users_id2 
						JOIN `users` as U on U.id=F.users_id2
						WHERE F.users_id1=:users_id AND F.status=2
			UNION
				SELECT S.post_type, S.id as share_id, P.id as post_id, P.title, P.images, P.url, S.approved as is_approved, P.created_on,
						U.id as users_id, U.name, CONCAT('$path',profile_pic) as profile_pic,
						(SELECT COUNT(*) 
						FROM post_read AS PR
						WHERE PR.users_id=:users_id AND PR.post_id=S.id
						) AS post_status,
						
						(SELECT COUNT( C.id ) 
						FROM comment AS C
						WHERE C.share_id = S.id
						) AS c_count FROM `share` AS S
						JOIN `post` as P ON P.id=S.post_id
						JOIN users as U ON U.id = S.users_id
						WHERE S.users_id=:users_id
			)
			AS temp	ORDER BY temp.created_on DESC";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id1);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		
		foreach($result as $key => $value1){
			$domain_name = $result[$key]['url'];
		    $domain_name1[] = str_ireplace('www.', '', parse_url($domain_name, PHP_URL_HOST));
			//$domain_name = GeneralFunctions::get_domain($domain_name);
		}
		if(!empty($domain_name1)){
			foreach($domain_name1 as $key => $value2){
				if($domain_name1[$key] == "economist.com" || $domain_name1[$key] == "lemonde.fr" || $domain_name1[$key] == "theguardian.com" || $domain_name1[$key] == "nytimes.com" || $domain_name1[$key] == "lefigaro.fr" || $domain_name1[$key] == "bloomberg.com" || $domain_name1[$key] == "businessinsider.in" || $domain_name1[$key] == "techcrunch.com" || $domain_name1[$key] == "ft.com" || $domain_name1[$key] == "bbc.com" )
				{		
					$type[] = "1";		
				}
				else{ $type[] = "0";}
			}
		}
		$s=sizeof($result);
		for($i=0; $i<$s; $i++){
		
		$j=json_decode($result[$i]['images'],true);
		
		if(is_array($j)){
			$post_imgs="";
			foreach($j as $key=>$value){
						$m=$value;
						$post_imgs.=$m.',';
					}
			}
		$total_img= rtrim($post_imgs, ', ');
		
			$data[]=array(
				"post_type" => $result[$i]['post_type'],
				"post_status" => $result[$i]['post_status'] ? 1 :0,
				"type" => $type[$i],
				"share_id" => $result[$i]['share_id'],
				"post_id" => $result[$i]['post_id'],
				"post_title" => $result[$i]['title'],
				"post_images" => $total_img,
				"post_url" => $result[$i]['url'],
				"is_approved" => $result[$i]['is_approved'],
				"time_since" => $result[$i]['time_since'],
				"u_id" => $result[$i]['users_id'],
				"username" => $result[$i]['name'],
				"profile_pic" => $result[$i]['profile_pic'],
				"c_count" => $result[$i]['c_count'],
				"domain_name"=>$domain_name1[$i]
			);
		}
		return $data;
	}

	// home page-->to show posts by ol users-->
	function getHomePageDetailsWthPaging($offset, $row_count){
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";
		$data = array();
		
		$sql="SELECT temp.*, 
			CASE 
				WHEN DATEDIFF(NOW(),temp.created_on) != 0 THEN CONCAT(DATEDIFF(NOW(),temp.created_on) ,'d ago')
				WHEN HOUR(TIMEDIFF(NOW(),temp.created_on)) != 0 THEN CONCAT(HOUR(TIMEDIFF(NOW(),temp.created_on)) ,'h ago')
				WHEN MINUTE(TIMEDIFF(NOW(),temp.created_on)) != 0 THEN CONCAT(MINUTE(TIMEDIFF(NOW(),temp.created_on)) ,'m ago')
				ELSE
				CONCAT(SECOND(TIMEDIFF(NOW(),temp.created_on)) ,'s ago')
			END as time_since
		FROM (
		SELECT P.id as post_id, P.title, P.images, P.url, P.approved as is_approved, P.created_on, U.id as users_id,
			U.name, CONCAT('$path',profile_pic) as profile_pic, (SELECT COUNT( C.id ) FROM comment AS C join share on share.id=C.share_id WHERE share.post_id = P.id AND share.post_type=1
			) AS c_count FROM  `post` AS P
				JOIN users AS U ON P.users_id = U.id
		UNION
		SELECT S.post_id as post_id, P.title, P.images, P.url, S.approved as is_approved, S.created_on, U.id as users_id,
			U.name, CONCAT('$path',profile_pic) as profile_pic, (SELECT COUNT( C.id ) FROM comment AS C join share on share.id=C.share_id WHERE share.post_id = P.id AND share.post_type=2
			) AS c_count FROM `share` AS S
				JOIN users as U ON S.users_id = U.id
            JOIN post as P on S.post_id= P.id
		)
		 AS temp ORDER BY temp.created_on DESC LIMIT {$offset}, {$row_count}";
		$stmt=$conn->prepare($sql);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		
		foreach($result as $key => $value1){
			$domain_name = $result[$key]['url'];
		    $domain_name1[] = str_ireplace('www.', '', parse_url($domain_name, PHP_URL_HOST));
			//$domain_name = GeneralFunctions::get_domain($domain_name);		
		}
		if(!empty($domain_name1)){
			foreach($domain_name1 as $key => $value2){
				if($domain_name1[$key] == "economist.com" || $domain_name1[$key] == "lemonde.fr" || $domain_name1[$key] == "theguardian.com" || $domain_name1[$key] == "nytimes.com" || $domain_name1[$key] == "lefigaro.fr" || $domain_name1[$key] == "bloomberg.com" || $domain_name1[$key] == "businessinsider.in" || $domain_name1[$key] == "techcrunch.com" || $domain_name1[$key] == "ft.com" || $domain_name1[$key] == "bbc.com" )
				{		
					$type[] = "1";		
				}
				else{ $type[] = "0";}
			}
		}
		$s=sizeof($result);
		for($i=0; $i<$s; $i++){
		
		$j=json_decode($result[$i]['images'],true);

		if(is_array($j)){
			$post_imgs="";
			foreach($j as $key=>$value){
						$m=$value;
						$post_imgs.=$m.',';
					}
			}
		$total_img= rtrim($post_imgs, ', ');
		
			$data[]=array(
				"type" => $type[$i],
				"post_id" => $result[$i]['post_id'],
				"post_title" => $result[$i]['title'],
				"post_images" => $total_img,
				"post_url" => $result[$i]['url'],
				"is_approved" => $result[$i]['is_approved'],
				"time_since" => $result[$i]['time_since'],
				"id" => $result[$i]['users_id'],
				"username" => $result[$i]['name'],
				"profile_pic" => $result[$i]['profile_pic'],
				"c_count" => $result[$i]['c_count'],
				"domain_name"=>$domain_name1[$i]
			);
		}
		return $data;
	}
	
	function getHomePageDetailsWthoutPaging(){
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";
		$data = array();
		
		$sql="SELECT temp.*, 
CASE 
				WHEN DATEDIFF(NOW(),temp.created_on) != 0 THEN CONCAT(DATEDIFF(NOW(),temp.created_on) ,'d ago')
				WHEN HOUR(TIMEDIFF(NOW(),temp.created_on)) != 0 THEN CONCAT(HOUR(TIMEDIFF(NOW(),temp.created_on)) ,'h ago')
				WHEN MINUTE(TIMEDIFF(NOW(),temp.created_on)) != 0 THEN CONCAT(MINUTE(TIMEDIFF(NOW(),temp.created_on)) ,'m ago')
				ELSE
				CONCAT(SECOND(TIMEDIFF(NOW(),temp.created_on)) ,'s ago')
			END as time_since
		FROM (
		SELECT P.id as post_id, P.title, P.images, P.url, P.approved as is_approved, P.created_on, U.id as users_id,
			U.name, CONCAT('$path',profile_pic) as profile_pic, (SELECT COUNT( C.id ) FROM comment AS C join share on share.id=C.share_id WHERE share.post_id = P.id
			) AS c_count FROM  `post` AS P AND C.post_type=1
				JOIN users AS U ON P.users_id = U.id
		UNION
		SELECT S.post_id as post_id, P.title, P.images, P.url, S.approved as is_approved, S.created_on, U.id as users_id,
			U.name, CONCAT('$path',profile_pic) as profile_pic, (SELECT COUNT( C.id ) 
			FROM comment AS C
			WHERE C.post_id = S.post_id AND C.post_type=2
			) AS c_count FROM `share` AS S
				JOIN users as U ON S.users_id = U.id
            JOIN post as P on S.post_id= P.id
		)
		 AS temp ORDER BY temp.created_on DESC";
		$stmt=$conn->prepare($sql);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		
		foreach($result as $key => $value1){
			$domain_name = $result[$key]['url'];
		    $domain_name1[] = str_ireplace('www.', '', parse_url($domain_name, PHP_URL_HOST));
			//$domain_name = GeneralFunctions::get_domain($domain_name);
		}
		if(!empty($domain_name1)){
			foreach($domain_name1 as $key => $value2){
				if($domain_name1[$key] == "economist.com" || $domain_name1[$key] == "lemonde.fr" || $domain_name1[$key] == "theguardian.com" || $domain_name1[$key] == "nytimes.com" || $domain_name1[$key] == "lefigaro.fr" || $domain_name1[$key] == "bloomberg.com" || $domain_name1[$key] == "businessinsider.in" || $domain_name1[$key] == "techcrunch.com" || $domain_name1[$key] == "ft.com" || $domain_name1[$key] == "bbc.com" )
				{		
					$type[] = "1";		
				}
				else{ $type[] = "0";}
			}
		}
		$s=sizeof($result);
		for($i=0; $i<$s; $i++){
		
		$j=json_decode($result[$i]['images'],true);
		
		if(is_array($j)){
			$post_imgs="";
			foreach($j as $key=>$value){
						$m=$value;
						$post_imgs.=$m.',';
					}
			}
		$total_img= rtrim($post_imgs, ', ');
		
			$data[]=array(
				"type" => $type[$i],
				"post_id" => $result[$i]['post_id'],
				"post_title" => $result[$i]['title'],
				"post_images" => $total_img,
				"post_url" => $result[$i]['url'],
				"is_approved" => $result[$i]['is_approved'],
				"time_since" => $result[$i]['time_since'],
				"id" => $result[$i]['users_id'],
				"username" => $result[$i]['name'],
				"profile_pic" => $result[$i]['profile_pic'],
				"c_count" => $result[$i]['c_count'],
				"domain_name"=>$domain_name1[$i]
			);
		}
		return $data;
	}
	
	// get all comments---->>>
	function getAllComments($share_id, $users_id){
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";
		
		$sql1="SELECT users_id FROM `share` WHERE id = :share_id";
		$stmt1=$conn->prepare($sql1);
		$stmt1->bindValue(':share_id', $share_id);
		try{
			$stmt1->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$res = $stmt1->FetchAll(PDO::FETCH_ASSOC);
		$post_by = $res[0]['users_id'];
		
		$sql="SELECT temp.*, 
			CASE 
				WHEN DATEDIFF(NOW(),temp.created_on) != 0 THEN CONCAT(DATEDIFF(NOW(),temp.created_on) ,'d ago')
				WHEN HOUR(TIMEDIFF(NOW(),temp.created_on)) != 0 THEN CONCAT(HOUR(TIMEDIFF(NOW(),temp.created_on)) ,'h ago')
				WHEN MINUTE(TIMEDIFF(NOW(),temp.created_on)) != 0 THEN CONCAT(MINUTE(TIMEDIFF(NOW(),temp.created_on)) ,'m ago')
				ELSE
				CONCAT(SECOND(TIMEDIFF(NOW(),temp.created_on)) ,'s ago')
			END as time_since 
		FROM (

			SELECT c.id as comment_id, c.comment, c.created_on, u.id, u.name AS username, CONCAT(  '$path', profile_pic ) AS profile_pic
			FROM users AS u
			JOIN  `comment` AS c ON c.users_id = u.id
			WHERE  c.share_id =:share_id
		) AS temp
		ORDER BY temp.created_on DESC ";
		
		
			
		$stmt=$conn->prepare($sql);
		//$stmt->bindValue(':users_id', $users_id);
		$stmt->bindValue(':share_id', $share_id);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		return $result;
	}

	function insertComment($users_id, $share_id, $comment){
		global $conn;

		$sql="INSERT into `comment` VALUES (DEFAULT, :users_id, :share_id, :comment, 'n' , now())";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);
		$stmt->bindValue(':share_id', $share_id);
		$stmt->bindValue(':comment', $comment);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$comment_id=$conn->lastInsertId();
		return $comment_id;
	}

//search_users---------->>>>>>>>>>

	function getAllUsers($searchkey, $users_id1){
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";
		
		$sql="SELECT U.id AS users_id, U.name AS username, CONCAT('$path',profile_pic) as profile_pic, (
				
				SELECT status 
				FROM follow AS F
				WHERE F.users_id1 = :users_id1
				AND F.users_id2 = U.id
				) AS is_followed, (
				
				SELECT COUNT(*) 
				FROM `share` S 
				WHERE S.users_id = U.id 
				) AS p_count
				
				FROM  `users` AS U
				WHERE name LIKE '%{$searchkey}%' ";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id1', $users_id1);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		
		//foreach($result as $key => $value){
		//	if(empty($result[$key]['is_followed'])){
		//		$friend_request1 = "not send";
		//	}
		//	else{
		//		if($result[$key]['is_followed'] == 2){
		//			$friend_request1 = "accepted";
		//		}
		//		elseif($result[$key]['is_followed'] == 1){
		//			$friend_request1 = "sent";
		//		}
		//	}
		//	$friend_request[] = $friend_request1;
		//}
		
		$s=sizeof($result);
		for($i=0; $i<$s; $i++){
			$data[] = array(
				"p_count" => $result[$i]['p_count'],
				"id" => $result[$i]['users_id'],
				"username" => $result[$i]['username'],
				"profile_pic" => $result[$i]['profile_pic'],
				"is_followed" => $result[$i]['is_followed']?$result[$i]['is_followed']:0
				//"friend_request" => $friend_request[$i]
			);
		}
		return $data;
	}

	//PROFILE PAGE DETAILS-------->>>>>>
	function getAllPostsByUserwthpaging($users_id, $offset, $row_count){
		global $conn;

		$sql="SELECT temp.*,
			CASE 
				WHEN DATEDIFF(NOW(),temp.created_on) != 0 THEN CONCAT(DATEDIFF(NOW(),temp.created_on) ,'d ago')
				WHEN HOUR(TIMEDIFF(NOW(),temp.created_on)) != 0 THEN CONCAT(HOUR(TIMEDIFF(NOW(),temp.created_on)) ,'h ago')
				WHEN MINUTE(TIMEDIFF(NOW(),temp.created_on)) != 0 THEN CONCAT(MINUTE(TIMEDIFF(NOW(),temp.created_on)) ,'m ago')
				ELSE
				CONCAT(SECOND(TIMEDIFF(NOW(),temp.created_on)) ,'s ago')
			END as time_since FROM( 
			
			SELECT S.users_id as users_id1, S.users_id as users_id2, U.name, S.post_type, S.id as share_id, P.id as post_id, P.title, P.description, P.images, P.url, S.approved AS is_approved, S.created_on,
				(SELECT COUNT(*) 
				FROM post_read AS PR
				WHERE PR.users_id=:users_id AND PR.post_id=S.id
				) AS post_status,
				
				(SELECT COUNT( C.id ) 
				FROM comment AS C
				WHERE C.share_id = S.id 
				) AS c_count FROM  `share` AS S
				JOIN `post` AS P ON P.id=S.post_id
				JOIN `users` AS U ON U.id = S.users_id
				WHERE S.users_id=:users_id AND S.post_type=1				
	UNION

			SELECT SN.users_id1, SN.users_id2, U.name, S.post_type, S.id as share_id, P.id as post_id, P.title, P.description, P.images, P.url, S.approved AS is_approved, S.created_on,
				(SELECT COUNT(*) 
				FROM post_read AS PR
				WHERE PR.users_id=:users_id AND PR.post_id=S.id
				) AS post_status,
				
				(SELECT COUNT( C.id ) 
				FROM comment AS C
				WHERE C.share_id = S.id 
				) AS c_count FROM  `shared_noti` AS SN
				JOIN `share` AS S ON S.id= SN.share_id
				JOIN `post` AS P ON P.id=S.post_id
				JOIN `users` AS U ON U.id = SN.users_id1
				WHERE SN.users_id2=:users_id
			) AS temp 
				ORDER BY temp.created_on DESC LIMIT {$offset}, {$row_count} ";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		
		foreach($result as $key => $value1){
			$domain_name = $result[$key]['url'];
		    $domain_name1[] = str_ireplace('www.', '', parse_url($domain_name, PHP_URL_HOST));
			//$domain_name = GeneralFunctions::get_domain($domain_name);
		}
		if(!empty($domain_name1)){   //if to remove error:invalid argument --> if pageno=2 
			foreach($domain_name1 as $key => $value2){
				if($domain_name1[$key] == "economist.com" || $domain_name1[$key] == "lemonde.fr" || $domain_name1[$key] == "theguardian.com" || $domain_name1[$key] == "nytimes.com" || $domain_name1[$key] == "lefigaro.fr" || $domain_name1[$key] == "bloomberg.com" || $domain_name1[$key] == "businessinsider.in" || $domain_name1[$key] == "techcrunch.com" || $domain_name1[$key] == "ft.com" || $domain_name1[$key] == "bbc.com" )
				{		
					$type[] = "1";		
				}
				else{ $type[] = "0";}
			}
		}
		$s=sizeof($result);
		
		for($i=0; $i<$s; $i++){
		
		$j=json_decode($result[$i]['images'],true);
		if(is_array($j)){
			$post_imgs="";
			foreach($j as $key=>$value){
						$m=$value;
						$post_imgs.=$m.',';
					}
			}
		$total_img= rtrim($post_imgs, ', ');
		
		$k=json_decode($result[$i]['description'],true);
		if(is_array($k)){
			$post_desc="";
			foreach($k as $key=>$value){
						$n=$value;
						$post_desc.=$n."\n";
					}
			}
		$total_desc= rtrim($post_desc, ', ');
		
		
		
			$data[]=array(
				"u_id" => $result[$i]['users_id2'],
				"u_id2" => $result[$i]['users_id1'],
				"username" => $result[$i]['name'],
				"post_type" => $result[$i]['post_type'],
				"post_status" => $result[$i]['post_status'] ? 1 : 0,
				"type" => $type[$i],
				"share_id" => $result[$i]['share_id'],
				"post_id" => $result[$i]['post_id'],
				"post_title" => $result[$i]['title'],
				"post_description" => $total_desc,
				"post_images" => $total_img,
				"post_url" => $result[$i]['url'],
				"is_approved" => $result[$i]['is_approved'],
				"time_since" => $result[$i]['time_since'],
				"c_count" => $result[$i]['c_count'],
				"domain_name"=>$domain_name1[$i]
			);
		}
		return $data;
	}

	function getAllPostsByUserwthoutpaging($users_id){
		global $conn;

		$sql="SELECT temp.*,
			CASE 
				WHEN DATEDIFF(NOW(),temp.created_on) != 0 THEN CONCAT(DATEDIFF(NOW(),temp.created_on) ,'d ago')
				WHEN HOUR(TIMEDIFF(NOW(),temp.created_on)) != 0 THEN CONCAT(HOUR(TIMEDIFF(NOW(),temp.created_on)) ,'h ago')
				WHEN MINUTE(TIMEDIFF(NOW(),temp.created_on)) != 0 THEN CONCAT(MINUTE(TIMEDIFF(NOW(),temp.created_on)) ,'m ago')
				ELSE
				CONCAT(SECOND(TIMEDIFF(NOW(),temp.created_on)) ,'s ago')
			END as time_since FROM( 
			
			SELECT S.users_id as users_id1, S.users_id as users_id2, U.name, S.post_type, S.id as share_id, P.id as post_id, P.title, P.description, P.images, P.url, S.approved AS is_approved, S.created_on,
				(SELECT COUNT(*) 
				FROM post_read AS PR
				WHERE PR.users_id=:users_id AND PR.post_id=S.id
				) AS post_status,
				
				(SELECT COUNT( C.id ) 
				FROM comment AS C
				WHERE C.share_id = S.id 
				) AS c_count FROM  `share` AS S
				JOIN `post` AS P ON P.id=S.post_id
				JOIN `users` AS U ON U.id = S.users_id
				WHERE S.users_id=:users_id AND S.post_type=1				
	UNION

			SELECT SN.users_id1, SN.users_id2, U.name, S.post_type, S.id as share_id, P.id as post_id, P.title, P.description, P.images, P.url, S.approved AS is_approved, S.created_on,
				(SELECT COUNT(*) 
				FROM post_read AS PR
				WHERE PR.users_id=:users_id AND PR.post_id=S.id
				) AS post_status,
				
				(SELECT COUNT( C.id ) 
				FROM comment AS C
				WHERE C.share_id = S.id 
				) AS c_count FROM  `shared_noti` AS SN
				JOIN `share` AS S ON S.id= SN.share_id
				JOIN `post` AS P ON P.id=S.post_id
				JOIN `users` AS U ON U.id = SN.users_id1
				WHERE SN.users_id2=:users_id
			) AS temp 
				ORDER BY temp.created_on DESC ";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		
		foreach($result as $key => $value1){
			$domain_name = $result[$key]['url'];
		    $domain_name1[] = str_ireplace('www.', '', parse_url($domain_name, PHP_URL_HOST));
			//$domain_name = GeneralFunctions::get_domain($domain_name);
		}
		if(!empty($domain_name1)){
			foreach($domain_name1 as $key => $value2){
				if($domain_name1[$key] == "economist.com" || $domain_name1[$key] == "lemonde.fr" || $domain_name1[$key] == "theguardian.com" || $domain_name1[$key] == "nytimes.com" || $domain_name1[$key] == "lefigaro.fr" || $domain_name1[$key] == "bloomberg.com" || $domain_name1[$key] == "businessinsider.in" || $domain_name1[$key] == "techcrunch.com" || $domain_name1[$key] == "ft.com" || $domain_name1[$key] == "bbc.com" )
				{		
					$type[] = "1";		
				}
				else{ $type[] = "0";}
			}
		}
		$s=sizeof($result);
		for($i=0; $i<$s; $i++){
		
		$j=json_decode($result[$i]['images'],true);
		if(is_array($j)){
			$post_imgs="";
			foreach($j as $key=>$value){
						$m=$value;
						$post_imgs.=$m.',';
					}
			}
		$total_img= rtrim($post_imgs, ', ');
		
		$k=json_decode($result[$i]['description'],true);
		if(is_array($k)){
			$post_desc="";
			foreach($k as $key=>$value){
						$n=$value;
						$post_desc.=$n."\n";
					}
			}
		$total_desc= rtrim($post_desc, ', ');
		
			$data[]=array(
				"u_id" => $result[$i]['users_id2'],
				"u_id2" => $result[$i]['users_id1'],
				"username" => $result[$i]['name'],
				"post_type" => $result[$i]['post_type'],
				"post_status" => $result[$i]['post_status'] ? 1 : 0,
				"type" => $type[$i],
				"share_id" => $result[$i]['share_id'],
				"post_id" => $result[$i]['post_id'],
				"post_title" => $result[$i]['title'],
				"post_description" => $total_desc,
				"post_images" => $total_img,
				"post_url" => $result[$i]['url'],
				"is_approved" => $result[$i]['is_approved'],
				"time_since" => $result[$i]['time_since'],
				"c_count" => $result[$i]['c_count'],
				"domain_name"=>$domain_name1[$i]
			);
		}
		return $data;
	}
	
	function getOtherUserDetails($users_id, $others_id){   // with is_followed
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";
		
		$sql="SELECT U.id, U.name as username, U.profile_pic as profile_pic_name, U.cover_pic as cover_pic_name, CONCAT('$path',profile_pic) as profile_pic, CONCAT('$path',cover_pic) as cover_pic, U.email, U.fb_id, U.dob, U.gender, U.token, U.created_on, U.apn_id, U.device_id, ( 
		
				SELECT COUNT(*) 
				FROM `share` S 
				WHERE S.users_id = U.id 
				) AS p_count, (

				SELECT COUNT( F.id ) 
				FROM follow AS F
				WHERE F.users_id2 = U.id
				) AS followers, (

				SELECT COUNT( F.id ) 
				FROM follow AS F
				WHERE F.users_id1 =:others_id AND F.status=2
				) AS following, (
				
				SELECT `status` 
				FROM `follow` 
				WHERE `users_id1`=:users_id AND `users_id2`=:others_id
				) AS is_followed
				
				FROM users AS U
				WHERE U.id ='$others_id' ";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);
		$stmt->bindValue(':others_id', $others_id);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		$fresult=$result;
		if(empty($fresult[0]['is_followed'])){
			$fresult[0]['is_followed'] = 0;
		}
		return $fresult;
	}
	
	function getUserDetails($users_id){
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";
		
		$sql="SELECT U.id, U.name as username, U.profile_pic as profile_pic_name, U.cover_pic as cover_pic_name, CONCAT('$path',profile_pic) as profile_pic, CONCAT('$path',cover_pic) as cover_pic, U.email, U.fb_id, U.dob, U.gender, U.token, U.created_on, U.apn_id, U.device_id, ( 
		
				SELECT COUNT(*) 
				FROM `share` S 
				WHERE S.users_id = U.id 
				) AS p_count, (

				SELECT COUNT( F.id ) 
				FROM follow AS F
				WHERE F.users_id2 = U.id AND F.status=2
				) AS followers, (

				SELECT COUNT( F.id ) 
				FROM follow AS F
				WHERE F.users_id1 =:users_id AND F.status=2
				) AS following
				
				FROM users AS U
				WHERE U.id =:users_id ";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id' ,$users_id);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		
		return $result;
	}

	// function getUserDetails($users_id){
	// 	global $conn;

	// 	$sql="SELECT U.id, U.name, U.email, U.profile_pic, U.cover_pic, P.id AS post_id, P.title, P.images, P.created_on, C.id AS comment_id, C.comment, C.created_on, US.name AS cmtr_name, US.profile_pic AS cmtr_img, (
	// 			SELECT COUNT( P.id ) 
	// 			FROM post AS P
	// 			WHERE P.users_id = U.id
	// 			) AS p_count, (

	// 			SELECT COUNT( F.id ) 
	// 			FROM follow AS F
	// 			WHERE F.users_id2 = U.id
	// 			) AS followers, (

	// 			SELECT COUNT( F.id ) 
	// 			FROM follow AS F
	// 			WHERE F.users_id1 ='$users_id'
	// 			) AS following
	// 			FROM users AS U
	// 			LEFT JOIN post AS P ON P.users_id = U.id
	// 			LEFT JOIN COMMENT AS C ON C.post_id = P.id
	// 			LEFT JOIN users AS US ON C.users_id = US.id
	// 			WHERE U.id ='$users_id'
	// 			GROUP BY C.id
	// 			ORDER BY P.created_on DESC ";
	// 	$stmt=$conn->prepare($sql);
	// 	try{
	// 		$stmt->execute();
	// 	}
	// 	catch(PDOException $e){
	//		echo $e->getMessage();
	// 	}
	// 	$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
	// 	return $result;
	// }

	// function getPostCount($users_id){
	// 	global $conn;

	// 	$sql="SELECT COUNT( p.id ) AS post_count
	// 			FROM  `post` AS p
	// 			JOIN users AS u ON p.users_id = u.id
	// 			WHERE p.users_id='$users_id'";
	// 	$stmt=$conn->prepare($sql);
	// 	try{
	// 		$stmt->execute();
	// 	}
	// 	catch(PDOException $e){
	//		echo $e->getMessage();
	// 	}
	// 	$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
	// 	return $result;
	// }

	//share post---->>>>>
	
	function postBy($post_id){   //used in repost.php
		global $conn;
		
		$sql="SELECT  `users_id` FROM  `share` WHERE post_id =:post_id";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':post_id', $post_id);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		if ($result) {
			$user_id = $result[0]['users_id'];			
		}
		else{
			$user_id="";
		}
		return $user_id;
	}
	
	function repost($users_id1, $users_id2, $post_id, $action){
		global $conn;
		
		$sql="INSERT into `share` VALUES (DEFAULT, :users_id2, :post_id, :action, '2', 'n', now())";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id2', $users_id2);
		$stmt->bindValue(':post_id', $post_id);
		$stmt->bindValue(':action', $action );
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$repost_id=$conn->lastInsertId();
		
		$sql1="INSERT into `shared_noti` VALUES (DEFAULT, :users_id1, :users_id2, :share_id, now())";
		$stmt1=$conn->prepare($sql1);
		$stmt1->bindValue(':users_id1', $users_id1);
		$stmt1->bindValue(':users_id2', $users_id2);
		$stmt1->bindValue(':share_id', $repost_id);
		try{
			$stmt1->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		
		return $repost_id;
	}
	
	function approvePost($share_id){
		global $conn;
		
		$sql="UPDATE `share` SET `approved`='y' WHERE id=:share_id";
		$stmt=$conn->prepare($sql);
			$stmt->bindValue(':share_id', $share_id);
		try{ 
			$stmt->execute();
		}
		catch(PDOException $e){ 
			echo $e->getMessage(); 
		}
	}
	
	
	
	function underapprovePost($share_id){
		global $conn;
		
		$sql="UPDATE `share` SET `approved`='' WHERE id=:share_id";
		$stmt=$conn->prepare($sql);
			$stmt->bindValue(':share_id', $share_id);
		try{ 
			$stmt->execute();
		}
		catch(PDOException $e){ }
	}
	

	function disapprovePost($share_id){
		global $conn;

		$sql="UPDATE `share` SET `approved`='n' WHERE id=:share_id";
		$stmt=$conn->prepare($sql);
			$stmt->bindValue(':share_id', $share_id);
		try{ 
			$stmt->execute();
		}
		catch(PDOException $e){ 
			echo $e->getMessage(); 
		}
	}
	
	function likePost($users_id, $share_id){
		global $conn;
		
		$sql="INSERT INTO `likes` VALUES (DEFAULT, :users_id, :share_id, 'n', now())";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);
		$stmt->bindValue(':share_id', $share_id);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$like_id=$conn->lastInsertId();
		return $like_id;
	}
	/* can be used for repost
	function approvePost($post_id){
		global $conn;
		
		$sql="UPDATE `share` SET `approved`='y' WHERE `id`=:post_id";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':post_id', $post_id);
		try{ 
			$stmt->execute();
		}
		catch(PDOException $e){ 
			echo $e->getMessage(); 
		}
	}

	function disapprovePost($post_id){
		global $conn;

		$sql="UPDATE `share` SET `approved`='n' WHERE `id`=:post_id";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':post_id', $post_id);
		try{ 
			$stmt->execute();
		}
		catch(PDOException $e){ 
			echo $e->getMessage(); 
		}
	}
	*/
	function deletePost($share_id){
		global $conn;
		
		$sql="SELECT post_id FROM `share` WHERE id='$share_id'";
		$stmt=$conn->prepare($sql);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		$real_post_id = $result[0]['post_id'];
		
		$sql="DELETE FROM `share` WHERE id='$share_id'";
		$stmt=$conn->prepare($sql);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		
		//$sql1="DELETE FROM `post` WHERE id='$real_post_id'";
		//$stmt1=$conn->prepare($sql1);
		//try{
		//	$stmt1->execute();
		//}
		//catch(PDOException $e){
		//	echo $e->getMessage();
		//}
	}

	function postRead($users_id, $share_id){
		global $conn;
		
		$sql="INSERT INTO `post_read` VALUES(DEFAULT, :users_id, :share_id, now())";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);
		$stmt->bindValue(':share_id', $share_id);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $conn->lastInsertId();
		return $result;
	}
	
	function getPostDetails($share_id){
		global $conn;

		$sql="SELECT U.id as users_id, P.id, P.images, P.title, P.description, P.created_on, P.url, P.approved, 
			CASE 
			WHEN DATEDIFF(NOW(),P.created_on) != 0 THEN CONCAT(DATEDIFF(NOW(),P.created_on) ,'d ago')
			WHEN HOUR(TIMEDIFF(NOW(),P.created_on)) != 0 THEN CONCAT(HOUR(TIMEDIFF(NOW(),P.created_on)) ,'h ago')
			WHEN MINUTE(TIMEDIFF(NOW(),P.created_on)) != 0 THEN CONCAT(MINUTE(TIMEDIFF(NOW(),P.created_on)) ,'m ago')
			ELSE
			CONCAT(SECOND(TIMEDIFF(NOW(),P.created_on)) ,'s ago')
		END as time_since
				FROM  `post` AS P
				JOIN `share` AS S ON S.post_id=P.id
				JOIN `users` AS U ON U.id=S.users_id
				WHERE S.id='$share_id'";
		$stmt=$conn->prepare($sql);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		
		$domain_name = $result[0]['url'];
		$domain_name1 = str_ireplace('www.', '', parse_url($domain_name, PHP_URL_HOST));
		//$domain_name = GeneralFunctions::get_domain($domain_name);
		
		if($domain_name1 == "economist.com" || $domain_name1 == "lemonde.fr" || $domain_name1 == "theguardian.com" || $domain_name1 == "nytimes.com" || $domain_name1 == "lefigaro.fr" || $domain_name1 == "bloomberg.com" || $domain_name1 == "businessinsider.com" || $domain_name1 == "techcrunch.com" || $domain_name1 == "ft.com" || $domain_name1 == "bbc.com" )
			{		$type = "1";		}
		else
			{	$type = "0";		}
		
		$s=sizeof($result);
		for($i=0; $i<$s; $i++){
		
		$j=json_decode($result[$i]['images'],true);
		if(is_array($j)){
			$post_imgs="";
			foreach($j as $key=>$value){
						$m=$value;
						$post_imgs.=$m.',';
					}
			}
		$total_img= rtrim($post_imgs, ', ');
		$k=json_decode($result[$i]['description'],true);
		if(is_array($k)){
			$post_desc="";
			foreach($k as $key=>$value){
						$n=$value;
						$post_desc.=$n."\n";
					}
			}
		$total_desc= rtrim($post_desc, ', ');
		
			$data[]=array(
				"u_id" => $result[$i]['users_id'],
				"type" => $type,
				"post_id" => $result[$i]['id'],
				"post_title" => $result[$i]['title'],
				"post_description" => $total_desc,
				"post_images" => $total_img,
				"post_url" => $result[$i]['url'],
				"is_approved" => $result[$i]['approved'],
				"time_since" => $result[$i]['time_since'],
				"domain_name"=>$domain_name1
			);
		}
		return $data;

	}

// news-circle page----->>>>>>>>>
	function follow_user($users_id1, $users_id2){
		global $conn;
				
		$sql="INSERT INTO `follow` VALUES (DEFAULT, :users_id1, :users_id2, '1','n', now())";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id1', $users_id1);
		$stmt->bindValue(':users_id2', $users_id2);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){}
		$follow_id=$conn->lastInsertId();
		return $follow_id;
	}
	
	function get_follow_id($users_id1, $users_id2){
		global $conn;
				
		$sql="select * from `follow` where users_id1=:users_id1 and users_id2=:users_id2 
				UNION select * from `follow` where users_id2=:users_id1 and users_id1=:users_id2 ";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id1', $users_id1);
		$stmt->bindValue(':users_id2', $users_id2);
		try{$stmt->execute();}
		catch(PDOException $e){}
		$res=$stmt->fetchAll();
		$follow_id=$res[0]['id'];
		return $follow_id;
	}
	
	function getAllFollowRequests($users_id){
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";

		$sql="SELECT U.id, U.name as username, CONCAT('$path',profile_pic) as profile_pic
				FROM  `follow` AS F
				JOIN users AS U ON F.users_id1 = U.id
				WHERE users_id2 = :users_id AND status = '1'";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		return $result;
	}

	function getAllFollowers($users_id){
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";

		$sql="SELECT U.id, U.name as username, CONCAT('$path',profile_pic) as profile_pic, ( 
				
				SELECT COUNT(*) 
				FROM `share` S 
				WHERE S.users_id = U.id 
				) AS p_count,(

				SELECT status
				FROM follow
				WHERE follow.users_id2 = U.id
				AND follow.users_id1 =:users_id
				) AS is_followed
				
				FROM  `follow` AS F
				LEFT JOIN users AS U ON F.users_id1 = U.id
				WHERE F.users_id2 =:users_id
				AND F.status = '2'";

		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		
		$s=sizeof($result);
		for($i=0; $i<$s; $i++){
			$data[] = array(
				"id" => $result[$i]['id'],
				"username" => $result[$i]['username'],
				"profile_pic" => $result[$i]['profile_pic'],
				"p_count" => $result[$i]['p_count'],
				"is_followed" => $result[$i]['is_followed']?$result[$i]['is_followed']:"0"
			);
		}
		return $data;
	}
	
	function getAllFollowings($users_id){
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";

		$sql="SELECT U.id, U.name as username, CONCAT('$path',profile_pic) as profile_pic, ( 
				
				SELECT COUNT(*) 
				FROM `share` S 
				WHERE S.users_id = U.id 
				) AS p_count, (

				SELECT status
				FROM follow                                 
				WHERE follow.users_id1 = U.id
				AND follow.users_id2 =:users_id
				) AS is_followed
				
				FROM  `follow` AS F
				LEFT JOIN users AS U ON F.users_id2 = U.id
				WHERE F.users_id1 =:users_id
				AND F.status = '2'"; 	
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		
		$s=sizeof($result);
		for($i=0; $i<$s; $i++){
			$data[] = array(
				"id" => $result[$i]['id'],
				"username" => $result[$i]['username'],
				"profile_pic" => $result[$i]['profile_pic'],
				"p_count" => $result[$i]['p_count'],
				"is_followed" => $result[$i]['is_followed']?$result[$i]['is_followed']:"0"
			);
		}
		return $data;
	}
	
	function getAllFollowing($users_id){
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";

		$sql="SELECT U.id, U.name as username, CONCAT('$path',profile_pic) as profile_pic, ( 
				
				SELECT COUNT(*) 
				FROM `share` S 
				WHERE S.users_id = U.id 
				) AS p_count

				FROM  `follow` AS F
				LEFT JOIN users AS U ON F.users_id2 = U.id
				WHERE F.users_id1 =:users_id AND F.status=2";		
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		
		$s=sizeof($result);
		for($i=0; $i<$s; $i++){
			$data[] = array(
				"id" => $result[$i]['id'],
				"username" => $result[$i]['username'],
				"profile_pic" => $result[$i]['profile_pic'],
				"p_count" => $result[$i]['p_count'],
				"is_followed" => "2"
			);
		}
		return $data;
	}
	
	function isFollowing($users_id1, $users_id2){
		global $conn;
		
		$sql="SELECT `status` FROM `follow` WHERE `users_id1`=:users_id1 AND `users_id2`=:users_id2";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id1', $users_id1);
		$stmt->bindValue(':users_id2', $users_id2);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		$result = $result[0]['status'] ? $result[0]['status'] :"0";
		return $result;
	}

//notifications--->>>>
/*
	function viewLikeNotifications($users_id){
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";

		$sql="SELECT L.id AS like_id, P.id AS post_id, P.title, P.url, U.id AS users_id, U.name, CONCAT('$path',profile_pic) as profile_pic, L.created_on, L.is_red,
				CASE 
			WHEN DATEDIFF(NOW(),L.created_on) != 0 THEN CONCAT(DATEDIFF(NOW(),L.created_on) ,'d ago')
			WHEN HOUR(TIMEDIFF(NOW(),L.created_on)) != 0 THEN CONCAT(HOUR(TIMEDIFF(NOW(),L.created_on)) ,'h ago')
			WHEN MINUTE(TIMEDIFF(NOW(),L.created_on)) != 0 THEN CONCAT(MINUTE(TIMEDIFF(NOW(),L.created_on)) ,'m ago')
			ELSE
			CONCAT(SECOND(TIMEDIFF(NOW(),L.created_on)) ,'s ago')
		END as time_since
				FROM  `post` P
				JOIN likes L ON P.id = L.post_id
				JOIN users U ON L.users_id = U.id
				WHERE P.users_id =:users_id AND L.users_id != :users_id";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);		
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		foreach($result as $key => $value1){
			$domain_name = $result[$key]['url'];
		    $domain_name1[] = str_ireplace('www.', '', parse_url($domain_name, PHP_URL_HOST));
			//$domain_name = GeneralFunctions::get_domain($domain_name);
		}
		
		if(!empty($domain_name1)){
			foreach($domain_name1 as $key => $value2){
				if($domain_name1[$key] == "economist.com" || $domain_name1[$key] == "lemonde.fr" || $domain_name1[$key] == "theguardian.com" || $domain_name1[$key] == "nytimes.com" || $domain_name1[$key] == "lefigaro.fr" || $domain_name1[$key] == "bloomberg.com" || $domain_name1[$key] == "businessinsider.in" || $domain_name1[$key] == "techcrunch.com" || $domain_name1[$key] == "ft.com" || $domain_name1[$key] == "bbc.com" )
				{		
					$post_type[] = "1";		
				}
				else{ $post_type[] = "0";}
			}
		}
		
		$s=sizeof($result);
		for($i=0; $i<$s; $i++){
		
			$data[]=array(
				"type" => 1,
				"notification_id" => $result[$i]['like_id'],
				"post_type" => $post_type[$i],
				"is_read" => $result[$i]['is_red'],
				"post_id" => $result[$i]['post_id'],
				"share_id" => $result[$i]['share_id'],
				"post_title" => $result[$i]['title'],
				"post_url" => $result[$i]['url'],
				"time_since" => $result[$i]['time_since'],
				"created_on" => $result[$i]['created_on'],
				"id" => $result[$i]['users_id'],
				"username" => $result[$i]['name'],
				"profile_pic" => $result[$i]['profile_pic'],
				"domain_name"=>$domain_name1[$i]
			);
		}
		return $data;
	}
*/
	function viewShareNotifications($users_id){     
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";

		$sql="SELECT S.id AS share_id, P.id AS post_id, P.title, P.url, U.id AS users_id, U.name, CONCAT('$path',profile_pic) as profile_pic, S.created_on, S.is_red,
		CASE 
			WHEN DATEDIFF(NOW(),S.created_on) != 0 THEN CONCAT(DATEDIFF(NOW(),S.created_on) ,'d ago')
			WHEN HOUR(TIMEDIFF(NOW(),S.created_on)) != 0 THEN CONCAT(HOUR(TIMEDIFF(NOW(),S.created_on)) ,'h ago')
			WHEN MINUTE(TIMEDIFF(NOW(),S.created_on)) != 0 THEN CONCAT(MINUTE(TIMEDIFF(NOW(),S.created_on)) ,'m ago')
			ELSE
			CONCAT(SECOND(TIMEDIFF(NOW(),S.created_on)) ,'s ago')
		END as time_since
				FROM  `shared_noti` AS SN
				JOIN `share` AS S ON S.id=SN.share_id
				JOIN `post` AS P ON P.id=S.post_id
				JOIN `users` AS U ON U.id = SN.users_id2
				WHERE S.post_type=2 AND SN.users_id1 = :users_id ";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);		
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		foreach($result as $key => $value1){
			$domain_name = $result[$key]['url'];
		    $domain_name1[] = str_ireplace('www.', '', parse_url($domain_name, PHP_URL_HOST));
			//$domain_name = GeneralFunctions::get_domain($domain_name);
		}
		
		if(!empty($domain_name1)){
			foreach($domain_name1 as $key => $value2){
				if($domain_name1[$key] == "economist.com" || $domain_name1[$key] == "lemonde.fr" || $domain_name1[$key] == "theguardian.com" || $domain_name1[$key] == "nytimes.com" || $domain_name1[$key] == "lefigaro.fr" || $domain_name1[$key] == "bloomberg.com" || $domain_name1[$key] == "businessinsider.in" || $domain_name1[$key] == "techcrunch.com" || $domain_name1[$key] == "ft.com" || $domain_name1[$key] == "bbc.com" )
				{		
					$post_type[] = "1";		
				}
				else{ $post_type[] = "0";}
			}
		}
		
		$s=sizeof($result);
		for($i=0; $i<$s; $i++){
		
			$data[]=array(
				"type" => 2,
				"notification_id" => $result[$i]['share_id'],
				"share_id" => $result[$i]['share_id'],
				"post_type" => $post_type[$i],
				"is_read" => $result[$i]['is_red'],
				"post_id" => $result[$i]['post_id'],
				"post_title" => $result[$i]['title'],
				"post_url" => $result[$i]['url'],
				"time_since" => $result[$i]['time_since'],
				"created_on" => $result[$i]['created_on'],
				"id" => $result[$i]['users_id'],
				"username" => $result[$i]['name'],
				"profile_pic" => $result[$i]['profile_pic'],
				"domain_name"=>$domain_name1[$i]
			);
		}
		return $data;
	}

	function viewCommentNotifications($users_id){
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";

		$sql="SELECT S.id AS share_id, C.id as comment_id, P.id AS post_id, P.title, P.url, U.id AS users_id, U.name, CONCAT('$path',profile_pic) as profile_pic, C.created_on, C.is_red,
		CASE 
			WHEN DATEDIFF(NOW(),C.created_on) != 0 THEN CONCAT(DATEDIFF(NOW(),C.created_on) ,'d ago')
			WHEN HOUR(TIMEDIFF(NOW(),C.created_on)) != 0 THEN CONCAT(HOUR(TIMEDIFF(NOW(),C.created_on)) ,'h ago')
			WHEN MINUTE(TIMEDIFF(NOW(),C.created_on)) != 0 THEN CONCAT(MINUTE(TIMEDIFF(NOW(),C.created_on)) ,'m ago')
			ELSE
			CONCAT(SECOND(TIMEDIFF(NOW(),C.created_on)) ,'s ago')
		END as time_since
				FROM  `share` AS S 
				JOIN `post` AS P ON P.id=S.post_id
				JOIN `comment` C ON C.share_id = S.id
				JOIN `users` U ON U.id = C.users_id
				WHERE S.users_id =:users_id AND C.users_id != :users_id";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);		
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
		foreach($result as $key => $value1){
			$domain_name = $result[$key]['url'];
		    $domain_name1[] = str_ireplace('www.', '', parse_url($domain_name, PHP_URL_HOST));
			//$domain_name = GeneralFunctions::get_domain($domain_name);
		}
		
		if(!empty($domain_name1)){
			foreach($domain_name1 as $key => $value2){
				if($domain_name1[$key] == "economist.com" || $domain_name1[$key] == "lemonde.fr" || $domain_name1[$key] == "theguardian.com" || $domain_name1[$key] == "nytimes.com" || $domain_name1[$key] == "lefigaro.fr" || $domain_name1[$key] == "bloomberg.com" || $domain_name1[$key] == "businessinsider.in" || $domain_name1[$key] == "techcrunch.com" || $domain_name1[$key] == "ft.com" || $domain_name1[$key] == "bbc.com" )
				{		
					$post_type[] = "1";		
				}
				else{ $post_type[] = "0";}
			}
		}
		
		$s=sizeof($result);
		for($i=0; $i<$s; $i++){
		
			$data[]=array(
				"type" => 3,
				"notification_id" => $result[$i]['comment_id'],
				"share_id" => $result[$i]['share_id'],
				"post_type" => $post_type[$i],
				"is_read" => $result[$i]['is_red'],
				"post_id" => $result[$i]['post_id'],
				"post_title" => $result[$i]['title'],
				"post_url" => $result[$i]['url'],
				"time_since" => $result[$i]['time_since'],
				"created_on" => $result[$i]['created_on'],
				"id" => $result[$i]['users_id'],
				"username" => $result[$i]['name'],
				"profile_pic" => $result[$i]['profile_pic'],
				"domain_name"=>$domain_name1[$i]
			);
		}
		return $data;
	}
	
	function viewFollowRequestNotifications($users_id){
		global $conn;
		$path=BASE_PATH . "/timthumb.php?src=uploads/";

		$sql="SELECT F.id as follow_req_id, F.is_red, F.created_on, U.id AS users_id, U.name, CONCAT('$path',profile_pic) as profile_pic,
			CASE 
				WHEN DATEDIFF(NOW(),F.created_on) != 0 THEN CONCAT(DATEDIFF(NOW(),F.created_on) ,'d ago')
				WHEN HOUR(TIMEDIFF(NOW(),F.created_on)) != 0 THEN CONCAT(HOUR(TIMEDIFF(NOW(),F.created_on)) ,'h ago')
				WHEN MINUTE(TIMEDIFF(NOW(),F.created_on)) != 0 THEN CONCAT(MINUTE(TIMEDIFF(NOW(),F.created_on)) ,'m ago')
				ELSE
				CONCAT(SECOND(TIMEDIFF(NOW(),F.created_on)) ,'s ago')
			END as time_since
				FROM  `follow` F
				JOIN users AS U ON U.id=F.users_id1 
				WHERE F.users_id2 = :users_id AND F.status = '1'";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);		
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
				
		$s=sizeof($result);
		for($i=0; $i<$s; $i++){
		
			$data[]=array(
				"type" => 4,
				"notification_id" => $result[$i]['follow_req_id'],
				"share_id" => $result[$i]['users_id'],
				"is_read" => $result[$i]['is_red'],
				"time_since" => $result[$i]['time_since'],
				"created_on" => $result[$i]['created_on'],
				"id" => $result[$i]['users_id'],
				"username" => $result[$i]['name'],
				"profile_pic" => $result[$i]['profile_pic']
			);
		}
		return $data;
	}
	
	function loginDetails($email, $pass){
		global $conn;
		
		$sql="SELECT id, name, CONCAT('$path',profile_pic) as profile_pic, CONCAT('$path',cover_pic) as cover_pic, email, fb_id, dob, gender, token, created_on, apn_id, device_id FROM `users` WHERE `email`=:email AND `password`=:password";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':email', $email);
		$stmt->bindValue(':password', $pass);
		try{ 
			$stmt->execute();
		}
		catch(PDOException $e){ 
			echo $e->getMessage(); 
		}
		$result1=$stmt->fetch(PDO::FETCH_ASSOC);
		
		return $result1;
	}
	
	function logout($users_id){
		global $conn;

		$sql="UPDATE `users` SET `token`='' WHERE `id`=:users_id";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);
		try{ 
			$stmt->execute();
		}
		catch(PDOException $e){ 
			echo $e->getMessage(); 
		}
	}

?>