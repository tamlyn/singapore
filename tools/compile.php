<?php
 
/**
 * This file merges the two PO template files (singapore.pot and singapore.admin.pot)
 * with all existing language files (singapore.LANG.po)
 * 
 * @author Joel Sjögren <joel dot sjogren at nonea dot se>
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: compile.php,v 1.4 2004/02/02 16:31:36 tamlyn Exp $
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


/**
 * Parses a PO file and writes a file with
 * serialized strings for PHP
 *
 * @param    string    $input      file to read from
 * @param    string    $output     file to write to
 * @return   bool                  success
 **/
function parsePO ($input, $output)
{
    // Open PO-file
    file_exists($input) or die("The file {$input} doesn't exit.\n");
    $fp = @fopen($input, "r") or die("Couldn't open {$input}.\n");

    $type = 0;
    $strings = array();
    $escape = "\n";
    $string = "";
    $plural = 0;
    $header = "";
    while (!feof($fp)) {
        $line = trim(fgets($fp,1024));
        if ($line == "" || $line[0] == "#") continue;
        // New (msgid "text")
        if (preg_match("/msgid[[:space:]]+\"(.+)\"$/i", $line, $m)) {
            $type = 1;
            $string = stripcslashes($m[1]);
        // New id on several rows (msgid "") or header
        } elseif (preg_match("/msgid[[:space:]]+\"\"$/i", $line, $m)) {
            $type = 2;
            $string = "";
         // Add to id on several rows ("")
        } elseif (preg_match("/^\"(.*)\"$/i", $line, $m) && ($type == 1 || $type == 2 || $type == 3)) {
            $type = 3;
            $string .= stripcslashes($m[1]);
        // New string (msgstr "text")
        } elseif (preg_match("/msgstr[[:space:]]+\"(.+)\"$/i", $line, $m) && ($type == 1 || $type == 3) && $string) {
            $strings[$string] = stripcslashes($m[1]);
            $type = 4;
        // New string on several rows (msgstr "")
        } elseif (preg_match("/msgstr[[:space:]]+\"\"$/i", $line, $m) && ($type == 1 || $type == 3) && $string) {
            $type = 4;
            $strings[$string] = "";
         // Add to string on several rows ("")
        } elseif (preg_match("/^\"(.*)\"$/i", $line, $m) && $type == 4 && $string) {
            $strings[$string] .= stripcslashes($m[1]);
        
        /////Plural forms/////
         // New plural id (msgid_plural "text")
        } elseif (preg_match("/msgid_plural[[:space:]]+\".*\"$/i", $line, $m)) {
            $type = 6;
         // New plural string (msgstr[N] "text")
        } elseif (preg_match("/msgstr\[(\d+)\][[:space:]]+\"(.+)\"$/i", $line, $m) && ($type == 6) && $string) {
            $plural = $m[1];
            $strings[$string][$plural] = stripcslashes($m[2]);
            $type = 6;
        // New plural string on several rows (msgstr[N] "")
        } elseif (preg_match("/msgstr\[(\d+)\][[:space:]]+\"\"$/i", $line, $m) && ($type == 6) && $string) {
            $plural = $m[1];
            $strings[$string][$plural] = "";
            $type = 6;
         // Add to plural string on several rows ("")
        } elseif (preg_match("/^\"(.*)\"$/i", $line, $m) && $type == 6 && $string) {
            $strings[$string][$plural] .= stripcslashes($m[1]);
        
        /////Header section/////
         // Start header section
        } elseif (preg_match("/msgstr[[:space:]]+\"(.+)\"$/i", $line, $m) && $type == 2 && !$string) {
            $header = stripcslashes($m[1]);
            $type = 5;
         // Start header section
        } elseif (preg_match("/msgstr[[:space:]]+\"\"$/i", $line, $m) && !$string) {
            $header = "";
            $type = 5;
         // Add to header section
        } elseif (preg_match("/^\"(.*)\"$/i", $line, $m) && $type == 5) {
            $header .= stripcslashes($m[1]);
            
        // Reset
        } else {
            unset($strings[$string]);
            $type = 0;
            $string = "";
            $plural = 0;
        }
    }

    // Close PO-file
    fclose($fp);
    
    // Extract plural forms from header
    if(preg_match("/Plural-Forms:[[:space:]]+(.+)/i", $header, $m)) {
      $pluralString = str_replace("n","\$n",$m[1]);
      $pluralString = str_replace(" plural","\$plural",$pluralString);
    } else {
      $pluralString = "\$nplurals=1; \$plural=\$n==1?0:1;";
    }
    
    // Extract character set from header
    if(preg_match("/Content-Type:(.+)charset=(.+)/i", $header, $m))
      $charset = $m[2];
    else
      $charset = "";
    
    // Extract language name from header
    if(preg_match("/Language-Team:[[:space:]]+([^<\n]+)/i", $header, $m))
      $language = $m[1];
    else
      $language = "Unknown";
    
    $strings[0]["charset"] = $charset;
    $strings[0]["language"] = $language;
    $strings[0]["plural"] = $pluralString;
    
    // Open data file for writing
    $fp = @fopen($output, "wb") or die("Couldn't open file ({$output}).\n");
    fwrite($fp, serialize($strings));
    fclose($fp);

    // Return
    return true;
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>i18n po compiler</title>
<link rel="stylesheet" type="text/css" href="tools.css" />
</head>

<body>

<h1>i18n po compiler</h1>

<p><?php 
  $files = parseDirectory($OUTPUTPATH, 'po');
  foreach ($files as $file) {
    if (!preg_match("/singapore\.(admin\.)?[\w]+\.po$/i", $file)) continue;
    $outfile = preg_replace("/\.[^\.]+$/", ".pmo", $file);
    if(parsePO($file, $outfile))
      echo "Parsed $file to $outfile<br />";
    else
      echo "Could not parse $file<br />";
  }  

?>
</p>

<p>All operations complete. <a href="index.html">Return</a> to tools.</p>

</body>
</html>
