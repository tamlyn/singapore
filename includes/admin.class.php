<?php 

/**
 * Class providing admin functions.
 * 
 * @package singapore
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: admin.class.php,v 1.33 2004/12/09 22:50:31 tamlyn Exp $
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

define("SG_ADMIN",     1024);
define("SG_SUSPENDED", 2048);

/**
 * Provides gallery, image and user administration functions.
 * 
 * @uses Singapore
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 */
class sgAdmin extends Singapore
{
  /**
   * The text of the last error to have occurred
   * @var string
   */
  var $lastError = "";
  
  /**
   * Admin constructor. Doesn't call {@link Singapore} constructor.
   * @param string the path to the base singapore directory
   */
  function sgAdmin($basePath = "")
  {
    //import class definitions
    //io handler class included once config is loaded
    require_once $basePath."includes/translator.class.php";
    require_once $basePath."includes/gallery.class.php";
    require_once $basePath."includes/config.class.php";
    require_once $basePath."includes/image.class.php";
    require_once $basePath."includes/user.class.php";
    
    //start execution timer
    $this->scriptStartTime = microtime();

    //remove slashes
    if(get_magic_quotes_gpc()) {
      $_REQUEST = array_map(array("Singapore","arraystripslashes"), $_REQUEST);
      
      //as if magic_quotes_gpc wasn't insane enough, php doesn't add slashes 
      //to the tmp_name variable so I have to add them manually. Grrrr.
      if(isset($_FILES["sgImageFile"]["tmp_name"])) 
        $_FILES["sgImageFile"]["tmp_name"] = addslashes($_FILES["sgImageFile"]["tmp_name"]);
      if(isset($_FILES["sgArchiveFile"]["tmp_name"])) 
        $_FILES["sgArchiveFile"]["tmp_name"] = addslashes($_FILES["sgArchiveFile"]["tmp_name"]);
      $_FILES   = array_map(array("Singapore","arraystripslashes"), $_FILES);
    }
    
    $galleryId = isset($_REQUEST["gallery"]) ? $_REQUEST["gallery"] : ".";
    
    //load config from default ini file (singapore.ini)
    $this->config = new sgConfig("singapore.ini");
    $this->config->loadConfig("secret.ini.php");
    //set runtime values
    $this->config->pathto_logs = $this->config->pathto_data_dir."logs/";
    $this->config->pathto_cache = $this->config->pathto_data_dir."cache/";
    $this->config->pathto_current_template = $this->config->pathto_templates.$this->config->default_template."/";
    $this->config->pathto_admin_template = $this->config->pathto_templates.$this->config->admin_template_name."/";
    
    //load config from admin template ini file (admin.ini) if present
    $this->config->loadConfig($this->config->pathto_admin_template."admin.ini");
    
    $this->template = isset($_REQUEST["template"]) ? $_REQUEST["template"] : $this->config->default_template;
    
    //do not load gallery-specific ini files

    //convert octal strings to integers
    if(isset($this->config->directory_mode) && is_string($this->config->directory_mode)) $this->config->directory_mode = octdec($this->config->directory_mode);
    if(isset($this->config->umask) && is_string($this->config->umask)) $this->config->umask = octdec($this->config->umask);
    
    
    //set current language from request vars or config
    $this->language = isset($_REQUEST["lang"]) ? $_REQUEST["lang"] : $this->config->default_language;
    //read the standard language file
    $this->i18n = new Translator($this->config->pathto_locale."singapore.".$this->config->default_language.".pmo");
    //read extra admin language file
    $this->i18n->readLanguageFile($this->config->pathto_locale."singapore.admin.".$this->config->default_language.".pmo");

    //include IO handler class and create instance
    require_once $basePath."includes/io_".$this->config->io_handler.".class.php";
    $ioClassName = "sgIO_".$this->config->io_handler;
    $this->io = new $ioClassName($this->config);
    
    //set character set
    if(!empty($this->i18n->languageStrings[0]["charset"]))
      $this->character_set = $this->i18n->languageStrings[0]["charset"];
    else
      $this->character_set = $this->config->default_charset;
    
    //set action to perform
    if(empty($_REQUEST["action"])) $this->action = "menu";
    else $this->action = $_REQUEST["action"];
    
    //set page title
    $this->pageTitle = $this->config->gallery_name;
  }
  
  /**
   * @return string the value of {@link lastError}
   */
  function getLastError()
  {
    return $this->lastError;
  }
  
