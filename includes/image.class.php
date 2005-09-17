<?php 

/**
 * Image class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003-2005 Tamlyn Rhodes
 * @version $Id: image.class.php,v 1.9 2005/09/17 14:57:46 tamlyn Exp $
 */

/**
 * Data-only class used to store image data.
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003-2005 Tamlyn Rhodes
 */
class sgImage extends sgItem
{
  var $thumbnail = "";
  
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
   * Position of image within ->images[] array
   * @var int
   */
  var $index = -1;
  
  /**
   * Constructor
   * @param string     image id
   * @param sgGallery  reference to the parent gallery
   */
  function sgImage($id, &$parent)
  {
    $this->id = $id;
    $this->parent =& $parent;
    $this->config =& $GLOBALS["sgConfig"];
    $this->translator =& $GLOBALS["sgTranslator"];
  }
  
  function isImage() { return true; }
  
  function hasPrev() 
  {
    return (bool) $this->parentPosition();
  }
  
  function hasNext() 
  {
    $pp = $this->parentPosition();
    return $pp !== false && $pp < $this->parent->imageCount()-1; 
  }
  
  function &firstImage() 
  {
    return $this->parent->images[0];
  }
  
  function &prevImage() 
  {
    return $this->parent->images[$this->parentPosition()-1];
  }
  
  function &nextImage() 
  {
    return $this->parent->images[$this->parentPosition()+1];
  }
  
  function &lastImage() 
  {
    return $this->parent->images[count($this->parent->images)-1];
  }
  
  function nextLink()
  {
    if(!$this->hasNext())
      return "";
    
    $tmp =& $this->nextImage(); 
    return '<a href="'.$tmp->URL().'">'.$this->translator->_g("image|Next").'</a>';
  }
  
  function prevLink()
  {
    if(!$this->hasPrev())
      return "";
    
    $tmp =& $this->prevImage(); 
    return '<a href="'.$tmp->URL().'">'.$this->translator->_g("image|Previous").'</a>';
  }
  
  function parentLink()
  {
    return '<a href="'.$this->parent->URL().'">'.$this->translator->_g("image|Thumbnails").'</a>';
  }
  
  function imageURL()
  {
    if($this->isRemote())
      return $this->id;
    else
      return $this->config->pathto_galleries.$this->parent->idEncoded()."/".$this->idEncoded();
  }
  
  function imageHTML()
  {
    $ret  = "<img src=\"".$this->imageURL().'" class="sgImage" '; 
    $ret .= 'width="'.$this->width().'" height="'.$this->height().'" ';
    $ret .= 'alt="'.$this->name().$this->byArtistText().'" />';
    
    return $ret;
  }
  
  function thumbnailURL()
  {
    $this->loadThumbnail();
    return $this->thumbnail->URL();
  }
  
  function thumbnailHTML()
  {
    $this->loadThumbnail();
    $ret  = "<img src=\"".$this->thumbnail->URL().'" class="sgAlbumThumb" '; 
    $ret .= 'width="'.$this->thumbnail->width().'" height="'.$this->thumbnail->height().'" ';
    $ret .= 'alt="'.$this->name().$this->byArtistText().'" />';
    
    return $ret;
  }
  
  function thumbnailPopupHTML()
  {
    $this->loadThumbnail();
    $ret =  '<a href="'.$this->URL().'" onclick="';
    $ret .= "window.open('".$this->imageURL()."','','toolbar=0,resizable=1,";
    $ret .= "width=".($this->width()+20).",";
    $ret .= "height=".($this->height()+20)."');";
    $ret .= "return false;\">".$this->thumbnailHTML($index)."</a>";
  }
  
  /**
   * checks if image is remote (filename starts with 'http://')
   */
  function isRemote()
  {
    return substr($this->id, 0, 7) == "http://";
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
  
  function width()  { return $this->width; }
  function height() { return $this->height; }
  
  function imageRealURL() { return $this->imageURL(); }
  
  /** Accessor methods */
  function realWidth()  { return $this->width; }
  function realHeight() { return $this->height; }
  function type()   { return $this->type; }
  
  
  /* Private methods */
  
  /**
   * finds position of current image in parent array
   */
  function parentPosition()
  {
    foreach($this->parent->images as $key => $img)
      if($this->id == $img->id)
        return $key;
    
    return false;
  }
  
  function loadThumbnail()
  {
    //if thumbnail already exists, return
    if($this->thumbnail != null) return;
    
    //create thumbnail
    $this->thumbnail = new sgThumbnail($this, 
       $this->config->thumb_width_album,
       $this->config->thumb_height_album,
       $this->config->thumb_force_size_album);
  }
}

?>
