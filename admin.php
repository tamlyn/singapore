<?php

/**
 * Admin interface file.
 *
 * Checks the selected 'action', calls the appropriate methods and sets the 
 * required include file. Finally it includes the admin template's index file.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @package singapore
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: admin.php,v 1.23 2004/04/11 14:45:06 tamlyn Exp $
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
    case "view" :
      $sg->selectGallery();
      $includeFile = "view";
      break;
    case "login" :
      if($sg->login()) {
        $adminMessage = $sg->i18n->_g("Welcome to singapore admin!");
        $includeFile = "menu";
      } else {
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "login";
      }
      break;
    case "logout" :
      if($sg->logout()) {
        $adminMessage = $sg->i18n->_g("Thank you and goodbye!");
        $includeFile = "loggedout";
      } else
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
      break;
    case "editpass" :
      $includeFile = "editpass";
      break;
    case "savepass" :
      if($sg->savePass())
        $adminMessage = $sg->i18n->_g("Password saved");
      else {
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "editpass";
      }
      break;
    case "newgallery" :
      $sg->selectGallery();
      $includeFile = "newgallery";
      break;
    case "addgallery" :
      $sg->selectGallery();
      if($sg->addGallery()) {
        $sg->selectGallery($sg->gallery->id."/".$_REQUEST["newgallery"]);
        $adminMessage = $sg->i18n->_g("Gallery added");
        $includeFile = "editgallery";
      } else {
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "newgallery";
      }
      break;
    case "editgallery" :
      $sg->selectGallery();
      $includeFile = "editgallery";
      break;
    case "savegallery" :
      $sg->selectGallery();
      if($sg->saveGallery()) {
        $adminMessage = $sg->i18n->_g("Gallery info saved");
        $includeFile = "view";
      } else {
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "editgallery";
      }
      break;
    case "deletegallery" :
      $sg->selectGallery();
      if(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==$sg->i18n->_g("confirm|OK") || (count($sg->gallery->images)==0 && count($sg->gallery->galleries)==0)) {
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
    case "changethumbnail" :
      $sg->selectGallery();
      if(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==$sg->i18n->_g("confirm|OK")) {
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
    case "newimage" :
      $sg->selectGallery();
      $includeFile = "newimage";
      break;
    case "addimage" :
      $sg->selectGallery();
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
    case "editimage" :
      $sg->selectGallery();
      $includeFile = "editimage";
      break;
    case "saveimage" :
      $sg->selectGallery();
      if($sg->saveImage()) {
        $adminMessage = $sg->i18n->_g("Image saved successfully");
        $includeFile = "view";
      } else {
        $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
        $includeFile = "view";
      }
      break;
    case "deleteimage" :
      $sg->selectGallery();
      if(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==$sg->i18n->_g("confirm|OK")) {
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
    case "showgalleryhits" :
      $sg->selectGallery();
      $sg->loadGalleryHits();
      $includeFile = "galleryhits";
      break;
    case "showimagehits" :
      $sg->selectGallery();
      $sg->loadImageHits();
      $includeFile = "imagehits";
      break;
    case "purgecache" :
      if(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==$sg->i18n->_g("confirm|OK")) {
        if($sg->purgeCache())
          $adminMessage = $sg->i18n->_g("Thumbnail cache purged");
        else
          $adminMessage = $sg->i18n->_g("An error occurred:")." ".$sg->getLastError();
      } elseif(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==$sg->i18n->_g("confirm|Cancel")) {
        $includeFile = "menu";
      } else {
        $confirmTitle = $sg->i18n->_g("purge cached thumbnails");
        $dir = $sg->getListing($sg->config->pathto_cache,"all");
        $confirmMessage = $sg->i18n->_g("Are you sure you want to delete all %s cached thumbnails?",count($dir->files));
        $includeFile = "confirm";
      }
      break;
    case "menu" :
    default :
      $includeFile = "menu";
  }
else //not logged in
  $includeFile = "login";

if(empty($includeFile)) $includeFile = "menu";

//pass control over to template
include $sg->config->pathto_admin_template."index.tpl.php";


?>