  /**
   * Returns a link to the image or gallery with the correct formatting and path
   * NOTE: This takes its arguments in a different order to {@link Singapore::formatURL()}
   *
   * @author   Adam Sissman <adam at bluebinary dot com>
   */
  function formatAdminURL($action, $gallery = null, $image = null, $startat = null, $extra = null)
  {
    $ret  = $this->config->base_url."admin.php?";
    $ret .= "action=".$action;
    if($gallery != null)
      $ret .= "&amp;gallery=".$gallery;
    if($image != null)
      $ret .= "&amp;image=".$image;
    if($startat != null)
      $ret .= "&amp;startat=".$startat;
    if($extra != null)
      $ret .= $extra;
    
    return $ret;
  }

  /**
   * Tries to find temporary storage space
   */
  function findTempDirectory()
  {
    if(isset($_ENV["TMP"]) && is_writable($_ENV["TMP"])) return $_ENV["TMP"];
    elseif(isset($_ENV["TEMP"]) && is_writable($_ENV["TEMP"])) return $_ENV["TEMP"];
    elseif(is_writable("/tmp")) return "/tmp";
    else return null;
  }
  
  /**
   * Tests if $child is within $parent
   * @param string  relative or absolute path to parent directory
   * @param string  relative or absolute path to child directory or file
   * @return bool   true if $child is contained within $parent 
   */
  function isSubPath($parent, $child) {
    $parentPath = realpath($parent);
    return substr(realpath($child),0,strlen($parentPath)) == $parentPath;
  }

  /**
   * Loads hits info into the galleries object.
   */
  function loadGalleryHits()
  {
    for($i=$max=0;$i<count($this->gallery->galleries);$i++) {
      $this->gallery->galleries[$i]->hits = $this->io->getHits($this->gallery->galleries[$i]->id);
      if($this->gallery->galleries[$i]->hits->hits > $max) $max = $this->gallery->galleries[$i]->hits->hits;
    }
    $this->gallery->maxhits = $max;
  }
  
  /**
   * Loads hits info into the images object.
   */
  function loadImageHits()
  {
    $hits = $this->io->getHits($this->gallery->id);
    for($i=$max=0;$i<count($this->gallery->images);$i++) {
      for($j=0;$j<count($hits->images);$j++)
        if($hits->images[$j]->filename == $this->gallery->images[$i]->filename)
          $this->gallery->images[$i]->hits = $hits->images[$j];
      if(isset($this->gallery->images[$i]->hits->hits) && $this->gallery->images[$i]->hits->hits > $max) $max = $this->gallery->images[$i]->hits->hits;
    }
    $this->gallery->maxhits = $max;
  }
  
  /**
   * @return string
   */
  function galleryHitsTab()
  {
    $showing = $this->galleryTabShowing();
    if($this->gallery->id != ".") $links = "<a href=\"".$this->formatAdminURL("showgalleryhits",$this->gallery->parent)."\" title=\"".$this->i18n->_g("gallery|Up one level")."\">".$this->i18n->_g("gallery|Up")."</a>";
    if(empty($links)) return $showing;
    else return $showing." | ".$links;
  }
  
