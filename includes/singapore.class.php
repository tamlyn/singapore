<?php 

/**
 * Main class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: singapore.class.php,v 1.44 2005/02/27 06:47:07 tamlyn Exp $
 */

//define constants for regular expressions
define('SG_REGEXP_PROTOCOLURL', '(?:http://|https://|ftp://|mailto:)(?:[a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,4}(?::[0-9]+)?(?:/[^ \n\r\"\'<]+)?');
define('SG_REGEXP_WWWURL',      'www\.(?:[a-zA-Z0-9\-]+\.)*[a-zA-Z]{2,4}(?:/[^ \n\r\"\'<]+)?');
define('SG_REGEXP_EMAILURL',    '(?:[\w][\w\.\-]+)+@(?:[\w\-]+\.)+[a-zA-Z]{2,4}');

 
/**
 * Provides functions for handling galleries and images
 * @uses sgGallery
 * @uses sgImage
 * @uses sgConfig
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 */
class Singapore
{
  /**
   * current script version 
   * @var string
   */
  var $version = "0.9.11CVS";
  
  /**
   * instance of a {@link sgConfig} object representing the current 
   * script configuration
   * @var sgConfig
   */
  var $config;
  
  /**
   * instance of the currently selected IO handler object
   * @var sgIO_csv
   */
  var $io;
  
  /**
   * instance of a {@link Translator}
   * @var Translator
   */
  var $i18n;
  
  /**
   * instance of a {@link sgGallery} representing the current gallery
   * @var sgGallery
   */
  var $gallery;
  
  /**
   * reference to the currently selected {@link sgImage} object in the 
   * $images array of {@link $gallery}
   * @var sgImage
   */
  var $image;
  
  var $language = null;
  var $action = null;
  
  /**
   * Constructor, does all init type stuff. This code is a total mess.
   * @param string the path to the base singapore directory
   */
  function Singapore($basePath = "")
  {
    //import class definitions
    //io handler class included once config is loaded
    require_once $basePath."includes/translator.class.php";
    require_once $basePath."includes/gallery.class.php";
    require_once $basePath."includes/config.class.php";
    require_once $basePath."includes/image.class.php";
    require_once $basePath."includes/user.class.php";
    
    //start execution timer
    $this->scriptStartTime = microtime();
    
    //remove slashes
    if(get_magic_quotes_gpc())
      $_REQUEST = array_map(array("Singapore","arraystripslashes"), $_REQUEST);
    
    //load config from singapore root directory
    $this->config = new sgConfig($basePath."singapore.ini");
    $this->config->loadConfig($basePath."secret.ini.php");
    
    //if instantiated remotely...
    if(!empty($basePath)) {
      //...try to guess base path and relative url
      $this->config->base_path = $basePath;
      $this->config->base_url  = $basePath;
      //...load local config if present
      //may over-ride guessed values above
      $this->config->loadConfig("singapore.local.ini");
    }
    
    //set current gallery to root if not specified in url
    $galleryId = isset($_REQUEST[$this->config->url_gallery]) ? $_REQUEST[$this->config->url_gallery] : ".";
    
    //load config from gallery ini file (gallery.ini) if present
    $this->config->loadConfig($basePath.$this->config->pathto_galleries.$galleryId."/gallery.ini");
    //set current template from request vars or config
    $this->template = isset($_REQUEST[$this->config->url_template]) ? $_REQUEST[$this->config->url_template] : $this->config->default_template;
    $this->config->pathto_current_template = $this->config->pathto_templates.$this->template.'/';
    //load config from template ini file (template.ini) if present
    $this->config->loadConfig($basePath.$this->config->pathto_current_template."template.ini");
    
    
    //set runtime values
    $this->config->pathto_logs = $this->config->pathto_data_dir."logs/";
    $this->config->pathto_cache = $this->config->pathto_data_dir."cache/";
    $this->config->pathto_admin_template = $this->config->pathto_templates.$this->config->admin_template_name."/";
    
    
    //convert octal strings to integers
    if(isset($this->config->directory_mode) && is_string($this->config->directory_mode)) $this->config->directory_mode = octdec($this->config->directory_mode);
    if(isset($this->config->umask) && is_string($this->config->umask)) $this->config->umask = octdec($this->config->umask);
    
    
    //set current language from request vars or config
    if(!empty($_REQUEST[$this->config->url_lang]))
      $this->language = $_REQUEST[$this->config->url_lang];
    else {
      $this->language = $this->config->default_language;
      if($this->config->detect_language)
        foreach($this->getBrowserLanguages() as $lang)
          if($lang=="en" || file_exists($basePath.$this->config->pathto_locale."singapore.".$lang.".pmo")) {
            $this->language = $lang;
            break;
          }
    }
    
    //read the language file
    $this->i18n = new Translator($basePath.$this->config->pathto_locale."singapore.".$this->language.".pmo");
    
    //set the UMASK
    umask($this->config->umask);
    
    //include IO handler class and create instance
    require_once $basePath."includes/io_".$this->config->io_handler.".class.php";
    $ioClassName = "sgIO_".$this->config->io_handler;
    $this->io = new $ioClassName($this->config);
    
    //load gallery and image info
    $this->selectGallery($galleryId);
    
    //set character set
    if(!empty($this->i18n->languageStrings[0]["charset"]))
      $this->character_set = $this->i18n->languageStrings[0]["charset"];
    else
      $this->character_set = $this->config->default_charset;
    
    //temporary code
    if(isset($_REQUEST[$this->config->url_action])) $this->action = $_REQUEST[$this->config->url_action];
  }
  
