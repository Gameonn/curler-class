<?php
	require_once('../phpInclude/dbconn.php');
	require_once('../phpInclude/AdminClass.php');
	require_once('image_finder.class.php');
	require_once('simple_html_dom.php');
	require_once  'php_metaclass/Curler.class.php';
	require_once  'php_metaclass/MetaParser.class.php';
	
	// Set content-type
header('content-type: application/json; charset=utf-8');

$token=$_REQUEST['token']; 
$url = array_key_exists('url', $_REQUEST) ? $_REQUEST['url'] : null;
$action = $_REQUEST['action'] ? $_REQUEST['action'] : "";
$data=array();

	// finding domain-name
	if(strpos($url,'www'))
		$domain_name= str_ireplace('www.', '', parse_url($url, PHP_URL_HOST));
	else
		$domain_name= str_ireplace('mobile.', '', parse_url($url, PHP_URL_HOST));
if (!empty($token) && !empty($url)) {
	$users_id=getUsersId($token);
	if ($users_id) {
		$html = file_get_html( $url );
		if($html){
		//$html = file_get_html( $url );
		
switch ($domain_name) {
	
	case 'economist.com':
		foreach ($html->find('div[id=column-content] img' ) as $element){
			$pic = $element->src;
			$size = getimagesize($pic);
			if($size[0] > 100 && $size[1] > 100){
			$image[] = $pic; 
			}
		}
		$image=json_encode($image);
		foreach ($html->find('article hgroup h1') as $value) {
			$title = $value->plaintext;
		}
		foreach ($html->find('article div[class=main-content] p') as $value) {
		    $description[] = $value->plaintext;
		}
		$description=json_encode($description);
		if($title=="null")
		{
			foreach ($html->find('title') as $value) {
			$title = $value->plaintext;
			}
		}
		if($image=="null")	{
			$tempimg = 'http://www.google.com/s2/favicons?domain=www.'."$domain_name";
			$image[] = $tempimg;
			$image=json_encode($image);
		}
		if($description=="null")	$description="[]";
		
		break;
		
	case 'lemonde.fr':
		foreach ($html->find('div[id=articleBody] img' ) as $element){  
			$size = getimagesize($element->src);   
			if($size[0] > 100 && $size[1] > 100){
			$image[] = $element->src; 
			}
		}
		$image=json_encode($image);
		foreach ($html->find('article[class=article article_normal] h1') as $value) {
			$title = $value->plaintext;
		}
		foreach ($html->find('div[id=articleBody] p') as $value) {
		    $description[] = $value->plaintext;
		}
		$description=json_encode($description);
		if($title=="null")
		{
			foreach ($html->find('title') as $value) {
			$title = $value->plaintext;
			}
		}
		if($image=="null")	{
			$tempimg = 'http://www.google.com/s2/favicons?domain=www.'."$domain_name";
			$image[] = $tempimg;
			$image=json_encode($image);
		}
		if($description=="null")	$description="[]";
		
		break;	

	case 'theguardian.com':
	define('R','http://');
		 foreach ($html->find('figure[class=media-primary media-content] img' ) as $element){ //div[class=gs-container] img       //itemprop=associatedMedia image
		 	$pic=$element->src;
		 	$pic=ltrim($pic,"//");    
			$pic=R.$pic;
		 	$size = getimagesize($pic);            
		 	if($size[0] > 100 && $size[1] > 100){
			$image[] = $pic; 
			}
		}
		$image=json_encode($image);
		foreach ($html->find('h1[class=content__headline]') as $value) {
			$title = $value->plaintext;
		}
		foreach ($html->find('div[class=content__article-body] p') as $value) {
		    $description[] = $value->plaintext;
		}
		$description=json_encode($description);
		if($title=="null")
		{
			foreach ($html->find('title') as $value) {
			$title = $value->plaintext;
			}
		}
		if($image=="null")	{
			$tempimg = 'http://www.google.com/s2/favicons?domain=www.'."$domain_name";
			$image[] = $tempimg;
			$image=json_encode($image);
		}
		if($description=="null")	$description="[]";
		
		break;	

	case 'nytimes.com':

		   // curling
			$curler = (new Curler());
			$body = $curler->get($url);
			$parser = (new MetaParser($body, $url));
			$meta_detail=$parser->getDetails();
			$meta_detail['openGraph']['image'];
		
		
		foreach ($html->find('title') as $value) { //h1[id=story-heading]    //div[id=story-meta] //h1[class=story-heading]
			$title = $value->plaintext;
		}
		foreach ($html->find('div[class=story-body] p') as $value) {         //p[class=story-content]
		    $description[] = $value->plaintext;
		}
		$description=json_encode($description);
		if($title=="null")
		{
			foreach ($html->find('title') as $value) {
			$title = $value->plaintext;
			}
		}
		
			$image[] = 	$meta_detail['openGraph']['image'];
			$image=json_encode($image);
		
	if($image=="null" || $image=="")	{
		$tempimg = 'http://www.google.com/s2/favicons?domain=www.'."$domain_name";
		$image[] = $tempimg;
		$image=json_encode($image);
		}
		if($description=="null")	$description="[]";
		
		break;	

	case 'lefigaro.fr':
		foreach ($html->find('div[class=fig-main-media] img' ) as $element){   //  figure[class=fig-photo] img  
			$size = getimagesize($element->src);
			if($size[0] > 100 && $size[1] > 100){
			$image[] = $element->src; 
			}
		}
		$image=json_encode($image);
		foreach ($html->find('div[class=fig-article-headline] h1') as $value) {
			$title = $value->plaintext;
		}
		foreach ($html->find('div[class=fig-article-body] p') as $value) {
		    $description[] = $value->plaintext;
		}
		$description=json_encode($description);
		if($title=="null")
		{
			foreach ($html->find('title') as $value) {
			$title = $value->plaintext;
			}
		}
		if($image=="null")	{
			$tempimg = 'http://www.google.com/s2/favicons?domain=www.'."$domain_name";
			$image[] = $tempimg;
			$image=json_encode($image);
		}
		if($description=="null")	$description="[]";
		
		break;	

	case 'bloomberg.com':
		foreach ($html->find('figure[class=inline-image] img' ) as $element){
			$size = getimagesize($element->src);
			if($size[0] > 100 && $size[1] > 100){
			$image[] = $element->src; 
			}
		}
		$image=json_encode($image);
		foreach ($html->find('h1 span[class=lede-headline__highlighted]') as $value) {
			$title = $value->plaintext;
		}
		foreach ($html->find('section[class=article-body] p') as $value) {
		    $description[] = $value->plaintext;
		}
		$description=json_encode($description);
		if($title=="null")
		{
			foreach ($html->find('title') as $value) {
			$title = $value->plaintext;
			}
		}
		if($image=="null")	{
			$tempimg = 'http://www.google.com/s2/favicons?domain=www.'."$domain_name";
			$image[] = $tempimg;
			$image=json_encode($image);
		}
		if($description=="null")	$description="[]";
		
		break;	

	case 'businessinsider.in':
	define('R','http://www.businessinsider.in');
	 	foreach ($html->find('div[class=Normal] img' ) as $element){  //div[class=image] img
	 		$pic=R.$element->src;                     // data-original   warning cnt opn file--
	 		//$pic = $element->data-original;
			$size = getimagesize($pic);
			if($size[0] > 100 && $size[1] > 100){
			$image[] = $pic; 
			}
		}
		$image=json_encode($image);
		foreach ($html->find('h1') as $value) {
			$title = $value->plaintext;
		}
		foreach ($html->find('div[class=Normal]') as $value) {
		    $description[] = $value->plaintext;
		}
		$description=json_encode($description);
		if($title=="null")
		{
			foreach ($html->find('title') as $value) {
			$title = $value->plaintext;
			}
		}
		if($image=="null")	{
			$tempimg = 'http://www.google.com/s2/favicons?domain=www.'."$domain_name";
			$image[] = $tempimg;
			$image=json_encode($image);
		}
		if($description=="null")	$description="[]";
		
		break;	

	case 'techcrunch.com':
		foreach ($html->find('div[class=article-entry text] img' ) as $element){
			$size = getimagesize($element->src);
			if($size[0] > 100 && $size[1] > 100){
			$image[] = $element->src; 
			}
		}
		$image=json_encode($image);
		foreach ($html->find('h1[class=alpha tweet-title]') as $value) {
			$title = $value->plaintext;
		}
		foreach ($html->find('div[class=article-entry text] p') as $value) {  // p
		    $description[] = $value->plaintext;
		}
		$description=json_encode($description);
		if($title=="null")
		{
			foreach ($html->find('title') as $value) {
			$title = $value->plaintext;
			}
		}
		if($image=="null")	{
			$tempimg = 'http://www.google.com/s2/favicons?domain=www.'."$domain_name";
			$image[] = $tempimg;
			$image=json_encode($image);
		}
		if($description=="null")	$description="[]";
		
		break;	

	case 'ft.com':
	
		//foreach ($html->find('div[id=storyContent] img' ) as $element){     //class=fullstoryImage or div[id=storyContent] img
		//	$size = getimagesize($element->src);
		//	if($size[0] > 100 && $size[1] > 100){
		//	$image[] = $element->src; 
		//	}
		//}
		//$image=json_encode($image);
		$tempimg = 'http://www.google.com/s2/favicons?domain=www.'."$domain_name";
		$image[] = $tempimg;
		$image=json_encode($image);
		foreach ($html->find('title') as $value) {// div[class=syndicationHeadline] h1  if only h1-->then incorrect else nthg
			$title = $value->plaintext;
		}
		foreach ($html->find('p') as $value) {
		    $description[] = $value->plaintext;
		}
		$description=json_encode($description);
		if($title=="null")
		{
			foreach ($html->find('title') as $value) {
			$title = $value->plaintext;
			}
		}
		if($image=="null")	{
			$tempimg = 'http://www.google.com/s2/favicons?domain=www.'."$domain_name";
			$image[] = $tempimg;
			$image=json_encode($image);
		}
		if($description=="null")	$description="[]";
		
		break;
		
	case 'bbc.com':	
		foreach ($html->find('div[class=caption] img' ) as $element){
			$size = getimagesize($element->src);
			if($size[0] > 100 && $size[1] > 100){
			$image[] = $element->src; 
			}
		}
		$image=json_encode($image);
		foreach ($html->find('h1') as $value) {
			$title = $value->plaintext;
		}
		foreach ($html->find('div[class=story-body] p') as $value) {
		    $description[] = $value->plaintext;
		}
		$description=json_encode($description);
		if($title=="null")
		{
			foreach ($html->find('title') as $value) {
			$title = $value->plaintext;
			}
		}
		if($image=="null")	{
			$tempimg = 'http://www.google.com/s2/favicons?domain=www.'."$domain_name";
			$image[] = $tempimg;
			$image=json_encode($image);
		}
		if($description=="null")	$description="[]";
		
		break;

	default:
		  // curling
			$curler = (new Curler());
			$body = $curler->get($url);
			$parser = (new MetaParser($body, $url));
			$meta_detail=$parser->getDetails();
			$meta_detail['openGraph']['image'];
		
			$image[] = 	$meta_detail['openGraph']['image'];
			$image=json_encode($image);
		
	if($image=="null" || $image=="")	{
		$tempimg = 'http://www.google.com/s2/favicons?domain=www.'."$domain_name";
		$image[] = $tempimg;
		$image=json_encode($image);
		}
		foreach ($html->find('title') as $value) {
			$title = $value->plaintext;
		}
		$description="[]";
}
//if(empty($image)){
	//$finder = new ImageFinder($url);

	//Get images
	//$fimages = $finder->get_images();

	//Output result
	//$result = array('images' => $fimages);
	//$image = json_encode($result);
	//ob_start('ob_gzhandler');
//}
		
		$sql="INSERT INTO `post` VALUES(DEFAULT, :users_id, :title, :image, :description, :url, :action , 'n', now() )";
		$stmt=$conn->prepare($sql);
		$stmt->bindValue(':users_id', $users_id);
		$stmt->bindValue(':title', $title);
		$stmt->bindValue(':image', $image);
		$stmt->bindValue(':description', $description);
		$stmt->bindValue(':url', $url);
		$stmt->bindValue(':action', $action );
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo "Insertion failed".$e->getMessage();
		}
		$data = $conn->lastInsertId();
		$success="1";
		$msg="Post inserted successfully";
		// inserting into share table
			$sql="INSERT INTO `share` VALUES(DEFAULT, :users_id, :post_id, :action, '1', 'n', now() )";
			$stmt=$conn->prepare($sql);
			$stmt->bindValue(':users_id', $users_id);
			$stmt->bindValue(':post_id', $data);
			$stmt->bindValue(':action', $action );
			try{
				$stmt->execute();
			}
			catch(PDOException $e){
				echo "Insertion failed".$e->getMessage();
			}
		}
		else{
			$success="0";
			$msg="Invalid url";
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

	echo json_encode(array("success" => $success, "msg"=>$msg, "post_id" => $data, "title" => $title, "images" => $image, "description" => $description ));
	
?>
