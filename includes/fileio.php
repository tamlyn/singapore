<?php 

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  fileio.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>       *
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

//fileio version of backend functions - use csv files

function sgGetGallery($gallery, $galleryOnly = false) {
  $gal->id = $gallery;
  $gal->img = array();
  
  $fp = @fopen(sgGetConfig("pathto_galleries")."$gal->id/metadata.csv","r");
  
  if($fp) {
    while($temp[] = fgetcsv($fp,4096));
    fclose($fp);
    
    list(
      $gal->filename,
      ,
      $gal->owner,
      $gal->groups,
      $gal->permissions,
      $gal->categories,
      $gal->name,
      $gal->artist,
      $gal->email,
      $gal->copyright,
      $gal->desc
    ) = $temp[1];
    
    if($galleryOnly) return $gal;
    
    for($i=0;$i<count($temp)-3;$i++)
      list(
        $gal->img[$i]->filename,
        $gal->img[$i]->thumbnail,
        $gal->img[$i]->owner,
        $gal->img[$i]->groups,
        $gal->img[$i]->permissions,
        $gal->img[$i]->categories,
        $gal->img[$i]->name,
        $gal->img[$i]->artist,
        $gal->img[$i]->email,
        $gal->img[$i]->copyright,
        $gal->img[$i]->desc,
        $gal->img[$i]->location,
        $gal->img[$i]->date,
        $gal->img[$i]->camera,
        $gal->img[$i]->lens,
        $gal->img[$i]->film,
        $gal->img[$i]->darkroom,
        $gal->img[$i]->digital
      ) = $temp[$i+2];
    
    return $gal;
  } elseif($dir = sgGetListing(sgGetConfig("pathto_galleries")."$gal->id/", "jpegs")) {
    
    $temp = strtr($gal->id, "_", " ");
    if(strpos($temp, " - ")) list($gal->artist,$gal->name) = explode(" - ", $temp);
    else {
      $gal->artist = "";
      $gal->name = $temp;
    }
    
    //set default values
    $gal->filename = isset($dir->files[0])?$dir->files[0]:"__none__";
    $gal->owner = "__nobody__";
    $gal->groups = "__nogroup__";
    $gal->permissions = 4096;
    $gal->categories = "";
    $gal->email = "";
    $gal->copyright = "";
    $gal->desc = "";
    
    if($galleryOnly) return $gal;
    
    for($i=0;$i<count($dir->files);$i++) {
      $gal->img[$i]->filename = $dir->files[$i];
      //trim off file extension and replace underscores with spaces
      $temp = strtr(substr($gal->img[$i]->filename, 0, strrpos($gal->img[$i]->filename,".")-strlen($gal->img[$i]->filename)), "_", " ");
      //split string in two on " - " delimiter
      if(strpos($temp, " - ")) list($gal->img[$i]->artist,$gal->img[$i]->name) = explode(" - ", $temp);
      else {
        $gal->img[$i]->name = $temp;
        $gal->img[$i]->artist = "";
      }
      
      //set default values
      $gal->img[$i]->thumbnail = "";
      $gal->img[$i]->owner = "__nobody__";
      $gal->img[$i]->groups = "__nogroup__";
      $gal->img[$i]->permissions = 4096;
      $gal->img[$i]->categories = "";
      
      $gal->img[$i]->email = $gal->img[$i]->copyright = $gal->img[$i]->desc =
      $gal->img[$i]->location = $gal->img[$i]->date = $gal->img[$i]->camera =
      $gal->img[$i]->lens = $gal->img[$i]->film = $gal->img[$i]->darkroom =
      $gal->img[$i]->digital = "";
    }
    
    //sort($gal->img);
    
    return $gal;
  } else 
      return false;
}

