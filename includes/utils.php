<?php 

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  utils.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>        *
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


//utility functions - produce no output on stdout

function sgGetListing($wd, $type = ""){
  $dir->path = $wd;
  $dir->files = array();
  $dir->dirs = array();
  $dp = @opendir($wd);
  
  if(!$dp) return false;
  
  switch($type) {
    case "" :
    case "dirs" :
      while(false !== ($entry = readdir($dp)))
        if(
          is_dir($wd.$entry) && 
          $entry != "." && 
          $entry != ".." && 
          $entry != "CVS" //used in development to ignore CVS directories
        ) $dir->dirs[] = $entry;
      sort($dir->dirs);
      break;
    case "jpegs" :
      while(false !== ($entry = readdir($dp)))
        if(
          strpos(strtolower($entry),".jpg") || 
          strpos(strtolower($entry),".jpeg")
        ) $dir->files[] = $entry;
      sort($dir->files);
      break;
    case "all" :
      while(false !== ($entry = readdir($dp)))
        if(is_dir($wd.$entry)) $dir->dirs[] = $entry;
        else $dir->files[] = $entry;
      sort($dir->dirs);
      sort($dir->files);
      break;
    default :
      while(false !== ($entry = readdir($dp)))
        if(strpos(strtolower($entry),$type)) 
          $dir->files[] = $entry;
      sort($dir->files);
  }
  closedir($dp);
  return $dir;
}


//this function recursively deletes all subdirectories and 
//files in specified directory. USE WITH EXTREME CAUTION!! 
function rmdir_all($wd)
{
  if(!$dp = opendir($wd)) return false;
  $success = true;
  while(false !== ($entry = readdir($dp))) {
    if($entry == "." || $entry == "..") continue;
    if(is_dir("$wd/$entry")) $success &= rmdir_all("$wd/$entry");
    else $success &= unlink("$wd/$entry");
  }
  closedir($dp);
  $success &= rmdir($wd);
  return $success;
}

function sgGetImage($gallery, $image) {
  $gal = sgGetGallery($gallery);
  
  for($i=0;$i<count($gal->img);$i++)
    if($gal->img[$i]->filename == $image) {
      $gal->img[$i]->prev = array();
      $gal->img[$i]->next = array();
      for($j=0;$j<sgGetConfig("preview_thumb_number");$j++) if($i>$j) $gal->img[$i]->prev[$j] = $gal->img[$i-$j-1];
      for($j=0;$j<sgGetConfig("preview_thumb_number");$j++) if($i<count($gal->img)-$j-1) $gal->img[$i]->next[$j] = $gal->img[$i+$j+1];
      $gal->img[$i]->index = $i;
      $gal->img[$i]->galname = $gal->name;
      return $gal->img[$i];
    }
  return false;
}

function sgPutImage($gallery, $img) {
  $gal = sgGetGallery($gallery);
  
  for($i=0;$i<count($gal->img);$i++)
    if($gal->img[$i]->filename == $img->filename) 
      $gal->img[$i] = $img;
      
  return sgPutGallery($gal);
}

function sgGetGalleryInfo($gallery){
  return sgGetGallery($gallery, true);
}

//this function really belongs in adminutils.php but is
//included here because it is used on every page and
//adminutils.php is not included on every page
function sgIsLoggedIn() {
  if(isset($_SESSION["user"]) && $_SESSION["user"]->check == md5(sgGetConfig("secret_string").$_SERVER["REMOTE_ADDR"]) && (time() - $_SESSION["user"]->loginTime < 1800)) {
		$_SESSION["user"]->loginTime = time();
	  return true;
  }
  return false;
}

function sgLogView($gallery, $image = "") {
  $hits = sgGetHits($gallery);
  
  if(empty($image)) $selected = &$hits; //$selected represents gallery
  else {
    if(isset($hits->img)) {
      //search selected for image in existing log
      for($i=0;$i<count($hits->img);$i++) 
        if($hits->img[$i]->filename == $image) {
          $selected = &$hits->img[$i]; //$selected represents selected image 
          break;
        }
    } else {
      $hits->img = array();
      $i = 0;
    }
    //if image not found then add it
    if($i == count($hits->img)) {
      $hits->img[$i]->filename = $image;
      $selected = &$hits->img[$i]; //$selected represents new image
    }
  }
  
  $selected->hits = isset($selected->hits)?$selected->hits+1:1; //increase hit count by one
  $selected->lasthit = time(); //log time of last hit
  
  //save modified hits data
  sgPutHits($hits);

  //return number of hits
  return $selected->hits;
}



?>
