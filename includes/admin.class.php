<?php 

/**
 * Class providing admin functions.
 * 
 * @package singapore
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003-2005 Tamlyn Rhodes
 * @version $Id: admin.class.php,v 1.46 2005/12/01 00:10:45 tamlyn Exp $
 */

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
  
  var $includeFile = "login";
  
  /**
   * Admin constructor. Doesn't call {@link Singapore} constructor.
   * @param string the path to the base singapore directory
   */
  function sgAdmin($basePath = "")
  {
    //import class definitions
    //io handler class included once config is loaded
    require_once $basePath."includes/item.class.php";
    require_once $basePath."includes/translator.class.php";
    require_once $basePath."includes/thumbnail.class.php";
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
      foreach($_FILES as $key => $nothing)
        $_FILES[$key]["tmp_name"] = addslashes($_FILES[$key]["tmp_name"]);
      $_FILES   = array_map(array("Singapore","arraystripslashes"), $_FILES);
    }
    
    $galleryId = isset($_REQUEST["gallery"]) ? $_REQUEST["gallery"] : ".";
    
    //load config from singapore root directory
    $this->config = sgConfig::getInstance();
    $this->config->loadConfig($basePath."singapore.ini");
    $this->config->loadConfig($basePath."secret.ini.php");
    
    //set runtime values
    $this->config->pathto_logs = $this->config->pathto_data_dir."logs/";
    $this->config->pathto_cache = $this->config->pathto_data_dir."cache/";
    $this->config->pathto_current_template = $this->config->pathto_templates.$this->config->default_template."/";
    $this->config->pathto_admin_template = $this->config->pathto_templates.$this->config->admin_template_name."/";
    
    //load config from admin template ini file (admin.ini) if present
    $this->config->loadConfig($basePath.$this->config->pathto_admin_template."admin.ini");
    
    $this->template = $this->config->default_template;
    
    //do not load gallery-specific ini files

    //set current language from request vars or config
    $this->language = isset($_REQUEST["lang"]) ? $_REQUEST["lang"] : $this->config->default_language;
    //read the language file
    $this->translator = Translator::getInstance($this->language);
    $this->translator->readLanguageFile($this->config->base_path.$this->config->pathto_locale."singapore.".$this->language.".pmo");
    $this->translator->readLanguageFile($this->config->base_path.$this->config->pathto_locale."singapore.admin.".$this->language.".pmo");
    
    //include IO handler class and create instance
    require_once $basePath."includes/io_".$this->config->io_handler.".class.php";
    $ioClassName = "sgIO_".$this->config->io_handler;
    $this->io = new $ioClassName($this->config);
    
    //set character set
    if(!empty($this->translator->languageStrings[0]["charset"]))
      $this->character_set = $this->translator->languageStrings[0]["charset"];
    else
      $this->character_set = $this->config->default_charset;
    
    //set action to perform
    if(empty($_REQUEST["action"])) $this->action = "menu";
    else $this->action = $_REQUEST["action"];
    
    //set page title
    $this->pageTitle = $this->config->gallery_name;
    
    //set root node of crumb line
    $holder = new sgGallery("", new stdClass);
    $holder->name = $this->config->gallery_name;
    $this->ancestors = array($holder);
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
    if($gallery != null) $ret .= "&amp;gallery=".$gallery;
    if($image != null)   $ret .= "&amp;image=".$image;
    if($startat != null) $ret .= "&amp;startat=".$startat;
    if($extra != null)   $ret .= $extra;
    if($this->language != $this->config->default_language) $ret .= '&amp;'.$this->config->url_lang.'='.$this->language;
    if($this->template != $this->config->default_template) $ret .= '&amp;'.$this->config->url_template.'='.$this->template;
    
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
    elseif(is_writable("/windows/temp")) return "/windows/temp";
    elseif(is_writable("/winnt/temp")) return "/winnt/temp";
    else return null;
  }
  
  function getMaxHits($array) 
  {
    $max = 0;
    foreach($array as $obj)
      if($obj->hits > $max)
        $max = $obj->hits;
    return $max;
  }
  
  /**
   * Returns true if the current admin action has been confirmed (i.e. by clicking OK)
   */
  function actionConfirmed()
  {
    return isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"] == $this->translator->_g("confirm|OK");
  }
  
  /**
   * Returns true if the current admin action has been cancelled (i.e. by clicking Cancel)
   */
  function actionCancelled()
  {
    return isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"] == $this->translator->_g("confirm|Cancel");
  }
  
  /**
   * Checks request variables for action to perform, checks user permissions, 
   * performs action and sets file to include.
   */
  function doAction()
  {
    //check if user is logged in
    if(!$this->isLoggedIn() && $this->action != "login")
      return;
   
    //choose which file to include and/or perform admin actions
    switch($this->action) {
      case "addgallery" :
        $this->selectGallery();
        if(!$this->checkPermissions($this->gallery,"add")) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "view";
        } elseif($this->addGallery()) {
          $this->selectGallery($this->gallery->id."/".$_REQUEST["newgallery"]);
          $this->adminMessage = $this->translator->_g("Gallery added");
          $this->includeFile = "editgallery";
        } else {
          $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
          $this->includeFile = "newgallery";
        }
        break;
      case "addimage" :
        $this->selectGallery();
        if(!$this->checkPermissions($this->gallery,"add")) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "view";
          break;
        } 
        switch($_REQUEST["sgLocationChoice"]) {
          case "remote" :
          case "single" :
            if($this->addImage()) {
              $this->adminMessage = $this->translator->_g("Image added");
              $this->includeFile = "editimage";
            } else {
              $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
              $this->includeFile = "newimage";
            }
            break;
          case "multi" :
            if($this->addMultipleImages()) {
              $this->adminMessage = $this->translator->_g("Archive contents added");
              $this->includeFile = "view";
            } else {
              $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
              $this->includeFile = "newimage";
            }
            break;
          default :
            $this->includeFile = "newimage";
            break;
        }
        break;
      case "changethumbnail" :
        $this->selectGallery();
        if(!$this->checkPermissions($this->gallery,"edit")) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "view";
        } elseif($this->actionConfirmed()) {
          if($this->saveGalleryThumbnail())
            $this->adminMessage = $this->translator->_g("Thumbnail changed");
          else
            $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
          $this->includeFile = "editgallery";
        } elseif($this->actionCancelled()) {
          $this->includeFile = "editgallery";
        } else {
          $this->includeFile = "changethumbnail";
        }
        break;
      case "deletegallery" :
        $this->selectGallery();
        if(!$this->checkPermissions($this->gallery,"delete")) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "view";
        } elseif($this->actionConfirmed()) {
          if($this->deleteGallery()) {
            $this->selectGallery($this->ancestors[0]->id);
            $this->adminMessage = $this->translator->_g("Gallery deleted");
          } else {
            $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
          }
          $this->includeFile = "view";
        } elseif($this->actionCancelled()) {
          $this->includeFile = "view";
        } else {
          $GLOBALS["confirmTitle"] = $this->translator->_g("delete gallery");
          $GLOBALS["confirmMessage"] = $this->translator->_g("Gallery %s is not empty.\nAre you sure you want to irretrievably delete it and all subgalleries and images it contains?", "<em>".$this->gallery->name."</em>");
          $this->includeFile = "confirm";
        }
        break;
      case "deleteimage" :
        $this->selectGallery();
        if(!$this->checkPermissions($this->gallery,"delete")) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "view";
        } elseif($this->actionConfirmed()) {
          if($this->deleteImage())
            $this->adminMessage = $this->translator->_g("Image deleted");
          else
            $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
          $this->includeFile = "view";
        } elseif($this->actionCancelled()) {
          $this->includeFile = "view";
        } else {
          $GLOBALS["confirmTitle"] = $this->translator->_g("delete image");
          $GLOBALS["confirmMessage"] = $this->translator->_g("Are you sure you want to irretrievably delete image %s from gallery %s?","<em>".$this->image->name().$this->image->byArtistText()."</em>","<em>".$this->gallery->name()."</em>");
          $this->includeFile = "confirm";
        }
        break;
      case "deleteuser" :
        if(!$this->user->isAdmin()) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "menu";
        } elseif($this->actionConfirmed()) {
          if($this->deleteUser())
            $this->adminMessage = $this->translator->_g("User deleted");
          else
            $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
          $this->includeFile = "manageusers";
        } elseif($this->actionCancelled()) {
          $this->includeFile = "manageusers";
        } else {
          $GLOBALS["confirmTitle"] = $this->translator->_g("delete user");
          $GLOBALS["confirmMessage"] = $this->translator->_g("Are you sure you want to permanently delete user %s?","<em>".$_REQUEST["user"]."</em>");
          $this->includeFile = "confirm";
        }
        break;
      case "editgallery" :
        $this->selectGallery();
        if(!$this->checkPermissions($this->gallery,"edit")) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "view";
        } else
          $this->includeFile = "editgallery";
        break;
      case "editimage" :
        $this->selectGallery();
        if(!$this->checkPermissions($this->gallery,"edit")) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "view";
        } else
          $this->includeFile = "editimage";
        break;
      case "editpass" :
        if($this->user->isGuest()) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "menu";
        } else
          $this->includeFile = "editpass";
        break;
      case "editpermissions" :
        $this->selectGallery();
        if(!$this->user->isAdmin() && !$this->user->isOwner($this->gallery)) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "view";
        } else
          $this->includeFile = "editpermissions";
        break;
      case "editprofile" :
        if($this->user->isGuest()) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "menu";
        } else
          $this->includeFile = "editprofile";
        break;
      case "edituser" :
        if(!$this->user->isAdmin() && $_REQUEST["user"] != $this->user->username || $this->user->isGuest()) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "menu";
        } else
          $this->includeFile = "edituser";
        break;
      case "login" :
        if($this->doLogin()) {
          $this->adminMessage = $this->translator->_g("Welcome to singapore admin!");
          $this->includeFile = "menu";
        } else {
          $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
          $this->includeFile = "login";
        }
        break;
      case "logout" :
        $this->logout();
        $this->adminMessage = $this->translator->_g("Thank you and goodbye!");
        $this->includeFile = "login";
        break;
      case "manageusers" :
        if(!$this->user->isAdmin()) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "menu";
        } else
          $this->includeFile = "manageusers";
        break;
      case "multi" :
        $this->selectGallery();
        if(!isset($_REQUEST["sgGalleries"]) && !isset($_REQUEST["sgImages"])) {
          $this->adminMessage = $this->translator->_g("Please select one or more items");
          $this->includeFile = "view";
        } elseif($_REQUEST["subaction"]==$this->translator->_g("Copy or move")) {
          $this->includeFile = "multimove";
        } elseif($_REQUEST["subaction"]==$this->translator->_g("Delete")) {
          if($this->actionConfirmed()) {
            if(!$this->checkPermissions($this->gallery,"delete")) {
              $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
            } else {
              if(isset($_REQUEST["sgImages"])) {
                $success = $this->deleteMultipleImages();
                $successMessage = $this->translator->_g("%s images deleted.", $success);
              } else {
                $success = $this->deleteMultipleGalleries();
                $successMessage = $this->translator->_g("%s galleries deleted.", $success);
              }
              if($success)
                $this->adminMessage = $successMessage;
              else
                $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
            }
            $this->includeFile = "view";
          } elseif($this->actionCancelled()) {
            $this->includeFile = "view";
          } else {
            if(isset($_REQUEST["sgImages"])) {
              $GLOBALS["confirmTitle"] = $this->translator->_g("Delete Images");
              $GLOBALS["confirmMessage"] = $this->translator->_g("Are you sure you want to permanently delete %s images?",count($_REQUEST["sgImages"])); 
            } else{
              $GLOBALS["confirmTitle"] = $this->translator->_g("Delete Galleries");
              $GLOBALS["confirmMessage"] = $this->translator->_g("Are you sure you want to permanently delete %s galleries?",count($_REQUEST["sgGalleries"]));
            }
            $this->includeFile = "confirm";
          }
        } elseif($_REQUEST["subaction"]==$this->translator->_g("Re-index")) {
          $this->adminMessage = "This feature is not yet implemented";
          $this->includeFile = "view";
        }
        break;
      case "multimove" :
        $this->selectGallery();
        if($this->actionConfirmed()) {
          $this->adminMessage = "not moved";
        } 
        $this->includeFile = "view";
        break;
      case "newgallery" :
        $this->selectGallery();
        if(!$this->checkPermissions($this->gallery,"add")) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "view";
        } else
          $this->includeFile = "newgallery";
        break;
      case "newimage" :
        $this->selectGallery();
        if(!$this->checkPermissions($this->gallery,"add")) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "view";
        } else
          $this->includeFile = "newimage";
        break;
      case "newuser" :
        if(!$this->user->isAdmin()) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "menu";
        } elseif($this->addUser())
          $this->includeFile = "edituser";
        else {
          $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
          $this->includeFile = "manageusers";
        }
        break;
      case "purgecache" :
        if(!$this->user->isAdmin()) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "menu";
        } elseif($this->actionConfirmed()) {
          if($this->purgeCache())
            $this->adminMessage = $this->translator->_g("Thumbnail cache purged");
          else
            $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
          $this->includeFile = "menu";
        } elseif($this->actionCancelled()) {
          $this->includeFile = "menu";
        } else {
          $dir = $this->getListing($this->config->pathto_cache,"all");
          $GLOBALS["confirmTitle"] = $this->translator->_g("purge cached thumbnails");
          $GLOBALS["confirmMessage"] = $this->translator->_g("Are you sure you want to delete all %s cached thumbnails?",count($dir->files));
          $this->includeFile = "confirm";
        }
        break;
      case "reindex" :
        $this->selectGallery();
        if(!$this->checkPermissions($this->gallery,"edit"))
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
        else {
          $imagesAdded = $this->reindexGallery();
          $this->adminMessage = $this->translator->_g("Gallery re-indexed. %s images added.",$imagesAdded);
        }
        $this->includeFile = "view";
        break;
      case "savegallery" :
        $this->selectGallery();
        if(!$this->checkPermissions($this->gallery,"edit")) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "view";
        } elseif($this->saveGallery()) {
          $this->adminMessage = $this->translator->_g("Gallery info saved");
          $this->includeFile = "view";
        } else {
          $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
          $this->includeFile = "editgallery";
        }
        break;
      case "saveimage" :
        $this->selectGallery();
        if(!$this->checkPermissions($this->gallery,"edit")) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "view";
        } elseif($this->saveImage()) {
          $this->adminMessage = $this->translator->_g("Image info saved");
          $this->includeFile = "view";
        } else {
          $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
          $this->includeFile = "view";
        }
        break;
      case "savepass" :
        if($this->user->isGuest()) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "menu";
        } elseif($this->savePass()) {
          $this->adminMessage = $this->translator->_g("Password saved");
          $this->includeFile = "menu";
        } else {
          $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
          $this->includeFile = "editpass";
        }
        break;
      case "savepermissions" :
        $this->selectGallery();
        if(!$this->user->isAdmin() && !$this->user->isOwner($this->gallery)) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "view";
        } elseif($this->savePermissions()) {
          $this->adminMessage = $this->translator->_g("Permissions saved");
          $this->includeFile = "view";
        } else {
          $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
          $this->includeFile = "editpermissions";
        }
        break;
      case "saveprofile" :
        if($_REQUEST["user"] != $this->user->username || $this->user->isGuest()) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "menu";
        } elseif($this->saveUser()) {
          $this->adminMessage = $this->translator->_g("User info saved");
          $this->includeFile = "menu";
        } else {
          $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
          $this->includeFile = "editprofile";
        }
        break;
      case "saveuser" :
        if(!$this->user->isAdmin()) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "menu";
        } elseif($this->saveUser()) {
          $this->adminMessage = $this->translator->_g("User info saved");
          $this->includeFile = "manageusers";
        } else {
          $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
          $this->includeFile = "edituser";
        }
        break;
      case "showgalleryhits" :
        $this->selectGallery();
        /*if(!$this->checkPermissions($this->gallery,"read")) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "menu";
        } else {*/
          $this->includeFile = "galleryhits";
        //}
        break;
      case "showimagehits" :
        $this->selectGallery();
        /*if(!$this->checkPermissions($this->gallery,"read")) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "menu";
        } else {*/
          $this->includeFile = "imagehits";
        //}
        break;
      case "suspenduser" :
        if(!$this->user->isAdmin()) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "menu";
        } elseif($this->suspendUser())
          $this->adminMessage = $this->translator->_g("User info saved");
        else
          $this->adminMessage = $this->translator->_g("An error occurred:")." ".$this->getLastError();
        $this->includeFile = "manageusers";
        break;
      case "view" :
        $this->selectGallery();
        /*if(!$this->checkPermissions($this->gallery,"read")) {
          $this->adminMessage = $this->translator->_g("You do not have permission to perform this operation.");
          $this->includeFile = "menu";
        } else*/
          $this->includeFile = "view";
        break;
      case "menu" :
      default :
        $this->includeFile = "menu";
    }
  }
  
  function &allGalleriesArray()
  {
    $root =& $this->io->getGallery(".", new stdClass, 100);
    return $this->allGalleriesRecurse($root);
  }
  
  function &allGalleriesRecurse(&$gal)
  {
    if($gal->hasChildGalleries()) {
      $galArray = array();
      foreach($gal->galleries as $child)
        $galArray = array_merge($galArray, $this->allGalleriesRecurse($child));
      array_unshift($galArray, $gal);
      return $galArray;
    } else 
      return array($gal);
    
  }
  
  
  
  /**
   * Returns a two-dimensional array of links for the admin bar.
   * 
   * @returns string
   */
  function adminLinksArray()
  {
    if(!$this->isLoggedIn()) return array(0 => array($this->translator->_g("admin bar|Back to galleries") => "."));
    
    $ret[0][$this->translator->_g("admin bar|Admin")] = $this->formatAdminURL("menu");
    $ret[0][$this->translator->_g("admin bar|Galleries")] = $this->formatAdminURL("view", isset($this->gallery) ? $this->gallery->idEncoded() : null);
    $ret[0][$this->translator->_g("admin bar|Log out")] = $this->formatAdminURL("logout");
    if($this->isGalleryPage() || $this->isAlbumPage() || $this->isImagePage()) {
      $ret[1][$this->translator->_g("admin bar|Edit gallery")] = $this->formatAdminURL("editgallery",$this->gallery->idEncoded());
      $ret[1][$this->translator->_g("admin bar|Access control")] = $this->formatAdminURL("editpermissions",$this->gallery->idEncoded());
      $ret[1][$this->translator->_g("admin bar|Delete gallery")] = $this->formatAdminURL("deletegallery",$this->gallery->idEncoded());
      $ret[1][$this->translator->_g("admin bar|New subgallery")] = $this->formatAdminURL("newgallery",$this->gallery->idEncoded());
      $ret[1][$this->translator->_g("admin bar|Re-index gallery")] = $this->formatAdminURL("reindex",$this->gallery->idEncoded());
      if($this->isImagePage()) {
        $ret[2][$this->translator->_g("admin bar|Edit image")] = $this->formatAdminURL("editimage",$this->gallery->idEncoded(),$this->image->id);
        $ret[2][$this->translator->_g("admin bar|Delete image")] = $this->formatAdminURL("deleteimage",$this->gallery->idEncoded(),$this->image->id);
      }
      $ret[2][$this->translator->_g("admin bar|New image")] = $this->formatAdminURL("newimage",$this->gallery->idEncoded());
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
              else $this->lastError = $this->translator->_g("Could not save user info");
            }
            else 
              $this->lastError = $this->translator->_g("New password must be between 6 and 16 characters long.");
          else 
            $this->lastError = $this->translator->_g("The new passwords you entered do not match.");
        else 
          $this->lastError = $this->translator->_g("The current password you entered does not match the one in the database.");
      }
    
    if(!$found) $this->lastError = $this->translator->_g("The username specified was not found in the database.");
    
    //some sort of error occurred so:
    return false;
  }
  
  
  /**
   * Attempts to log a registered user into admin.
   * 
   * @return boolean true on success; false otherwise
   */
  function doLogin()
  {
    if(!empty($_POST["sgUsername"]) && !empty($_POST["sgPassword"])) {
      if($this->loadUser($_POST["sgUsername"]) && md5($_POST["sgPassword"]) == $this->user->userpass){
        if($this->user->permissions & SG_SUSPENDED) {
          $this->logout();
          $this->lastError = $this->translator->_g("Your account has been suspended");
          return false;
        } else {
          $_SESSION["sgUser"]["username"]  = $this->user->username;
          $_SESSION["sgUser"]["ip"]        = $_SERVER["REMOTE_ADDR"];
          $_SESSION["sgUser"]["loginTime"] = time();
          return true;
        }
      }
      $this->logout();
      $this->lastError = $this->translator->_g("Username and/or password incorrect");
      return false;
    }
    $this->lastError = $this->translator->_g("You must enter a username and password");
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
  function checkPermissions($obj, $action, $gallery = null, $image = null, $ancestor = 0)
  {
    if($this->user->isAdmin() || $this->user->isOwner($obj))// || (!$this->user->isGuest() && $obj->owner == "__nobody__"))
      return true;
    
    //print_r($obj);
    
    switch($action) {
      case "read" :
        if(($obj->permissions & SG_IHR_READ) == SG_IHR_READ)
          if($this->galleryIsRoot($ancestor))
            return false;
          else 
            return $this->checkPermissions($this->ancestors[$ancestor+1], $action, $gallery, $image, $ancestor+1);
        else
          return $obj->permissions & SG_WLD_READ
             || ($this->isInGroup($this->user->groups, $obj->groups) && $obj->permissions & SG_GRP_READ);
      case "edit" :
        if(($obj->permissions & SG_IHR_EDIT) == SG_IHR_EDIT)
          if($this->galleryIsRoot($ancestor))
            return false;
          else 
            return $this->checkPermissions($obj, $action, $gallery, $image, ++$ancestor);
        else
          return $obj->permissions & SG_WLD_EDIT 
             || ($this->isInGroup($this->user->groups, $obj->groups) && $obj->permissions & SG_GRP_EDIT);
      case "add" :
        return $obj->permissions & SG_WLD_ADD 
           || ($this->isInGroup($this->user->groups, $obj->groups) && $obj->permissions & SG_GRP_ADD);
      case "delete" :
        return $obj->permissions & SG_WLD_DELETE 
           || ($this->isInGroup($this->user->groups, $obj->groups) && $obj->permissions & SG_GRP_DELETE);
      default :
        return false;
    }
  }
  
  function savePermissions()
  {
    $obj =& $this->gallery;
    
    $perms = 0;
    
    switch($_POST["sgRead"]) {
      case "inherit" : $perms |= SG_IHR_READ; break;
      case "group"   : $perms |= SG_GRP_READ; break;
      case "world"   : $perms |= SG_WLD_READ; break;
      case "owner"   : break;
    }
        
    switch($_POST["sgEdit"]) {
      case "inherit" : $perms |= SG_IHR_EDIT; break;
      case "group"   : $perms |= SG_GRP_EDIT; break;
      case "world"   : $perms |= SG_WLD_EDIT; break;
      case "owner"   : break;
    }
        
    switch($_POST["sgAdd"]) {
      case "inherit" : $perms |= SG_IHR_ADD; break;
      case "group"   : $perms |= SG_GRP_ADD; break;
      case "world"   : $perms |= SG_WLD_ADD; break;
      case "owner"   : break;
    }
        
    switch($_POST["sgDelete"]) {
      case "inherit" : $perms |= SG_IHR_DELETE; break;
      case "group"   : $perms |= SG_GRP_DELETE; break;
      case "world"   : $perms |= SG_WLD_DELETE; break;
      case "owner"   : break;
    }
        
    $obj->permissions |= $perms;
    $obj->permissions &= $perms;
    
    if($this->user->isAdmin() || $this->user->isOwner($obj));
      $obj->groups = $_POST["sgGroups"];
    if($this->user->isAdmin())
      $obj->owner = $_POST["sgOwner"];
    
    if($this->io->putGallery($this->gallery))
      return true;
    
    $this->lastError = $this->translator->_g("Could not save gallery info");
    return false;
  }
  
  /**
   * Creates a new user.
   * 
   * @return bool true on success; false otherwise
   */
  function addUser()
  {
    $users = $this->io->getUsers();
    foreach($users as $usr)
      if($usr->username == $_REQUEST["user"]) {
        $this->lastError = $this->translator->_g("Username already exists");
        return false;
      }
    
    if(!preg_match("/^[a-zA-Z0-9_]{3,}$/",$_REQUEST["user"])) {
      $this->lastError = $this->translator->_g("Username must be at least 3 characters long and contain only alphanumeric characters");
      return false;
    }
    
    $users[count($users)] = new sgUser($_REQUEST["user"], md5("password"));
    
    if($this->io->putUsers($users))
      return true;
    
    $this->lastError = $this->translator->_g("Could not save user info");
    return true;
  }
  
  /**
   * Deletes a user.
   * 
   * @return bool true on success; false otherwise
   */
  function deleteUser($username = null)
  {
    if($username == null)
      $username = $_REQUEST["user"];
      
    if($username == "admin" || $username == "guest") {
      $this->lastError = $this->translator->_g("Cannot delete built in accounts");
      return false;
    }
      
    $users = $this->io->getUsers();
    foreach($users as $i => $usr)
      if($usr->username == $username) {
        
        //delete user at offset $i from $users
        array_splice($users,$i,1);
        
        if($this->io->putUsers($users))
          return true;
    
        $this->lastError = $this->translator->_g("Could not save user info");
        return false;
      }
    
    $this->lastError = $this->translator->_g("Username not recognised");
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
        if($this->user->isAdmin() && $_REQUEST["action"] == "saveuser") {
          $users[$i]->groups = $this->prepareText($_REQUEST["sgGroups"]);
          $users[$i]->permissions = ($_REQUEST["sgType"] == "admin") ? $users[$i]->permissions | SG_ADMIN : $users[$i]->permissions & ~SG_ADMIN;
          if(isset($_REQUEST["sgPassword"]) && $_REQUEST["sgPassword"] != "**********")
            $users[$i]->userpass = md5($_REQUEST["sgPassword"]);
        }
        if($this->io->putUsers($users))
          return true;
        $this->lastError = $this->translator->_g("Could not save user info");
        return false;
      }
    $this->lastError = $this->translator->_g("Username not recognised");
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
        $this->lastError = $this->translator->_g("Could not save user info");
        return false;
      }
    $this->lastError = $this->translator->_g("Username not recognised");
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
    $dir = Singapore::getListing($this->config->pathto_galleries.$this->gallery->id,$this->config->recognised_extensions);
    for($i=0; $i<count($dir->files); $i++) {
      $found = false;
      for($j=0; $j<count($this->gallery->images); $j++)
        if($dir->files[$i] == $this->gallery->images[$j]->id) {
          $found = true;
          break;
        }
      if(!$found) {
        $this->gallery->images[$j] = new sgImage($dir->files[$i], $this->gallery, $this->config);
        $this->gallery->images[$j]->name = $dir->files[$i];
        list(
          $this->gallery->images[$j]->width, 
          $this->gallery->images[$j]->height, 
          $this->gallery->images[$j]->type
        ) = GetImageSize($this->config->pathto_galleries.$this->gallery->id."/".$this->gallery->images[$j]->id);
        $imagesAdded++;
      }
    }
    
    if($this->io->putGallery($this->gallery))
      return $imagesAdded;
      
    $this->lastError = $this->translator->_g("Could not save gallery info");
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
    
    //fail if directory already exists
    if(file_exists($path)) {
      $this->lastError = $this->translator->_g("Gallery already exists");
      return false;
    }
    
    //create directory or fail
    if(!Singapore::mkdir($path)) {
      $this->lastError = $this->translator->_g("Could not create directory");
      return false;
    }
    
    //explicitly set permissions on gallery directory
    @chmod($path, octdec($this->config->directory_mode));
    
    $gal = new sgGallery($newGalleryId, $this->gallery, $this->config);
    $gal->name = $_REQUEST["newgallery"];
    
    //set full permissions on guest-created objects
    if($this->user->isGuest())
      $gal->permissions = SG_GRP_READ | SG_GRP_EDIT | SG_GRP_ADD | SG_GRP_DELETE
                        | SG_WLD_READ | SG_WLD_EDIT | SG_WLD_ADD | SG_WLD_DELETE;
    else
      $gal->owner = $this->user->username;
    
    //save gallery metadata
    if($this->io->putGallery($gal))
      return true;
    
    $this->lastError = $this->translator->_g("Could not save gallery info");
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
      
    $this->lastError = $this->translator->_g("Could not save gallery info");
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
      $this->lastError = $this->translator->_g("Object not found");
      return false;
    }
  
    //check that the gallery to delete is not the top level directory
    if(realpath($this->config->pathto_galleries.$galleryId) == realpath($this->config->pathto_galleries)) {
      $this->lastError = $this->translator->_g("Cannot delete the top level directory");
      return false;
    }
    
    //remove the offending directory and all contained therein
    return $this->rmdir_all($this->config->pathto_galleries.$galleryId);
  }
  
  /**
   * Deletes several galleries from the current gallery.
   *
   * @return int|false  number of galleries deleted on success; false otherwise
   */
  function deleteMultipleGalleries() {
    $success = true;
    $deleted = 0;
    foreach($_REQUEST["sgGalleries"] as $gallery => $val) {
      $success &= $this->deleteGallery($gallery);
      $deleted += 1;
    }
    
    //reload gallery data if we deleted any
    if($deleted)
      $this->selectGallery();
    
    if(!$success) $this->lastError = $this->translator->_g("One or more galleries could not be deleted");
    return $success ? $deleted : false;
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
            $this->lastError = $this->translator->_g("File already exists");
            return false;
        }
      
      if(!move_uploaded_file($_FILES["sgImageFile"]["tmp_name"],$path)) {
        $this->lastError = $this->translator->_g("Could not upload file"); 
        return false;
      }
      
      // try to change file-permissions
      @chmod($path, octdec($this->config->file_mode));
      
    }
    
    $img = new sgImage($image, $this->gallery, $this->config);
    
    $img->name = strtr(substr($image, strrpos($image,"/"), strrpos($image,".")-strlen($image)), "_", " ");
    list($img->width, $img->height, $img->type) = GetImageSize($path);
    $img->owner = $this->user->username;
    
    $this->gallery->images[count($this->gallery->images)] = $img;
    
    if($this->io->putGallery($this->gallery)) {
      $this->selectImage($image);
      return true;
    }
    
    $this->lastError = $this->translator->_g("Could not add image to gallery");
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
      $this->lastError = $this->translator->_g("Could not find temporary storage space"); 
      return false;
    }
    
    //create new temp directory in system temp dir but stop after 100 attempts
    while(!Singapore::mkdir($tmpdir = $systmpdir."/".uniqid("sg")) && $tries++<100);
    
    $archive = $_FILES["sgArchiveFile"]["tmp_name"];
  
    if(!is_uploaded_file($archive)) {
      $this->lastError = $this->translator->_g("Could not upload file"); 
      return false;
    }
    
    //decompress archive to temp
    $cmd  = escapeshellcmd($this->config->pathto_unzip);
    $cmd .= ' -d "'.escapeshellcmd(realpath($tmpdir));
    $cmd .= '" "'.escapeshellcmd(realpath($archive)).'"';
    
    if(!exec($cmd)) {
      $this->lastError = $this->translator->_g("Could not decompress archive"); 
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
            $this->lastError = $this->translator->_g("File already exists");
            $success = false;
            continue;
        }
      
      copy($wd.'/'.$srcImage,$path);
      
      // try to change file-permissions
      @chmod($path, octdec($this->config->file_mode));
      
      $img = new sgImage($image, $this->gallery, $this->config);
      
      $img->name = strtr(substr($image, strrpos($image,"/"), strrpos($image,".")-strlen($image)), "_", " ");
      list($img->width, $img->height, $img->type) = GetImageSize($path);
      $img->owner = $this->user->username;
      
      $this->gallery->images[] = $img;
    }
    
    //add any directories as subgalleries, if allowed
    if($this->config->allow_dir_upload == 1 && !$this->user->isGuest() 
    || $this->config->allow_dir_upload == 2 &&  $this->user->isAdmin())
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
        
        //change directory permissions (but not contents)
        @chmod($path, octdec($this->config->directory_mode));
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
      $this->lastError = $this->translator->_g("Some archive contents could not be added");
      
    return $success;
  }
  
  /**
   * Saves image info to the database.
   *
   * @return boolean true on success; false otherwise
   */
  function saveImage()
  {
    $this->image->id   = $this->prepareText($_REQUEST['image']);
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
    
    $this->lastError = $this->translator->_g("Could not save image information");
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
      $image = $this->image->id;
  
    for($i=0;$i<count($this->gallery->images);$i++)
      if($this->gallery->images[$i]->id == $image)
        array_splice($this->gallery->images,$i,1);
    
    if(file_exists($this->config->pathto_galleries.$this->gallery->id."/".$image)
      //security check: make sure requested file is in galleries directory
      && $this->isSubPath($this->config->pathto_galleries,$this->config->pathto_galleries.$this->gallery->id."/".$image))
      unlink($this->config->pathto_galleries.$this->gallery->id."/".$image);
    
    if($this->io->putGallery($this->gallery)) {
      $this->image = null;
      return true;
    } else {
      $this->lastError = $this->translator->_g("Could not delete image");
      return false;
    }
  }
  
  /**
   * Deletes several images from the current gallery.
   *
   * @return int|false  number of images deleted on success; false otherwise
   */
  function deleteMultipleImages() {
    $success = true;
    $deleted = 0;
    foreach($_REQUEST["sgImages"] as $image => $val) {
      $success &= $this->deleteImage($image);
      $deleted += 1;
    }
    if(!$success) $this->lastError = $this->translator->_g("One or more images could not be deleted");
    return $success ? $deleted : false;
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
