<?php

/**
 * Tests the current PHP configuration to see if it is suitable for 
 * running singapore.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: test.php,v 1.8 2004/04/09 17:53:38 tamlyn Exp $
 */

if(isset($_REQUEST["phpinfo"])) {
  phpinfo();
  exit;
}
 
function doTests()
{
  setupHeader("Testing PHP version");
  setupMessage("PHP version is ".phpversion());
  $bits = explode(".",phpversion());
  if(strcmp($bits[0],"4")<0 || strcmp($bits[0],"4")==0 && strcmp($bits[1],"1")<0) {
    setupError("singapore requires PHP 4.1.0 or higher ");
    return false;
  }
  
  $success = true;
  
  setupHeader("Testing PHP configuration");
  //setupMessage("If any of these tests fail, you may be able to change the configuration ".
  //             "directive (specified in brackets) either in php.ini or by adding ".
  //             "<code>ini_set(\"<b>directive_name</b>, 1)</code> to <code>includes/header.php</code>");

  if(!ini_get("safe_mode")) setupMessage("Safe mode disabled");
  else $success &= setupError("PHP is running in 'safe mode' (<code>safe_mode</code>). Singapore may still function correctly but safe mode operation is not supported");
  
  $session_save_path = ini_get("session.save_path");
  if(!empty($session_save_path) && is_writable($session_save_path) || ini_get("session.save_handler")!="files") setupMessage("Session save path seems to be correctly specified");
  else $success &= setupError("Session save path does not exist or is not writable (<code>session.save_path</code>). Singapore will function but you may not be able to use the admin interface");

  if(ini_get("session.use_trans_sid")) setupMessage("Transparent session id support enabled");
  else setupMessage("Transparent session id support disabled (<code>use_trans_sid</code>). Singapore will function but will <b>require</b> cookies for the admin interface to function");

  if(ini_get("file_uploads")) setupMessage("File uploading enabled");
  else $success &= setupError("File uploading disabled (<code>file_uploads</code>). Singapore will function but you will not be able to upload images via the admin interface");

  $upload_tmp_dir = ini_get("upload_tmp_dir");
  if(empty($upload_tmp_dir) || is_writable($upload_tmp_dir)) setupMessage("Upload temp directory seems to be correctly specified");
  else $success &= setupError("Upload directory directory does not exist or is not writable (<code>upload_tmp_dir</code>). Singapore will function but you may not be able to upload images via the admin interface");
  
  //setupMessage("Maximum upload size is ".floor(ini_get("upload_max_filesize")/1024)."KB. You will not be able to upload files larger than this via the admin interface");
  
  if(ini_get("allow_url_fopen")) setupMessage("Remote file handling enabled");
  else $success &= setupError("Remote file handling disabled (<code>allow_url_fopen</code>). Singapore will function but you will not be able to generate thumbnails for remotely hosted files");
  
  
  

  setupHeader("Testing for config file");
  
  if(file_exists("singapore.ini")) setupMessage("Config file found");
  else $success &= setupError("Config file not found - singapore.ini must be located in the root singapore directory");
  
  setupHeader("Testing for GD");
  //get phpinfo data
  ob_start();
  phpinfo(8);
  $phpinfo = ob_get_contents();
  ob_end_clean();
  
  //find gd version
  $phpinfo = strip_tags($phpinfo);
  $phpinfo = stristr($phpinfo,"gd version");
  $phpinfo = stristr($phpinfo,"version");
  
  if(!$phpinfo) $success &= setupError("GD not found. You may be able to use ImageMagick instead");
  else {
    //extract text version and number version
    $gd_version_text = substr($phpinfo,0,strpos($phpinfo,"\n"));
    $gd_version_number = substr($gd_version_text,0,strpos($gd_version_text,"."));
    $gd_version_number = substr($gd_version_number, strlen($gd_version_number)-1);
    setupMessage("Found GD: $gd_version_text");
    if($gd_version_number=="1") setupMessage("This version of GD produces very poor quality thumbnails and use of ImageMagick is greatly preferred");
    elseif($gd_version_number=="2") setupMessage("To take advantage of GD2's higher quality thumbnails change the <code>thumbnail_software</code> option in singapore.ini to \"gd2\"");
  }
  
  setupHeader("Testing for ImageMagick");
  
  $foundIM = exec("convert");
  $whereIM = exec("whereis convert");
  if($foundIM) {
    if($whereIM) setupMessage("Found ImageMagick at $whereIM");
    else setupMessage("Found ImageMagick");
    setupMessage("To take advantage of ImageMagick's higher quality thumbnails change the <code>thumbnail_software</code> option in singapore.ini to \"im\"");
  } else $success &= setupError("ImageMagick not found but that doesn't mean it's not there. Alternatively you may be able to install it yourself (even without shell access to the server)");
  
  return $success;
}


//output functions
function setupHeader($var)
{
  echo "\n</p>\n\n<h2>{$var}...</h2>\n\n<p>\n";
}

function setupMessage($var)
{
  echo "{$var}.<br />\n";
  return true;
}

function setupError($var)
{
  echo "<font color=\"#ff0000\">{$var}</font>.<br />\n";
  return false;
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>testing singapore</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="templates/default/main.css" />
</head>

<body>

<h1>Testing singapore</h1>

<p>This script will try to find out if your server is capable of running singapore. 
No changes are made at this time.

<?php 
//if file is parsed by php then this block will never be executed
if(false) { 
?>
</p>

<p>PHP is not installed or is not configured correctly. See the 
<a href="http://www.php.net/manual/">PHP manual</a> for more information.

<?php 
} //end php test

  if(doTests()) {
    setupHeader("OK");
    setupMessage("All tests completed successfully. <a href=\"setup.php\">Proceed to setup</a>.");
  } else {
    setupHeader("Oops");
    setupError("One or more problems were encountered. You may want to fix them before <a href=\"setup.php\">proceeding to setup</a>.");
  }

?>
</p>

<p><small>Complete <a href="test.php?phpinfo">phpinfo</a> for advanced users.</small></p>

</body>
</html>
