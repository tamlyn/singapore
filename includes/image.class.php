<?php 

/**
 * Image class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003-2005 Tamlyn Rhodes
 * @version $Id: image.class.php,v 1.21 2006/03/21 02:29:14 tamlyn Exp $
 */

//include the base class
require_once dirname(__FILE__)."/item.class.php";
 
/**
 * Data-only class used to store image data.
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003-2005 Tamlyn Rhodes
 */
class sgImage extends sgItem
{
  /**
   * Width in pixels of the image
   * @var int
   */
  var $width = 0;
  
  /**
   * Height in pixels of the image
   * @var int
   */
  var $height = 0;
  
  /**
   * Image file format flag as returned by GetImageSize()
   * @var int
   */
  var $type;
  
  
  /**
   * Configurable field
   */
  var $camera = "";
  var $lens = "";
  var $film = "";
  var $darkroom = "";
  var $digital = "";
  
  /**
   * Constructor
   * @param string     image id
   * @param sgGallery  reference to the parent gallery
   */
  function sgImage($id, &$parent)
  {
    $this->id = $id;
    $this->parent =& $parent;
    $this->config =& sgConfig::getInstance();
    $this->translator =& Translator::getInstance();
  }
  
  /**
   * Over-rides the method in the item class
   * @return true  returns true
   */
  function isImage() { return true; }
  
  
  /**
   * @return bool  true if image height is greater than width
   */
  function isPortrait() 
  { 
    return $this->width()/$this->height() > 1; 
  }
  
  /**
   * @return bool  true if image height is greater than width
   */
  function isLandscape() 
  { 
    return $this->width()/$this->height() < 1; 
  }
  
  function hasPrev() 
  {
    return (bool) $this->index();
  }
  
  function hasNext() 
  {
    $index = $this->index();
    return $index !== false && $index < $this->parent->imageCount()-1; 
  }
  
  function &firstImage() 
  {
    return $this->parent->images[0];
  }
  
  function &prevImage() 
  {
    return $this->parent->images[$this->index()-1];
  }
  
  function &nextImage() 
  {
    return $this->parent->images[$this->index()+1];
  }
  
  function &lastImage() 
  {
    return $this->parent->images[count($this->parent->images)-1];
  }
  
  function firstLink($action = null)
  {
    if(!$this->hasPrev())
      return "";
    
    $tmp =& $this->firstImage(); 
    return '<a href="'.$tmp->URL(null, $action).'">'.$this->firstText().'</a>';
  }
  
  function prevLink($action = null)
  {
    if(!$this->hasPrev())
      return "";
    
    return '<a href="'.$this->prevURL($action).'">'.$this->prevText().'</a>';
  }
  
  function nextLink($action = null)
  {
    if(!$this->hasNext())
      return "";
    
    return '<a href="'.$this->nextURL($action).'">'.$this->nextText().'</a>';
  }
  
  function lastLink($action = null)
  {
    if(!$this->hasNext())
      return "";
    
    $tmp =& $this->lastImage(); 
    return '<a href="'.$tmp->URL(null, $action).'">'.$this->lastText().'</a>';
  }
  
  function prevURL($action = null)
  {
    $tmp =& $this->prevImage(); 
    return $tmp->URL(null, $action);
  }
  
  function nextURL($action = null)
  {
    $tmp =& $this->nextImage(); 
    return $tmp->URL(null, $action);
  }
  
  function firstText() { return $this->translator->_g("image|First"); }
  function prevText() { return $this->translator->_g("image|Previous"); }
  function nextText() { return $this->translator->_g("image|Next"); }
  function lastText() { return $this->translator->_g("image|Last"); }
  function parentText() { return $this->translator->_g("image|Thumbnails"); }
  
  function imageURL()
  {
    if($this->config->full_image_resize) {
      $img = $this->thumbnail("image");
      return $img->URL();
    } else
      return $this->realURL();
  }
  
  function realURL()
  {
    if($this->isRemote())
      return $this->id;
    else
      return $this->config->base_url.$this->config->pathto_galleries.$this->parent->idEncoded()."/".$this->idEncoded();
  }
  
