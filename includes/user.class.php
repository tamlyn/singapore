<?php 

/**
 * User class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: user.class.php,v 1.2 2004/10/26 04:32:36 tamlyn Exp $
 */

/**
 * Data-only class used to store user data.
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 */
class sgUser
{
  /**
   * Filename of the image if the image is local or the full URL of the image
   * if the image is remotely hosted.
   * @var string
   */
  var $username = "";

  /**
   * MD5 hash of password
   * @var string
   */
  var $userpass = "5f4dcc3b5aa765d61d8327deb882cf99";
  
  /**
   * Bit-field of permissions
   * @var int
   */
  var $permissions = 0;
  
  /**
   * Space-separated list of groups of which the user is a member
   * @var string
   */
  var $groups = "";
  
  /**
   * Email address of user
   * @var string
   */
  var $email = "";
  
  /**
   * The name or title of the image
   * @var string
   */
  var $fullname = "";
  
  /**
   *Description of user account
   * @var string
   */
  var $description = "";
  
  /**
   * Statistics (what's this? I don't know!)
   * @var string
   */
  var $stats = "";
  
  /**
   * Constructor forces username and userpass to have values
   */
  function sgUser($username, $userpass)
  {
    $this->username = $username;
    $this->userpass = $userpass;
  }
}

?>
