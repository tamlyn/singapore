<?php 

/**
 * Gallery class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003-2005 Tamlyn Rhodes
 * @version $Id: gallery.class.php,v 1.9 2005/09/17 14:57:46 tamlyn Exp $
 */

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
    $this->config =& $GLOBALS["sgConfig"];
    $this->translator =& $GLOBALS["sgTranslator"];
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
  
  function imageCountText()       { return $this->translator->_ng("%s image", "%s images", $this->imageCount()); }
  function galleryCountText()     { return $this->translator->_ng("%s gallery", "%s galleries", $this->galleryCount()); }
  
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
  
  function thumbnailURL()
  {
    $this->loadThumbnail();
    return $this->thumbnail->URL();
  }
  
  function thumbnailHTML()
  {
    $this->loadThumbnail();
    if($this->thumbnail == null) {
      $ret = nl2br($this->translator->_g("No\nthumbnail"));
    } else {    
      $ret  = "<img src=\"".$this->thumbnail->URL().'" class="sgGalleryThumb" '; 
      $ret .= 'width="'.$this->thumbnail->width().'" height="'.$this->thumbnail->height().'" ';
      $ret .= 'alt="'.$this->translator->_g("Sample image from gallery").'" />';
    }
    
    return $ret;
  }
  
  /**
   * Removes script-generated HTML (BRs and URLs) but leaves any other HTML
   * @return string the summary of the gallery
   */
  function summaryStripped()
  {
    return str_replace("<br />","\n",$this->summary());
  }
  
  /**
   * Removes script-generated HTML (BRs and URLs) but leaves any other HTML
   * @return string the description of the gallery
   */
  function descriptionStripped()
  {
    $ret = str_replace("<br />","\n",$this->description());
    
    if($this->config->enable_clickable_urls) {
      //strip off html from autodetected URLs
      $ret = preg_replace('{<a href="('.SG_REGEXP_PROTOCOLURL.')\">\1</a>}', '\1', $ret);
      $ret = preg_replace('{<a href="http://('.SG_REGEXP_WWWURL.')">\1</a>}', '\1', $ret);
      $ret = preg_replace('{<a href="mailto:('.SG_REGEXP_EMAILURL.')">\1</a>}', '\1', $ret);
    }
    
    return $ret;
  }
  
  /** Accessor methods */
  function summary()       { return $this->summary; }
  
  /** Private methods */
  function loadThumbnail()
  {
    //if thumbnail already exists, return
    if($this->thumbnail != null) return;
    
    if($this->filename == "__none__" || $this->imageCount() == 0) 
      return;
    elseif($this->filename == "__random__") {
      srand(time()); //seed random number generator and select random image
      $img =& $this->images[rand(0,count($this->images)-1)];
    } else
      $img =& $this->findImage($this->filename);
    
    //create thumbnail
    $this->thumbnail = new sgThumbnail($img, 
       $this->config->thumb_width_gallery,
       $this->config->thumb_height_gallery,
       $this->config->thumb_force_size_gallery);
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
