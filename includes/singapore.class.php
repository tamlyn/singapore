<?php 

/**
 * Main class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003 Tamlyn Rhodes
 * @version $Id: singapore.class.php,v 1.8 2003/11/24 20:07:04 tamlyn Exp $
 */
 
/**
 * Provides functions for handling galleries and images
 * @uses sgGallery
 * @uses sgImage
 * @uses sgConfig
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot org>
 */
class Singapore
{
  /**
   * current script version 
   * @var string
   */
  var $version = "0.9.7";
  
  /**
   * instance of a {@link sgConfig} object representing the current 
   * script configuration
   * @var sgConfig
   */
  var $config;
  
  /**
   * instance of the currently selected IO handler object
   * @var sgIO_csv|sgIO_iifn
   */
  var $io;
  
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
  
  
  var $action = null;
  
  /**
   * Constructor
   * @param string the path to the base singapore directory
   */
  function Singapore($basePath = "")
  {
    //import class definitions
    require_once $basePath."includes/gallery.class.php";
    require_once $basePath."includes/image.class.php";
    require_once $basePath."includes/config.class.php";
    require_once $basePath."includes/io_csv.class.php";
    
    //start execution timer
    $this->scriptStartTime = microtime();
    
    //remove slashes
    if(get_magic_quotes_gpc())
      $_REQUEST = array_map("stripslashes", $_REQUEST);
    
    $galleryId = !empty($_REQUEST["gallery"]) ? $_REQUEST["gallery"] : ".";
    
    //try to validate gallery id
    if(strlen($galleryId)>1 && $galleryId{1} != '/') $galleryId = './'.$galleryId;
    
    //detect back-references to avoid file-system walking
    if(strpos($galleryId,"../")!==false) $galleryId = ".";
    
    //load config from default ini file (singapore.ini)
    $this->config = new sgConfig("singapore.ini");
    
    //load config from gallery ini file (gallery.ini) if present
    $this->config->loadConfig($this->config->pathto_galleries.$galleryId."/gallery.ini");
    //load config from template ini file (template.ini) if present
    $this->config->loadConfig($this->config->pathto_current_template."template.ini");
    
    //fetch the gallery and image info
    $this->io = new sgIO_csv($this->config);
    $this->gallery = $this->io->getGallery($galleryId);
    
    //sort galleries and images
    $GLOBALS["temp"]["gallery_sort_order"] = $this->config->gallery_sort_order;
    $GLOBALS["temp"]["image_sort_order"] = $this->config->image_sort_order;
    if($this->config->gallery_sort_order!="x") usort($this->gallery->galleries, array("Singapore","gallerySort"));
    if($this->config->image_sort_order!="x") usort($this->gallery->images, array("Singapore","imageSort"));
    
    //read the language file
    $this->readLanguageFile($this->config->language);
    
    $this->startat = isset($_REQUEST["startat"]) ? $_REQUEST["startat"] : 0;
    
    //set page title
    $this->pageTitle = $this->config->gallery_name;
    
    //do the logging stuff and select the image (if any)
    if(empty($_REQUEST["image"])) {
      if($this->config->track_views) $hits = $this->logGalleryView();
      if($this->config->show_views) $this->gallery->hits = $hits;
    } else {
      $this->selectImage($_REQUEST["image"]);
      if($this->config->track_views) $hits = $this->logImageView();
      if($this->config->show_views) $this->image->hits = $hits;
    }
    
    //encode the gallery name
    $bits = explode("/",$this->gallery->id);
    for($i=0;$i<count($bits);$i++)
      $bits[$i] = rawurlencode($bits[$i]);
    $this->gallery->idEncoded = implode("/",$bits);
    
    //find the parent
    $this->gallery->parent = substr($this->gallery->idEncoded, 0, strrpos($this->gallery->idEncoded, "/"));
    
    //set character set
    if(!empty($this->languageStrings[0]["charset"]))
      $this->character_set = $this->languageStrings[0]["charset"];
    else
      $this->character_set = $this->config->default_charset;
      
    //temporary code
    if(isset($_REQUEST["action"])) $this->action = $_REQUEST["action"];
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
      case "d" : return strcmp($a->date, $b->date); //date
      case "D" : return strcmp($b->date, $a->date); //date (reverse)
      case "l" : return strcmp($a->location, $b->location); //location
      case "L" : return strcmp($b->location, $a->location); //location (reverse)
    }
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
      $this->gallery->hits->images[$i]->filename = $this->image->filename;
      $numhits = $this->gallery->hits->images[$i]->hits = 1;
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
    $this->image->name = "Image not found '$image'";
    return false;
  }
  
  
  /**
   * @return bool true if this is an image page; false otherwise
   */
  function isImage()
  {
    return !empty($this->image);
  }
  
  /**
   * @return bool true if this is an image thumbnail page; false otherwise
   */
  function isGallery($index = null)
  {
    return !$this->galleryHasSubGalleries($index) && !$this->isImage();
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
   * @uses scriptExecTime()
   * @returns string the script execution time
   */
  function scriptExecTimeText()
  {
    if($this->config->show_execution_time)
      return $this->_g("Page created in %s seconds",$this->scriptExecTime());
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
    return $this->_g("singapore|Powered by %s",$this->versionLink());
  }
  
  function allRightsReserved()
  {
    return $this->_g("All rights reserved.");
  }
  
  function copyrightMessage()
  {
    return $this->_g("Images may not be reproduced in any form without the express written permission of the copyright holder.");
  }
  
  function adminLink()
  {
    return "<a href=\"admin.php\">".$this->_g("Log in")."</a>";
  }
  
  /**
   * @returns stdClass|false a data object representing the desired gallery
   * @static
   */
  function getListing($wd, $type = "dirs")
  {
    $dir->path = $wd;
    $dir->files = array();
    $dir->dirs = array();
    $dp = opendir($dir->path);
    
    if(!$dp) return false;
    
    switch($type) {
      case "images" :
        while(false !== ($entry = readdir($dp)))
          if(!is_dir($entry) && preg_match("/\.(jpeg|jpg|jpe|png|gif|bmp|tif|tiff)$/i",$entry))
            $dir->files[] = $entry;
        sort($dir->files);
        rewinddir($dp);
        //run on and get dirs too
      case "dirs" :
        while(false !== ($entry = readdir($dp)))
          if(
            is_dir($wd.$entry) && 
            $entry{0} != '.'
          ) $dir->dirs[] = $entry;
        sort($dir->dirs);
        
        break;
      case "all" :
        while(false !== ($entry = readdir($dp)))
          if(is_dir($wd.$entry)) $dir->dirs[] = $entry;
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
    $galleries = explode("/",$this->gallery->id);
    $crumb[0]->id = ".";
    $crumb[0]->path = ".";
    $crumb[0]->name = $this->config->gallery_name;
    
    for($i=1;$i<count($galleries);$i++) {
      $crumb[$i]->id = $galleries[$i];
      $crumb[$i]->path = $crumb[$i-1]->path."/".rawurlencode($galleries[$i]);
      $crumb[$i]->name = $galleries[$i];
    }
    
    if($this->isImage()) {
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
      $ret .= "<a href=\"".$this->config->base_url."gallery=".$crumbArray[$i]->path."\">".$crumbArray[$i]->name."</a> &gt;\n";
    }
    $ret .= $crumbArray[$i]->name;
    return $ret;
  }
  
  function crumbLine()
  {
    return $this->_g("crumb line|You are here:")." ".$this->crumbLineText();
  }
  
  /////////////////////////////
  //////gallery functions//////
  /////////////////////////////
  
  
  /**
   * If the specified gallery is a gallery then it returns the number
   * of images contained otherwise the number of galleries is returned
   * @param int the index of the sub gallery to count (optional)
   * @return string the contents of the specified gallery
   */
  function galleryContents($index = null)
  {
    if($this->isGallery($index))
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
    return $this->_ng("%s gallery", "%s galleries", $this->galleryCount($index));
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
    return $this->_ng("%s image", "%s images", $this->imageCount($index));
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
   * @uses imageThumbnailImage
   * @return string
   */
  function galleryThumbnailLinked($index = null)
  {
    if($index === null) $galleryId = $this->gallery->idEncoded;
    else $galleryId = urlencode($this->gallery->galleries[$index]->id);
    
    $ret  = "<a href=\"".$this->config->base_url."gallery=".$galleryId."\">";
    $ret .= $this->galleryThumbnailImage($index);
    $ret .= "</a>";
    return $ret;
  }
  
  /**
   * @return string
   */
  function galleryThumbnailImage($index = null)
  {
    if($index === null) $gal = $this->gallery;
    else $gal = $this->gallery->galleries[$index];
    
    switch($gal->filename) {
    case "__random__" :
      if(count($gal->images)>0) {
        srand(time());
        $index = rand(0,count($gal->images)-1);
        $ret  = "<img src=\"thumb.php?gallery=".urlencode($gal->id);
        $ret .= "&amp;image=".urlencode($gal->images[$index]->filename);
        $ret .= "&amp;size=".$this->config->gallery_thumb_size."\" class=\"sgGallery\" ";
        $ret .= "alt=\"".$this->_g("Sample image from gallery")."\" />";
        break;
      }
    case "__none__" :
      $ret = nl2br($this->_g("No\nthumbnail"));
      break;
    default :
      $ret  = "<img src=\"thumb.php?gallery=".urlencode($gal->id);
      $ret .= "&amp;image=".urlencode($gal->filename);
      $ret .= "&amp;size=".$this->config->gallery_thumb_size."\" class=\"sgGallery\""; 
      $ret .= "alt=\"".$this->_g("Sample image from gallery")."\" />";
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
    if($this->isGallery()) {
      $total = $this->imageCount();
      $perPage = $this->config->main_thumb_number;
    } else {
      $total = $this->galleryCount();
      $perPage = $this->config->gallery_thumb_number;
    }
    
    if($this->startat+$perPage > $total)
      $last = $total;
    else
      $last = $this->startat+$perPage;
    
    return $this->_g("Showing %s-%s of %s",($this->startat+1),$last,$total);
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
      $ret .= "<a href=\"".$this->config->base_url."gallery=".$this->gallery->parent."\" title=\"".$this->_g("gallery|Up one level")."\">".$this->_g("gallery|Up")."</a>";
    if($this->galleryHasNext()) 
      $ret .= " ".$this->galleryNextLink();
        
    return $ret;
  }
  
  function galleryHasNext() {
    if($this->isGallery())
      return count($this->gallery->images)>$this->startat+$this->config->main_thumb_number;
    else
      return count($this->gallery->galleries)>$this->startat+$this->config->gallery_thumb_number;
  }
  
  function galleryHasPrev() {
    return $this->startat>0;
  }
  
  function galleryNextURL() {
    return $this->config->base_url."gallery=".$this->gallery->idEncoded."&amp;startat=".($this->startat+
      ($this->isGallery()?$this->config->main_thumb_number:$this->config->gallery_thumb_number));
  }
  
  function galleryNextLink() {
    return "<a href=\"".$this->galleryNextURL()."\">".$this->_g("gallery|Next")."</a>";
  }
  
  function galleryPrevURL() {
    return $this->config->base_url."gallery=".$this->gallery->idEncoded."&amp;startat=".($this->startat-
      ($this->isGallery()?$this->config->main_thumb_number:$this->config->gallery_thumb_number));
  }
  
  function galleryPrevLink() {
    return "<a href=\"".$this->galleryPrevURL()."\">".$this->_g("gallery|Previous")."</a>";
  }
  
  /**
   * @return string
   */
  function galleryByArtist()
  {
    if(!empty($this->gallery->artist)) return " ".$this->_g("artist name|by %s",$this->gallery->artist);
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
      if($this->config->obfuscate_email)
        $ret[$this->_g("Email")] = strtr($this->gallery->email,array("@" => " <b>at</b> ", "." => " <b>dot</b> "));
      else
        $ret[$this->_g("Email")] = "<a href=\"mailto:".$this->gallery->email."\">".$this->gallery->email."</a>";
    if(!empty($this->gallery->desc))
      $ret[$this->_g("Description")] = $this->gallery->desc;
    if(!empty($this->gallery->copyright))
      $ret[$this->_g("Copyright")] = $this->gallery->copyright;
    elseif(!empty($this->gallery->artist))
      $ret[$this->_g("Copyright")] = $this->gallery->artist;
    if(!empty($this->gallery->hits))
      $ret[$this->_g("Viewed")] = $this->_ng("viewed|%s time", "viewed|%s times",$this->gallery->hits);
    
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
    return array_slice($this->gallery->images, $this->startat, $this->config->main_thumb_number);
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
    return array_slice($this->gallery->galleries, $this->startat, $this->config->gallery_thumb_number);
  }
  
  /**
   * @uses imageThumbnailImage
   * @return string
   */
  function imageThumbnailLinked()
  {
    $ret  = "<a href=\"".$this->config->base_url."gallery=".$this->gallery->idEncoded."&amp;image=".urlencode($this->image->filename)."\">";
    $ret .= $this->imageThumbnailImage();
    $ret .= "</a>";
    return $ret;
  }
  
  /**
   * @return string
   */
  function imageThumbnailImage()
  {
    list($thumbWidth, $thumbHeight) = $this->thumbnailSize($this->imageWidth(), $this->imageHeight(), $this->config->main_thumb_size);
    $ret  = "<img src=\"thumb.php?gallery=".$this->gallery->idEncoded."&amp;image=";
    $ret .= urlencode($this->image->filename)."&amp;size=".$this->config->main_thumb_size."\" ";
    $ret .= "class=\"sgThumbnail\" width=\"".$thumbWidth."\" height=\"".$thumbHeight."\" ";
    $ret .= "alt=\"".$this->image->name.$this->imageByArtist()."\" />";
    return $ret;
  }
  
  function thumbnailSize($imageWidth, $imageHeight, $maxsize)
  {
    if($imageWidth < $imageHeight && ($imageWidth>$maxsize || $imageHeight>$maxsize)) {
      $thumbWidth = floor($imageWidth/$imageHeight * $maxsize);
      $thumbHeight = $maxsize;
    } elseif($imageWidth>$maxsize || $imageHeight>$maxsize) {
      $thumbWidth = $maxsize;
      $thumbHeight = floor($imageHeight/$imageWidth * $maxsize);
    } elseif($imageWidth == 0 || $imageHeight == 0) {
      $thumbWidth = $maxsize;
      $thumbHeight = $maxsize;
    } else {
      $thumbWidth = $imageWidth;
      $thumbHeight = $imageHeight;
    }
    return array($thumbWidth, $thumbHeight);
  }
  
  function imageWidth()
  {
    if(!$this->config->max_image_size || ($this->config->max_image_size > $this->image->width && $this->config->max_image_size > $this->image->height)) 
      return $this->image->width;
    else
      if($this->image->width < $this->image->height)
        return floor($this->image->width/$this->image->height * $this->config->max_image_size);
      else
        return $this->config->max_image_size;
  }
  
  function imageHeight()
  {
    if(!$this->config->max_image_size || ($this->config->max_image_size > $this->image->width && $this->config->max_image_size > $this->image->height)) 
      return $this->image->height;
    else
      if($this->image->width > $this->image->height)
        return floor($this->image->height/$this->image->width * $this->config->max_image_size);
      else
        return $this->config->max_image_size;
  }
  
  
  
  ///////////////////////////
  //////image functions//////
  ///////////////////////////
  
  /**
   * @return string link for adding a comment to image
   */
  function imageCommentLink()
  {
    return "<a href=\"".$this->config->base_url."action=addcomment&amp;gallery=".$this->gallery->idEncoded."&amp;image=".rawurlencode($this->image->filename)."\">".$this->_g("Add a comment")."</a>";
  }
  
  /**
   * @return string the name of the image
   */
  function imageName()
  {
    return $this->image->name;
  }
  
  /**
   * @return string the name of the image's artist
   */
  function imageArtist()
  {
    return $this->image->artist;
  }
  
  /**
   * If there is an artist defined for this image it returns " by" followed
   * by the artist name; otherwise it returns and empty string
   * @return string
   */
  function imageByArtist()
  {
    if(!empty($this->image->artist)) return " ".$this->_g("artist name|by %s",$this->image->artist);
    else return "";
  }
  
  /**
   * @uses imageURL
   * @return string
   */
  function image()
  {
    $ret = "<img src=\"".$this->imageURL()."\" ";
    if($this->imageWidth() && $this->imageHeight())
      $ret .= "width=\"".$this->imageWidth()."\" height=\"".$this->imageHeight()."\" ";
    $ret .= "alt=\"".$this->imageName().$this->imageByArtist()."\" />\n";
    return $ret;
  }
  
  /**
   * @return string
   */
  function imageURL()
  {
    if($this->config->max_image_size)
      return "thumb.php?gallery=".$this->gallery->idEncoded."&amp;image=".rawurlencode($this->image->filename)."&amp;size=".$this->config->max_image_size;
    
    //check if image is local (filename does not start with 'http://')
    if(substr($this->image->filename,0,7)!="http://") 
      return $this->config->pathto_galleries.$this->gallery->idEncoded."/".rawurlencode($this->image->filename);
    else 
      return $this->image->filename;
  }
  
  /**
   * @return string
   */
  function imagePreviewThumbnails()
  {
    $ret = "";
    for($i=$this->config->preview_thumb_number;$i>0;$i--) {
      if(isset($this->gallery->images[$this->image->index-$i])) 
        $temp = $this->gallery->images[$this->image->index-$i];
      else
        continue;
      
      list($thumbWidth, $thumbHeight) = $this->thumbnailSize($temp->width, $temp->height, $this->config->preview_thumb_size);
      $ret .= "<a href=\"".$this->config->base_url."gallery=".$this->gallery->idEncoded."&amp;image=".urlencode($temp->filename)."\">";
      $ret .= "<img src=\"thumb.php?gallery=".$this->gallery->idEncoded."&amp;image=".urlencode($temp->filename)."&amp;";
      $ret .= "size=".$this->config->preview_thumb_size."\" width=\"".$thumbWidth."\" height=\"".$thumbHeight."\" alt=\"$temp->name\" />";
      $ret .= "</a>\n";
    }
    
    list($thumbWidth, $thumbHeight) = $this->thumbnailSize($this->image->width, $this->image->height, $this->config->preview_thumb_size);
    $ret .= "<img src=\"thumb.php?gallery=".$this->gallery->idEncoded."&amp;image=".urlencode($this->image->filename)."&amp;";
    $ret .= "size=".$this->config->preview_thumb_size."\" width=\"".$thumbWidth."\" height=\"".$thumbHeight."\" alt=\"".$this->imageName()."\" />\n";
    
    for($i=1;$i<=$this->config->preview_thumb_number;$i++) {
      if(isset($this->gallery->images[$this->image->index+$i])) 
        $temp = $this->gallery->images[$this->image->index+$i];
      else
        continue;
      list($thumbWidth, $thumbHeight) = $this->thumbnailSize($temp->width, $temp->height, $this->config->preview_thumb_size);
      $ret .= "<a href=\"".$this->config->base_url."gallery=".$this->gallery->idEncoded."&amp;image=".urlencode($temp->filename)."\">";
      $ret .= "<img src=\"thumb.php?gallery=".$this->gallery->idEncoded."&amp;image=".urlencode($temp->filename)."&amp;";
      $ret .= "size=".$this->config->preview_thumb_size."\" width=\"".$thumbWidth."\" height=\"".$thumbHeight."\" alt=\"$temp->name\" />";
      $ret .= "</a>\n";
    }
    return $ret;
  }
  
  /**
   * @uses imageHasPrev
   * @return string
   */
  function imagePrevLink()
  {
    if($this->imageHasPrev())
      return "<a href=\"".$this->config->base_url."gallery=".$this->gallery->idEncoded."&amp;image=".
             urlencode($this->gallery->images[$this->image->index-1]->filename)."\">".$this->_g("image|Previous")."</a> | \n";
  }

  /**
   * @return boolean
   */
  function imageHasPrev()
  {
    return isset($this->gallery->images[$this->image->index-1]);
  }
  
  /**
   * @return string
   */
  function imageParentLink()
  {
    return "<a href=\"".$this->config->base_url."gallery=".$this->gallery->idEncoded."&amp;startat=".
           (floor($this->image->index/$this->config->main_thumb_number)*$this->config->main_thumb_number)."\">".$this->_g("image|Thumbnails")."</a>\n";
  }
  
  /**
   * @uses imageHasNext
   * @return string
   */
  function imageNextLink()
  {
    if($this->imageHasNext())
      return " | <a href=\"".$this->config->base_url."gallery=".$this->gallery->idEncoded."&amp;image=".
             urlencode($this->gallery->images[$this->image->index+1]->filename)."\">".$this->_g("image|Next")."</a>\n";
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
      if($this->config->obfuscate_email)
        $ret[$this->_g("Email")] = strtr($this->image->email,array("@" => " <b>at</b> ", "." => " <b>dot</b> "));
      else
        $ret[$this->_g("Email")] = "<a href=\"mailto:".$this->image->email."\">".$this->image->email."</a>";
    if(!empty($this->image->location))
      $ret[$this->_g("Location")] = $this->image->location;
    if(!empty($this->image->date))
      $ret[$this->_g("Date")] = $this->image->date;
    if(!empty($this->image->desc))
      $ret[$this->_g("Description")] = $this->image->desc;
    if(!empty($this->image->camera))
      $ret[$this->_g("Camera")] = $this->image->camera;
    if(!empty($this->image->lens))
      $ret[$this->_g("Lens")] = $this->image->lens;
    if(!empty($this->image->film))
      $ret[$this->_g("Film")] = $this->image->film;
    if(!empty($this->image->darkroom))
      $ret[$this->_g("Darkroom manipulation")] = $this->image->darkroom;
    if(!empty($this->image->digital))
      $ret[$this->_g("Digital manipulation")] = $this->image->digital;
    if(!empty($this->image->copyright))
      $ret[$this->_g("Copyright")] = $this->image->copyright;
    elseif(!empty($this->image->artist))
      $ret[$this->_g("Copyright")] = $this->image->artist;
    if(!empty($this->image->hits))
      $ret[$this->_g("Viewed")] = $this->_ng("viewed|%s time", "viewed|%s times",$this->image->hits);
    
    return $ret;
  }

  
  //////////////////////////////
  //////language functions//////
  //////////////////////////////
  
  
  /**
   * Reads a language file and saves the strings in an array.
   * Note that the language code is used in the filename for the
   * datafile, and is case sensitive.
   *
   * @author   Joel Sjögren <joel dot sjogren at nonea dot se>
   * @param    string    $lang       language code
   * @return   bool                  sucess
   */
  function readLanguageFile ($lang)
  {
      // Set the array to empty if it doesn't exists
      if (empty($this->languageStrings)) $this->languageStrings = array();
      
      // check if locale directory exists
      if (!is_dir($this->config->pathto_locale)) 
        return false; // Directory doesn't exist, return unsuccessful
      
      // Look for the language file (case sensitive)
      $d = dir($this->config->pathto_locale);
      $languageFile = false;
      while (($file = $d->read()) !== false) {
        if ($file == "singapore.$lang.pmo")
          $languageFile = $d->path . DIRECTORY_SEPARATOR . $file;
      }
      // Couldn't find a matching file
      if (!$languageFile) return false;
      
      // Open the file
      $fp = @fopen($languageFile, "r");
      if (!@$fp) return false;
      
      // Read contents
      $str = '';
      while (!feof($fp)) $str .= fread($fp, 1024);
      // Unserialize
      $newStrings = unserialize($str);
      
      //Append new strings to current languageStrings array
      $this->languageStrings = array_merge($this->languageStrings, $newStrings);
      
      // Return successful
      return (bool) $newStrings;
  }
  
  /**
   * Returns a translated string, or the same if no language is chosen.
   * You can pass more arguments to use for replacement within the
   * string - just like sprintf(). It also removes anything before 
   * the first | in the text to translate. This is used to distinguish 
   * strings with different meanings, but with the same spelling.
   * Examples:
   * _g("Text");
   * _g("Use a %s to drink %s", _g("glass"), "water");
   *
   * @author   Joel Sjögren <joel dot sjogren at nonea dot se>
   * @param    string    $text       text to translate
   * @return   string                translated string
   */
  function _g ($text)
  {
      // String exists and is not empty?
      if (!empty($this->languageStrings[$text])) {
          $text = $this->languageStrings[$text];
      } else {
        $text = preg_replace("/^[^\|]*\|/", "", $text);
      }
      
      // More arguments were passed? sprintf() them...
      if (func_num_args() > 1) {
          $args = func_get_args();
          array_shift($args);
          //preg_match_all("/%((\d+\\\$)|.)/", str_replace("%%", "", $text), $m);
          //while (count($args) < count($m[0])) $args[] = '';
          $text = vsprintf($text, $args);
      }
      return $text;
  }
  
  /**
   * Plural form of _g().
   *
   * @param    string    $msgid1     singular form of text to translate
   * @param    string    $msgid2     plural form of text to translate
   * @param    string    $n          number
   * @return   string                translated string
   */
  function _ng ($msgid1, $msgid2, $n)
  {
      //calculate which plural to use
      if(!empty($this->languageStrings[0]["plural"]))
        eval($this->languageStrings[0]["plural"]);
      else 
        $plural = $n==1?0:1;
      
      // String exists and is not empty?
      if (!empty($this->languageStrings[$msgid1][$plural])) {
        $text = $this->languageStrings[$msgid1][$plural];
      } else {
        $text = preg_replace("/^[^\|]*\|/", "", ($n == 1 ? $msgid1 : $msgid2));
      }
      
      if (func_num_args() > 3) {
          $args = func_get_args();
          array_shift($args);
          array_shift($args);
          return vsprintf($text, $args);
      }
      
      return sprintf($text, $n);
  }
  
  /**
   * Returns a translated string, like {@link _g()}, but removes anything 
   * before the |
   * This is used to separate strings with different meanings,
   * but with the same spelling.
   *
   * @author   Joel Sjögren <joel dot sjogren at nonea dot se>
   * @param    string    $text       text to translate
   * @return   string                translated string
   */
  function __g ($text)
  {
      // Call _g()
      $args = func_get_args();
      $text = call_user_func_array(array(&$this,"_g"), $args);
      // Remove leading |
      return preg_replace("/^[^\|]+\|/", "", $text);
  }
  
}


?>
