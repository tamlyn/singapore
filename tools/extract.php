<?php

/**
 * This file searches for strings in all project files matching the filter and
 * creates two PO template files (singapore.pot and singapore.admin.pot)
 * 
 * @author Joel Sjögren <joel dot sjogren at nonea dot se>
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: extract.php,v 1.8 2004/11/01 08:17:34 tamlyn Exp $
 */

// Programs to call (insert path to them if necessary)
$GETTEXT_EXTRACT = "xgettext";


//require config class
require_once "../includes/config.class.php";
//create config object
$config = new sgConfig("../singapore.ini");

$BASEPATH = realpath("..");
$OUTPUTPATH = "../".$config->pathto_locale;
$standardPot = $OUTPUTPATH."singapore.pot";
$adminPot = $OUTPUTPATH."singapore.admin.pot";


  
  
/**
 * Parses a directory and returns full path to all the files
 * matching the filter (file name suffix)
 *
 * @param    string    $dir        full directory name (must end with /)
 * @param    string    $filter     file name suffixes separated by | (optional, default don't filter)
 * @return   array                 an array with all files
 **/
function parseDirectory ($dir, $filter = "php|html|tpl|inc")
{
  $ret = array();
  if (is_dir($dir)) {
    $d = dir($dir);
    while (($file = $d->read()) !== false) {
      if ($file == '.' || $file == '..') continue;
      $fullfile = $d->path . $file;
      if (is_dir($fullfile)) $ret = array_merge($ret,parseDirectory($fullfile."/"));
      else if (!$filter || preg_match("/\.({$filter})$/i", $file)) $ret[] = $fullfile;
    }
  }
  return $ret;
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>i18n string extractor</title>
<link rel="stylesheet" type="text/css" href="tools.css" />
</head>

<body>

<h1>i18n string extractor</h1>

<p><?php 
  // Create tempfile
  $temp = $OUTPUTPATH."/".'~tempfile';
  $fp = fopen($temp, 'wb') or die("Couldn't open tempfile {$temp} for writing.\n");
  
  // Get all files matching pattern in current template
  $files = parseDirectory("../".$config->pathto_templates.$config->default_template.'/');
  $files[count($files)] = "../includes/singapore.class.php";
  fwrite($fp, implode("\n", $files));
  
  // Close tempfile
  fclose($fp);
  
  
  // Call gettext
  $res = shell_exec("{$GETTEXT_EXTRACT} --debug --keyword=_g --keyword=_ng:1,2 --keyword=__g -C -F --output=\"" . $standardPot . "\" --files-from=\"" . $temp . "\"");
  if (trim($res)) die("Something seemed to go wrong with gettext:\n" . $res . "\n");
  else echo "Standard strings extracted $standardPot<br />";
  
  // Remove tempfile
  unlink($temp);

  
  
///////admin///////////

  // Create tempfile
  $temp = $OUTPUTPATH."/".'~tempfile';
  $fp = fopen($temp, 'w') or die("Couldn't open tempfile {$temp} for writing.\n");
  
  // Get all files matching pattern in current template
  $files = parseDirectory("../".$config->pathto_templates.$config->admin_template_name.'/');
  $files[] = "../includes/admin.class.php";
  $files[] = "../admin.php";
  fwrite($fp, implode("\n", $files));
  
  // Close tempfile
  fclose($fp);
  
  // Call gettext
  $res = shell_exec("{$GETTEXT_EXTRACT} --debug --keyword=_g --keyword=_ng:1,2 -C -F -x \"" . $standardPot . "\" --output=\"" . $adminPot . "\" --files-from=\"" . $temp . "\"");
  if (trim($res)) die("Something seemed to go wrong with gettext:\n" . $res . "\n");
  else echo "Admin strings extracted to $adminPot<br />";
  
  // Remove tempfile
  unlink($temp);

?>
</p>

<p>You may now <a href="merge.php">merge</a> the strings into all previously 
translated PO files.</p>

</body>
</html>
