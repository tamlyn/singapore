<?php

/**
 * Creates cache and logs directories required to run singapore and ensures 
 * all required directories are writeable.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: setup.php,v 1.11 2004/10/19 21:50:28 tamlyn Exp $
 */

//require config class
require_once "includes/config.class.php";
//create config object
$config = new sgConfig();
$config->pathto_logs  = $config->pathto_data_dir."logs/";
$config->pathto_cache = $config->pathto_data_dir."cache/";
    

function doSetdown()
{
  echo "The setdown function has been depreciated in favour of the <a href=\"tools/cleanup.php\">cleanup script</a>.";
  return true;
}

function doSetup()
{
  $success = true;
  setupHeader("Creating directories");
  
  if(is_writable($GLOBALS["config"]->pathto_data_dir)) {
    setupMessage("Data directory is writable");
    if(file_exists($GLOBALS["config"]->pathto_cache))
      if(is_writable($GLOBALS["config"]->pathto_cache))
        setupMessage("Cache directory already exists at ".$GLOBALS["config"]->pathto_cache." and is writable");
      else
        $success = setupError("Cache directory already exists at ".$GLOBALS["config"]->pathto_cache." but is not writable. Please CHMOD to 777");
    else
      if(mkdir($GLOBALS["config"]->pathto_cache, 0755)) 
        setupMessage("Created cache directory at ".$GLOBALS["config"]->pathto_cache);
      else
        $success = setupError("Could not create cache directory at ".$GLOBALS["config"]->pathto_cache);
    if($GLOBALS["config"]->track_views)
      if(file_exists($GLOBALS["config"]->pathto_logs))
        if(is_writable($GLOBALS["config"]->pathto_logs))
          setupMessage("Logs directory already exists at ".$GLOBALS["config"]->pathto_logs." and is writable");
        else
          $success = setupError("Logs directory already exists at ".$GLOBALS["config"]->pathto_logs." but is not writable. Please CHMOD to 777");
      else
        if(mkdir($GLOBALS["config"]->pathto_logs, 0755)) 
          setupMessage("Created logs directory at ".$GLOBALS["config"]->pathto_logs);
        else
          $success = setupError("Could not create logs directory at ".$GLOBALS["config"]->pathto_logs);
    else
      setupMessage("View logging disabled. Logs directory not created");
  }
  else
    $success = setupError("Data directory (".$GLOBALS["config"]->pathto_data_dir.") is not writable. Please CHMOD to 777");

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
<link rel="stylesheet" type="text/css" href="docs/docstyle.css" />
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
