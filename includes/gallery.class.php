<?php 

/**
 * Gallery class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot org>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003 Tamlyn Rhodes
 * @version $Id: gallery.class.php,v 1.3 2003/12/14 14:39:18 tamlyn Exp $
 */

/**
 * Data-only class used to store gallery data.
 * 
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot org>
 * @copyright (c)2003 Tamlyn Rhodes
 */
class sgGallery
{
  /**
   * The id of the gallery. 
   * This is the path to the gallery from root and must be unique.
   * @var string
   */
  var $id;
  
  /**
   * Filename of the image used to represent this gallery.
   * Special values:
   * - <code>__none__</code> no thumbnail is displayed
   * - <code>__random__</code> a random image is chosen every time
   * @var string
   */
  var $filename = "__none__";
  
  /**
   * Name of the user to which the gallery belongs (not used)
   * @var string
   */
  var $owner = "__nobody__";
  
  /**
   * Space-separated list of groups to which the gallery belongs (not used)
   * @var string
   */
  var $groups = "__nogroup__";
  
  /**
   * Bit-field of permissions (not used)
   * @var int
   */
  var $permissions = 4095;
  
  /**
   * Space-separated list of categories to which the gallery belongs (not used)
   * @var string
   */
  var $categories = "";
  
  /**
   * The name or title of the gallery
   * @var string
   */
  var $name = "";
  
  /**
   * The name of the gallery owner (or annyone else)
   * @var string
   */
  var $artist = "";
  
  /**
   * Email of the gallery owner (or anyone else)
   * @var string
   */
  var $email = "";
  
  /**
   * Optional copyright information
   * @var string
   */
  var $copyright = "";
  
  /**
   * Multiline description of the current gallery
   * @var string
   */
  var $desc = "";
  
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
   * Used to store the number of hits or views
   * @var int|null
   */
  
  var $hits = null;
  
  function sgGallery($id)
  {
    $this->id = $id;
  }
}


?>
