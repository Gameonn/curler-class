 <?php
 require_once('dbconn.php');
 
 class GeneralFunctions{

 public static  function randomFileNameGenerator($prefix){
       $r=substr(str_replace(".","",uniqid($prefix,true)),0,20);
       if(file_exists("../upload/$r")) randomFileNameGenerator($prefix);
       else return $r;
     }
	 
public static function get_domain($url)
{
  $pieces = parse_url($url);
  $domain = isset($pieces['host']) ? $pieces['host'] : '';
  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
    return $regs['domain'];
  }
  return false;
}
 }