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

//require config class
require_once "includes/class_configuration.php";
//create config object
$sgConfig = new sgConfiguration();


function doSetdown()
{
  $success = true;
  
  setupHeader("Removing directories");
  
  if(is_writable($GLOBALS["sgConfig"]->pathto_data_dir)) {
    setupMessage("Data directory is writable");
    if(file_exists($GLOBALS["sgConfig"]->pathto_cache))
      if(is_writable($GLOBALS["sgConfig"]->pathto_cache))
        if(rmdir_all($GLOBALS["sgConfig"]->pathto_cache)) 
          setupMessage("Cache directory deleted");
        else
          $success = setupError("Error deleting cache directory at ".$GLOBALS["sgConfig"]->pathto_cache);
      else
        $success = setupError("Cache is not writable so cannot delete it");
    else
      setupMessage("Cache directory not found at ".$GLOBALS["sgConfig"]->pathto_cache);
    
    if(file_exists($GLOBALS["sgConfig"]->pathto_logs))
      if(is_writable($GLOBALS["sgConfig"]->pathto_logs))
        if(rmdir_all($GLOBALS["sgConfig"]->pathto_logs)) 
          setupMessage("Logs directory deleted");
        else
          $success = setupError("Error deleting logs directory at ".$GLOBALS["sgConfig"]->pathto_logs);
      else
        $success = setupError("Logs is not writable so cannot delete it");
    else
      setupMessage("Logs directory not found at ".$GLOBALS["sgConfig"]->pathto_logs);
  } else
    $success = setupError("Data directory (".$GLOBALS["sgConfig"]->pathto_data_dir.") is not writable. Please CHMOD to 777");
      
  return $success;
  
}

function doSetup()
{
  $success = true;
  setupHeader("Creating directories");
  
  if(is_writable($GLOBALS["sgConfig"]->pathto_data_dir)) {
    setupMessage("Data directory is writable");
    if(file_exists($GLOBALS["sgConfig"]->pathto_cache))
      if(is_writable($GLOBALS["sgConfig"]->pathto_cache))
        setupMessage("Cache directory already exists at ".$GLOBALS["sgConfig"]->pathto_cache." and is writable");
      else
        $success = setupError("Cache directory already exists at ".$GLOBALS["sgConfig"]->pathto_cache." but is not writable. Please CHMOD to 777");
    else
      if(mkdir($GLOBALS["sgConfig"]->pathto_cache, 0755)) 
        setupMessage("Created cache directory at ".$GLOBALS["sgConfig"]->pathto_cache);
      else
        $success = setupError("Could not create cache directory at ".$GLOBALS["sgConfig"]->pathto_cache);
    if($GLOBALS["sgConfig"]->track_views)
      if(file_exists($GLOBALS["sgConfig"]->pathto_logs))
        if(is_writable($GLOBALS["sgConfig"]->pathto_logs))
          setupMessage("Logs directory already exists at ".$GLOBALS["sgConfig"]->pathto_logs." and is writable");
        else
          $success = setupError("Logs directory already exists at ".$GLOBALS["sgConfig"]->pathto_logs." but is not writable. Please CHMOD to 777");
      else
        if(mkdir($GLOBALS["sgConfig"]->pathto_logs, 0755)) 
          setupMessage("Created logs directory at ".$GLOBALS["sgConfig"]->pathto_logs);
        else
          $success = setupError("Could not create logs directory at ".$GLOBALS["sgConfig"]->pathto_logs);
    else
      setupMessage("View logging disabled. Logs directory not created");
  }
  else
    $success = setupError("Data directory (".$GLOBALS["sgConfig"]->pathto_data_dir.") is not writable. Please CHMOD to 777");

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

//this function recursively deletes all subdirectories and 
//files in specified directory. USE WITH EXTREME CAUTION!! 
function rmdir_all($wd)
{
  if(!$dp = opendir($wd)) return false;
  $success = true;
  while(false !== ($entry = readdir($dp))) {
    if($entry == "." || $entry == "..") continue;
    if(is_dir("$wd/$entry")) $success &= rmdir_all("$wd/$entry");
    else $success &= unlink("$wd/$entry");
  }
  closedir($dp);
  $success &= rmdir($wd);
  return $success;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>singapore setup</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="themes/cornflower/extra.css" />
<!-- 
  This file was generated by singapore <http://singapore.sourceforge.net>
  Singapore is free software released under the terms of the GNU GPL.
-->
</head>

<body>

<?php 

  if($_SERVER["QUERY_STRING"]=="setdown") {
    echo "<h1>singapore setdown</h1>\n";
    echo "<p>This script will try to remove directories and files created during setup.";
    
    if(doSetdown()) {
      setupHeader("OK");
      setupMessage("All operations completed successfully.");
    } else {
      setupHeader("Oops");
      setupError("There was a problem.");
    }
  } else {
    echo "<h1>singapore setup</h1>\n";
    echo "<p>This script will try to setup singapore to run on your server.";

    if(doSetup()) {
      setupHeader("OK");
      setupMessage("All operations completed successfully. Before <a href=\"index.php\">proceeding</a> you should delete this file to prevent anyone from deleting your cache and logs directories.");
    } else {
      setupHeader("Oops");
      setupError("There was a problem. Please fix it and run this script again.");
    }
  }
?>
</p>

</body>
</html>
