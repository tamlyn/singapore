<?php 

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  config.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>       *
 *                                                                     *
 *  This file is part of singapore v0.9                                *
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


//this function serves up script configuration data
function sgGetConfig($var) {
  switch($var) {
    case "gallery_root":          return "galleries/";
       //path to galleries (must be accessible via local file system)

    case "thumbnail_cache":       return "cache/";
       //path to thumbnail cache (must be writeable by web server) 

    case "preview_thumb_number" : return 2;
       //number of images before and after current image to show 
       //preview thumbnails of in image view (eg a value of 2 here 
       //will result in 5 thumbnails (2 before + current + 2 after)
       
    case "preview_thumb_size" :   return 50;
       //maximum pixel size of preview thumbnails 

    case "main_thumb_size" :      return 100;
       //maximum pixel size of normal thambnails
       //if you make this much bigger you will also need to
       //increase some values in styles/sg.css 
       
    case "main_thumb_number" :    return 20;
       //maximum number of thumbnails to show on one screen

    case "gallery_thumb_size" :   return 80;
       //size of thumbnail used in gallery list

    case "gallery_thumb_number" : return 10;
       //maximum number of galleries to show on one screen

    case "track_views" :          return false;//$_SERVER["REMOTE_ADDR"]!="130.88.171.158";
       //wether to enable image view counting (buggy)

    case "log_path" :             return "logs/";
       //path used to store image view logs (if enabled)

    case "skin_name" :            return "cornflower";
       //the name of the current skin

    case "mysql_host" :           return "localhost";
       //name of host running mysql database
       
    case "mysql_user" :           return "singapore";
       //username for mysql database
       
    case "mysql_pass" :           return "";
       //password for username specified above
       
    return null;
  } 
}

 ?>