function sgPutGallery($gal) {
  
  $fp = fopen(sgGetConfig("pathto_galleries").$gal->id."/metadata.csv","w");
  
  if(!$fp) return false;

  fwrite($fp,"filename,thumbnail,owner,group(s),permissions,catergories,image name,artist name,artist email,copyright,image description,image location,date taken,camera info,lens info,film info,darkroom manipulation,digital manipulation");
  fwrite($fp,"\n".
  $gal->filename.",,".
  $gal->owner.",".
  $gal->groups.",".
  $gal->permissions.",".
  $gal->categories.",\"".
  str_replace("\"","\"\"",$gal->name)."\",\"".
  str_replace("\"","\"\"",$gal->artist)."\",\"".
  str_replace("\"","\"\"",$gal->email)."\",\"".
  str_replace("\"","\"\"",$gal->copyright)."\",\"".
  str_replace("\"","\"\"",$gal->desc)."\"");
  
  for($i=0;$i<count($gal->img);$i++)
    fwrite($fp,"\n".
    $gal->img[$i]->filename.",".
    $gal->img[$i]->thumbnail.",".
    $gal->img[$i]->owner.",".
    $gal->img[$i]->groups.",".
    $gal->img[$i]->permissions.",".
    $gal->img[$i]->categories.",\"".
    str_replace("\"","\"\"",$gal->img[$i]->name)."\",\"".
    str_replace("\"","\"\"",$gal->img[$i]->artist)."\",\"".
    str_replace("\"","\"\"",$gal->img[$i]->email)."\",\"".
    str_replace("\"","\"\"",$gal->img[$i]->copyright)."\",\"".
    str_replace("\"","\"\"",$gal->img[$i]->desc)."\",\"".
    str_replace("\"","\"\"",$gal->img[$i]->location)."\",\"".
    str_replace("\"","\"\"",$gal->img[$i]->date)."\",\"".
    str_replace("\"","\"\"",$gal->img[$i]->camera)."\",\"".
    str_replace("\"","\"\"",$gal->img[$i]->lens)."\",\"".
    str_replace("\"","\"\"",$gal->img[$i]->film)."\",\"".
    str_replace("\"","\"\"",$gal->img[$i]->darkroom)."\",\"".
    str_replace("\"","\"\"",$gal->img[$i]->digital)."\"");
  fclose($fp);

  return true;
}

function sgGetHits($gallery) {

  $hits->gal = $gallery;

  $fp = @fopen(sgGetConfig("pathto_logs").strtr("$hits->gal",":/?\\","----").".log","r");
  
  if($fp) {
    while($temp[] = fgetcsv($fp,255));
    fclose($fp);
  } else $temp = array();
  
  if(isset($temp[0])) 
    list(
      ,
      $hits->hits,
      $hits->lasthit
    ) = $temp[0];
  else {
    $hits->hits = 0;
    $hits->lasthit = 0;
  };
    
    
  for($i=0;$i<count($temp)-2;$i++)
    list(
      $hits->img[$i]->filename,
      $hits->img[$i]->hits,
      $hits->img[$i]->lasthit
    ) = $temp[$i+1];
  
  return $hits;
}

function sgPutHits($hits) {

  $fp = fopen(sgGetConfig("pathto_logs").strtr("$hits->gal",":/?\\","----").".log","w");
  if(!$fp) return false;
  
  fwrite($fp, ",".
    $hits->hits.",".
    $hits->lasthit
  );
    
  if(isset($hits->img)) 
    for($i=0;$i<count($hits->img);$i++)
      if(!empty($hits->img[$i]->filename)) fwrite($fp, "\n".
        $hits->img[$i]->filename.",".
        $hits->img[$i]->hits.",".
        $hits->img[$i]->lasthit
      );
    
  return true;
}

function sgGetUsers() {
  $fp = fopen("includes/adminusers.csv","r");
  for($i=0;$entry = fgetcsv($fp,1000,",");$i++) 
    list($users[$i]->username,$users[$i]->userpass,$users[$i]->permissions,$users[$i]->fullname,$users[$i]->description,$users[$i]->stats) = $entry;
  fclose($fp);
  return $users;
}

function sgPutUsers($users) {
  $fp = fopen("includes/adminusers.csv","w");
  if(!$fp) return false;
  $success = true;
  for($i=0;$i<count($users);$i++) 
    $success &= fwrite($fp,$users[$i]->username.",".$users[$i]->userpass.",".$users[$i]->permissions.",\"".$users[$i]->fullname."\",\"".$users[$i]->description."\",\"".$users[$i]->stats."\"\n");
  fclose($fp);
  return $success;
}


?>