  /**
   * Load gallery and image info
   * @param string the id of the gallery to load (optional)
   */
  function selectGallery($galleryId = "")
  {
    if(empty($galleryId)) $galleryId = isset($_REQUEST[$this->config->url_gallery]) ? $_REQUEST[$this->config->url_gallery] : ".";
    
    //try to validate gallery id
    if(strlen($galleryId)>1 && $galleryId{1} != '/') $galleryId = './'.$galleryId;
    
    //detect back-references to avoid file-system walking
    if(strpos($galleryId,"../")!==false) $galleryId = ".";
    
    //fetch the gallery and image info
    $this->gallery = $this->io->getGallery($galleryId);
    
    //check if gallery was successfully fetched
    if($this->gallery == null) {
      $this->gallery = new sgGallery($galleryId);
      $this->gallery->name = $this->i18n->_g("Gallery not found '%s'",htmlspecialchars($galleryId));
    }
    
    //sort galleries and images
    $GLOBALS["temp"]["gallery_sort_order"] = $this->config->gallery_sort_order;
    $GLOBALS["temp"]["image_sort_order"] = $this->config->image_sort_order;
    if($this->config->gallery_sort_order!="x") usort($this->gallery->galleries, array("Singapore","gallerySort"));
    if($this->config->image_sort_order!="x") usort($this->gallery->images, array("Singapore","imageSort"));
    
    //if startat is set the cast to int otherwise startat 0
    $this->startat = isset($_REQUEST[$this->config->url_startat]) ? (int)$_REQUEST[$this->config->url_startat] : 0;
    

    //encode the gallery name
    $this->gallery->idEncoded = $this->encodeId($this->gallery->id);
    
    $this->gallery->idEntities = htmlspecialchars($this->gallery->id);
    
    //find the parent
    $this->gallery->parent = substr($this->gallery->idEncoded, 0, strrpos($this->gallery->idEncoded, "/"));
    $this->gallery->parentName = urldecode(substr($this->gallery->parent,strrpos($this->gallery->parent,"/")+1));
    if($this->gallery->parentName == "") {
      $this->gallery->parentName = $this->config->gallery_name;
      $this->gallery->parent = ".";
    }
    
    //do the logging stuff and select the image (if any)
    if(empty($_REQUEST[$this->config->url_image])) {
      if($this->config->track_views) $hits = $this->logGalleryView();
      if($this->config->show_views) $this->gallery->hits = $hits;
    } else {
      $this->selectImage($_REQUEST[$this->config->url_image]);
      if($this->config->track_views) $hits = $this->logImageView();
      if($this->config->show_views) $this->image->hits = $hits;
    }
    
  }
  
  /**
   * Selects an image from the current gallery
   * @param mixed either the filename of the image to select or the integer 
   *  index of its position in the images array
   * @return boolean true on success; false otherwise
   */
  function selectImage($image) 
  {
    if(is_string($image)) {
      foreach($this->gallery->images as $index => $img)
        if($img->filename == $image) {
          $this->image =& $this->gallery->images[$index];
          $this->image->index = $index;
          return true;
        }
    } elseif(is_int($image) && $image >= 0 && $image < count($this->gallery->images)) {
      $this->image =& $this->gallery->images[$image];
      $this->image->index = $image;
      return true;
    }
    $this->image = new sgImage();
    $this->image->name = $this->i18n->_g("Image not found '%s'",htmlspecialchars($image));
    return false;
  }
  
  /**
   * Callback function for sorting galleries
   * @static
   */
  function gallerySort($a, $b) {
    switch($GLOBALS["temp"]["gallery_sort_order"]) {
      case "p" : return strcmp($a->id, $b->id); //path
      case "P" : return strcmp($b->id, $a->id); //path (reverse)
      case "n" : return strcmp($a->name, $b->name); //name
      case "N" : return strcmp($b->name, $a->name); //name (reverse)
      case "i" : return strcasecmp($a->name, $b->name); //case-insensitive name
      case "I" : return strcasecmp($b->name, $a->name); //case-insensitive name (reverse)
    }
  }
  
