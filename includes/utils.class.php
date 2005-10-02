<?php 

/**
 * Static class providing utility methods
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2005 Tamlyn Rhodes
 * @version $Id: utils.class.php,v 1.4 2005/10/02 03:35:24 tamlyn Exp $
 */


/**
 * Static class providing utility methods
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 */
class sgUtils
{

  /**
   * Callback function for sorting things
   * @static
   */
  function multiSort($a, $b) {
    switch($GLOBALS["sgSortOrder"]) {
      case "p" : return strcmp($a->id, $b->id); //path
      case "P" : return strcmp($b->id, $a->id); //path (reverse)
      case "f" : return strcmp($a->filename, $b->filename); //filename
      case "F" : return strcmp($b->filename, $a->filename); //filename (reverse)
      case "n" : return strcmp($a->name, $b->name); //name
      case "N" : return strcmp($b->name, $a->name); //name (reverse)
      case "i" : return strcasecmp($a->name, $b->name); //case-insensitive name
      case "I" : return strcasecmp($b->name, $a->name); //case-insensitive name (reverse)
      case "a" : return strcmp($a->artist, $b->artist); //artist
      case "A" : return strcmp($b->artist, $a->artist); //artist (reverse)
      case "d" : return strcmp($a->date, $b->date); //date
      case "D" : return strcmp($b->date, $a->date); //date (reverse)
      case "l" : return strcmp($a->location, $b->location); //location
      case "L" : return strcmp($b->location, $a->location); //location (reverse)
    }
  }
  
  function conditional($conditional, $iftrue, $iffalse = null)
  {
    if($conditional) return sprintf($iftrue, $conditional);
    elseif($iffalse != null) return sprintf($iffalse, $conditional);
    else return "";
  }
  
  /**
   * Callback function for recursively stripping slashes
   * @static
   */
  function arraystripslashes($toStrip)
  {
    if(is_array($toStrip))
      return array_map(array("Singapore","arraystripslashes"), $toStrip);
    else
      return stripslashes($toStrip);
  }
  
  function thumbnailPath($gallery, $image, $width, $height, $forceSize, $mode = 1)
  {
    switch($mode) {
      case 0 :
        return $GLOBALS["sgConfig"]->base_path.$GLOBALS["sgConfig"]->pathto_data_dir."cache/".$width."x".$height.($forceSize?"f":"").strtr("-$gallery-$image",":/?\\","----");
      case 1 :
        return $GLOBALS["sgConfig"]->base_path.$GLOBALS["sgConfig"]->pathto_galleries.$gallery."/_thumbs/".$width."x".$height.($forceSize?"f":"").strtr("-$image",":/?\\","----");
    }
  }
  
  /**
   * @param string  relative or absolute path to directory
   * @param string  regular expression of files to return (optional)
   * @param bool    true to get hidden directories too
   * @returns stdClass|false  a data object representing the directory and its contents
   * @static
   */
  function getListing($wd, $mask = null, $getHidden = false)
  {
    $dir = new stdClass;
    $dir->path = realpath($wd)."/";
    $dir->files = array();
    $dir->dirs = array();
    
    $dp = opendir($dir->path);
    if(!$dp) return false;

    while(false !== ($entry = readdir($dp)))
      if(is_dir($dir->path.$entry) && (($entry{0} != '.' && $entry{0} != '_') || $getHidden))
        $dir->dirs[] = $entry;
      elseif(!is_dir($entry) && ($mask == null || preg_match("/\.($mask)$/i",$entry)))
        $dir->files[] = $entry;
    
    sort($dir->files);
    sort($dir->dirs);
    closedir($dp);
    return $dir;
  }
  
  /**
   * Recursively deletes all directories and files in the specified directory.
   * USE WITH EXTREME CAUTION!!
   * @returns boolean true on success; false otherwise
   * @static
   */
  function rmdir_all($wd)
  {
    if(!$dp = opendir($wd)) return false;
    $success = true;
    while(false !== ($entry = readdir($dp))) {
      if($entry == "." || $entry == "..") continue;
      if(is_dir("$wd/$entry")) $success &= $this->rmdir_all("$wd/$entry");
      else $success &= unlink("$wd/$entry");
    }
    closedir($dp);
    $success &= rmdir($wd);
    return $success;
  }
  
  /**
   * Returns an array of language codes specified in the Accept-Language HHTP
   * header field of the user's browser. q= components are ignored and removed.
   * hyphens (-) are converted to underscores (_).
   * @return array  accepted language codes
   * @static
   */
  function getBrowserLanguages()
  {
    $langs = array();
    foreach(explode(",",$_SERVER["HTTP_ACCEPT_LANGUAGE"]) as $bit)
      if($pos = strpos($bit,";"))
        $langs[] = strtr(substr($bit,0,$pos),"-","_");
      else
        $langs[] = strtr($bit,"-","_");
    return $langs;
  }
  
    
  function dump($item)
  {
    echo "<pre>\n";
    sgUtils::dump_s($item, 0);     
    echo "</pre>";
  }
  
  function dump_s($value, $depth)
  {
    if(is_object($value))
      switch(get_class($value)) {
        case "sgimage" :
          echo "sgImage ({$value->id})";
          break;
        case "sggallery" :
          echo "sgGallery ({$value->id})";
          break;
        default :
          echo "Object (".get_class($value).")";
      }
    elseif(is_array($value)) {
      echo "Array (\n";
      foreach($value as $key => $val)
        echo "  $key => ".sgUtils::dump_s($val, $depth+1);
    } else
      echo "$value";
        
    echo "\n";
  }
  
  /**
   * Wrapper for mkdir() implementing the safe-mode hack
   */
  function mkdir($path)
  {
    if($GLOBALS['sgConfig']->safe_mode_hack) {
      $connection = ftp_connect($GLOBALS['sgConfig']->ftp_server);
      // login to ftp server
      $result = ftp_login($connection, $GLOBALS['sgConfig']->ftp_user, $GLOBALS['sgConfig']->ftp_pass);
   
      // check if connection was made
      if ((!$connection) || (!$result))
        return false;
  
      ftp_chdir($connection, $GLOBALS['sgConfig']->ftp_base_path); // go to destination dir
      if(ftp_mkdir($connection, $path)) // create directory
        ftp_site($connection, "CHMOD ".decoct($GLOBALS['sgConfig']->directory_mode)." ".$path);
        
      ftp_close($connection); // close connection
    } else 
      mkdir($path, $GLOBALS["sgConfig"]->directory_mode);
  }
    
}


?>
