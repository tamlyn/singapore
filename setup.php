<?php

/**
 * Creates cache and logs directories required to run singapore and ensures 
 * all required directories are writeable.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: setup.php,v 1.12 2004/10/26 04:32:38 tamlyn Exp $
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

function doMySQLSetup($config) {
  $config->loadConfig("secret.ini.php");
  ob_implicit_flush();   
  
  setupHeader("Connecting to database");
  $mp = mysql_connect($config->mysql_host, $config->mysql_user, $config->mysql_pass);
  if(!$mp) return setupError("Could not connect to database, please ensure settings are correctly configured");
  
  if(!mysql_select_db($config->mysql_database))
    return setupError("Could not select database, please ensure settings are correctly configured");
  setupMessage("Connected to database");

  setupHeader("Creating tables");
  if(mysql_query("SELECT * FROM ".$config->mysql_prefix."galleries"))
    setupMessage("'".$config->mysql_prefix."galleries' table already exists - skipped");
  elseif(mysql_query("CREATE TABLE ".$config->mysql_prefix."galleries (".
    "id varchar(250) NOT NULL, ".
    "lang varchar(16) NOT NULL DEFAULT '', ".
    "filename varchar(200), ".
    "owner varchar(32), ".
    "groups varchar(64), ".
    "permissions int UNSIGNED, ".
    "categories varchar(255), ".
    "name varchar(255), ".
    "artist varchar(255), ".
    "email varchar(255), ".
    "copyright varchar(255), ".
    "description text, ".
    "summary text, ".
    "date varchar(255),".
    "hits smallint UNSIGNED,".
    "lasthit int UNSIGNED,".
    "PRIMARY KEY (id, lang)".
    ")")) setupMessage("'".$config->mysql_prefix."galleries' table created");
  else
    setupError("Unable to create '".$config->mysql_prefix."galleries' table:".mysql_error());
    
  if(mysql_query("SELECT * FROM ".$config->mysql_prefix."images"))
    setupMessage("'".$config->mysql_prefix."images' table already exists - skipped");
  elseif(mysql_query("CREATE TABLE ".$config->mysql_prefix."images (".
    "galleryid varchar(250) NOT NULL, ".
    "filename varchar(200) NOT NULL, ".
    "lang varchar(16) NOT NULL DEFAULT '', ".
    "thumbnail varchar(255), ".
    "owner varchar(32), ".
    "groups varchar(64), ".
    "permissions int, ".
    "categories varchar(64), ".
    "name varchar(255), ".
    "artist varchar(255), ".
    "email varchar(255), ".
    "copyright varchar(255), ".
    "description text, ".
    "width smallint UNSIGNED, ".
    "height smallint UNSIGNED, ".
    "type tinyint UNSIGNED, ".
    "location varchar(255), ".
    "date varchar(255), ".
    "camera varchar(255), ".
    "lens varchar(255), ".
    "film varchar(255), ".
    "darkroom text, ".
    "digital text, ".
    "hits smallint UNSIGNED,".
    "lasthit int UNSIGNED,".
    "PRIMARY KEY (galleryid, filename, lang)".
    ")")) setupMessage("'".$config->mysql_prefix."images' table created");
  else
    setupError("Unable to create '".$config->mysql_prefix."images' table:".mysql_error());
    
  if(mysql_query("SELECT * FROM ".$config->mysql_prefix."users"))
    setupMessage("'".$config->mysql_prefix."users' table already exists - skipped");
  elseif(mysql_query("CREATE TABLE ".$config->mysql_prefix."users (".
    "username varchar(32) NOT NULL, ".
    "userpass char(32) NOT NULL, ".
    "permissions int UNSIGNED, ".
    "groups varchar(64), ".
    "email varchar(255), ".
    "fullname varchar(255), ".
    "description varchar(255), ".
    "stats varchar(255), ".
    "PRIMARY KEY (username)".
    ")")) {
      setupMessage("'".$config->mysql_prefix."users' table created");
      if(mysql_query("INSERT INTO ".$config->mysql_prefix."users VALUES".
         '("admin", "5f4dcc3b5aa765d61d8327deb882cf99", 1024, "", "", "Administrator", "Default administrator account", "")') &&
         mysql_query("INSERT INTO ".$config->mysql_prefix."users VALUES".
         '("guest", "5f4dcc3b5aa765d61d8327deb882cf99", 0, "", "", "Guest", "Restricted use account for guests who do not have a user account", "")'))
        setupMessage("Inserted default users into '".$config->mysql_prefix."users' table");
      else
        setupError("Unable to insert default users into '".$config->mysql_prefix."users' table:".mysql_error());
  } else
    setupError("Unable to create '".$config->mysql_prefix."users' table");
  
  mysql_close($mp);
  
  return true;
}

function doMySQLDelete($config) {
  $config->loadConfig("secret.ini.php");
  ob_implicit_flush();   
  
  setupHeader("Connecting to database");
  $mp = mysql_connect($config->mysql_host, $config->mysql_user, $config->mysql_pass);
  if(!$mp) return setupError("Could not connect to database, please ensure settings are correctly configured");
  
  if(!mysql_select_db($config->mysql_database))
    return setupError("Could not select database, please ensure settings are correctly configured");
  setupMessage("Connected to database");

  setupHeader("Deleting tables");
  if(!mysql_query("SELECT * FROM ".$config->mysql_prefix."galleries"))
    setupMessage("'".$config->mysql_prefix."galleries' table not found - skipped");
  elseif(mysql_query("DROP TABLE ".$config->mysql_prefix."galleries")) 
    setupMessage("'".$config->mysql_prefix."galleries' table deleted");
  else
    setupError("Unable to delete '".$config->mysql_prefix."galleries' table:".mysql_error());
    
  if(!mysql_query("SELECT * FROM ".$config->mysql_prefix."images"))
    setupMessage("'".$config->mysql_prefix."images' table not found - skipped");
  elseif(mysql_query("DROP TABLE ".$config->mysql_prefix."images")) 
    setupMessage("'".$config->mysql_prefix."images' table deleted");
  else
    setupError("Unable to delete '".$config->mysql_prefix."images' table:".mysql_error());
    
  if(!mysql_query("SELECT * FROM ".$config->mysql_prefix."users"))
    setupMessage("'".$config->mysql_prefix."users' table not found - skipped");
  elseif(mysql_query("DROP TABLE ".$config->mysql_prefix."users")) 
    setupMessage("'".$config->mysql_prefix."users' table deleted");
  else
    setupError("Unable to delete '".$config->mysql_prefix."users' table:".mysql_error());
    
  mysql_close($mp);
  
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

  switch($_SERVER["QUERY_STRING"]) {
    case "setdown" :
      echo "<h1>singapore setdown</h1>\n";
      echo "<p>This script will try to remove directories and files created during setup.";
      
      if(doSetdown()) {
        setupHeader("OK");
        setupMessage("All operations completed successfully.");
      } else {
        setupHeader("Oops");
        setupError("There was a problem.");
      }
      break;
    case "sqlsetup" :
      echo "<h1>singapore mysql setup</h1>\n";
      echo "<p>This script will create the necessary tables to use singapore in MySQL mode.";
      
      if(doMySQLSetup($config)) {
        setupHeader("OK");
        setupMessage("All operations completed successfully. <a href=\"index.php\">Proceed</a> to singapore.");
      } else {
        setupHeader("Oops");
        setupError("There was a problem.");
      }
      break;
    case "sqldelete" :
      echo "<h1>singapore mysql delete</h1>\n";
      echo "<p>This script will delete all tables used by singapore.";
      
      if(doMySQLDelete($config)) {
        setupHeader("OK");
        setupMessage("All operations completed successfully. <a href=\"index.php\">Return</a> to singapore.");
      } else {
        setupHeader("Oops");
        setupError("There was a problem.");
      }
      break;
    default :
      echo "<h1>singapore setup</h1>\n";
      echo "<p>This script will try to setup singapore to run on your server.";
  
      if(doSetup()) {
        setupHeader("OK");
        setupMessage("All operations completed successfully. <a href=\"index.php\">Proceed</a> to singapore.");
      } else {
        setupHeader("Oops");
        setupError("There was a problem. Please fix it and run this script again.");
      }
  }
?>
</p>

</body>
</html>
