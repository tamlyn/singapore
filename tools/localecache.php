<?php
 
/**
 * This file merges the two PO template files (singapore.pot and singapore.admin.pot)
 * with all existing language files (singapore.LANG.po)
 * 
 * @author Joel Sjögren <joel dot sjogren at nonea dot se>
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: localecache.php,v 1.3 2004/10/14 02:02:19 tamlyn Exp $
 */

//require config class
require_once "../includes/config.class.php";
require_once "../includes/translator.class.php";
//create config object
$config = new sgConfig("../singapore.ini");

$BASEPATH = realpath("..");
$OUTPUTPATH = "../".$config->pathto_locale;
$OUTPUTFILE = "../".$config->pathto_data_dir."languages.cache";
  
  
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

function saveCache($availableLanguages, $output)
{
  // Open data file for writing
  $fp = @fopen($output, "wb") or die("Couldn't open file ({$output}).\n");
  fwrite($fp, serialize($availableLanguages));
  return fclose($fp);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>language cache updater</title>
<link rel="stylesheet" type="text/css" href="tools.css" />
</head>

<body>

<h1>language cache updater</h1>

<p><?php 
  $files = parseDirectory($OUTPUTPATH, 'pmo');
  $availableLanguages = array();
  foreach ($files as $file) {
    if (!preg_match("/singapore\.([\w]+)\.pmo$/i", $file, $matches)) continue;
    $i18n = new Translator($file);
    $availableLanguages[$matches[1]] = $i18n->languageStrings[0]['language'];
    echo "Added $matches[1] => ".$i18n->languageStrings[0]['language']." to available languages.<br />\n";
  }
  
  //add english which has no translation files
  $availableLanguages["en"] = "English";
  
  ksort($availableLanguages);
  if(saveCache($availableLanguages, $OUTPUTFILE))
    echo "Cache file saved as ".$OUTPUTFILE;
  else
    echo "Could not save cache file as ".$OUTPUTFILE;
?>
</p>

<p>All operations complete. <a href="index.html">Return</a> to tools.</p>

</body>
</html>