  /**
   * Returns a two-dimensional array of links for the admin bar.
   * 
   * @returns string
   */
  function adminLinksArray()
  {
    if(!$this->isLoggedIn()) return array(0 => array($this->i18n->_g("admin bar|Back") => "."));
    
    $ret[0][$this->i18n->_g("admin bar|Admin")] = $this->formatAdminURL("menu");
    $ret[0][$this->i18n->_g("admin bar|Galleries")] = $this->formatAdminURL("view", isset($this->gallery) ? $this->gallery->idEncoded : null);
    $ret[0][$this->i18n->_g("admin bar|Log out")] = $this->formatAdminURL("logout");
    if(isset($this->gallery)) {
      $ret[1][$this->i18n->_g("admin bar|Edit gallery")] = $this->formatAdminURL("editgallery",$this->gallery->idEncoded);
      $ret[1][$this->i18n->_g("admin bar|Edit permissions")] = $this->formatAdminURL("editpermissions",$this->gallery->idEncoded);
      $ret[1][$this->i18n->_g("admin bar|Delete gallery")] = $this->formatAdminURL("deletegallery",$this->gallery->idEncoded);
      $ret[1][$this->i18n->_g("admin bar|New subgallery")] = $this->formatAdminURL("newgallery",$this->gallery->idEncoded);
      $ret[1][$this->i18n->_g("admin bar|Re-index gallery")] = $this->formatAdminURL("reindex",$this->gallery->idEncoded);
      if($this->isImage()) {
        $ret[2][$this->i18n->_g("admin bar|Edit image")] = $this->formatAdminURL("editimage",$this->gallery->idEncoded,$this->image->filename);
        $ret[2][$this->i18n->_g("admin bar|Delete image")] = $this->formatAdminURL("deleteimage",$this->gallery->idEncoded,$this->image->filename);
      }
      $ret[2][$this->i18n->_g("admin bar|New image")] = $this->formatAdminURL("newimage",$this->gallery->idEncoded);
    }
    return $ret;
  }
  
  
  /**
   * Saves the new password if it is correctly specified.
   * 
   * @return boolean true on success; false otherwise
   */
  function savePass()
  {
    $users = $this->io->getUsers();
    
    $found = false;
    for($i=0;$i < count($users);$i++)
      if($_POST["sgUsername"] == $users[$i]->username) {
        $found = true;
        if(md5($_POST["sgOldPass"]) == $users[$i]->userpass)
          if($_POST["sgNewPass1"]==$_POST["sgNewPass2"])
            if(strlen($_POST["sgNewPass1"]) >= 6 && strlen($_POST["sgNewPass1"]) <= 16) { 
              $users[$i]->userpass = md5($_POST["sgNewPass1"]);
              if($this->io->putUsers($users)) return true;
              else $this->lastError = $this->i18n->_g("Could not save user info");
            }
            else 
              $this->lastError = $this->i18n->_g("New password must be between 6 and 16 characters long.");
          else 
            $this->lastError = $this->i18n->_g("The new passwords you entered do not match.");
        else 
          $this->lastError = $this->i18n->_g("The current password you entered does not match the one in the database.");
      }
    
    if(!$found) $this->lastError = $this->i18n->_g("The username specified was not found in the database.");
    
    //some sort of error occurred so:
    return false;
  }
  
  /**
   * @return boolean true if user is logged in; false otherwise
   */
  function isLoggedIn()
  {
    if(isset($_SESSION["sgUser"]) && $_SESSION["sgUser"]->check == md5($_SERVER["REMOTE_ADDR"]) && (time() - $_SESSION["sgUser"]->loginTime < 1800)) {
      $_SESSION["sgUser"]->loginTime = time();
      return true;
    }
    return false;
  }

  /**
   * Attempts to log a registered user into admin.
   * 
   * @return boolean true on success; false otherwise
   */
  function doLogin() 
  {
    if(isset($_POST["sgUsername"]) && isset($_POST["sgPassword"])) {
      $users = $this->io->getUsers();
      for($i=0;$i < count($users);$i++)
        if($_POST["sgUsername"] == $users[$i]->username && md5($_POST["sgPassword"]) == $users[$i]->userpass){
          if($users[$i]->permissions & SG_SUSPENDED) {
            $this->lastError = $this->i18n->_g("Your account has been suspended");
            return false;
          } else { 
            $_SESSION["sgUser"] = $users[$i];
            $_SESSION["sgUser"]->check = md5($_SERVER["REMOTE_ADDR"]);
            $_SESSION["sgUser"]->ip = $_SERVER["REMOTE_ADDR"];
            $_SESSION["sgUser"]->loginTime = time();
            return true;
          }
        }
      $this->logout();
      $this->lastError = $this->i18n->_g("Username and/or password incorrect");
      return false;
    }
    $this->lastError = $this->i18n->_g("You must enter a username and password");
    return false;
  }
  
  /**
   * Cancels a users admin session.
   * 
   * @return true
   */
  function logout()
  {
    $_SESSION["sgUser"] = null;
    return true;
  }
  
  /**
   * Checks if the specified operation is permitted on the specified object
   * 
   * @param sgImage|sgGallery the object to be operated on
   * @param string the action to perform (either 'read', 'edit', 'add' or 'delete')
   * @return bool true if permissions are satisfied; false otherwise
   */
  function checkPermissions($obj, $action, $gallery = null, $image = null)
  {
    if($this->isAdmin() || $this->isOwner($obj))// || (!$this->isGuest() && $obj->owner == "__nobody__"))
      return true;
    
    $usr = $_SESSION["sgUser"];
    switch($action) {
      case "read" :
        return $obj->permissions & SG_WLD_READ
           || ($this->isInGroup($usr->groups, $obj->groups) && $obj->permissions & SG_GRP_READ);
      case "edit" :
        return $obj->permissions & SG_WLD_EDIT 
           || ($this->isInGroup($usr->groups, $obj->groups) && $obj->permissions & SG_GRP_EDIT);
      case "add" :
        return $obj->permissions & SG_WLD_ADD 
           || ($this->isInGroup($usr->groups, $obj->groups) && $obj->permissions & SG_GRP_ADD);
      case "delete" :
        return $obj->permissions & SG_WLD_DELETE 
           || ($this->isInGroup($usr->groups, $obj->groups) && $obj->permissions & SG_GRP_DELETE);
      default :
        return false;
    }
  }
  
