<?php 

/**
 * Performs necessary actions to uninstall singapore.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: uninstall.php,v 1.1 2004/12/02 12:01:59 tamlyn Exp $
 */

//path to singapore root
$basePath = "../";

require_once "uninstall.inc.php";
require_once $basePath."includes/config.class.php";

//determine current step in setup process
$setupStep = isset($_REQUEST["step"]) ? $_REQUEST["step"] : "reset";

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>singapore setdown</title>
<link rel="stylesheet" type="text/css" href="../docs/docstyle.css" />
</head>

<body>

<?php 

switch($setupStep) {
  case "reset" :
    setupHeader("Step 1 of 2: Reset permissions");
    setupMessage("Resetting file permissions on server-generated content"); 
    setupMessage("Nothing is deleted at this time");
    
    makeWritable($basePath);
    setupHeader("OK");
    setupMessage("This step completed successfully");
    setupHeader("WARNING!");
    setupMessage("The following step will delete all gallery, image and user information from the currently selected database. Gallery directories and image files will not be deleted but all extended information (e.g. name, artist, copyright etc.) will be irretrievably lost");
    echo '<br /><a href="index.html">Finish</a>';
    echo ' | <a href="uninstall.php?step=database">Next: I AM SURE I WANT TO delete database information &gt;&gt;</a>';
    break;
    
  case "database" :
    setupHeader("Step 2 of 2: Delete database information");
    
    //create config object
    $config = new sgConfig($basePath."singapore.ini");
    $config->loadConfig($basePath."secret.ini.php");
    $config->base_path = $basePath;
    //include base classes
    require_once $basePath."includes/io.class.php";
    require_once $basePath."includes/io_sql.class.php";
    
    switch($config->io_handler) {
      case "csv" :
          setupMessage("The default CSV file database does not require uninstalling");
          setupHeader("OK");
          setupMessage("This step completed successfully");
          break;
          
        case "mysql" :
          require_once $basePath."includes/io_mysql.class.php";
          setupMessage("Setup will now delete all gallery, image and user information");
          setupHeader("Connecting to MySQL database");
          $io = new sgIO_mysql($config);
          if(!$io) {
            setupError("Error connecting to database. Please ensure database settings are correct");
            break;
          } else setupMessage("Connected");
          if(sqlDropTables($io)) {
            setupHeader("OK");
            setupMessage("This step completed successfully");
          } else {
            setupHeader("Oops!");
            setupError('There was a problem. Please fix it and <a href="index.php?step=database">retry this step</a>');
          }
          break;
          
        case "sqlite" :
          setupMessage("Setup will now delete all gallery, image and user information");
          setupHeader("Deleting SQLite database file");
          if(unlink($basePath.$config->pathto_data_dir."sqlite.dat")) {
            setupMessage("Deleted database file '".$basePath.$config->pathto_data_dir."sqlite.dat'");
            setupHeader("OK");
            setupMessage("This step completed successfully");
          } else {
            setupError("Unable to delete database file '".$basePath.$config->pathto_data_dir."sqlite.dat'");
            setupHeader("Oops!");
            setupError('There was a problem. Please fix it and <a href="index.php?step=database">retry this step</a>');
          }
          break;
        default :
          setupError("Unrecognised io_handler");
    }
    echo '<br /><a href="uninstall.php?step=reset">&lt;&lt; Previous: Reset permissions</a>';
    echo ' | <a href="index.html">Finish</a>';
    break;
}
?>

</body>
</html>
