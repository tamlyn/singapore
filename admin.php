<?php

/**
 * Admin interface file.
 *
 * Checks the selected 'action', checks user permissions, calls the appropriate 
 * methods and sets the required include file. Finally it includes the admin 
 * template's index file.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @package singapore
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: admin.php,v 1.28 2004/11/01 08:17:31 tamlyn Exp $
 */

//include main class
require_once "includes/singapore.class.php";
require_once "includes/admin.class.php";

//create the admin object
$sg = new sgAdmin();

//set session arg separator to be xml compliant
ini_set("arg_separator.output", "&amp;");

//start session
session_name($sg->config->session_name);
session_start();

//send content-type and character encoding header
header("Content-type: text/html; charset=".$sg->character_set);

//check if user is logged in
if($sg->isLoggedIn() || $sg->action == "login") 
  //choose which file to include and/or perform admin actions
  switch($sg->action) {
    case "addgallery" :
      $sg->selectGallery();
      if(!$sg->checkPermissions($sg->gallery,"add")) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "view";
      } elseif($sg->addGallery()) {
        $sg->selectGallery($sg->gallery->id."/".$_REQUEST["newgallery"]);
        $adminMessage = $sg->i18n->_g("Gallery added");
        $includeFile = "editgallery";
      } else {
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "newgallery";
      }
      break;
    case "addimage" :
      $sg->selectGallery();
      if(!$sg->checkPermissions($sg->gallery,"add")) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "view";
        break;
      } 
      switch($_REQUEST["sgLocationChoice"]) {
        case "remote" :
        case "single" :
          if($sg->addImage()) {
            $adminMessage = $sg->i18n->_g("Image added");
            $includeFile = "editimage";
          } else {
            $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
            $includeFile = "newimage";
          }
          break;
        case "multi" :
          if($sg->addMultipleImages()) {
            $adminMessage = $sg->i18n->_g("Archive contents added");
            $includeFile = "view";
          } else {
            $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
            $includeFile = "newimage";
          }
          break;
        default :
          $includeFile = "newimage";
          break;
      }
      break;
    case "changethumbnail" :
      $sg->selectGallery();
      if(!$sg->checkPermissions($sg->gallery,"edit")) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "view";
      } elseif(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==$sg->i18n->_g("confirm|OK")) {
        if($sg->saveGalleryThumbnail())
          $adminMessage = $sg->i18n->_g("Thumbnail changed");
        else
          $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "editgallery";
      } elseif(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==$sg->i18n->_g("confirm|Cancel")) {
        $includeFile = "editgallery";
      } else {
        $includeFile = "changethumbnail";
      }
      break;
    case "deletegallery" :
      $sg->selectGallery();
      if(!$sg->checkPermissions($sg->gallery,"delete")) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "view";
      } elseif(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==$sg->i18n->_g("confirm|OK") || (count($sg->gallery->images)==0 && count($sg->gallery->galleries)==0)) {
        if($sg->deleteGallery()) {
          $sg->selectGallery(rawurldecode($sg->gallery->parent));
          $adminMessage = $sg->i18n->_g("Gallery deleted");
        } else {
          $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        }
        $includeFile = "view";
      } elseif(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==$sg->i18n->_g("confirm|Cancel")) {
        $includeFile = "view";
      } else {
        $confirmTitle = $sg->i18n->_g("delete gallery");
        $confirmMessage = $sg->i18n->_g("Gallery %s is not empty.\nAre you sure you want to irretrievably delete it and all subgalleries and images it contains?", "<em>".$sg->gallery->name."</em>");
        $includeFile = "confirm";
      }
      break;
    case "deleteimage" :
      $sg->selectGallery();
      if(!$sg->checkPermissions($sg->gallery,"delete")) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "view";
      } elseif(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==$sg->i18n->_g("confirm|OK")) {
        if($sg->deleteImage())
          $adminMessage = $sg->i18n->_g("Image deleted");
        else
          $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "view";
      } elseif(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==$sg->i18n->_g("confirm|Cancel")) {
        $includeFile = "view";
      } else {
        $confirmTitle = $sg->i18n->_g("delete image");
        $confirmMessage = $sg->i18n->_g("Are you sure you want to irretrievably delete image %s from gallery %s?","<em>".$sg->imageName().$sg->imageByArtist()."</em>","<em>".$sg->gallery->name."</em>");
        $includeFile = "confirm";
      }
      break;
    case "deleteuser" :
      if(!$sg->isAdmin()) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "menu";
      } elseif(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==$sg->i18n->_g("confirm|OK")) {
        if($sg->deleteUser())
          $adminMessage = $sg->i18n->_g("User deleted");
        else
          $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "manageusers";
      } elseif(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==$sg->i18n->_g("confirm|Cancel")) {
        $includeFile = "manageusers";
      } else {
        $confirmTitle = $sg->i18n->_g("delete user");
        $confirmMessage = $sg->i18n->_g("Are you sure you want to permanently delete user %s?","<em>".$_REQUEST["user"]."</em>");
        $includeFile = "confirm";
      }
      break;
    case "editgallery" :
      $sg->selectGallery();
      if(!$sg->checkPermissions($sg->gallery,"edit")) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "view";
      } else
        $includeFile = "editgallery";
      break;
    case "editimage" :
      $sg->selectGallery();
      if(!$sg->checkPermissions($sg->gallery,"edit")) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "view";
      } else
        $includeFile = "editimage";
      break;
    case "editpass" :
      if($sg->isGuest()) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "menu";
      } else
        $includeFile = "editpass";
      break;
    case "editpermissions" :
      $sg->selectGallery();
      if(!$sg->isAdmin() && !$sg->isOwner($sg->gallery)) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "view";
      } else
        $includeFile = "editpermissions";
      break;
    case "editprofile" :
      if($sg->isGuest()) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "menu";
      } else
        $includeFile = "editprofile";
      break;
    case "edituser" :
      if(!$sg->isAdmin() && $_REQUEST["user"] != $_SESSION["sgUser"]->username || $sg->isGuest()) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "menu";
      } else
        $includeFile = "edituser";
      break;
    case "login" :
      if($sg->doLogin()) {
        $adminMessage = $sg->i18n->_g("Welcome to singapore admin!");
        $includeFile = "menu";
      } else {
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "login";
      }
      break;
    case "logout" :
      $sg->logout();
      $adminMessage = $sg->i18n->_g("Thank you and goodbye!");
      $includeFile = "login";
      break;
    case "manageusers" :
      if(!$sg->isAdmin()) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "menu";
      } else
        $includeFile = "manageusers";
      break;
    case "multigallery" :
    case "multiimage" :
      $adminMessage = "This feature is not yet implemented";
      $sg->selectGallery();
      $includeFile = "view";
      break;
    case "newgallery" :
      $sg->selectGallery();
      if(!$sg->checkPermissions($sg->gallery,"add")) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "view";
      } else
        $includeFile = "newgallery";
      break;
    case "newimage" :
      $sg->selectGallery();
      if(!$sg->checkPermissions($sg->gallery,"add")) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "view";
      } else
        $includeFile = "newimage";
      break;
    case "newuser" :
      if(!$sg->isAdmin()) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "menu";
      } elseif($sg->addUser())
        $includeFile = "edituser";
      else {
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "manageusers";
      }
      break;
    case "purgecache" :
      if(!$sg->isAdmin()) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "menu";
      } elseif(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==$sg->i18n->_g("confirm|OK")) {
        if($sg->purgeCache())
          $adminMessage = $sg->i18n->_g("Thumbnail cache purged");
        else
          $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "menu";
      } elseif(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==$sg->i18n->_g("confirm|Cancel")) {
        $includeFile = "menu";
      } else {
        $confirmTitle = $sg->i18n->_g("purge cached thumbnails");
        $dir = $sg->getListing($sg->config->pathto_cache,"all");
        $confirmMessage = $sg->i18n->_g("Are you sure you want to delete all %s cached thumbnails?",count($dir->files));
        $includeFile = "confirm";
      }
      break;
    case "reindex" :
      $sg->selectGallery();
      if(!$sg->checkPermissions($sg->gallery,"edit"))
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
      else {
        $imagesAdded = $sg->reindexGallery();
        $adminMessage = $sg->i18n->_g("Gallery re-indexed. %s images added.",$imagesAdded);
      }
      $includeFile = "view";
      break;
    case "savegallery" :
      $sg->selectGallery();
      if(!$sg->checkPermissions($sg->gallery,"edit")) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "view";
      } elseif($sg->saveGallery()) {
        $adminMessage = $sg->i18n->_g("Gallery info saved");
        $includeFile = "view";
      } else {
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "editgallery";
      }
      break;
    case "saveimage" :
      $sg->selectGallery();
      if(!$sg->checkPermissions($sg->gallery,"edit")) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "view";
      } elseif($sg->saveImage()) {
        $adminMessage = $sg->i18n->_g("Image info saved");
        $includeFile = "view";
      } else {
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "view";
      }
      break;
    case "savepass" :
      if($sg->isGuest()) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "menu";
      } elseif($sg->savePass()) {
        $adminMessage = $sg->i18n->_g("Password saved");
        $includeFile = "menu";
      } else {
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "editpass";
      }
      break;
    case "savepermissions" :
      $sg->selectGallery();
      if(!$sg->isAdmin() && !$sg->isOwner($sg->gallery)) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "view";
      } elseif($sg->savePermissions()) {
        $adminMessage = $sg->i18n->_g("Permissions saved");
        $includeFile = "view";
      } else {
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "editpermissions";
      }
      break;
    case "saveprofile" :
      if($_REQUEST["user"] != $_SESSION["sgUser"]->username || $sg->isGuest()) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "menu";
      } elseif($sg->saveUser()) {
        $adminMessage = $sg->i18n->_g("User info saved");
        $includeFile = "menu";
      } else {
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "editprofile";
      }
      break;
    case "saveuser" :
      if(!$sg->isAdmin()) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "menu";
      } elseif($sg->saveUser()) {
        $adminMessage = $sg->i18n->_g("User info saved");
        $includeFile = "manageusers";
      } else {
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "edituser";
      }
      break;
    case "showgalleryhits" :
      $sg->selectGallery();
      /*if(!$sg->checkPermissions($sg->gallery,"read")) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "menu";
      } else {*/
        $sg->loadGalleryHits();
        $includeFile = "galleryhits";
      //}
      break;
    case "showimagehits" :
      $sg->selectGallery();
      /*if(!$sg->checkPermissions($sg->gallery,"read")) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "menu";
      } else {*/
        $sg->loadImageHits();
        $includeFile = "imagehits";
      //}
      break;
    case "suspenduser" :
      if(!$sg->isAdmin()) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "menu";
      } elseif($sg->suspendUser())
        $adminMessage = $sg->i18n->_g("User info saved");
      else
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
      $includeFile = "manageusers";
      break;
    case "view" :
      $sg->selectGallery();
      /*if(!$sg->checkPermissions($sg->gallery,"read")) {
        $adminMessage = $sg->i18n->_g("You do not have permission to perform this operation.");
        $includeFile = "menu";
      } else*/
        $includeFile = "view";
      break;
    case "menu" :
    default :
      $includeFile = "menu";
  }
else //not logged in
  $includeFile = "login";

//pass control over to template
include $sg->config->pathto_admin_template."index.tpl.php";


?>