  /**
   * Callback function for sorting images
   * @static
   */
  function imageSort($a, $b) {
    switch($GLOBALS["temp"]["image_sort_order"]) {
      case "n" : return strcmp($a->name, $b->name); //name
      case "N" : return strcmp($b->name, $a->name); //name (reverse)
      case "i" : return strcasecmp($a->name, $b->name); //case-insensitive name
      case "I" : return strcasecmp($b->name, $a->name); //case-insensitive name (reverse)
      case "a" : return strcmp($a->artist, $b->artist); //artist
      case "A" : return strcmp($b->artist, $a->artist); //artist (reverse)
      case "f" : return strcmp($a->filename, $b->filename); //filename
      case "F" : return strcmp($b->filename, $a->filename); //filename (reverse)
      case "d" : return strcmp($a->date, $b->date); //date
      case "D" : return strcmp($b->date, $a->date); //date (reverse)
      case "l" : return strcmp($a->location, $b->location); //location
      case "L" : return strcmp($b->location, $a->location); //location (reverse)
    }
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
  
  function formatEmail($email, $forceObfuscate = false)
  {
    if($this->config->obfuscate_email || $forceObfuscate)
        return strtr($email,array("@" => ' <b>'.$this->i18n->_g("email|at").'</b> ', "." => ' <b>'.$this->i18n->_g("email|dot").'</b> '));
      else
        return "<a href=\"mailto:".$email."\">".$email."</a>";
  }

  /**
   * rawurlencode() supplied string but preserve / character for cosmetic reasons.
   * @param  string
   * @return string
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
   * Returns image name for image pages and gallery name for gallery pages.
   * If either of these is empty, returns gallery_name config option.
   *
   * @return string  Title of current page
   */
  function pageTitle()
  {
    if($this->isImage() && $this->imageName()!="")
      return $this->imageName();
    elseif(($this->isAlbum() || $this->isGallery()) && $this->galleryName()!="")
      return $this->galleryName();
    else
      return $this->config->gallery_name;
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

  /**
   * @return int|null the number of image hits or null
   */
  function logImageView() 
  {
    $this->gallery->hits = $this->io->getHits($this->gallery->id);
    if(!$this->gallery->hits) return null;

    if(isset($this->gallery->hits->images)) {
      //search selected for image in existing log
      for($i=0;$i<count($this->gallery->hits->images);$i++) 
        if($this->gallery->hits->images[$i]->filename == $this->image->filename) {
          $numhits = ++$this->gallery->hits->images[$i]->hits;
          $this->gallery->hits->images[$i]->lasthit = time();
          break;
        }
    } else {
      $this->gallery->hits->images = array();
      $i = 0;
    }
    //if image not found then add it
    if($i == count($this->gallery->hits->images)) {
      $this->gallery->hits->images[$i] = new stdClass;
      $this->gallery->hits->images[$i]->filename = $this->image->filename;
      $this->gallery->hits->images[$i]->hits = $numhits = 1;
      $this->gallery->hits->images[$i]->lasthit = time();
    }
    
    
    //save modified hits data
    $this->io->putHits($this->gallery->id,$this->gallery->hits);
  
    //return number of hits
    return $numhits;
  }
  
  /**
   * @return int|null the number of gallery hits or null
   */
  function logGalleryView() 
  {
    $this->gallery->hits = $this->io->getHits($this->gallery->id);
    if(!$this->gallery->hits) return null;
    
    if(isset($this->gallery->hits->hits) && $this->startat == 0) $numhits = ++$this->gallery->hits->hits;
    elseif(isset($this->gallery->hits->hits)) $numhits = $this->gallery->hits->hits;
    else $numhits = $this->gallery->hits->hits = 1;

    $this->gallery->hits->lasthit = time();
        
    //save modified hits data
    $this->io->putHits($this->gallery->id,$this->gallery->hits);
  
    //return number of hits
    return $numhits;
  }

  /**
   * @return bool true if this is an image page; false otherwise
   */
  function isImage()
  {
    return !empty($this->image);
  }
  
  /**
   * @return bool true if this is a non-album gallery page; false otherwise
   */
  function isGallery($index = null)
  {
    return !empty($this->gallery) && $this->galleryHasSubGalleries($index);
  }
  
  /**
   * @return bool true if this is an album page; false otherwise
   */
  function isAlbum($index = null)
  {
    return !$this->isGallery($index) && !$this->isImage() && !empty($this->gallery);
  }
  
  /**
   * @return int the script execution time in seconds rounded to two decimal places
   */
  function scriptExecTime()
  {
    $scriptStartTime = $this->scriptStartTime;
    $scriptEndTime = microtime();
    
    list($usec, $sec) = explode(" ",$scriptStartTime); 
    $scriptStartTime = (float)$usec + (float)$sec; 
    list($usec, $sec) = explode(" ",$scriptEndTime); 
    $scriptEndTime = (float)$usec + (float)$sec; 
    
    $scriptExecTime = floor(($scriptEndTime - $scriptStartTime)*100)/100;
    return $scriptExecTime;
  }
  
  /**
   * Displays the script execution time if configured to do so
   * @returns string  the script execution time
   */
  function scriptExecTimeText()
  {
    if($this->config->show_execution_time)
      return $this->i18n->_g("Page created in %s seconds",$this->scriptExecTime());
    else
      return "";
  }
  
  function versionText()
  {
    return "singapore v".$this->version;
  }
  
  function versionLink()
  {
    return '<a href="http://singapore.sourceforge.net/">'.$this->versionText().'</a>';
  }
  
  function poweredByVersion()
  {
    return $this->i18n->_g("singapore|Powered by %s",$this->versionLink());
  }
  
  function allRightsReserved()
  {
    return $this->i18n->_g("All rights reserved.");
  }
  
  function copyrightMessage()
  {
    return $this->i18n->_g("Images may not be reproduced in any form without the express written permission of the copyright holder.");
  }
  
  function adminLink()
  {
    return '<a href="'.$this->config->base_url.'admin.php">'.$this->i18n->_g("Log in")."</a>";
  }
  
  /**
   * @returns stdClass|false a data object representing the desired gallery
   * @static
   */
  function getListing($wd, $type = "dirs")
  {
    $dir = new stdClass;
    $dir->path = realpath($wd).DIRECTORY_SEPARATOR;
    $dir->files = array();
    $dir->dirs = array();
    $dp = opendir($dir->path);
    
    if(!$dp) return false;

    switch($type) {
      case "images" :
        while(false !== ($entry = readdir($dp)))
          if(!is_dir($entry) && preg_match("/\.(".$this->config->recognised_extensions.")$/i",$entry))
            $dir->files[] = $entry;
        sort($dir->files);
        rewinddir($dp);
        //run on and get dirs too
      case "dirs" :
       while(false !== ($entry = readdir($dp)))
          if(is_dir($dir->path.$entry) && $entry{0} != '.') 
            $dir->dirs[] = $entry;
        sort($dir->dirs);
        break;
      case "all" :
        while(false !== ($entry = readdir($dp)))
          if(is_dir($dir->path.$entry)) $dir->dirs[] = $entry;
          else $dir->files[] = $entry;
        sort($dir->dirs);
        sort($dir->files);
        break;
      default :
        while(false !== ($entry = readdir($dp)))
          if(strpos(strtolower($entry),$type)) 
            $dir->files[] = $entry;
        sort($dir->files);
    }
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
  
  /**
   * Checks to see if the user is currently logged in to admin mode. Also resets
   * the login timeout to the current time.
   * @returns boolean true if the user is logged in; false otherwise
   * @static
   */
  function isLoggedIn()
  {
    if(isset($_SESSION["sgUser"]) && $_SESSION["sgUser"]->check == md5($_SERVER["REMOTE_ADDR"]) && (time() - $_SESSION["sgUser"]->loginTime < 1800)) {
  	  $_SESSION["sgUser"]->loginTime = time();
  	  return true;
    }
    return false;
  }
  
  /**
   * Creates an array of objects each representing an item in the crumb line.
   * @return array the items of the crumb line
   */
  function crumbLineArray()
  {
    $crumb[0] = new stdClass;
    $crumb[0]->id = ".";
    $crumb[0]->path = ".";
    $crumb[0]->name = $this->config->gallery_name;
    
    if(!isset($this->gallery->id))
      return $crumb;
    
    $galleries = explode("/",$this->gallery->id);
    $currentPath = ".";
    
    for($i=1;$i<count($galleries);$i++) {
      $crumb[$i] = new stdClass;
      $crumb[$i]->id = $galleries[$i];
      $currentPath .= "/".$galleries[$i];
      $crumb[$i]->path = $this->encodeId($currentPath);
      $crumb[$i]->name = $galleries[$i];
    }
    
    if($this->isImage()) {
      $crumb[$i] = new stdClass;
      $crumb[$i]->id = "";
      $crumb[$i]->path = "";
      $crumb[$i]->name = $this->image->name;
    }
    
    return $crumb;
  }
  
  /**
   * @return string the complete crumb line with links
   */
  function crumbLineText()
  {
    $crumbArray = $this->crumbLineArray();
    $ret = "";
    for($i=0;$i<count($crumbArray)-1;$i++) {
      $ret .= "<a href=\"".$this->formatURL($crumbArray[$i]->path)."\">".$crumbArray[$i]->name."</a> &gt;\n";
    }
    $ret .= $crumbArray[$i]->name;
    return $ret;
  }
  
  function crumbLine()
  {
    return $this->i18n->_g("crumb line|You are here:")." ".$this->crumbLineText();
  }
  
  function imageMap()
  {
    if(!$this->config->imagemap_navigation) return;
    
    $imageWidth = $this->imageWidth();
    $imageHeight = $this->imageHeight();
    $middleX = round($imageWidth/2);
    $middleY = round($imageHeight/2);
    
    $ret  = "<map name=\"sgNavMap\" id=\"sgNavMap\">\n";
    if($this->imageHasNext()) $ret .= '<area href="'.$this->imageNextURL().'" alt="'.$this->i18n->_g("image|Next").'" title="'.$this->i18n->_g("image|Next").'" shape="poly" ';
    else $ret .= '<area href="'.$this->imageParentURL().'" alt="'.$this->i18n->_g("image|Thumbnails").'" title="'.$this->i18n->_g("image|Thumbnails").'" shape="poly" ';
    $ret .= "coords=\"$middleX,$middleY,$imageWidth,$imageHeight,$imageWidth,0,$middleX,$middleY\" />\n";
    if($this->imageHasPrev()) $ret .= '<area href="'.$this->imagePrevURL().'" alt="'.$this->i18n->_g("image|Previous").'" title="'.$this->i18n->_g("image|Previous").'" shape="poly" ';
    else $ret .= '<area href="'.$this->imageParentURL().'" alt="'.$this->i18n->_g("image|Thumbnails").'" title="'.$this->i18n->_g("image|Thumbnails").'" shape="poly" ';
    $ret .= "coords=\"$middleX,$middleY,0,0,0,$imageHeight,$middleX,$middleY\" />\n";
    $ret .= '<area href="'.$this->imageParentURL().'" alt="'.$this->i18n->_g("image|Thumbnails").'" title="'.$this->i18n->_g("image|Thumbnails").'" shape="poly" ';
    $ret .= "coords=\"$middleX,$middleY,0,0,$imageWidth,0,$middleX,$middleY\" />\n";
    $ret .= '</map>';
    
    return $ret;
  }
  
  /**
   * Generates the HTML code for the language select box
   * @return string select box HTML code
   */
  function languageFlipper()
  {
    if(!$this->config->language_flipper) return "";
    
    $languageCache = $this->config->base_path.$this->config->pathto_data_dir."languages.cache";
    // Look for the language file
    if(!file_exists($languageCache))
      return "";
    
    // Open the file
    $fp = @fopen($languageCache, "r");
    if (!$fp) return "";
    
    // Read contents
    $str = '';
    while (!feof($fp)) $str .= fread($fp, 1024);
    // Unserialize
    $availableLanguages = @unserialize($str);
    
    $ret  = '<div class="sgLanguageFlipper">';
    $ret .= '<form method="get" action="'.$_SERVER["PHP_SELF"]."\">\n";
    //carry over current get vars
    foreach($_GET as $var => $val)
      $ret .= '<input type="hidden" name="'.$var.'" value="'.$val."\" />\n";
    $ret .= '<select name="'.$this->config->url_lang."\">\n";
    $ret .= '  <option value="'.$this->config->default_language.'">'.$this->i18n->_g("Select language...")."</option>\n";
    foreach($availableLanguages as $code => $name) {
      $ret .= '  <option value="'.$code.'"';
      if($code == $this->language && $this->language != $this->config->default_language)
        $ret .= 'selected="true" ';
      $ret .= '>'.htmlentities($name)."</option>\n";
    }
    $ret .= "</select>\n";
    $ret .= '<input type="submit" class="button" value="'.$this->i18n->_g("Go")."\" />\n";
    $ret .= "</form></div>\n";
    return $ret;
  }
  
  /**
   * Generates the HTML code for the language select box
   * @return string select box HTML code
   */
  function templateFlipper()
  {
    if(!$this->config->template_flipper) return "";
    
    //get list of installed templates
    $templates = $this->getListing($this->config->base_path.$this->config->pathto_templates, "dirs");
    
    $ret  = '<div class="sgTemplateFlipper">';
    $ret .= '<form method="get" action="'.$_SERVER["PHP_SELF"]."\">\n";
    //carry over current get vars
    foreach($_GET as $var => $val)
      $ret .= '<input type="hidden" name="'.$var.'" value="'.$val."\" />\n";
    $ret .= '<select name="'.$this->config->url_template."\">\n";
    $ret .= '  <option value="'.$this->config->default_template.'">'.$this->i18n->_g("Select template...")."</option>\n";
    foreach($templates->dirs as $name)
      //do not list admin template(s)
      if(strpos($name, "admin_")===false) {
        $ret .= '  <option value="'.$name.'"';
        if($name == $this->template && $this->template != $this->config->default_template)
          $ret .= 'selected="true" ';
        $ret .= '>'.$name."</option>\n";
      }
    $ret .= "</select>\n";
    $ret .= '<input type="submit" class="button" value="'.$this->i18n->_g("Go")."\" />\n";
    $ret .= "</form></div>\n";
    return $ret;
  }
  
  
  /////////////////////////////
  //////gallery functions//////
  /////////////////////////////
  
  
  /**
   * If the specified gallery is an album then it returns the number of 
   * images contained otherwise the number of sub-galleries is returned
   * @param int the index of the sub gallery to count (optional)
   * @return string the contents of the specified gallery
   */
  function galleryIdEncoded($index = null)
  {
    if($index === null)
      return $this->gallery->idEncoded;
    else
      return $this->encodeId($this->gallery->galleries[$index]->id);
  }
  
	/**
   * If the specified gallery is an album then it returns the number of 
   * images contained otherwise the number of sub-galleries is returned
   * @param int the index of the sub gallery to count (optional)
   * @return string the contents of the specified gallery
   */
  function galleryContents($index = null)
  {
    if($this->isAlbum($index))
      return $this->imageCountText($index);
    else
      return $this->galleryCountText($index);
  }
  
	/**
   * @param int the index of the sub gallery to count (optional)
   * @return int the number of galleries in the specified gallery
   * or of the current gallery if $index is not specified
   */
  function galleryCount($index = null)
  {
    if($index === null)
      return count($this->gallery->galleries);
    else
      return count($this->gallery->galleries[$index]->galleries);
  }
  
  /**
   * @return string the number of galleries in the specified gallery
   */
  function galleryCountText($index = null)
  {
    return $this->i18n->_ng("%s gallery", "%s galleries", $this->galleryCount($index));
  }
  
  /**
   * @param int the index of the sub gallery to count (optional)
   * @return boolean true if the specified gallery (or the current gallery 
	 * if $index is not specified) has sub-galleries; false otherwise
   */
	function galleryHasSubGalleries($index = null)
	{
    return $this->galleryCount($index)>0;
	}
	
  /**
   * @return int the number of images in the specified gallery
   */
  function imageCount($index = null)
  {
    if($index === null)
      return count($this->gallery->images);
    else
      return count($this->gallery->galleries[$index]->images);
  }
  
  /**
   * @return string the number of images in the specified gallery
   */
  function imageCountText($index = null)
  {
    return $this->i18n->_ng("%s image", "%s images", $this->imageCount($index));
  }
  
  /**
   * @param int the index of the sub gallery to check (optional)
   * @return boolean true if the specified gallery (or the current gallery 
	 * if $index is not specified) contains one or more images; false otherwise
   */
	function galleryHasImages($index = null)
	{
	  return count($this->imageCount($index))>0;
	}
	
  /**
   * @uses galleryThumbnailImage
   * @return string
   */
  function galleryThumbnailLinked($index = null)
  {
    $ret  = "<a href=\"".$this->galleryURL($index)."\">";
    $ret .= $this->galleryThumbnailImage($index);
    $ret .= "</a>";
    return $ret;
  }
  
  function galleryURL($index = null)
  {
    if($index === null) 
      return $this->formatURL($this->gallery->idEncoded);
    else 
      return $this->formatURL($this->encodeId($this->gallery->galleries[$index]->id));
  }
  
  /**
   * @return string
   */
  function galleryThumbnailImage($index = null)
  {
    if($index === null) $gal = $this->gallery;
    else $gal = $this->gallery->galleries[$index];
    
    $image = $gal->filename;
    
    switch($gal->filename) {
      case "__none__" :
        $ret = nl2br($this->i18n->_g("No\nthumbnail"));
        break;
      case "__random__" :
        if(count($gal->images) == 0) {
          $ret = nl2br($this->i18n->_g("No\nthumbnail"));
          break;
        }
        //select random image then run on to next case
        srand(time());
        $image = $gal->images[rand(0,count($gal->images)-1)]->filename;
    default :
        $ret  = "<img src=\"".$this->thumbnailURL(rawurlencode($gal->id), $image,
                                          $this->config->thumb_width_gallery,
                                          $this->config->thumb_height_gallery,
                                          $this->config->thumb_force_size_gallery);
        $ret .= '" class="sgGalleryThumb" '; 
        $ret .= 'alt="'.$this->i18n->_g("Sample image from gallery").'" />';

    }
    return $ret;
  }
  
  /**
   * @return string
   */
  function galleryTab()
  {
    $showing = $this->galleryTabShowing();
    $links = $this->galleryTabLinks();
    if(empty($links)) return $showing;
    else return $showing." | ".$links;
  }
  
  /**
   * @return string
   */
  function galleryTabShowing()
  {
    if($this->isAlbum()) {
      $total = $this->imageCount();
      $perPage = $this->config->thumb_number_album;
    } else {
      $total = $this->galleryCount();
      $perPage = $this->config->thumb_number_gallery;
    }
    
    if($this->startat+$perPage > $total)
      $last = $total;
    else
      $last = $this->startat+$perPage;
    
    return $this->i18n->_g("Showing %s-%s of %s",($this->startat+1),$last,$total);
  }
  
  /**
   * @return string
   */
  function galleryTabLinks()
  {
    $ret = "";
    if($this->galleryHasPrev()) 
      $ret .= $this->galleryPrevLink()." ";
    if($this->gallery->id != ".") 
      $ret .= "<a href=\"".$this->formatURL($this->gallery->parent)."\" title=\"".$this->i18n->_g("gallery|Up one level")."\">".$this->i18n->_g("gallery|Up")."</a>";
    if($this->galleryHasNext()) 
      $ret .= " ".$this->galleryNextLink();
        
    return $ret;
  }
  
  function navigationLinks() {
    $ret = "<link rel=\"Top\" title=\"".$this->config->gallery_name."\" href=\"".$this->formatURL(".")."\" />\n";
    
    if($this->isImage()) {
      $ret .= "<link rel=\"Up\" title=\"".$this->galleryName()."\" href=\"".$this->imageParentURL()."\" />\n";
      if ($this->imageHasPrev()) {
        $ret .= "<link rel=\"First\" title=\"".$this->imageName(0)."\" href=\"".$this->imageFirstURL()."\" />\n";
        $ret .= "<link rel=\"Prev\" title=\"".$this->imageName($this->image->index-1)."\" href=\"".$this->imagePrevURL()."\" />\n";
      }
      if ($this->imageHasNext()) {
        $ret .= "<link rel=\"Next\" title=\"".$this->imageName($this->image->index+1)."\" href=\"".$this->imageNextURL()."\" />\n";
        $ret .= "<link rel=\"Last\" title=\"".$this->imageName($this->imageCount()-1)."\" href=\"".$this->imageLastURL()."\" />\n";
      }
    } else {
      if($this->gallery->id != ".")
        $ret .= "<link rel=\"Up\" title=\"".$this->gallery->parentName."\" href=\"".$this->formatURL($this->gallery->parent)."\" />\n";
      if($this->galleryHasPrev()) {
        $ret .= "<link rel=\"Prev\" title=\"".$this->i18n->_g("gallery|Previous")."\" href=\"".$this->galleryPrevURL()."\" />\n";
        $ret .= "<link rel=\"First\" title=\"".$this->i18n->_g("gallery|First")."\" href=\"".$this->formatURL($this->gallery->idEncoded, null, 0)."\" />\n";
      }
      if($this->galleryHasNext()) {
        $ret .= "<link rel=\"Next\" title=\"".$this->i18n->_g("gallery|Next")."\" href=\"".$this->galleryNextURL()."\" />\n";
        $ret .= "<link rel=\"Last\" title=\"".$this->i18n->_g("gallery|Last")."\" href=\"".$this->formatURL($this->gallery->idEncoded, null, $this->lastPageIndex())."\" />\n";
      } 
    }
    return $ret;
  }
  
  
  /** 
   * @return int the number of 'pages' or 'screen-fulls'
   */
  function galleryPageCount() {
    if($this->isAlbum())
      return intval($this->imageCount()/$this->config->thumb_number_album)+1;
    else
      return intval($this->galleryCount()/$this->config->thumb_number_gallery)+1;
  }
  
  /** 
   * @return int
   */
  function lastPageIndex() {
    if($this->isAlbum())
      return ($this->galleryPageCount()-1)*
        ($this->isAlbum()?$this->config->thumb_number_album:$this->config->thumb_number_gallery);
  }
  
  /**
   * @return bool true if there is at least one more page
   */
  function galleryHasNext() {
    if($this->isAlbum())
      return count($this->gallery->images)>$this->startat+$this->config->thumb_number_album;
    else
      return count($this->gallery->galleries)>$this->startat+$this->config->thumb_number_gallery;
  }
  
  /**
   * @return bool true if there is at least one previous page
   */
  function galleryHasPrev() {
    return $this->startat>0;
  }
  
  /**
   * @return string the URL of the next page
   */
  function galleryNextURL() {
    return $this->formatURL($this->gallery->idEncoded, null, ($this->startat+
      ($this->isAlbum()?$this->config->thumb_number_album:$this->config->thumb_number_gallery)));
  }
  
  function galleryNextLink() {
    return "<a href=\"".$this->galleryNextURL()."\">".$this->i18n->_g("gallery|Next")."</a>";
  }
  
  /**
   * @return string the URL of the previous page
   */
  function galleryPrevURL() {
    return $this->formatURL($this->gallery->idEncoded, null, ($this->startat-
      ($this->isAlbum()?$this->config->thumb_number_album:$this->config->thumb_number_gallery)));
  }
  
  function galleryPrevLink() {
    return "<a href=\"".$this->galleryPrevURL()."\">".$this->i18n->_g("gallery|Previous")."</a>";
  }
  
  /**
   * @return string
   */
  function galleryByArtist()
  {
    if(!empty($this->gallery->artist)) return " ".$this->i18n->_g("artist name|by %s",$this->gallery->artist);
    else return "";
  }
  
  /**
   * @return string the name of the gallery
   */
  function galleryName($index = null)
  {
    if($index===null)
      return $this->gallery->name;
    else
      return $this->gallery->galleries[$index]->name;
  }
  
  /**
   * @return string the name of the gallery's artist
   */
  function galleryArtist($index = null)
  {
    if($index===null)
      return $this->gallery->artist;
    else
      return $this->gallery->galleries[$index]->artist;
  }
  
  /**
   * @return string the summary field of the gallery
   */
  function gallerySummary($index = null)
  {
    if($index===null)
      return $this->gallery->summary;
    else
      return $this->gallery->galleries[$index]->summary;
  }
  
  /**
   * Removes script-generated HTML (BRs and URLs) but leaves any other HTML
   * @return string the summary of the gallery
   */
  function gallerySummaryStripped($index = null)
  {
    return str_replace("<br />","\n",$this->gallerySummary($index));
  }
  
  /**
   * @return string the description of the gallery
   */
  function galleryDescription($index = null)
  {
    if($index===null)
      return $this->gallery->desc;
    else
      return $this->gallery->galleries[$index]->desc;
  }
  
  /**
   * Removes script-generated HTML (BRs and URLs) but leaves any other HTML
   * @return string the description of the gallery
   */
  function galleryDescriptionStripped($index = null)
  {
    $ret = $this->galleryDescription($index);
    
    $ret = str_replace("<br />","\n",$ret);
    
    if($this->config->enable_clickable_urls) {
      //strip off html from autodetected URLs
      $ret = preg_replace('{<a href="('.SG_REGEXP_PROTOCOLURL.')\">\1</a>}', '\1', $ret);
      $ret = preg_replace('{<a href="http://('.SG_REGEXP_WWWURL.')">\1</a>}', '\1', $ret);
      $ret = preg_replace('{<a href="mailto:('.SG_REGEXP_EMAILURL.')">\1</a>}', '\1', $ret);
    }
    
    return $ret;
  }
  
  /**
   * @return string the gallery's hits
   */
  function galleryViews($index = null)
  {
    if($index===null)
      return $this->gallery->hits->hits;
    else
      return $this->gallery->galleries[$index]->hits->hits;
  }
  
  /**
   * @return array array of details
   */
  function galleryDetailsArray()
  {
    $ret = array();
    if(!empty($this->gallery->email))
        $ret[$this->i18n->_g("Email")] = $this->formatEmail($this->gallery->email);
    if(!empty($this->gallery->date))
      $ret[$this->i18n->_g("Date")] = $this->gallery->date;
    if(!empty($this->gallery->desc))
      $ret[$this->i18n->_g("Description")] = $this->gallery->desc;
    if(!empty($this->gallery->copyright))
      $ret[$this->i18n->_g("Copyright")] = $this->gallery->copyright;
    elseif(!empty($this->gallery->artist))
      $ret[$this->i18n->_g("Copyright")] = $this->gallery->artist;
    if($this->config->show_views && !empty($this->gallery->hits))
      $ret[$this->i18n->_g("Viewed")] = $this->i18n->_ng("viewed|%s time", "viewed|%s times",$this->gallery->hits);
    
    return $ret;
  }
  
  /**
   * @return array array of sgImage objects
   */
  function galleryImagesArray()
  {
    return $this->gallery->images;
  }
  
  /**
   * @return array array of {@link sgImage} objects
   */
  function gallerySelectedImagesArray()
  {
    return array_slice($this->gallery->images, $this->startat, $this->config->thumb_number_album);
  }
  
  /**
   * @return int number of images in current view
   */
  function gallerySelectedImagesCount()
  {
    return min(count($this->gallery->images) - $this->startat, $this->config->thumb_number_album);
  }
  
  /**
   * @return array array of {@link sgGallery} objects
   */
  function galleryGalleriesArray()
  {
    return $this->gallery->galleries;
  }
  
  /**
   * @return array array of {@link sgGallery} objects
   */
  function gallerySelectedGalleriesArray()
  {
    return array_slice($this->gallery->galleries, $this->startat, $this->config->thumb_number_gallery);
  }
  
  /**
   * @return int number of galleries in current view
   */
  function gallerySelectedGalleriesCount()
  {
    return min(count($this->gallery->galleries) - $this->startat, $this->config->thumb_number_gallery);
  }
  
  /**
   * Image thumbnail that links to the appropriate image page
   * @return string
   */
  function imageThumbnailLinked($index = null)
  {
    if($index === null) $img = $this->image;
    else $img = $this->gallery->images[$index];
    
    $ret  = "<a href=\"".$this->formatURL($this->gallery->idEncoded, $img->filename)."\">";
    $ret .= $this->imageThumbnailImage($index);
    $ret .= "</a>";
    return $ret;
  }
  
  /**
   * Image thumbnail that pops up a new window containing the image
   * @return string
   * @depreciated
   */
  function imageThumbnailPopup($index = null)
  {
    $ret =  '<a href="'.$this->imageURL($index).'" onclick="';
    $ret .= "window.open('".$this->imageURL($index)."','','toolbar=0,resizable=1,";
    $ret .= "width=".($this->imageWidth($index)+20).",";
    $ret .= "height=".($this->imageheight($index)+20)."');";
    $ret .= "return false;\">".$this->imageThumbnailImage($index)."</a>";

    return $ret;
  }
  
  /**
   * Creates a correctly formatted &lt;img&gt; tag to display the album 
   * thumbnail of the specified image
   * @param int index of image (optional)
   * @return string html
   */
  function imageThumbnailImage($index = null)
  {
    if($index === null) $img = $this->image;
    else $img = $this->gallery->images[$index];
    
    $ret  = '<img src="'.$this->thumbnailURL(
                           $this->gallery->idEncoded, $img->filename,
                           $this->config->thumb_width_album,
                           $this->config->thumb_height_album,
                           $this->config->thumb_force_size_album).'" ';
    $ret .= 'width="'.$this->thumbnailWidth(
                           $this->imageRealWidth($index), $this->imageRealHeight($index),
                           $this->config->thumb_width_album,
                           $this->config->thumb_height_album,
                           $this->config->thumb_force_size_album).'" ';
    $ret .= 'height="'.$this->thumbnailHeight(
                           $this->imageRealWidth($index), $this->imageRealHeight($index),
                           $this->config->thumb_width_album,
                           $this->config->thumb_height_album,
                           $this->config->thumb_force_size_album).'" ';
    $ret .= 'alt="'.$this->imageName($index).$this->imageByArtist($index).'" ';
    $ret .= 'title="'.$this->imageName($index).$this->imageByArtist($index).'" />';
    
    return $ret;
  }
  
  /**
   * Calculates thumbnail width given:
   * @param int original image width
   * @param int original image height
   * @param int required image width
   * @param int required image height
   * @param bool force size of thumbnail
   * @return int width of thumbnail in pixels
   */
  function thumbnailWidth($imageWidth, $imageHeight, $maxWidth, $maxHeight, $forceSize)
  {
    //if aspect ratio is to be constrained set crop size
    if($forceSize) {
      $newAspect = $maxWidth/$maxHeight;
      $oldAspect = $imageWidth/$imageHeight;
      if($newAspect > $oldAspect) {
        $cropWidth = $imageWidth;
        $cropHeight = round($imageHeight*($oldAspect/$newAspect));
      } else {
        $cropWidth = round($imageWidth*($newAspect/$oldAspect));
        $cropHeight = $imageHeight;
      }
    //else crop size is image size
    } else {
      $cropWidth = $imageWidth;
      $cropHeight = $imageHeight;
    }
    
    if($cropHeight > $maxHeight && ($cropWidth <= $maxWidth || ($cropWidth > $maxWidth && round($cropHeight/$cropWidth * $maxWidth) > $maxHeight)))
      return round($cropWidth/$cropHeight * $maxHeight);
    elseif($cropWidth > $maxWidth)
      return $maxWidth;
    else
      return $imageWidth;
  }
  
  /**
   * Calculates thumbnail height given:
   * @param int original image width
   * @param int original image height
   * @param int required image width
   * @param int required image height
   * @param bool force size of thumbnail
   * @return int height of thumbnail in pixels
   */
  function thumbnailHeight($imageWidth, $imageHeight, $maxWidth, $maxHeight, $forceSize)
  {
    //if aspect ratio is to be constrained set crop size
    if($forceSize) {
      $newAspect = $maxWidth/$maxHeight;
      $oldAspect = $imageWidth/$imageHeight;
      if($newAspect > $oldAspect) {
        $cropWidth = $imageWidth;
        $cropHeight = round($oldAspect/$newAspect * $imageHeight);
      } else {
        $cropWidth = round($newAspect/$oldAspect * $imageWidth);
        $cropHeight = $imageHeight;
      }
    //else crop size is image size
    } else {
      $cropWidth = $imageWidth;
      $cropHeight = $imageHeight;
    }
    
    if($cropWidth > $maxWidth && ($cropHeight <= $maxHeight || ($cropHeight > $maxHeight && round($cropWidth/$cropHeight * $maxHeight) > $maxWidth)))
      return round($cropHeight/$cropWidth * $maxWidth);
    elseif($cropHeight > $maxHeight)
      return $maxHeight;
    else
      return $imageHeight;
  }
  
  /**
   * Calculates image width by supplying appropriate values to {@link thumbnailWidth()} 
   * @param int index of image (optional)
   * @return int width of image in pixels 
   */
  function imageWidth($index = null)
  {
    if($this->config->full_image_resize)
      return $this->thumbnailWidth(
               $this->imageRealWidth($index), $this->imageRealHeight($index), 
               $this->config->thumb_width_image, $this->config->thumb_height_image, 
               $this->config->thumb_force_size_image);
    else
      return $this->imageRealWidth($index);
  }
  
  /**
   * Calculates image height by supplying appropriate values to {@link thumbnailHeight()} 
   * @param int index of image (optional)
   * @return int height of image in pixels
   */
  function imageHeight($index = null)
  {
    if($this->config->full_image_resize)
      return $this->thumbnailHeight(
               $this->imageRealWidth($index), $this->imageRealHeight($index), 
               $this->config->thumb_width_image, $this->config->thumb_height_image, 
               $this->config->thumb_force_size_image);
    else
      return $this->imageRealHeight($index);
  }
  
  /**
   * Returns the size of the original image 
   * @param int index of image (optional)
   * @return int width of image in pixels
   */
  function imageRealWidth($index = null)
  {
    if($index === null)
      return $this->image->width;
    else
      return $this->gallery->images[$index]->width;
  }
  
  /**
   * Returns the size of the original image 
   * @param int index of image (optional)
   * @return int height of image in pixels
   */
  function imageRealHeight($index = null)
  {
    if($index === null)
      return $this->image->height;
    else
      return $this->gallery->images[$index]->height;
  }
  
  
  
  ///////////////////////////
  //////image functions//////
  ///////////////////////////
  
  /**
   * @return string
   */
  function imageViews($index = null)
  {
    //todo: redo hit logging system to allow this
  }
  
  /**
   * @return string link for adding a comment to image
   */
  function imageCommentLink()
  {
    return "<a href=\"".$this->formatURL($this->gallery->idEncoded, $this->image->filename, null, "addcomment")."\">".$this->i18n->_g("Add a comment")."</a>";
  }
  
  /**
   * @return string the name of the image
   */
  function imageName($index = null)
  {
    if($index===null)
      return $this->image->name;
    else
      return $this->gallery->images[$index]->name;
  }
  
  /**
   * @return string the name of the image's artist
   */
  function imageArtist($index = null)
  {
    if($index===null)
      return $this->image->artist;
    else
      return $this->gallery->images[$index]->artist;
  }
  
  /**
   * If there is an artist defined for this image it returns " by" followed
   * by the artist name; otherwise it returns and empty string
   * @return string
   */
  function imageByArtist($index = null)
  {
    if($this->imageArtist($index)!="") return " ".$this->i18n->_g("artist name|by %s",$this->imageArtist($index));
    else return "";
  }
  
  /**
   * @return string
   */
  function imageDescription($index = null)
  {
    if($index===null)
      return $this->image->desc;
    else
      return $this->gallery->images[$index]->desc;
  }
  
  /**
   * @return string the html to display the current image
   */
  function image()
  {
    $ret = '<img src="'.$this->imageURL().'" class="sgImage" ';
    if($this->imageWidth() && $this->imageHeight())
      $ret .= 'width="'.$this->imageWidth().'" height="'.$this->imageHeight().'" ';
    if($this->config->imagemap_navigation)
      $ret .= 'usemap="#sgNavMap" border="0" ';
    $ret .= 'alt="'.$this->imageName().$this->imageByArtist().'" />';
    return $ret;
  }
  
  /**
   * @return string the url of the current image
   */
  function imageURL($index = null)
  {
    if($this->config->full_image_resize)
      return $this->thumbnailURL($this->gallery->idEncoded, 
                                 ($index===null) ? $this->image->filename : $this->gallery->images[$index]->filename,
                                 $this->config->thumb_width_image,
                                 $this->config->thumb_height_image,
                                 $this->config->thumb_force_size_image);
    else 
      return $this->imageRealURL($index);
  }
  
  
  /**
   * @return string the url of the current image unresized
   */
  function imageRealURL($index = null)
  {
    $image = ($index===null) ? $this->image->filename : $this->gallery->images[$index]->filename;
    
    //check if image is local (filename does not start with 'http://')
    if(substr($image,0,7)!="http://") 
      return $this->config->base_url.$this->config->pathto_galleries.
        $this->gallery->idEncoded."/".rawurlencode($image);
    else 
      return $image;
  }
  
  
  /**
   * @param  string  this text/html will be inserted before each linked thumbnail (optional)
   * @param  string  this text/html will be inserted after each linked thumbnail (optional)
   * @return string  the html to display the preview thumbnails
   */
  function imagePreviewThumbnails($before = '', $after = '')
  {
    $ret = "";
    for($i = $this->image->index - $this->config->thumb_number_preview; $i <= $this->image->index + $this->config->thumb_number_preview; $i++) {
      if(!isset($this->gallery->images[$i])) 
        continue;
      
      $ret .= $before;
      $ret .= '<a href="'.$this->formatURL($this->gallery->idEncoded, $this->gallery->images[$i]->filename).'">';
      $ret .= '<img src="'.$this->thumbnailURL(
                             $this->gallery->idEncoded, $this->gallery->images[$i]->filename,
                             $this->config->thumb_width_preview,
                             $this->config->thumb_height_preview,
                             $this->config->thumb_force_size_preview).'" ';
      $ret .= 'width="'.$this->thumbnailWidth(
                             $this->imageRealWidth($i), $this->imageRealHeight($i),
                             $this->config->thumb_width_preview,
                             $this->config->thumb_height_preview,
                             $this->config->thumb_force_size_preview).'" ';
      $ret .= 'height="'.$this->thumbnailHeight(
                             $this->imageRealWidth($i), $this->imageRealHeight($i),
                             $this->config->thumb_width_preview,
                             $this->config->thumb_height_preview,
                             $this->config->thumb_force_size_preview).'" ';
      $ret .= 'alt="'.$this->imageName($i).$this->imageByArtist($i).'" ';
      $ret .= 'title="'.$this->imageName($i).$this->imageByArtist($i).'" ';
      if($i==$this->image->index) $ret .= 'class="sgPreviewThumbCurrent" ';
      else $ret .= 'class="sgPreviewThumb" ';
      $ret .= "/></a>";
      $ret .= $after;
      $ret .= "\n";
    }
    
    return $ret;
  }
  
  /**
   * @return string html link to the previous image if one exists
   */
  function imagePrevLink()
  {
    if($this->imageHasPrev())
      return "<a href=\"".$this->imagePrevURL()."\" title=\"".$this->imageName($this->image->index-1)."\">".$this->i18n->_g("image|Previous")."</a> | ";
  }

  /**
   * @return string html link to the first image if not already first
   */
  function imageFirstLink()
  {
    if($this->imageHasPrev())
      return "<a href=\"".$this->imageFirstURL()."\" title=\"".$this->imageName(0)."\">".$this->i18n->_g("image|First")."</a> | ";
  }

  /**
   * @return string
   */
  function imageParentLink()
  {
    return "<a href=\"".$this->imageParentURL()."\" title=\"".$this->galleryName()."\">".$this->i18n->_g("image|Thumbnails")."</a>";
  }
  
  /**
   * @return string html link to the next image if one exists
   */
  function imageNextLink()
  {
    if($this->imageHasNext())
      return " | <a href=\"".$this->imageNextURL()."\" title=\"".$this->imageName($this->image->index+1)."\">".$this->i18n->_g("image|Next")."</a>";
  }
  
  /**
   * @return string html link to the last image if not already last
   */
  function imageLastLink()
  {
    if($this->imageHasNext())
      return " | <a href=\"".$this->imageLastURL()."\" title=\"".$this->imageName($this->imageCount()-1)."\">".$this->i18n->_g("image|Last")."</a>";
  }
  
  function imageFirstURL()
  {
    return $this->formatURL($this->gallery->idEncoded, $this->gallery->images[0]->filename);
  }
  
  function imagePrevURL()
  {
    return $this->formatURL($this->gallery->idEncoded, $this->gallery->images[$this->image->index-1]->filename);
  }
  
  function imageParentURL()
  {
    return $this->formatURL($this->gallery->idEncoded, null, (floor($this->image->index/$this->config->thumb_number_album)*$this->config->thumb_number_album));
  }
  
  function imageNextURL()
  {
    return $this->formatURL($this->gallery->idEncoded, $this->gallery->images[$this->image->index+1]->filename);
  }
  
  function imageLastURL()
  {
    return $this->formatURL($this->gallery->idEncoded, $this->gallery->images[$this->imageCount()-1]->filename);
  }
  
  /**
   * @return boolean
   */
  function imageHasPrev()
  {
    return isset($this->gallery->images[$this->image->index-1]);
  }
  
  /**
   * @return boolean
   */
  function imageHasNext()
  {
    return isset($this->gallery->images[$this->image->index+1]);
  }
  
  
  /**
   * @return array
   */
  function imageDetailsArray()
  {
    $ret = array();
    if(!empty($this->image->email))
      $ret[$this->i18n->_g("Email")] = $this->formatEmail($this->image->email);
    if(!empty($this->image->location))
      $ret[$this->i18n->_g("Location")] = $this->image->location;
    if(!empty($this->image->date))
      $ret[$this->i18n->_g("Date")] = $this->image->date;
    if(!empty($this->image->desc))
      $ret[$this->i18n->_g("Description")] = $this->image->desc;
    if(!empty($this->image->camera))
      $ret[$this->i18n->_g("Camera")] = $this->image->camera;
    if(!empty($this->image->lens))
      $ret[$this->i18n->_g("Lens")] = $this->image->lens;
    if(!empty($this->image->film))
      $ret[$this->i18n->_g("Film")] = $this->image->film;
    if(!empty($this->image->darkroom))
      $ret[$this->i18n->_g("Darkroom manipulation")] = $this->image->darkroom;
    if(!empty($this->image->digital))
      $ret[$this->i18n->_g("Digital manipulation")] = $this->image->digital;
    if(!empty($this->image->copyright))
      $ret[$this->i18n->_g("Copyright")] = $this->image->copyright;
    elseif(!empty($this->image->artist))
      $ret[$this->i18n->_g("Copyright")] = $this->image->artist;
    if($this->config->show_views && !empty($this->image->hits))
      $ret[$this->i18n->_g("Viewed")] = $this->i18n->_ng("viewed|%s time", "viewed|%s times",$this->image->hits);
    
    return $ret;
  }

  
}


?>
