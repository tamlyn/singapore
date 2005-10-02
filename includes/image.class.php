<?php 

/**
 * Image class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003-2005 Tamlyn Rhodes
 * @version $Id: image.class.php,v 1.11 2005/10/02 03:35:24 tamlyn Exp $
 */

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
    $this->config =& $GLOBALS["sgConfig"];
    $this->translator =& $GLOBALS["sgTranslator"];
  }
  
  function isImage() { return true; }
  
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
    return '<a href="'.$tmp->URL($action).'">'.$this->translator->_g("image|First").'</a>';
  }
  
  function prevLink($action = null)
  {
    if(!$this->hasPrev())
      return "";
    
    $tmp =& $this->prevImage(); 
    return '<a href="'.$tmp->URL($action).'">'.$this->translator->_g("image|Previous").'</a>';
  }
  
  function nextLink($action = null)
  {
    if(!$this->hasNext())
      return "";
    
    $tmp =& $this->nextImage(); 
    return '<a href="'.$tmp->URL($action).'">'.$this->translator->_g("image|Next").'</a>';
  }
  
  function lastLink($action = null)
  {
    if(!$this->hasNext())
      return "";
    
    $tmp =& $this->lastImage(); 
    return '<a href="'.$tmp->URL($action).'">'.$this->translator->_g("image|Last").'</a>';
  }
  
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
      return $this->config->pathto_galleries.$this->parent->idEncoded()."/".$this->idEncoded();
  }
  
  function imageHTML($class = "sgImage")
  {
    $ret  = "<img src=\"".$this->imageURL().'" '; 
    $ret .= 'class="'.$class.'" '; 
    $ret .= 'width="'.$this->width().'" height="'.$this->height().'" ';
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
  
  function width()
  {
    if($this->config->full_image_resize) {
      $img = $this->thumbnail("image");
      return $img->width();
    } else
      return $this->width;
  }
  
  function height()
  {
    if($this->config->full_image_resize) {
      $img = $this->thumbnail("image");
      return $img->height();
    } else
      return $this->height; 
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
  
  /** Accessor methods */
  function realWidth()  { return $this->width; }
  function realHeight() { return $this->height; }
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
