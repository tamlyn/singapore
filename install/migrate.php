<?php 

//permissions bit flags
define("SG_GRP_READ",   1);
define("SG_GRP_EDIT",   2);
define("SG_GRP_ADD",    4);
define("SG_GRP_DELETE", 8);
define("SG_WLD_READ",   16);
define("SG_WLD_EDIT",   32);
define("SG_WLD_ADD",    64);
define("SG_WLD_DELETE", 128);

$basePath = "../";

function setPerms($obj) {
  $obj->permissions = 0;
  if(!empty($_POST["sgGrpRead"]))   $obj->permissions |= SG_GRP_READ;
  if(!empty($_POST["sgGrpEdit"]))   $obj->permissions |= SG_GRP_EDIT;
  if(!empty($_POST["sgGrpAdd"]))    $obj->permissions |= SG_GRP_ADD;
  if(!empty($_POST["sgGrpDelete"])) $obj->permissions |= SG_GRP_DELETE;
  if(!empty($_POST["sgWldRead"]))   $obj->permissions |= SG_WLD_READ;
  if(!empty($_POST["sgWldEdit"]))   $obj->permissions |= SG_WLD_EDIT;
  if(!empty($_POST["sgWldAdd"]))    $obj->permissions |= SG_WLD_ADD;
  if(!empty($_POST["sgWldDelete"])) $obj->permissions |= SG_WLD_DELETE;
  
  $obj->groups = $_REQUEST["sgGroups"];
  $obj->owner = $_REQUEST["sgOwner"];
  
  return $obj;
}


