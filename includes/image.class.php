<?php 

/**
 * Image class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: image.class.php,v 1.6 2004/10/26 04:32:36 tamlyn Exp $
 */

/**
 * Data-only class used to store image data.
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 */
class sgImage
{
  /**
   * Filename of the image if the image is local or the full URL of the image
   * if the image is remotely hosted.
   * @var string
   */
  var $filename = "";

  var $thumbnail = "";
  
  /**
   * Name of the user to which the image belongs (not used)
   * @var string
   */
  var $owner = "__nobody__";
  
  /**
   * Space-separated list of groups to which the image belongs (not used)
   * @var string
   */
  var $groups = "";
  
  /**
   * Bit-field of permissions (not used)
   * @var int
   */
  var $permissions = 0;
  
  /**
   * Space-separated list of categories to which the image belongs (not used)
   * @var string
   */
  var $categories = "";
  
  /**
   * The name or title of the image
   * @var string
   */
  var $name = "";
  
  /**
   * The name of the artist (or anyone else)
   * @var string
   */
  var $artist = "";
  
  /**
   * Email of the artist (or anyone else)
   * @var string
   */
  var $email = "";
  
  /**
   * Optional copyright information
   * @var string
   */
  var $copyright = "";
  
  /**
   * Multiline description of the image
   * @var string
   */
  var $desc = "";
  
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
  var $location = "";
  var $date = "";
  var $camera = "";
  var $lens = "";
  var $film = "";
  var $darkroom = "";
  var $digital = "";
  
  //non-db fields
  var $hits = 0;
  var $lasthit = 0;
  
  /**
   * Position of image within ->images[] array
   * @var int
   */
  var $index = -1;
  
}

?>
