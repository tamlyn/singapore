<?php 

/**
 * Singapore gallery item class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2005 Tamlyn Rhodes
 * @version $Id: item.class.php,v 1.1 2005/06/17 20:08:34 tamlyn Exp $
 */

//permissions bit flags
define("SG_GRP_READ",   1);
define("SG_GRP_EDIT",   2);
define("SG_GRP_ADD",    4);
define("SG_GRP_DELETE", 8);
define("SG_WLD_READ",   16);
define("SG_WLD_EDIT",   32);
define("SG_WLD_ADD",    64);
define("SG_WLD_DELETE", 128);
define("SG_IHR_READ",   17);
define("SG_IHR_EDIT",   34);
define("SG_IHR_ADD",    68);
define("SG_IHR_DELETE", 136);

/**
 * Abstract class from which sgImage and sgGallery are derived.
 * 
 * @abstract
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2005 Tamlyn Rhodes
 */
class sgItem
{
  /**
   * The id of the item. In the case of galleries this is the path to the 
   * gallery from root and must be unique. For images it is the image file
   * name (or URL for remote images).
   * @var string
   */
  var $id;
   
  /**
   * Username of the user to which the item belongs
   * @var string
   */
  var $owner = "__nobody__";
  
  /**
   * Space-separated list of groups to which the item belongs
   * @var string
   */
  var $groups = "";
  
  /**
   * Bit-field of permissions
   * @var int
   */
  var $permissions = 0;
  
  /**
   * Space-separated list of categories to which the item belongs (not used)
   * @var string
   */
  var $categories = "";
  
  /**
   * The name or title of the item
   * @var string
   */
  var $name = "";
  
  /**
   * The name of the original item creator (or anyone else)
   * @var string
   */
  var $artist = "";
  
  /**
   * Email of the original item creator (or anyone else)
   * @var string
   */
  var $email = "";
  
  /**
   * Optional copyright information
   * @var string
   */
  var $copyright = "";
  
  /**
   * Multiline description of the item
   * @var string
   */
  var $desc = "";
  
  /**
   * Date associated with item
   * @var string
   */
  var $date = "";
  
  var $location = "";
  
  //non-db fields
  var $hits = 0;
  var $lasthit = 0;
  
  /**
   * Pointer to the parent sgItem
   * @var sgItem
   */
  var $parent = null;
  
  /** Accessor methods */
  function getName()        { return $this->name; }
  function getArtist()      { return $this->artist; }
  function getDate()        { return $this->date; }
  function getLocation()    { return $this->location; }
  function getDescription() { return $this->desc; }
  
  function canEdit() { }
  
  function isAlbum()   { return false; }
  function isGallery() { return false; }
  function isImage()   { return false; }
}


?>