function convertDirectory ($path, $io_in, $io_out)
{
  if (is_dir($path)) {
    $gallery = $io_in->getGallery($path);
    echo "<ul><li>Checking $path<br />\n";
    if($gallery) {
      if($gallery->summary != "" && empty($_REQUEST["convertOverwrite"]))
        echo "Did NOT overwrite non-empty summary in $path<br />\n";
      else {
        if($_REQUEST["convertType"]!='none')
          $gallery->summary = $gallery->desc;
        if($_REQUEST["convertType"]=='move')
          $gallery->desc = "";
      }

      $gallery = setPerms($gallery);
      
      for($i=0; $i<count($gallery->images); $i++)
        $gallery->images[$i] = setPerms($gallery->images[$i]);
      
      if($io_out->putGallery($gallery))
        echo "Successfully converted $path<br />\n";
      else
        echo "Problem saving data file for $path<br />\n";
    } else
      echo "Skipping $path<br />\n";
    $d = dir($path);
    while (($file = $d->read()) !== false) {
      if ($file == '.' || $file == '..') continue;
      $path = $d->path."/".$file;
      if (is_dir($path)) {
        convertDirectory($path);
      }
    }
    echo "</li></ul>\n";
  }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>singapore database migration</title>
<link rel="stylesheet" type="text/css" href="../docs/docstyle.css" />
</head>

<body>

<?php 

require_once $basePath."includes/config.class.php";
require_once $basePath."includes/gallery.class.php";
require_once $basePath."includes/image.class.php";
require_once $basePath."includes/io.class.php";
require_once $basePath."includes/io_sql.class.php";
$config = new sgConfig($basePath."singapore.ini");


switch($setupStep) {
  case "select" :
    setupHeader("Step 1 of 2: Select databases");
    setupMessage("Nothing is deleted at this time");
    
    makeWritable($basePath);
    setupHeader("OK");
    setupMessage("This step completed successfully");
    setupHeader("WARNING!");
    setupMessage("The following step will delete all gallery, image and user information from the currently selected database. Gallery directories and image files will not be deleted but all extended information (e.g. name, artist, copyright etc.) will be irretrievably lost");
    echo '<br /><a href="index.html">Finish</a>';
    echo ' | <a href="uninstall.php?step=database">Next: I AM SURE I WANT TO delete database information &gt;&gt;</a>';
    break;
    
  case "defaults" :
    setupHeader("Step 2 of 3: Select default permissions");
    setupMessage("Nothing is deleted at this time");
    
    makeWritable($basePath);
    setupHeader("OK");
    setupMessage("This step completed successfully");
    setupHeader("WARNING!");
    setupMessage("The following step will delete all gallery, image and user information from the currently selected database. Gallery directories and image files will not be deleted but all extended information (e.g. name, artist, copyright etc.) will be irretrievably lost");
    echo '<br /><a href="index.html">Finish</a>';
    echo ' | <a href="uninstall.php?step=database">Next: I AM SURE I WANT TO delete database information &gt;&gt;</a>';
    break;
    
  case "convert" :
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

if(isset($_REQUEST["convertType"])) { 

  require_once $basePath."includes/config.class.php";
  require_once $basePath."includes/gallery.class.php";
  require_once $basePath."includes/image.class.php";
  require_once $basePath."includes/io.class.php";
  require_once $basePath."includes/io_sql.class.php";
  $config = new sgConfig($basePath."singapore.ini");
  
  $config->base_path = $basePath;
  
  //echo "<ul>\n";
  convertDirectory($config->base_path.$config->pathto_galleries);
  //echo "</ul>\n";
  
  echo "<p>All operations complete.</p>\n";

} else { ?>
<p>This will convert all your metadata files from singapore 0.9.6, 0.9.7, 0.9.8 or 0.9.9 to 0.9.10.</p>

<form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
<h3>summary field</h3>
<p>There is a new gallery summary field that is displayed instead of the 
description in the parent gallery. You can choose to either copy or move the 
old description field to the summary field or leave both untouched:</p>

<p><input type="radio" class="radio" name="convertType" value="copy" checked="true" /> Copy<br />
<input type="radio" class="radio" name="convertType" value="move" /> Move<br />
<input type="radio" class="radio" name="convertType" value="none" /> Neither<br />

<p>By default only empty summary fields will be written to. Check this option to
allow the summary field to be overwritten <input type="checkbox" class="checkbox" name="convertOverwrite" /></p>

<h3>permissions</h3>

<p>This version introduces multiple authorised users and image &amp; gallery 
permissions. Please choose the default permissions that you would like all 
objects to be set to. The default permissions selected below are recommended as 
they will make all images &amp; galleries readable by everyone but only 
modifiable by administrators. See the readme for more information on the 
permissions model used by singapore.</p>
<table>
<tr>
  <td>Owner</td>
  <td><input type="text" name="sgOwner" value="__nobody__" /></td>
</tr>
<tr>
  <td>Groups</td>
  <td><input type="text" name="sgGroups" value="" /></td>
</tr>
<tr>
  <td>Group permissions</td>
  <td><div class="inputbox">
    <input type="checkbox" class="checkbox" name="sgGrpRead" checked="true"/> Read
    <input type="checkbox" class="checkbox" name="sgGrpEdit" /> Edit
    <input type="checkbox" class="checkbox" name="sgGrpAdd" /> Add
    <input type="checkbox" class="checkbox" name="sgGrpDelete" /> Delete
  </div></td>
</tr>
<tr>
  <td>World permissions</td>
  <td><div class="inputbox">
    <input type="checkbox" class="checkbox" name="sgWldRead" checked="true"/> Read
    <input type="checkbox" class="checkbox" name="sgWldEdit" /> Edit
    <input type="checkbox" class="checkbox" name="sgWldAdd" /> Add
    <input type="checkbox" class="checkbox" name="sgWldDelete" /> Delete
  </div></td>
</tr>
</table>


<p>Please note that while the script will create backups of your metadata files 
it is highly recommended that you create your own backups for added security.</p>

<input type="submit" class="button" value="Go" /></p>
</form>
<?php } ?>

<p><a href="index.html">Return</a> to tools.</p>

</body>
</html>