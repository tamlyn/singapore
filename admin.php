<?php

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  admin.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>        *
 *                                                                     *
 *  This file is part of singapore v0.9.3                              *
 *                                                                     *
 *  singapore is free software; you can redistribute it and/or modify  *
 *  it under the terms of the GNU General Public License as published  *
 *  by the Free Software Foundation; either version 2 of the License,  *
 *  or (at your option) any later version.                             *
 *                                                                     *
 *  singapore is distributed in the hope that it will be useful,       *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty        *
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.            *
 *  See the GNU General Public License for more details.               *
 *                                                                     *
 *  You should have received a copy of the GNU General Public License  *
 *  along with this; if not, write to the Free Software Foundation,    *
 *  Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA      *
 \* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
 

//set session arg separator to be xml compliant
ini_set("arg_separator.output", "&amp;");

//start session
session_name("sgAdmin");
session_start();


//require config class
require_once "includes/class_configuration.php";
//create config object
$sgConfig = new sgConfiguration();


//include required files
require "includes/adminutils.php";
require "includes/frontend.php";
require "includes/utils.php";
require "includes/backend.php";


$pageTitle = "singapore admin";

//include header file
include $GLOBALS["sgConfig"]->pathto_header;

//if user is logged in show admin toolbar
if(sgIsLoggedIn()) sgShowAdminBar();

if(sgIsLoggedIn()) {
  if(isset($_REQUEST["action"])) {
    switch($_REQUEST["action"]) {
      case "login" :
        if(sgLogin()) sgShowAdminOptions();
        else {
          echo "<h1>login error</h1>\n";
          echo "<p>Sorry, unrecognised username or password.</p>\n";
          sgLoginForm();
        }
        break;
      case "logout" :
        if(sgLogout())
          echo "<h1>logged out</h1>\n<p><a href=\"index.php\">Return to galleries</a>.</p>\n";
        break;
      case "editpass" :
        echo "<h1>change password</h1>\n";
        echo "<p>Please choose a new password between 6 and 16 characters in length.</p>\n";
        sgEditPass();
        break;
      case "savepass" :
        sgSavePass();
        break;
      case "newgallery" :
        sgNewGallery();
        break;
      case "addgallery" :
        if(sgAddGallery($_REQUEST["gallery"]))
          sgEditGallery($_REQUEST["gallery"]);
        else 
          echo "<h1>error creating gallery</h1>\n<p>Gallery could not be created. ".
               "It may already exist or you may not have permission to create a gallery here.</p>";
        break;
      case "editgallery" :
        sgEditGallery($_REQUEST["gallery"]);
        break;
      case "savegallery" :
        sgSaveGallery($_REQUEST["gallery"]);
        break;
      case "deletegallery" :
        sgShowGalleryDeleteConfirmation($_REQUEST["gallery"]);
        break;
      case "deletegallery-confirmed" :
        if($_REQUEST["confirm"]=="OK") 
          if(sgDeleteGallery($_REQUEST["gallery"])) 
            echo "<h1>gallery deleted</h1>\n";
          else 
            echo "<h1>error deleting gallery</h1>\n<p>An error was encountered and the gallery was not deleted.</p>\n";
        else sgShowAdminOptions();
        break;
      case "changethumbnail" :
        sgChangeThumbnail($_REQUEST["gallery"]);
        break;
      case "savethumbnail" :
        if($_REQUEST["confirm"]=="OK") 
          if(sgSaveThumbnail($_REQUEST["gallery"], $_REQUEST["sgThumbName"])) 
            echo "<h1>thumbnail changed</h1>\n";
          else
            echo "<h1>error changing thumbnail</h1>\n";
        sgEditGallery($_REQUEST["gallery"]);
        break;
      case "newimage" :
        sgNewImage($_REQUEST["gallery"]);
        break;
      case "addimage" :
        if($image = sgAddImage($_REQUEST["gallery"])) sgEditImage($_REQUEST["gallery"], $image);
        else echo "<h1>error adding image</h1>\n <p>Image could not be added. ".
                  "It may already exist or you may not have permission to add an image here.</p>";
        break;
      case "editimage" :
        sgEditImage($_REQUEST["gallery"],$_REQUEST["image"]);
        break;
      case "saveimage" :
        sgSaveImage($_REQUEST["gallery"],$_REQUEST["image"]);
        break;
      case "deleteimage" :
        sgShowImageDeleteConfirmation($_REQUEST["gallery"],$_REQUEST["image"]);
        break;
      case "deleteimage-confirmed" :
        if($_REQUEST["confirm"]=="OK") 
          if(sgDeleteImage($_REQUEST["gallery"],$_REQUEST["image"]))
            echo "<h1>image deleted</h1>\n\n<ul>\n".
            "  <li><a href=\"index.php?gallery=$gallery&amp;image=$_REQUEST[sgNextImage]\">View next image</a></li>\n".
            "  <li><a href=\"index.php?gallery=$gallery&amp;image=$_REQUEST[sgPrevImage]\">View previous image</a></li>\n".
            "  <li><a href=\"index.php?gallery=$gallery\">View gallery</a></li>\n</ul>";
          else echo "<h1>error deleting image</h1>\n<p>An error was encountered and the image was not deleted.</p>\n";
        else sgShowAdminOptions();
        break;
      case "showgalleryhits" :
        sgShowGalleryHits(isset($_REQUEST["gallery"])?$_REQUEST["gallery"]:"",isset($_REQUEST["startat"])?$_REQUEST["startat"]:0);
        break;
      case "showimagehits" :
        sgShowImageHits(isset($_REQUEST["gallery"])?$_REQUEST["gallery"]:"",isset($_REQUEST["startat"])?$_REQUEST["startat"]:0);
        break;
      case "purgecache" :
        sgShowPurgeConfirmation();
        break;
      case "purgecache-confirmed" :
        if($_REQUEST["confirm"]=="OK") 
          if(sgPurgeCache()) echo "<h1>thumbnails purged</h1>\n<p><a href=\"admin.php\">Admin options</a></p>\n";
          else echo "<h1>error purging thumbnails</h1>\n<p>An error occurred. <a href=\"admin.php\">Admin options</a></p>\n";
        else sgShowAdminOptions();
        break;
    }
  } else sgShowAdminOptions();
} elseif(isset($_REQUEST["action"]) && $_REQUEST["action"]=="login") {
  if(sgLogin()) sgShowAdminOptions();
  else {
    echo "<h1>login error</h1>\n";
    echo "<p>Sorry, unrecognised username or password.</p>\n";
    sgLoginForm();
  }
} else {
  echo "<h1>admin login</h1>\n";
  sgLoginForm();
}

//include footer file
include $GLOBALS["sgConfig"]->pathto_footer;

?>
