<?php 

/**
 * User class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: user.class.php,v 1.5 2006/03/14 19:38:54 tamlyn Exp $
 */

/**
 * Class used to represent a user.
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 */
class sgUser
{
  /**
   * Username of user. Special cases are 'guest' and 'admin'.
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
   * The name or title of the user
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
   * Constructor ensures username and userpass have values
   */
  function sgUser($username, $userpass)
  {
    $this->username = $username;
    $this->userpass = $userpass;
  }
  
  /**
   * Checks if currently logged in user is an administrator.
   * 
   * @return bool true on success; false otherwise
   */
  function isAdmin()
  {
    return $this->permissions & SG_ADMIN;
  }
  
  /**
   * Checks if currently logged in user is a guest.
   * 
   * @return bool true on success; false otherwise
   */
  function isGuest()
  {
    return $this->username == "guest";
  }
  
  /**
   * Checks if this user is the owner of the specified item.
   * 
   * @param sgItem  the item to check
   * @return bool   true if this user is the owner; false otherwise
   */
  function isOwner($item)
  {
    return $item->owner == $this->username;
  }
  
  /**
   * Checks if this user is a member of the group(s) supplied
   * 
   * @return bool true on success; false otherwise
   */
  function isInGroup($groups)
  {
    return (bool) array_intersect(explode(" ",$groups),explode(" ",$this->groups));
  }
  
  
}

?>
