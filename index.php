<?php

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  index.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>        *
 *                                                                     *
 *  This file is part of singapore v0.9.4a                             *
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

//only start session if session is already registered
if(isset($_REQUEST["sgAdmin"])) {
  //set session arg separator to be xml compliant
  ini_set("arg_separator.output", "&amp;");
  
  //start session
  session_name("sgAdmin");
  session_start();
}

//require config class
require_once "includes/class_configuration.php";
//create config object
$sgConfig = new sgConfiguration();


//include required files
require_once "includes/frontend.php";
require_once "includes/utils.php";
require_once "includes/backend.php";


//include header file
include $GLOBALS["sgConfig"]->pathto_header;

//if user is logged in show admin toolbar
if(sgIsLoggedIn()) sgShowAdminBar();

if(isset($_REQUEST["image"])) sgShowImage($_REQUEST["gallery"],$_REQUEST["image"]);
elseif(isset($_REQUEST["gallery"])) sgShowThumbnails($_REQUEST["gallery"],isset($_REQUEST["startat"])?$_REQUEST["startat"]:0);
else sgShowIndex(isset($_REQUEST["gallery"])?$_REQUEST["gallery"]:"",isset($_REQUEST["startat"])?$_REQUEST["startat"]:0);

//include footer file
include $GLOBALS["sgConfig"]->pathto_footer;

?>
