<?php 

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  utils.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>        *
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


//utility functions - produce no output on stdout

function sgGetListing($wd){
  $dp = opendir($wd);
  while($entry = readdir($dp))
    if(is_dir($wd.$entry) && $entry != "." && $entry != "..") $dir->dirs[] = $entry;
  closedir($dp);
  $dir->path = $wd;
  sort($dir->dirs);
  return $dir;
}

function sgGetImage($gallery, $image) {
  $gal = sgGetGallery($gallery);
  
  for($i=0;$i<count($gal->img);$i++)
    if($gal->img[$i]->filename == $image) {
      for($j=0;$j<sgGetConfig("preview_thumb_number");$j++) if($i>$j) $gal->img[$i]->prev[$j] = $gal->img[$i-$j-1];
      for($j=0;$j<sgGetConfig("preview_thumb_number");$j++) if($i<count($gal->img)-$j-1) $gal->img[$i]->next[$j] = $gal->img[$i+$j+1];
      //$gal->img[$i]->gal = $gal;
      //unset($gal->img[$i]->gal->img);
      return $gal->img[$i];
    }
  return null;
}

function sgPutImage($gallery, $img) {
  $gal = sgGetGallery($gallery);
  
  for($i=0;$i<count($gal->img);$i++)
    if($gal->img[$i]->filename == $img->filename) 
      $gal->img[$i] = $img;
      
  sgPutGallery($gal);
}

function sgGetGalleryInfo($gallery){
  return sgGetGallery($gallery, true);
}

function sgIsLoggedIn() {
  if($_SESSION["user"]->check == md5("That girl understood, man. Phew! She fell in love with me, man. $_SERVER[REMOTE_ADDR]") && (time() - $_SESSION["user"]->loginTime < 1800)) {
		$_SESSION["user"]->loginTime = time();
	  return true;
  }
  return false;
}



?>
