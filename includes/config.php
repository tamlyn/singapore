<?php 

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  config.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>       *
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


//this function serves up script configuration data
function sgGetConfig($var) {
  switch($var) {
    case "main_thumb_size" :      return 100;
       //maximum pixel size of normal thambnails
       //if you make this much bigger you will also need to
       //increase some values in styles/sg.css 
       
    case "main_thumb_number" :    return 20;
       //maximum number of thumbnails to show on one screen

    case "gallery_thumb_size" :   return 80;
       //size of thumbnail used in galleries list

    case "gallery_thumb_number" : return 10;
       //maximum number of galleries to show on one screen

    case "preview_thumb_size" :   return 50;
       //maximum pixel size of preview thumbnails 

    case "preview_thumb_number" : return 2;
       //number of images before and after current image to show 
       //preview thumbnails of in image view (eg a value of 2 here 
       //will result in 5 thumbnails (2 before + current + 2 after)
       
    case "theme_name" :           return "xp";
       //the name of the current theme

    case "pathto_themes":         return "themes/";
       //path to themes
       
    case "pathto_cache":          return "cache/";
       //path to thumbnail cache (must be writeable by web server)
       
    case "pathto_galleries":      return "../../photosoc.man.ac.uk/gallery/galleries/";
       //path to galleries (must be accessible via local file system)

    case "pathto_logs" :          return "logs/";
       //path used to store image view logs (if enabled)

    case "pathto_header" :        return "includes/header.php";
       //path to header file

    case "pathto_footer" :        return "includes/footer.php";
       //path to footer file
    
    case "pathto_extra_css" :     return "";
       //path to custom css file or empty for default

    case "track_views" :          return true;
       //whether to enable image view counting

    case "show_views" :          return true;
       //whether to display image view counting

    case "show_execution_time" :  return true;
       //true to display script execution time; false to hide

    case "mysql_host" :           return "localhost";
       //name of host running mysql database
       
    case "mysql_user" :           return "";
       //username for mysql database
       
    case "mysql_pass" :           return "";
       //password for username specified above
       
    case "secret_string" :        return "That girl understood, man. Phew! She fell in love with me, man.";
       //this string can be *anything* you want but should
       //be unique to your site and be kept secret
  }
  return null;
}

 ?>
