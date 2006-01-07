<?php 

/**
 * Gallery class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003-2005 Tamlyn Rhodes
 * @version $Id: gallery.class.php,v 1.17 2006/01/07 16:24:14 tamlyn Exp $
 */

//include the base class
require_once dirname(__FILE__)."/item.class.php";
 
/**
 * Data-only class used to store gallery data.
 * 
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003-2005 Tamlyn Rhodes
 */
class sgGallery extends sgItem
{
  /**
   * Filename of the image used to represent this gallery.
   * Special values:
   * - __none__ no thumbnail is displayed
   * - __random__ a random image is chosen every time
   * @var string
   */
  var $filename = "__none__";
  
  /**
   * Short multiline summary of gallery contents
   * @var string
   */
  var $summary = "";
  
  /**
   * Array of {@link sgImage} objects
   * @var array
   */
  var $images = array();
  
  /**
   * Array of {@link sgGallery} objects
   * @var array
   */
  var $galleries = array();
  
  /**
   * Constructor
   * @param string     gallery id
   * @param sgGallery  reference to the parent gallery
   */
  function sgGallery($id, &$parent)
  {
    $this->id = $id;
    $this->parent =& $parent;
    $this->config =& sgConfig::getInstance();
    $this->translator =& Translator::getInstance();
  }
  
  /** @return bool  true if this is a non-album gallery; false otherwise  */
  function isGallery()         { return $this->hasChildGalleries(); }
  
  /** @return bool  true if this is an album; false otherwise */
  function isAlbum()           { return !$this->isGallery(); }
  
  /** @return bool  true if this is the root gallery; false otherwise */
  function isRoot()            { return $this->id == "."; }
  
  /** @return bool  true if this gallery has child galleries; false otherwise */
  function hasChildGalleries() { return $this->galleryCount() != 0; }
  
  /** @return bool  true if this gallery contains one or more images; false otherwise  */
	function hasImages()	       { return $this->imageCount() != 0; }
  
  function imageCount()        { return count($this->images); }
  function galleryCount()      { return count($this->galleries); }
  
  function imageCountText()    { return $this->translator->_ng("%s image", "%s images", $this->imageCount()); }
  function galleryCountText()  { return $this->translator->_ng("%s gallery", "%s galleries", $this->galleryCount()); }
  
  /**
   * Caches returned value for use with repeat requests
   * @return string  the rawurlencoded version of the gallery id
   */
  function idEncoded()
  {
    return isset($this->idEncoded) ? $this->idEncoded : $this->idEncoded = $this->encodeId($this->id);
  }
  
  /**
   * rawurlencode() supplied string but preserve / character for cosmetic reasons.
   * @param  string  id to encode
   * @return string  encoded id
   * @static
   */
  function encodeId($id)
  {
    $in = explode("/",$id);
    $out = array();
    for($i=1;$i<count($in);$i++)
      $out[$i-1] = rawurlencode($in[$i]);
    return $out ? implode("/",$out) : ".";
  }
  
  function nameForce()
  {
    if($this->name)
      return $this->name;
    elseif($this->isRoot())
      return $this->config->gallery_name;
    else
      return substr($this->id, strrpos($this->id,'/')+1);
  }
  
  /**
   * If the gallery is an album then it returns the number of 
   * images contained otherwise the number of sub-galleries is returned
   * @return string  the contents of the specified gallery
   */
  function itemCountText()
  {
    if($this->isAlbum())
      return $this->imageCountText();
    else
      return $this->galleryCountText();
  }
  
  /**
   * If the gallery is an album then it returns the number of 
   * images contained otherwise the number of sub-galleries is returned
   * @return int  the contents of the specified gallery
   */
  function itemCount()
  {
    if($this->isAlbum())
      return $this->imageCount();
    else
      return $this->galleryCount();
  }
  
  /**
   * @return int  number of galleries in current view
   */
  function galleryCountSelected()
  {
    return min($this->galleryCount() - $this->startat, $this->config->thumb_number_gallery);
  }
  