  function savePermissions()
  {
    if($this->isImage())
      $obj =& $this->image;
    else
      $obj =& $this->gallery;
    
    $perms = 0;
    if(!empty($_POST["sgGrpRead"]))   $perms |= SG_GRP_READ;
    if(!empty($_POST["sgGrpEdit"]))   $perms |= SG_GRP_EDIT;
    if(!empty($_POST["sgGrpAdd"]))    $perms |= SG_GRP_ADD;
    if(!empty($_POST["sgGrpDelete"])) $perms |= SG_GRP_DELETE;
    if(!empty($_POST["sgWldRead"]))   $perms |= SG_WLD_READ;
    if(!empty($_POST["sgWldEdit"]))   $perms |= SG_WLD_EDIT;
    if(!empty($_POST["sgWldAdd"]))    $perms |= SG_WLD_ADD;
    if(!empty($_POST["sgWldDelete"])) $perms |= SG_WLD_DELETE;
    
    $obj->permissions |= $perms;
    $obj->permissions &= $perms;
    
    if($this->isAdmin() || $obj->owner == $_SESSION["sgUser"]->username)
      $obj->groups = $_POST["sgGroups"];
    if($this->isAdmin())
      $obj->owner = $_POST["sgOwner"];
    
    if($this->io->putGallery($this->gallery))
      return true;
    
    $this->lastError = $this->i18n->_g("Could not save gallery info");
    return false;
  }
  
  /**
   * Checks if currently logged in user is an administrator.
   * 
   * @return bool true on success; false otherwise
   */
  function isAdmin($usr = null)
  {
    if($usr == null)
      $usr = $_SESSION["sgUser"];
    return $usr->permissions & SG_ADMIN;
  }
  
  /**
   * Checks if currently logged in user is a guest.
   * 
   * @return bool true on success; false otherwise
   */
  function isGuest($usr = null)
  {
    if($usr == null)
      $usr = $_SESSION["sgUser"];
    return $usr->username == "guest";
  }
  
  /**
   * Checks if at least one group name in $groups1 is also in $groups2
   * 
   * @return bool true on success; false otherwise
   */
  function isOwner($obj)
  {
    return $obj->owner == $_SESSION["sgUser"]->username;
  }
  
  /**
   * Checks if at least one group name in $groups1 is also in $groups2
   * 
   * @return bool true on success; false otherwise
   */
  function isInGroup($groups1,$groups2)
  {
    return (bool) array_intersect(explode(" ",$groups1),explode(" ",$groups2));
  }
  
  /**
   * Creates a new user.
   * 
   * @return bool true on success; false otherwise
   */
  function addUser()
  {
    $users = $this->io->getUsers();
    for($i=0; $i<count($users); $i++)
      if($users[$i]->username == $_REQUEST["user"]) {
        $this->lastError = $this->i18n->_g("Username already exists");
        return false;
      }
    
    if(!preg_match("/[a-zA-Z0-9_]{3,}/",$_REQUEST["user"])) {
      $this->lastError = $this->i18n->_g("Username must be at least 3 characters long and contain only alphanumeric characters");
      return false;
    }
    
    $users[$i] = new sgUser($_REQUEST["user"], md5("password"));
    
    if($this->io->putUsers($users))
      return true;
    
    $this->lastError = $this->i18n->_g("Could not save user info");
    return true;
  }
  
  /**
   * Deletes a user.
   * 
   * @return bool true on success; false otherwise
   */
  function deleteUser($user = null)
  {
    if($user == null)
      $user = $_REQUEST["user"];
      
    if($user == "admin" || $user == "guest") {
      $this->lastError = $this->i18n->_g("Cannot delete built in accounts");
      return false;
    }
      
    $users = $this->io->getUsers();
    for($i=0; $i<count($users); $i++)
      if($users[$i]->username == $_REQUEST["user"]) {
        
        //delete user at offset $i from $users
        array_splice($users,$i,1);
        
        if($this->io->putUsers($users))
          return true;
    
        $this->lastError = $this->i18n->_g("Could not save user info");
        return false;
      }
    
    $this->lastError = $this->i18n->_g("Username not recognised");
    return false;
  }
  
