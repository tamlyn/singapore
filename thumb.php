<?php

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  thumb.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>        *
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

//require config class
require_once "includes/class_configuration.php";
//create config object
$sgConfig = new sgConfiguration();


showThumb($_REQUEST["gallery"],$_REQUEST["image"], $_REQUEST["size"]);

function showThumb($gallery, $image, $maxsize) {
  header("Content-type: image/jpeg");
  
  //check if image is remote (filename starts with 'http://')
  $isRemoteFile = substr($image,0,7)=="http://";
  
  if($isRemoteFile) $imagePath = $image;
  else $imagePath = $GLOBALS["sgConfig"]->pathto_galleries."$gallery/$image";
  $thumbPath = $GLOBALS["sgConfig"]->pathto_cache.$maxsize.strtr("-$gallery-$image",":/?\\","----");
  $imageModified = @filemtime($imagePath);
  $thumbModified = @filemtime($thumbPath);
  $thumbQuality = $GLOBALS["sgConfig"]->thumbnail_quality;
  
  if($imageModified<$thumbModified) { //if thumbnail is newer than image output cached thumbnail
    header("Last-Modified: ".gmdate("D, d M Y H:i:s",$thumbModified)." GMT");
    readfile($thumbPath);
    exit;
  }
  
  //thumbnail is out of date or doesn't exist so create new one
  
  $imgSize = GetImageSize($imagePath);

  if($imgSize[0]<$imgSize[1]){
    $y = $maxsize;
    $x = round($imgSize[0]/$imgSize[1] * $maxsize);
  }else{
    $x = $maxsize;
    $y = round($imgSize[1]/$imgSize[0] * $maxsize);
  }

  
  switch($GLOBALS["sgConfig"]->thumbnail_software) {
  case "im" : //use ImageMagick
    
    //IM doesn't handle remote files so copy locally
    if($isRemoteFile) {
      $ip = fopen($imagePath, "rb");
      $tp = fopen($thumbPath, "wb");
      while(fwrite($tp,fread($ip, 4096)));
      fclose($tp);
      fclose($ip);
      $imagePath = $thumbPath;
    }
    
    exec("convert -geometry {$x}x{$y} -quality $thumbQuality \"".escapeshellcmd($imagePath).'" "'.escapeshellcmd($thumbPath).'"');
    readfile($thumbPath);
    
    break;
  case "gd2" : //use GD 2.x 
    
    $image = ImageCreateFromJPEG($imagePath);
    $thumb = ImageCreateTrueColor($x,$y);
  
    ImageCopyResampled($thumb,$image,0,0,0,0,$x,$y,$imgSize[0],$imgSize[1]);
    
    ImageJPEG($thumb,"",$thumbQuality);
    ImageJPEG($thumb,$thumbPath,$thumbQuality);
    ImageDestroy($image);
    ImageDestroy($thumb);
    
    break;
  case "gd1" :
  default : //use GD 1.6.x by default
    
    $image = ImageCreateFromJPEG($imagePath);
    $thumb = ImageCreate($x,$y);
  
    ImageCopyResized($thumb,$image,0,0,0,0,$x,$y,$imgSize[0],$imgSize[1]);
    
    ImageJPEG($thumb,"",$thumbQuality);
    ImageJPEG($thumb,$thumbPath,$thumbQuality);
    ImageDestroy($image);
    ImageDestroy($thumb);
  }

}

?>
