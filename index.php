<?php

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  index.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>        *
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

//only start session if session is already registered
if(isset($_REQUEST["sgAdmin"])) {
  //set session arg separator to be xml compliant
  ini_set("arg_separator.output", "&amp;");
  
  //start session
  session_name("sgAdmin");
  session_start();
}

//include required files
require "includes/config.php";
require "includes/frontend.php";
require "includes/utils.php";
require "includes/backend.php";

//include header file
include sgGetConfig("pathto_header");

if(isset($_REQUEST["image"])) sgShowImage($_REQUEST["gallery"],$_REQUEST["image"]);
elseif(isset($_REQUEST["gallery"])) sgShowThumbnails($_REQUEST["gallery"],isset($_REQUEST["startat"])?$_REQUEST["startat"]:0);
else {
  //echo("<h1>singapore</h1>\n");
  sgShowIndex(sgGetConfig("pathto_galleries"),isset($_REQUEST["startat"])?$_REQUEST["startat"]:0);
}

//include footer file
include sgGetConfig("pathto_footer");

?>