  /**
   * Saves a user's info.
   * 
   * @return bool true on success; false otherwise
   */
  function saveUser() {
    $users = $this->io->getUsers();
    for($i=0; $i<count($users); $i++)
      if($users[$i]->username == $_REQUEST["user"]) {
        $users[$i]->email = $this->prepareText($_REQUEST["sgEmail"]);
        $users[$i]->fullname = $this->prepareText($_REQUEST["sgFullname"]);
        $users[$i]->description = $this->prepareText($_REQUEST["sgDescription"]);
        if($this->isAdmin() && $_REQUEST["action"] == "saveuser") {
          $users[$i]->groups = $this->prepareText($_REQUEST["sgGroups"]);
          $users[$i]->permissions = ($_REQUEST["sgType"] == "admin") ? $users[$i]->permissions | SG_ADMIN : $users[$i]->permissions & ~SG_ADMIN;
          if(isset($_REQUEST["sgPassword"]) && $_REQUEST["sgPassword"] != "**********")
            $users[$i]->userpass = md5($_REQUEST["sgPassword"]);
        }
        if($this->io->putUsers($users))
          return true;
        $this->lastError = $this->i18n->_g("Could not save user info");
        return false;
      }
    $this->lastError = $this->i18n->_g("Username not recognised");
    return false;
  }
  
  /**
   * Suspend or unsuspend a user's account.
   * 
   * @return bool true on success; false otherwise
   */
  function suspendUser() {
    $users = $this->io->getUsers();
    for($i=0; $i<count($users); $i++)
      if($users[$i]->username == $_REQUEST["user"]) {
      
        $users[$i]->permissions = ($users[$i]->permissions & SG_SUSPENDED) ? $users[$i]->permissions & ~SG_SUSPENDED : $users[$i]->permissions | SG_SUSPENDED;
        if($this->io->putUsers($users))
          return true;
        $this->lastError = $this->i18n->_g("Could not save user info");
        return false;
      }
    $this->lastError = $this->i18n->_g("Username not recognised");
    return false;
  }
  
  /**
   * Check for images in current gallery directory which are
   * not in the metadata and add them.
   * 
   * @return int the number of images added
   */
  function reindexGallery()
  {
    $imagesAdded = 0;
    $dir = $this->getListing($this->config->pathto_galleries.$this->gallery->id,"images");
    for($i=0; $i<count($dir->files); $i++) {
      $found = false;
      for($j=0; $j<count($this->gallery->images); $j++)
        if($dir->files[$i] == $this->gallery->images[$j]->filename) {
          $found = true;
          break;
        }
      if(!$found) {
        $this->gallery->images[$j] = new sgImage();
        $this->gallery->images[$j]->filename = $dir->files[$i];
        $this->gallery->images[$j]->name = $dir->files[$i];
        list(
          $this->gallery->images[$j]->width, 
          $this->gallery->images[$j]->height, 
          $this->gallery->images[$j]->type
        ) = GetImageSize($this->config->pathto_galleries.$this->gallery->id."/".$this->gallery->images[$j]->filename);
        $imagesAdded++;
      }
    }
    
    if($this->io->putGallery($this->gallery))
      return $imagesAdded;
      
    $this->lastError = $this->i18n->_g("Could not save gallery info");
      return 0;

  }
  
  /**
   * Creates a directory.
   *
   * @return boolean true on success; false otherwise
   */
  function addGallery()
  {
    $newGalleryId = $this->gallery->id."/".$_REQUEST["newgallery"];
    $path = $this->config->pathto_galleries.$newGalleryId;
    
    if(file_exists($path)) {
      $this->lastError = $this->i18n->_g("Gallery already exists");
      return false;
    }
    
    if(!mkdir($path, $this->config->directory_mode)) {
      $this->lastError = $this->i18n->_g("Could not create directory");
      return false;
    }
    
    $gal = new sgGallery($newGalleryId);
    $gal->name = $_REQUEST["newgallery"];
    
    //set full permissions on guest-created objects
    if($this->isGuest())
      $gal->permissions = SG_GRP_READ | SG_GRP_EDIT | SG_GRP_ADD | SG_GRP_DELETE
                        | SG_WLD_READ | SG_WLD_EDIT | SG_WLD_ADD | SG_WLD_DELETE;
    else
      $gal->owner = $_SESSION["sgUser"]->username;
    
    if($this->io->putGallery($gal))
      return true;
      
    $this->lastError = $this->i18n->_g("Could not save gallery info");
    return false;
  }
  
