<?php

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  admin.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>        *
 *                                                                     *
 *  This file is part of singapore v0.9.2                              *
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

//include required files
require "includes/config.php";
require "includes/adminutils.php";
require "includes/frontend.php";
require "includes/utils.php";
require "includes/backend.php";

//include header file
include sgGetConfig("pathto_header");

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
        sgLogout();
        echo "<h1>logged out</h1>\n";
        echo "<p><a href=\"index.php\">Return to galleries</a>.</p>\n";
        break;
      case "editpass" :
        echo "<h1>change password</h1>\n";
        echo "<p>Please choose a new password between 6 and 16 characters in length.</p>\n";
        sgEditPass();
        break;
      case "savepass" :
        echo "<h1>change password</h1>\n";
        sgSavePass();
        break;
      case "newgallery" :
        sgEditGallery("");
        break;
      case "editgallery" :
        sgEditGallery($_REQUEST["gallery"]);
        break;
      case "savegallery" :
        sgSaveGallery($_REQUEST["gallery"]);
        break;
      case "newimage" :
        sgEditImage($_REQUEST["gallery"],"");
        break;
      case "editimage" :
        sgEditImage($_REQUEST["gallery"],$_REQUEST["image"]);
        break;
      case "saveimage" :
        sgSaveImage($_REQUEST["gallery"],$_REQUEST["image"]);
        break;
      case "showgalleryhits" :
        sgShowGalleryHits($_REQUEST["gallery"],isset($_REQUEST["startat"])?$_REQUEST["startat"]:0);
        break;
      case "showimagehits" :
        sgShowImageHits($_REQUEST["gallery"],isset($_REQUEST["startat"])?$_REQUEST["startat"]:0);
        break;
      case "confirmpurge" :
        sgShowPurgeConfirmation();
        break;
      case "dopurge" :
        if($_REQUEST["confirm"]=="OK") sgPurgeCache();
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
include sgGetConfig("pathto_footer");

?>
