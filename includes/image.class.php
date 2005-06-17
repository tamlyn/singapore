<?php 

/**
 * Image class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003-2005 Tamlyn Rhodes
 * @version $Id: image.class.php,v 1.8 2005/06/17 20:08:33 tamlyn Exp $
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
  
  function sgImage($id, &$parent)
  {
    $this->id = $id;
    $this->parent = $parent;
  }
  
  function isImage() { return true; }
  function hasNext() { return false; }
  
  function prevImage() 
  {
    $temp = &$this->parent;
    unset($this->parent);
    $pos = array_keys($temp->images, $this);
    $this->parent = &$temp;
    return $this->parent->images[$pos[0]-1];
  }
  
  /** Accessor methods */
  function getWidth()  { return $this->width; }
  function getHeight() { return $this->height; }
  function getType()   { return $this->type; }
}

?>
