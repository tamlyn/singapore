<?php 

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  config.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>       *
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


//this object holds script configuration data
class sgConfiguration
{
  var $main_thumb_size = 100;
     //maximum pixel size of normal thumbnails
     //if you make this much bigger you will probably 
     //also need to increase some values in the stylesheet 
     
  var $main_thumb_number = 20;
     //maximum number of thumbnails to show on one screen

  var $gallery_thumb_size = 80;
     //size of thumbnail used in galleries list

  var $gallery_thumb_number = 10;
     //maximum number of galleries to show on one screen

  var $preview_thumb_size = 50;
     //maximum pixel size of preview thumbnails 

  var $preview_thumb_number = 2;
     //number of images before and after current image to show 
     //preview thumbnails of in image view (eg a value of 2 here 
     //will result in 5 thumbnails (2 before + current + 2 after)
     
  var $theme_name = "cornflower";
     //the name of the current theme

  var $pathto_themes = "themes/";
     //path to themes
     
  var $pathto_cache = "../temp/cache/";
     //path to thumbnail cache (must be writable by web server)
     
  var $pathto_galleries = "../temp/galleries/";
     //path to galleries (must be accessible via local file system)

  var $pathto_logs = "../temp/logs/";
     //path used to store image view logs (if enabled)

  var $pathto_header = "includes/header.php";
     //path to header file

  var $pathto_footer = "includes/footer.php";
     //path to footer file
  
  var $pathto_extra_css = "";
     //path to custom css file or empty for default

  var $track_views = true;
     //whether to enable image view counting

  var $show_views = true;
     //whether to display image view counting

  var $show_execution_time = true;
     //true to display script execution time; false to hide

  var $mysql_host = "localhost";
     //name of host running mysql database
     
  var $mysql_user = "";
     //username for mysql database
     
  var $mysql_pass = "";
     //password for username specified above
     
  var $secret_string = "That girl understood, man. Phew! She fell in love with me, man.";
     //this string can be *anything* you want but should
     //be unique to your site and be kept secret
     
  var $thumbnail_quality = 75;
     //the JPEG quality of generated thumbnails
     //100 is the best quality; 0 is the lowest
     
  var $thumbnail_software = "gd1";
     //the software to use to generate the thumbnails
     //  gd1 = GD v1.x
     //  gd2 = GD v2.x
     //  im  = ImageMagick
     
     
  //these two options have no effect yet:
  var $directory_mode = 0755;
     //the permissions to set on directories
     //(i.e. galleries) created by the script
     
  var $file_mode = 0644;
     //the permissions to set on files (i.e. images, metadata 
     //files, thumbnails and logs) created by the script
     
  //these properties are set at run-time in the constructor   
  var $pathto_current_theme;

  //constructor
  function sgConfiguration()
  {
    $this->pathto_current_theme = $this->pathto_themes.$this->theme_name."/";
  }
}

?>