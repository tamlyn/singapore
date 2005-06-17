<?php 

/**
 * Static class providing utility methods
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2005 Tamlyn Rhodes
 * @version $Id: utils.class.php,v 1.1 2005/06/17 20:08:34 tamlyn Exp $
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
  
  /**
   * Obfuscates the given email address by replacing "." with "dot" and "@" with "at"
   * @param string  email address to obfuscate
   * @param boolean  override the obfuscate_email config setting (optional)
   * @return string  obfuscated email address or HTML mailto link
   */
  function formatEmail($email, $forceObfuscate = false)
  {
    if($this->config->obfuscate_email || $forceObfuscate)
        return strtr($email,array("@" => ' <b>'.$this->i18n->_g("email|at").'</b> ', "." => ' <b>'.$this->i18n->_g("email|dot").'</b> '));
      else
        return "<a href=\"mailto:".$email."\">".$email."</a>";
  }

  /**
   * rawurlencode() supplied string but preserve / character for cosmetic reasons.
   * @param  string  id to encode
   * @return string  encoded id
   */
  function encodeId($id)
  {
    $in = explode("/",$id);
    $out = array();
    for($i=1;$i<count($in);$i++)
      $out[$i-1] = rawurlencode($in[$i]);
    return $out ? implode("/",$out) : ".";
  }
  
  /**
   * Returns a link to the image or gallery with the correct formatting and path
   *
   * @author   Adam Sissman <adam at bluebinary dot com>
   * @param string  gallery id
   * @param string  image filename (optional)
   * @param int  page offset (optional)
   * @param string  action to perform (optional)
   * @return string formatted URL
   */
  function formatURL($gallery, $image = null, $startat = null, $action = null)
  {
    if($this->config->use_mod_rewrite) { //format url for use with mod_rewrite
      $ret  = $this->config->base_url.$gallery;
      if($startat) $ret .= ','.$startat;
      $ret .= '/';
      if($image)   $ret .= rawurlencode($image);
      
      $query = array();
      if($action)  $query[] = $this->config->url_action."=".$action;
      if($this->language != $this->config->default_language) $query[] = $this->config->url_lang.'='.$this->language;
      if($this->template != $this->config->default_template) $query[] = $this->config->url_template.'='.$this->template;
      
      if(!empty($query))
        $ret .= '?'.implode('&amp;', $query);
    } else { //format plain url
      $ret  = $this->config->index_file_url;
      $ret .= $this->config->url_gallery."=".$gallery;
      if($startat) $ret .= "&amp;".$this->config->url_startat."=".$startat;
      if($image)   $ret .= "&amp;".$this->config->url_image."=".rawurlencode($image);
      if($action)  $ret .= "&amp;".$this->config->url_action."=".$action;
      if($this->language != $this->config->default_language) $ret .= '&amp;'.$this->config->url_lang.'='.$this->language;
      if($this->template != $this->config->default_template) $ret .= '&amp;'.$this->config->url_template.'='.$this->template;
    }
    
    return $ret;
  }
  
  /**
   * Returns a link to thumb.php with the correct formatting and path
   *
   * @author   Adam Sissman <adam at bluebinary dot com>
   */
  function thumbnailURL($gallery, $image, $width, $height, $forceSize)
  {
    $ret = $this->config->base_url;
    $ret .= "thumb.php";
    $ret .= "?gallery=".$gallery."&amp;image=".rawurlencode($image);
    $ret .= "&amp;width=".$width."&amp;height=".$height;
    if($forceSize) $ret .= "&amp;force=1";

    return $ret;
  }


  function thumbnailPath($base, $gallery, $image, $width, $height, $forceSize, $mode = 0)
  {
    switch($mode) {
      case 0 :
        return $base.$width."x".$height.($forceSize?"f":"").strtr("-$gallery-$image",":/?\\","----");
      case 1 :
        return $base."_thumbs/".$width."x".$height.($forceSize?"f":"").strtr("-$image",":/?\\","----");
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
  
    
    
}


?>
