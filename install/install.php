<?php 

/**
 * Performs necessary actions to install singapore.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: install.php,v 1.2 2004/12/15 17:04:56 tamlyn Exp $
 */

//path to singapore root
$basePath = "../";

require_once "install.inc.php";
require_once $basePath."includes/config.class.php";

//determine current step in setup process
$setupStep = isset($_REQUEST["step"]) ? $_REQUEST["step"] : "test";

if($setupStep=="phpinfo") {
  phpinfo();
  exit;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>singapore setup</title>
<link rel="stylesheet" type="text/css" href="../docs/docstyle.css" />
</head>

<body>

<?php 
//if file is parsed by php then this block will never be executed
if(false) { 
?>
</p>

<h1>Oh dear...</h1>

<p>PHP is not installed or is not configured correctly. See the 
<a href="http://www.php.net/manual/">PHP manual</a> for more information.

<?php 
} //end php test

switch($setupStep) {
  case "test" :
    setupHeader("Step 1 of 3: Test Server");
    setupMessage("Attempting to find out if your server is capable of running singapore"); 
    setupMessage("No changes are made at this time");
    if(testServer()) {
      setupHeader("OK");
      setupMessage("All tests completed successfully");
    } else {
      setupHeader("Oops!");
      setupError("One or more problems were encountered. You may want to fix them and <a href=\"index.php?step=test\">retry this step</a>");
    }
    echo '<br /><a href="index.html">&lt;&lt; Previous: welcome</a>';
    echo ' | <a href="install.php?step=phpinfo">View PHP configuration</a>';
    echo ' | <a href="install.php?step=directories">Next: create directories &gt;&gt;</a>';
    break;
    
  case "directories" :
    setupHeader("Step 2 of 3: Create Directories");
    setupMessage("Setup will now create the directories necessary to store thumbnails and logs");
    
    //create config object
    $config = new sgConfig($basePath."singapore.ini");
    $config->pathto_logs  = $config->pathto_data_dir."logs/";
    $config->pathto_cache = $config->pathto_data_dir."cache/";
    $config->base_path = $basePath;
        
    if(createDirectories($config)) {
      setupHeader("OK");
      setupMessage("This step completed successfully");
    } else {
      setupHeader("Oops!");
      setupError('There was a problem. Please fix it and <a href="index.php?step=directories">retry this step</a>');
    }
    echo '<br /><a href="install.php?step=test">&lt;&lt; Previous: test server</a>';
    echo ' | <a href="install.php?step=database">Next: setup database &gt;&gt;</a>';
    break;
    
  case "database" :
    setupHeader("Step 3 of 3: Setup Database");
          
    //create config object
    $config = new sgConfig($basePath."singapore.ini");
    $config->loadConfig($basePath."secret.ini.php");
    $config->base_path = $basePath;
    
    switch($config->io_handler) {
      case "csv" :
          setupMessage("The default CSV file database does not require any further setting up");
          setupHeader("OK");
          setupMessage("This step completed successfully");
          break;
          
        case "mysql" :
          require_once $basePath."includes/io_mysql.class.php";
          setupMessage("Setup will now create the tables necessary to run singapore on a MySQL database");
          setupHeader("Connecting to database");
          $io = new sgIO_mysql($config);
          if(!$io) setupError("Error connecting to database. Please ensure database settings are correct");
          if(sqlCreateTables($io)) {
            setupHeader("OK");
            setupMessage("This step completed successfully");
          } else {
            setupHeader("Oops!");
            setupError('There was a problem. Please fix it and <a href="index.php?step=database">retry this step</a>');
          }
          break;
          
        case "sqlite" :
          require_once $basePath."includes/io_sqlite.class.php";
          setupMessage("Setup will now create the database and tables necessary to run singapore on SQLite");
          setupHeader("Opening database file");
          $io = new sgIO_sqlite($config);
          if(!$io) {
            setupError("Error connecting to database. Please ensure database settings are correct");
            break;
          } else setupMessage("Success");
          if(sqlCreateTables($io)) {
            setupHeader("OK");
            setupMessage("This step completed successfully");
          } else {
            setupHeader("Oops!");
            setupError('There was a problem. Please fix it and <a href="index.php?step=database">retry this step</a>');
          }
          break;
        default :
          setupError("Unrecognised io_handler.");
    }
    setupMessage("Don't forget to delete or protect this <code>install</code> directory to prevent unauthorised access");
    echo '<br /><a href="install.php?step=directories">&lt;&lt; Previous: create directories</a>';
    echo ' | <a href="index.html">Finish</a>';
    break;
}
?>

</body>
</html>
