<?php
error_reporting(0);
$servername = $_SERVER['HTTP_HOST'];
$pathimg=$servername."/";
define("ROOT_PATH",$_SERVER['DOCUMENT_ROOT']);
define("UPLOAD_PATH","http://code-brew.com/projects/nusit/");
define("BASE_PATH","http://code-brew.com/projects/nusit/");

$DB_HOST = 'localhost';
$DB_DATABASE = 'codebrew_nusit';
$DB_USER = 'codebrew_super';
$DB_PASSWORD = 'core2duo';



define('SMTP_USER','pargat@code-brew.com');
define('SMTP_EMAIL','pargat@code-brew.com');
define('SMTP_PASSWORD','core2duo');
define('SMTP_NAME','nusit');
define('SMTP_HOST','mail.code-brew.com');
define('SMTP_PORT','25');
