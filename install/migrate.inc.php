<?php 

/**
 * Contains functions used during the database migration process.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: migrate.inc.php,v 1.2 2004/12/15 17:04:56 tamlyn Exp $
 */


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