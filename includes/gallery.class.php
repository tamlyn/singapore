<?php 

/**
 * Gallery class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: gallery.class.php,v 1.7 2004/10/26 04:32:36 tamlyn Exp $
 */

/**
 * Data-only class used to store gallery data.
 * 
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003, 2004 Tamlyn Rhodes
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
   * - __none__ no thumbnail is displayed
   * - __random__ a random image is chosen every time
   * @var string
   */
  var $filename = "__none__";
  
  /**
   * Username of the user to which the gallery belongs
   * @var string
   */
  var $owner = "__nobody__";
  
  /**
   * Space-separated list of groups to which the gallery belongs
   * @var string
   */
  var $groups = "";
  
  /**
   * Bit-field of permissions
   * @var int
   */
  var $permissions = 0;
  
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
   * The name of the gallery owner (or anyone else)
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
   * Short multiline summary of gallery contents
   * @var string
   */
  var $summary = "";
  
  /**
   * Date associated with gallery
   * @var string
   */
  var $date = "";
  
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
  
  //non-db fields
  var $hits = 0;
  var $lasthit = 0;
  
  function sgGallery($id)
  {
    $this->id = $id;
  }
}


?>