  /**
   * @return int  number of image in current view
   */
  function imageCountSelected()
  {
    return min($this->imageCount() - $this->startat, $this->config->thumb_number_album);
  }
  
  
  /**
   * @return string  the absolute, canonical system path to the image
   */
  function realPath()
  {
    return realpath($this->config->base_path.$this->config->pathto_galleries.$this->id);
  }
  
  function thumbnailURL($type = "gallery")
  {
    $thumb = $this->thumbnail($type);
    return $thumb->URL();
  }
  
  function thumbnailHTML($class = "sgThumbGallery", $type = "gallery")
  {
    $thumb = $this->thumbnail($type);
    if($thumb == null) {
      $ret = nl2br($this->translator->_g("No\nthumbnail"));
    } else {    
      $ret  = '<img src="'.$thumb->URL().'" ';
      $ret .= 'class="'.$class.'" '; 
      $ret .= 'width="'.$thumb->width().'" height="'.$thumb->height().'" ';
      $ret .= 'alt="'.$this->translator->_g("Sample image from gallery").'" />';
    }
    
    return $ret;
  }
  
  function thumbnailLink($class = "sgThumbGallery", $type = "gallery")
  {
    return '<a href="'.$this->URL().'">'.$this->thumbnailHTML($class, $type).'</a>';
  }
  
  /**
   * Removes script-generated HTML (BRs and URLs) but leaves any other HTML
   * @return string the summary of the gallery
   */
  function summaryStripped()
  {
    return str_replace("<br />","\n",$this->summary());
  }
  
  function hasPrev()
  {
    return (bool) $this->index();
  }
  
  function hasNext()
  {
    $index = $this->index();
    return $index !== false && $index < $this->parent->galleryCount()-1;
  }
  
  function &prevGallery()
  {
    $tmp =& new sgGallery($this->parent->id.'/'.$this->parent->galleries[$this->index()-1], $this->parent);
    return $tmp;
  }
  
  function &nextGallery()
  {
    $tmp =& new sgGallery($this->parent->id.'/'.$this->parent->galleries[$this->index()+1], $this->parent);
    return $tmp;
  }
  
  function prevURL($action = null)
  {
    $tmp =& $this->prevGallery();
    return $tmp->URL($action);
  }
  
  function nextURL($action = null)
  {
    $tmp =& $this->nextGallery();
    return $tmp->URL($action);
  }
  
  function prevLink($action = null)
  {
    return '<a href="'.$this->prevURL($action).'">'.$this->prevText().'</a>';
  }
  
  function nextLink($action = null)
  {
    return '<a href="'.$this->nextURL($action).'">'.$this->nextText().'</a>';
  }
  
  function prevText()
  {
    return $this->translator->_g("Previous gallery");
  }
  
  function nextText()
  {
    return $this->translator->_g("Next gallery");
  }
  
  /**
   * finds position of current gallery in parent array
   */
  function index()
  {
    if(!$this->isRoot())
      foreach($this->parent->galleries as $key => $galleryId) 
        if(basename($this->id) == $galleryId)
          return $key;
    
    return false;
  }
  
  /** Accessor methods */
  function summary()       { return $this->summary; }
  
  /** Private methods */
  function thumbnail($type)
  {
    //only create thumbnail if it doesn't already exist
    if(!isset($this->thumbnails[$type])) {
    
      if($this->filename == "__none__" || $this->imageCount() == 0) 
        return;
      elseif($this->filename == "__random__") {
        srand(time()); //seed random number generator and select random image
        $img =& $this->images[rand(0,count($this->images)-1)];
      } else
        $img =& $this->findImage($this->filename);
      
      //create thumbnail
      $this->thumbnails[$type] =& new sgThumbnail($img, $type);
    }
    
    return $this->thumbnails[$type];
  }
  
  /**
   * Finds an image from the current gallery
   * @param mixed either the filename of the image to select or the integer 
   *  index of its position in the images array
   * @return sgImage the image found
   */
  function &findImage($image) 
  {
    if(is_string($image))
      foreach($this->images as $index => $img)
        if($img->id == $image) 
          return $this->images[$index];
    elseif(is_int($image) && $image >= 0 && $image < $this->imageCount())
      return $this->images[$image];
    
    return null;
  }
  
  
  
}


?>
