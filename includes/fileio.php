<?php 

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\ 
 *  fileio.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>       *
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

//fileio version of backend functions - use csv files

function sgGetGallery($gallery, $galleryOnly = false) {
  $gal->id = $gallery;
  
  $fp = @fopen(sgGetConfig("gallery_root")."$gal->id/metadata.csv","r");
  
  if($fp) {
    while($temp[] = fgetcsv($fp,4096));
    fclose($fp);
    
    list($gal->filename,$gal->flags,$gal->artist,$gal->name,,$gal->desc) = $temp[1];
    
    if($galleryOnly) return $gal;
    
    for($i=0;$i<count($temp)-3;$i++) {
      list($gal->img[$i]->filename,$gal->img[$i]->flags,$gal->img[$i]->artist,$gal->img[$i]->name,$gal->img[$i]->location,$gal->img[$i]->desc,$gal->img[$i]->date,$gal->img[$i]->camera,$gal->img[$i]->lens,$gal->img[$i]->film) = $temp[$i+2];
    }
    
    return $gal;
  } else {
    $dp = opendir(sgGetConfig("gallery_root")."$gal->id/");
    while($entry = readdir($dp))
      if(stristr($entry,".jpg") || stristr($entry,".jpeg")) $gal->img[]->filename = $entry;
    closedir($dp);
    
    $temp = strtr($gal->id, "_", " ");
    if(strstr($temp, " - ")) list($gal->artist,$gal->name) = explode(" - ", $temp);
    else $gal->name = $temp;
    
    $gal->filename = $gal->img[0]->filename;
    
    if($galleryOnly) return $gal;
    
    for($i=0;$i<count($gal->img);$i++) {
      $temp = strtr(substr($gal->img[$i]->filename, 0, -4), "_", " ");
      if(strstr($temp, " - ")) list($gal->img[$i]->artist,$gal->img[$i]->name) = explode(" - ", $temp);
      else $gal->img[$i]->name = $temp;
    }
    
    //sort($gal->img);
    return $gal;
  }
}

function sgPutGallery($gal) {
  
  $fp = fopen(sgGetConfig("gallery_root").$gal->id."/metadata.csv","w");
  
  if($fp) {
    fwrite($fp,"filename,flags,artist name,image name,image location,image description,date taken,camera info,lens info,film info\n");
    fwrite($fp,$gal->filename.",".$gal->flags.",\"".$gal->artist."\",\"".$gal->name."\",,\"".$gal->desc."\"");
    
    for($i=0;$i<count($gal->img);$i++)
      fwrite($fp,"\n".$gal->img[$i]->filename.",".$gal->img[$i]->flags.",\"".$gal->img[$i]->artist."\",\"".$gal->img[$i]->name."\",\"".$gal->img[$i]->location."\",\"".$gal->img[$i]->desc."\",\"".$gal->img[$i]->date."\",\"".$gal->img[$i]->camera."\",\"".$gal->img[$i]->lens."\",\"".$gal->img[$i]->film."\"");
  }
  fclose($fp);
}

function sgLogView($gallery, $image = "") {
  //read data from file
  $temp= @file(sgGetConfig("log_path").$gallery.".log");
  //if(!$data) $data[0][0] = time();
  
  for($i=0;$i<count($temp);$i++) $data[$i] = explode(",",$temp[$i]);
  
  if(!$image) $i = 0; //log gallery hit
  else for($i=1;$i<count($data);$i++) if($data[$i][0] == $image) break; //log image hit
  
  //if image not found then add it
  if($i == count($data)) $data[$i][0] = $image;
   
  $hits = $data[$i][1]++; //increase hit count by one
  $data[$i][2] = time(); //log time of last hit
  
  //write data back to file
  $fp = fopen(sgGetConfig("log_path").$gallery.".log","w");
  for($i=0;$i<count($data);$i++) fwrite($fp,$data[$i][0].",".$data[$i][1].",".$data[$i][2]."\n");
  fclose($fp);
  
  return $hits;
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
  for($i=0;$i<count($users);$i++) 
    fwrite($fp,$users[$i]->username.",".$users[$i]->userpass.",".$users[$i]->permissions.",\"".$users[$i]->fullname."\",\"".$users[$i]->description."\",\"".$users[$i]->stats."\"\n");
  fclose($fp);
}


?>