  function imageHTML($class = "sgImage")
  {
    $ret  = "<img src=\"".$this->imageURL().'" '; 
    $ret .= 'class="'.$class.'" '; 
    $ret .= 'width="'.$this->width().'" height="'.$this->height().'" ';
    if($this->config->imagemap_navigation) $ret .= 'usemap="#sgNavMap" border="0" ';
    $ret .= 'alt="'.$this->name().$this->byArtistText().'" />';
    
    return $ret;
  }
  
  function thumbnailURL($type = "album")
  {
    $thumb = $this->thumbnail($type);
    return $thumb->URL();
  }
  
  function thumbnailHTML($class = "sgThumbnailAlbum", $type = "album")
  {
    $thumb = $this->thumbnail($type);
    $ret  = "<img src=\"".$thumb->URL().'" ';
    $ret .= 'class="'.$class.'" '; 
    $ret .= 'width="'.$thumb->width().'" height="'.$thumb->height().'" ';
    $ret .= 'alt="'.$this->name().$this->byArtistText().'" />';
    return $ret;
  }
  
  function thumbnailLink($class = "sgThumbnailAlbum", $type = "album")
  {
    return '<a href="'.$this->URL().'">'.$this->thumbnailHTML($class, $type).'</a>';
  }
  
  function thumbnailPopupLink($class = "sgThumbnailAlbum", $type = "album")
  {
    $ret =  '<a href="'.$this->URL().'" onclick="';
    $ret .= "window.open('".$this->imageURL()."','','toolbar=0,resizable=1,";
    $ret .= "width=".($this->width()+20).",";
    $ret .= "height=".($this->height()+20)."');";
    $ret .= "return false;\">".$this->thumbnailHTML($class, $type)."</a>";
  }
  
  function nameForce()
  {
    if($this->name)
      return $this->name;
    elseif($this->isRemote())
      return substr($this->id, strrpos($this->id,'/') + 1, strrpos($this->id,'.') - strrpos($this->id,'/') - 1);
    else
      return substr($this->id, 0, strrpos($this->id,'.'));
  }
  
  /**
   * checks if image is remote (filename starts with 'http://')
   */
  function isRemote($image = null)
  {
    if($image == null) $image = $this->id;
    return substr($image, 0, 7) == "http://";
  }
  
  /**
   * @return string  the absolute, canonical system path to the image
   */
  function realPath()
  {
    if($this->isRemote())
      return $this->id;
    else
      return realpath($this->config->base_path.$this->config->pathto_galleries.$this->parent->id."/".$this->id);
  }
  
  /**
   * @return string  the rawurlencoded version of the image id
   */
  function idEncoded()
  {
    return rawurlencode($this->id);
  }
  
  function width()
  {
    if($this->config->full_image_resize) {
      $img = $this->thumbnail("image");
      return $img->width();
    } else
      return $this->realWidth();
  }
  
  function height()
  {
    if($this->config->full_image_resize) {
      $img = $this->thumbnail("image");
      return $img->height();
    } else
      return $this->realHeight(); 
  }
  
  /**
   * finds position of current image in parent array
   */
  function index()
  {
    foreach($this->parent->images as $key => $img)
      if($this->id == $img->id)
        return $key;
    
    return false;
  }
  
  function realWidth()
  {
    //try to load image dimensions if not already loaded
    if($this->width == 0) {
      $size = @GetImageSize($this->realPath());
      if($size)
        list($this->width, $this->height, $this->type) = $size;
      else
        return $this->config->thumb_width_image;
    }
    return $this->width; 
  }
  
  function realHeight() 
  {
    //try to load image dimensions if not already loaded
    if($this->height == 0) {
      $size = @GetImageSize($this->realPath());
      if($size)
        list($this->width, $this->height, $this->type) = $size;
      else
        return $this->config->thumb_height_image;
    }
    return $this->height; 
  }
  
  /** Accessor methods */
  function type()       { return $this->type; }
  
  
  /* Private methods */
  
  function &thumbnail($type)
  {
    //only create thumbnail if it doesn't already exist
    if(!isset($this->thumbnails[$type])) 
      $this->thumbnails[$type] =& new sgThumbnail($this, $type);
    
    return $this->thumbnails[$type];
  }
}

?>
