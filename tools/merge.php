<?php

/**
 * This file merges the two PO template files (singapore.pot and singapore.admin.pot)
 * with all existing language files (singapore.LANG.po)
 * 
 * @author Joel Sjögren <joel dot sjogren at nonea dot se>
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: merge.php,v 1.3 2004/02/02 16:31:37 tamlyn Exp $
 */

// Programs to call (insert path to them if necessary)
$GETTEXT_MERGE   = "msgmerge";


//require config class
require_once "../includes/config.class.php";
//create config object
$config = new sgConfig("../singapore.ini");

$BASEPATH = realpath("..");
$OUTPUTPATH = "../".$config->pathto_locale;
$standardPot = $OUTPUTPATH."singapore.pot";
$adminPot = $OUTPUTPATH."singapore.admin.pot";
$createbackups = true;

  
  
/**
 * Parses a directory and returns full path to all the files
 * matching the filter (file name suffix)
 *
 * @param    string    $dir        full directory name (must end with /)
 * @param    string    $filter     file name suffixes separated by | (optional, default don't filter)
 * @return   array                 an array with all files
 **/
function parseDirectory ($dir, $filter = 'php|html|tpl|inc')
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
<title>i18n string merger</title>
<link rel="stylesheet" type="text/css" href="tools.css" />
</head>

<body>

<h1>i18n string merger</h1>

<p><?php 
  $files = parseDirectory($OUTPUTPATH, 'po');
  foreach ($files as $file) {
    if (!preg_match("/singapore\.[\w]+\.po$/i", $file)) continue;
    $backup = $file . '.bak';
    if (file_exists($backup)) @unlink($backup);
    @rename($file, $backup);
    $res = shell_exec("{$GETTEXT_MERGE} --strict \"{$backup}\" \"" . $standardPot . "\" > \"{$file}\"");
    if (trim($res)) echo "Something seemed to go wrong with msgmerge:\n" . $res . "\n";
    else echo "$standardPot merged with $file<br />";
    // Remove backup?
    if (!@$createbackups) @unlink($backup);
  }  

///////admin///////////

  $files = parseDirectory($OUTPUTPATH, 'po');
  foreach ($files as $file) {
    if (!preg_match("/singapore\.admin\.[\w]+\.po$/i", $file)) continue;
    $backup = $file . '.bak';
    if (file_exists($backup)) @unlink($backup);
    @rename($file, $backup);
    $res = shell_exec("{$GETTEXT_MERGE} --strict \"{$backup}\" \"" . $adminPot . "\" > \"{$file}\"");
    if (trim($res)) echo "Something seemed to go wrong with msgmerge:\n" . $res . "\n";
    else echo "$adminPot merged with $file<br />";
    // Remove backup?
    if (!@$createbackups) @unlink($backup);
  }

?>
</p>

<p>Once you have translated the updated PO files you may 
<a href="compile.php">compile</a> them into PHP serialized objects for use with 
singapore.</p>

</body>
</html>
