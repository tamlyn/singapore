<?php 

/**
 * Contains functions used during the install process.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: install.inc.php,v 1.1 2004/12/02 12:01:59 tamlyn Exp $
 */

/**
 * Test server configuration.
 * @return bool  true if no errors occurred; false otherwise
 */
function testServer()
{
  setupHeader("Testing PHP version");
  setupMessage("PHP version is ".phpversion());
  $bits = explode(".",phpversion());
  if(strcmp($bits[0],"4")<0 || strcmp($bits[0],"4")==0 && strcmp($bits[1],"1")<0)
    return setupError("singapore requires PHP 4.1.0 or higher ");
  
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
  
  if(file_exists("../singapore.ini")) setupMessage("Config file found");
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
    if($gd_version_number=="1") setupMessage("Please change the <code>thumbnail_software</code> option in singapore.ini to \"gd1\". Note: GD1 produces very poor quality thumbnails so please use GD2 or ImageMagick if available");
  }
  
  
  setupHeader("Testing for ImageMagick");
  
  $foundIM = exec("convert");
  $whereIM = exec("whereis convert");
  if($foundIM) {
    if($whereIM) setupMessage("Found ImageMagick at $whereIM");
    else setupMessage("Found ImageMagick");
    setupMessage("To take advantage of ImageMagick change the <code>thumbnail_software</code> option in singapore.ini to \"im\"");
  } else setupMessage("ImageMagick not found but that doesn't mean it's not there (on Win32 this crude test will always fail). If it really is not available you may be able to install it yourself (even without shell access to the server)");
  
  return $success;
}


/**
 * Creates cache and logs directories required to run singapore and ensures 
 * all required directories are writeable.
 * @return bool  true if no errors occurred; false otherwise
 */
function createDirectories($config)
{
  $success = true;
  setupHeader("Creating directories");
  
  if(is_writable($config->base_path.$config->pathto_data_dir)) {
    setupMessage("Data directory is writable");
    if(file_exists($config->base_path.$config->pathto_cache))
      if(is_writable($config->base_path.$config->pathto_cache))
        setupMessage("Cache directory already exists at ".$config->base_path.$config->pathto_cache." and is writable");
      else
        $success = setupError("Cache directory already exists at ".$config->base_path.$config->pathto_cache." but is not writable. Please CHMOD to 777");
    else
      if(mkdir($config->base_path.$config->pathto_cache, $config->directory_mode)) 
        setupMessage("Created cache directory at ".$config->base_path.$config->pathto_cache);
      else
        $success = setupError("Could not create cache directory at ".$config->base_path.$config->pathto_cache);
    if($config->track_views)
      if(file_exists($config->base_path.$config->pathto_logs))
        if(is_writable($config->base_path.$config->pathto_logs))
          setupMessage("Logs directory already exists at ".$config->base_path.$config->pathto_logs." and is writable");
        else
          $success = setupError("Logs directory already exists at ".$config->base_path.$config->pathto_logs." but is not writable. Please CHMOD to 777");
      else
        if(mkdir($config->base_path.$config->pathto_logs, $config->directory_mode)) 
          setupMessage("Created logs directory at ".$config->base_path.$config->pathto_logs);
        else
          $success = setupError("Could not create logs directory at ".$config->base_path.$config->pathto_logs);
    else
      setupMessage("View logging disabled. Logs directory not created");
  }
  else
    $success = setupError("Data directory (".$config->base_path.$config->pathto_data_dir.") is not writable. Please CHMOD to 777");

  return $success;
  
}

/**
 * Creates the tables and inserts the default users.
 * @param sgIO_sql  pointer to a singapore SQL backend object
 */
function sqlCreateTables(&$io) {
  $success = true;
  setupHeader("Creating tables");
  if(@$io->query("SELECT * FROM ".$io->config->sql_prefix."galleries"))
    setupMessage("'".$io->config->sql_prefix."galleries' table already exists - skipped");
  elseif($io->query("CREATE TABLE ".$io->config->sql_prefix."galleries (".
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
    ")")) setupMessage("'".$io->config->sql_prefix."galleries' table created");
  else
    $success = setupError("Unable to create '".$io->config->sql_prefix."galleries' table:".$io->error());
    
  if(@$io->query("SELECT * FROM ".$io->config->sql_prefix."images"))
    setupMessage("'".$io->config->sql_prefix."images' table already exists - skipped");
  elseif($io->query("CREATE TABLE ".$io->config->sql_prefix."images (".
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
    ")")) setupMessage("'".$io->config->sql_prefix."images' table created");
  else
    $success = setupError("Unable to create '".$io->config->sql_prefix."images' table:".$io->error());
    
  if(@$io->query("SELECT * FROM ".$io->config->sql_prefix."users"))
    setupMessage("'".$io->config->sql_prefix."users' table already exists - skipped");
  elseif($io->query("CREATE TABLE ".$io->config->sql_prefix."users (".
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
      setupMessage("'".$io->config->sql_prefix."users' table created");
      if($io->query("INSERT INTO ".$io->config->sql_prefix."users VALUES".
         '("admin", "5f4dcc3b5aa765d61d8327deb882cf99", 1024, "", "", "Administrator", "Default administrator account", "")') &&
         $io->query("INSERT INTO ".$io->config->sql_prefix."users VALUES".
         '("guest", "5f4dcc3b5aa765d61d8327deb882cf99", 0, "", "", "Guest", "Restricted use account for guests who do not have a user account", "")'))
        setupMessage("Inserted default users into '".$io->config->sql_prefix."users' table");
      else
        $success = setupError("Unable to insert default users into '".$io->config->sql_prefix."users' table:".$io->error());
  } else
    $success = setupError("Unable to create '".$io->config->sql_prefix."users' table:".$io->error());
  
  return $success;
}


//output functions
function setupHeader($var)
{
  echo "\n</p>\n\n<h2>{$var}</h2>\n\n<p>\n";
}

/**
 * Print an information message. Always returns true.
 * @return true
 */
function setupMessage($var)
{
  echo "{$var}.<br />\n";
  return true;
}

/**
 * Print an error message. Always returns false.
 * @return false
 */
function setupError($var)
{
  echo "<span class=\"error\">{$var}</span>.<br />\n";
  return false;
}

 ?>