  function prepareText($text, $multiline = false)
  {
    if(get_magic_quotes_gpc())
      $text = stripslashes($text);
    
    if($multiline) {
      $text = strip_tags($text, $this->config->allowed_tags);
      $text = str_replace(array("\n","\r"), array("<br />",""), $text);
    } else
      $text = strip_tags($text);
      
    return $text;
  }
  
  /**
   * Saves gallery info to the database.
   *
   * @return boolean true on success; false otherwise
   */
  function saveGallery()
  {
    $this->gallery->categories = $_REQUEST["sgCategories"];
    $this->gallery->name = $this->prepareText($_REQUEST["sgGalleryName"]);
    $this->gallery->artist = $this->prepareText($_REQUEST["sgArtistName"]);
    $this->gallery->email = $this->prepareText($_REQUEST["sgArtistEmail"]);
    $this->gallery->date = $this->prepareText($_REQUEST["sgDate"]);
    $this->gallery->copyright = $this->prepareText($_REQUEST["sgCopyright"]);
    $this->gallery->summary = $this->prepareText($_REQUEST["sgSummary"],true);
    $this->gallery->desc = $this->prepareText($_REQUEST["sgGalleryDesc"],true);
    
    if($this->config->enable_clickable_urls) {
      //recognise URLs and htmlise them
      $this->gallery->desc = preg_replace('{(?<!href="|href=)\b('.SG_REGEXP_PROTOCOLURL.')\b(?!</a>)}', '<a href="$1">$1</a>', $this->gallery->desc);  //general protocol match
      $this->gallery->desc = preg_replace('{(?<!://)\b('.SG_REGEXP_WWWURL.')\b(?!</a>)}', '<a href="http://$1">$1</a>', $this->gallery->desc);  //web addresses starting www. without path info
      $this->gallery->desc = preg_replace('{(?<!mailto:|\.)\b('.SG_REGEXP_EMAILURL.')\b(?!</a>)}', '<a href="mailto:$1">$1</a>', $this->gallery->desc);  //email addresses *@*.*
    }
    
    if($this->io->putGallery($this->gallery))
      return true;
      
    $this->lastError = $this->i18n->_g("Could not save gallery info");
    return false;
  }
  
  /**
   * Deletes a gallery and everything contained within it.
   *
   * @return boolean true on success; false otherwise
   */
  function deleteGallery($galleryId = null)
  {
    if($galleryId === null)
      $galleryId = $_REQUEST['gallery'];
  
    //security check: make sure requested file is in galleries directory
    if(!$this->isSubPath($this->config->pathto_galleries,$this->config->pathto_galleries.$galleryId)) {
      $this->lastError = $this->i18n->_g("Object not found");
      return false;
    }
  
    //check that the gallery to delete is not the top level directory
    if(realpath($this->config->pathto_galleries.$galleryId) == realpath($this->config->pathto_galleries)) {
      $this->lastError = $this->i18n->_g("Cannot delete the top level directory");
      return false;
    }
    
    //remove the offending directory and all contained therein
    return $this->rmdir_all($this->config->pathto_galleries.$galleryId);
  }
  
