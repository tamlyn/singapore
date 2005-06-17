<?php 

/**
 * Gallery class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003-2005 Tamlyn Rhodes
 * @version $Id: gallery.class.php,v 1.8 2005/06/17 20:08:33 tamlyn Exp $
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
   * @param string  gallery id
   * @param sgItem  pointer to the parent gallery
   */
  function sgGallery($id, &$parent = null)
  {
    $this->id = $id;
    $this->parent = $parent;
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
  
  /**
   * Caches returned value for use with repeat requests
   * @return string  the urlencoded version of the gallery id
   */
  function getEncodedId()
  {
    return isset($this->idEncoded) ? $this->idEncoded : $this->idEncoded = sgUtils::encodeId($this->id);
  }
  
  /** Accessor methods */
  function getSummary()       { return $this->summary; }
  
  
}


?>
