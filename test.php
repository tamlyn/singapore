<?php

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  setup.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>        *
 *                                                                     *
 *  This file is part of singapore v0.9.4a                             *
 *                                                                     *
 *  singapore is free software; you can redistribute it and/or modify  *
 *  it under the terms of the GNU General Public License as published  *
 *  by the Free Software Foundation; either version 2 of the License,  *
 *  or (at your option) any later version.                             *
 *                                                                     *
 *  singapore is distributed in the hope that it will be useful,       *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty        *
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.            *
 *  See the GNU General Public License for more details.               *
 *                                                                     *
 *  You should have received a copy of the GNU General Public License  *
 *  along with this; if not, write to the Free Software Foundation,    *
 *  Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA      *
 \* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

function doTests()
{
  setupHeader("Testing PHP version");
  setupMessage("PHP version is ".phpversion());
  $bits = explode(".",phpversion());
  if(strcmp($bits[0],"4")<0 || strcmp($bits[0],"4")==0 && strcmp($bits[1],"1")<0) {
    setupError("singapore requires PHP 4.1.0 or higher ");
    return false;
  }
  
  setupHeader("Testing PHP configuration");
  //setupMessage("If any of these tests fail, you may be able to change the configuration ".
  //             "directive (specified in brackets) either in php.ini or by adding ".
  //             "<code>ini_set(\"<b>directive_name</b>, 1)</code> to <code>includes/header.php</code>");

  if(!ini_get("safe_mode")) setupMessage("Safe mode disabled");
  else setupError("PHP is running in 'safe mode' (<code>safe_mode</code>). Singapore <b>should</b> still function correctly but safe mode operation is not supported");
  
  if(is_writable(ini_get("session.save_path")) && ini_get("session.save_handler")=="files") setupMessage("Session save path correctly specified");
  else setupError("Session save path does not exist or is not writable (<code>session.save_path</code>). Singapore will function but you will not be able to use the admin interface");

  if(ini_get("session.use_trans_sid")) setupMessage("Transparent session id support enabled");
  else setupMessage("Transparent session id support disabled (<code>use_trans_sid</code>). Singapore will function but will <b>require</b> cookies for the admin interface to function");

  if(ini_get("file_uploads")) setupMessage("File uploading enabled");
  else setupError("File uploading disabled (<code>file_uploads</code>). Singapore will function but you will not be able to upload images via the admin interface");

  if(is_writable(ini_get("upload_tmp_dir"))) setupMessage("Upload directory correctly specified");
  else setupError("Upload directory directory does not exist or is not writable (<code>upload_tmp_dir</code>). Singapore will function but you will not be able to upload images via the admin interface");
  
  if(ini_get("allow_url_fopen")) setupMessage("Remote file handling enabled");
  else setupError("Remote file handling disabled (<code>allow_url_fopen</code>). Singapore will function but you will not be able to generate thumbnails for remotely hosted files");
  
  
  

  setupHeader("Searching for config file");
  
  if(file_exists("data/singapore.ini")) setupMessage("Config file found at data/singapore.ini");
  else setupMessage("Config file not found - must be in data/singapore.ini");
  
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
  
  if(!$phpinfo) setupMessage("GD not found. You may be able to use ImageMagick instead");
  else {
    setupMessage("Found GD");
    //extract text version and number version
    $gd_version_text = substr($phpinfo,0,strpos($phpinfo,"\n"));
    $gd_version_number = substr($gd_version_text,0,strpos($gd_version_text,"."));
    $gd_version_number = substr($gd_version_number, strlen($gd_version_number)-1);
    setupMessage("GD version appears to be $gd_version_text\n");
    setupMessage("This means singapore ".($gd_version_number=="1"?"cannot":"can").
      " use the higher quality thumbnail generation functions of GD 2");
  }
  
    
  return true;
}


//output functions
function setupHeader($var)
{
  echo "\n</p>\n\n<h2>{$var}...</h2>\n\n<p>\n";
}

function setupMessage($var)
{
  echo "{$var}.<br />\n";
}

function setupError($var)
{
  echo "<font color=\"#ff0000\">{$var}</font>.<br />\n";
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>testing singapore</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="themes/cornflower/extra.css" />
<!-- 
  This file was generated by singapore <http://singapore.sourceforge.net>
  Singapore is free software released under the terms of the GNU GPL.
-->
</head>

<body>

<h1>testing singapore</h1>

<p>This script will try to find out if your server is capable of running singapore. 
No changes are made at this time

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
    setupError("There was a problem. Please fix it and run this script again.");
  }

?>
</p>

</body>
</html>