  /**
   * Saves changes to the gallery thumbnail to the database.
   *
   * @return boolean true on success; false otherwise
   */
  function saveGalleryThumbnail()
  {
    $this->gallery->filename = $_REQUEST['sgThumbName'];
    return $this->io->putGallery($this->gallery);
  }
  
  
  /**
   * Adds an image to the database.
   *
   * @return boolean true on success; false otherwise
   */
  function addImage()
  {
    if($_REQUEST["sgLocationChoice"] == "remote") {
      $image = $_REQUEST["sgImageURL"];
      $path = $image;
    } elseif($_REQUEST["sgLocationChoice"] == "single") {
      //set filename as requested and strip off any clandestine path info
      if($_REQUEST["sgNameChoice"] == "same") $image = basename($_FILES["sgImageFile"]["name"]);
      else $image = basename($_REQUEST["sgFileName"]);
      
      //make sure file has a recognised extension
      if(!preg_match("/\.(".$this->config->recognised_extensions.")$/i",$image)) $image .= ".jpeg";
      
      $path = $this->config->pathto_galleries.$this->gallery->id."/".$image;
      $srcImage = $image;
      
      if(file_exists($path))
        switch($this->config->upload_overwrite) {
          case 1 : //overwrite
            $this->deleteImage($image);
            break;
          case 2 : //generate unique
            for($i=0;file_exists($path);$i++) {
              $pivot = strrpos($srcImage,".");
              $image = substr($srcImage, 0, $pivot).'-'.$i.substr($srcImage, $pivot,strlen($srcImage)-$pivot);
              $path = $this->config->pathto_galleries.$this->gallery->id."/".$image;
            }
            break;
          case 0 : //raise error
          default :
            $this->lastError = $this->i18n->_g("File already exists");
            return false;
        }
      
      if(!move_uploaded_file($_FILES["sgImageFile"]["tmp_name"],$path)) {
        $this->lastError = $this->i18n->_g("Could not upload file"); 
        return false;
      }
      
    }
    
    $img = new sgImage();
    
    $img->filename = $image;
    $img->name = strtr(substr($image, strrpos($image,"/"), strrpos($image,".")-strlen($image)), "_", " ");
    list($img->width, $img->height, $img->type) = GetImageSize($path);
    $img->owner = $_SESSION["sgUser"]->username;
    
    $this->gallery->images[count($this->gallery->images)] = $img;
    
    if($this->io->putGallery($this->gallery)) {
      $this->selectImage($image);
      return true;
    }
    
    $this->lastError = $this->i18n->_g("Could not add image to gallery");
    @unlink($path);
    return false;
  }
  
  /**
   * Adds the contents of an uploaded archive to the database.
   *
   * @return boolean true on success; false otherwise
   */
  function addMultipleImages()
  {
    //find system temp directory
    if(!($systmpdir = $this->findTempDirectory())) {
      $this->lastError = $this->i18n->_g("Could not find temporary storage space"); 
      return false;
    }
    
    //create new temp directory in system temp dir but stop after 100 attempts
    while(!mkdir($tmpdir = $systmpdir."/".uniqid("sg"),$this->config->directory_mode) && $tries++<100);
    
    $archive = $_FILES["sgArchiveFile"]["tmp_name"];
  
    if(!is_uploaded_file($archive)) {
      $this->lastError = $this->i18n->_g("Could not upload file"); 
      return false;
    }
    
    //decompress archive to temp
    $cmd  = escapeshellcmd($this->config->pathto_unzip);
    $cmd .= ' -d "'.escapeshellcmd(realpath($tmpdir));
    $cmd .= '" "'.escapeshellcmd(realpath($archive)).'"';
    
    if(!exec($cmd)) {
      $this->lastError = $this->i18n->_g("Could not decompress archive"); 
      return false;
    }
    
    //start processing archive contents
    $wd = $tmpdir;
    $contents = $this->getListing($wd,"images");
    
    //cope with archives contained within a directory
    if(empty($contents->files) && count($contents->dirs) == 1)
      $contents = $this->getListing($wd .= '/'.$contents->dirs[0],"images");
      
    $success = true;

    //add any images to current gallery
    foreach($contents->files as $image) {
    
      //make sure file has a recognised extension
      if(!preg_match("/\.(".$this->config->recognised_extensions.")$/i",$image)) $image .= ".jpeg";
      
      $path = $this->config->pathto_galleries.$this->gallery->id."/".$image;
      $srcImage = $image;
      
      if(file_exists($path))
        switch($this->config->upload_overwrite) {
          case 1 : //overwrite
            $this->deleteImage($image);
            break;
          case 2 : //generate unique
            for($i=0;file_exists($path);$i++) {
              $pivot = strrpos($srcImage,".");
              $image = substr($srcImage, 0, $pivot).'-'.$i.substr($srcImage, $pivot,strlen($srcImage)-$pivot);
              $path = $this->config->pathto_galleries.$this->gallery->id."/".$image;
            }
            break;
          case 0 : //raise error
          default :
            $this->lastError = $this->i18n->_g("File already exists");
            $success = false;
            continue;
        }
      
      copy($wd.'/'.$srcImage,$path);
      
      $img = new sgImage();
      
      $img->filename = $image;
      $img->name = strtr(substr($image, strrpos($image,"/"), strrpos($image,".")-strlen($image)), "_", " ");
      list($img->width, $img->height, $img->type) = GetImageSize($path);
      $img->owner = $_SESSION["sgUser"]->username;
      
      $this->gallery->images[count($this->gallery->images)] = $img;
    }
    
    //add any directories as subgalleries, if allowed
    if($this->config->allow_dir_upload == 1 && !$this->isGuest($_SESSION["sgUser"]->username) 
    || $this->config->allow_dir_upload == 2 &&  $this->isAdmin($_SESSION["sgUser"]->username))
      foreach($contents->dirs as $gallery) {
        $path = $this->config->pathto_galleries.$this->gallery->id."/".$gallery;
  
        if(file_exists($path))
          switch($this->config->upload_overwrite) {
            case 1 : //overwrite
              $this->deleteGallery($this->gallery->id.'/'.$gallery);
              break;
            case 2 : //generate unique
              for($i=0;file_exists($path);$i++)
                $path = $this->config->pathto_galleries.$this->gallery->id."/".$gallery.'-'.$i;
              break;
            case 0 : //raise error
            default :
              $success = false;
              continue;
          }
  
        rename($wd.'/'.$gallery,$path);
      }
    
    //if images were added save metadata
    if(!empty($contents->files))
      $success &= $this->io->putGallery($this->gallery);
    
    //if subgalleries were added reload gallery data
    if(!empty($contents->dirs))
      $this->selectGallery();
    
    //remove temporary directory
    $this->rmdir_all($tmpdir);
    
    if(!$success)
      $this->lastError = $this->i18n->_g("Some archive contents could not be added");
      
    return $success;
  }
  
  /**
   * Saves image info to the database.
   *
   * @return boolean true on success; false otherwise
   */
  function saveImage()
  {
    $this->image->filename   = $this->prepareText($_REQUEST['image']);
    $this->image->thumbnail  = $this->prepareText($_REQUEST["sgThumbnail"]);
    $this->image->categories = $this->prepareText($_REQUEST["sgCategories"]);
    $this->image->name       = $this->prepareText($_REQUEST["sgImageName"]);
    $this->image->artist     = $this->prepareText($_REQUEST["sgArtistName"]);
    $this->image->email      = $this->prepareText($_REQUEST["sgArtistEmail"]);
    $this->image->location   = $this->prepareText($_REQUEST["sgLocation"]);
    $this->image->date       = $this->prepareText($_REQUEST["sgDate"]);
    $this->image->copyright  = $this->prepareText($_REQUEST["sgCopyright"]);
    $this->image->desc       = $this->prepareText($_REQUEST["sgImageDesc"],true);
    $this->image->camera     = $this->prepareText($_REQUEST["sgField01"]);
    $this->image->lens       = $this->prepareText($_REQUEST["sgField02"]);
    $this->image->film       = $this->prepareText($_REQUEST["sgField03"]);
    $this->image->darkroom   = $this->prepareText($_REQUEST["sgField04"]);
    $this->image->digital    = $this->prepareText($_REQUEST["sgField05"]);
    
    if($this->io->putGallery($this->gallery)) return true;
    
    $this->lastError = $this->i18n->_g("Could not save image information");
    return false;    
  }
  
  /**
   * Deletes an image from the current gallery.
   *
   * @param string the filename of the image to delete (optional)
   * @return boolean true on success; false otherwise
   */
  function deleteImage($image = null)
  {
    if($image === null)
      $image = $this->image->filename;
  
    for($i=0;$i<count($this->gallery->images);$i++)
      if($this->gallery->images[$i]->filename == $image)
        array_splice($this->gallery->images,$i,1);
    
    if(file_exists($this->config->pathto_galleries.$this->gallery->id."/".$image)
      //security check: make sure requested file is in galleries directory
      && $this->isSubPath($this->config->pathto_galleries,$this->config->pathto_galleries.$this->gallery->id."/".$image))
      unlink($this->config->pathto_galleries.$this->gallery->id."/".$image);
    
    if($this->io->putGallery($this->gallery)) {
      $this->image = null;
      return true;
    } else {
      $this->lastError = $this->i18n->_g("Could not delete image");
      return false;
    }
  }
  
  /**
   * Deletes the contents of the cache directory.
   *
   * @return boolean true on success; false otherwise
   */
  function purgeCache()
  {
    $dir = $this->getListing($this->config->pathto_cache,"all");
    
    $success = true;
    for($i=0;$i<count($dir->files);$i++) {
      $success &= unlink($dir->path.$dir->files[$i]);
    }
    
    return $success;
  }

}


?>
