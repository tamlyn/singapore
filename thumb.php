<?php

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  thumb.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>        *
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

//require config class
require_once "includes/class_configuration.php";
//create config object
$sgConfig = new sgConfiguration();


showThumb($_REQUEST["gallery"],$_REQUEST["image"], $_REQUEST["size"]);

function showThumb($gallery, $image, $maxsize) {
  
  //check if image is remote (filename starts with 'http://')
  $isRemoteFile = substr($image,0,7)=="http://";
  
  if($isRemoteFile) $imagePath = $image;
  else $imagePath = $GLOBALS["sgConfig"]->pathto_galleries."$gallery/$image";
  $thumbPath = $GLOBALS["sgConfig"]->pathto_cache.$maxsize.strtr("-$gallery-$image",":/?\\","----");
  $imageModified = @filemtime($imagePath);
  $thumbModified = @filemtime($thumbPath);
  $thumbQuality = $GLOBALS["sgConfig"]->thumbnail_quality;
  
  list($imageWidth, $imageHeight, $imageType) = GetImageSize($imagePath);
  
  //send appropriate headers
  switch($imageType) {
    case 1 : header("Content-type: image/gif"); break;
    case 2 : header("Content-type: image/jpeg"); break;
    case 3 : header("Content-type: image/png"); break;
    case 6 : header("Content-type: image/x-ms-bmp"); break;
    case 7 : 
    case 8 : header("Content-type: image/tiff"); break;
  }
  
  if($imageModified<$thumbModified) { //if thumbnail is newer than image output cached thumbnail
    header("Last-Modified: ".gmdate("D, d M Y H:i:s",$thumbModified)." GMT");
    readfile($thumbPath);
    exit;
  }
  
  //thumbnail is out of date or doesn't exist so create new one
  

  if($imageWidth < $imageHeight){
    $thumbWidth = round($imageWidth/$imageHeight * $maxsize);
    $thumbHeight = $maxsize;
  }else{
    $thumbWidth = $maxsize;
    $thumbHeight = round($imageHeight/$imageWidth * $maxsize);
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
    
    exec("convert -geometry {$thumbWidth}x{$thumbHeight} -quality $thumbQuality +profile \"*\" \"".escapeshellcmd($imagePath).'" "'.escapeshellcmd($thumbPath).'"');
    readfile($thumbPath);
    
    break;
  case "gd2" :
  case "gd1" :
  default : //use GD by default
    
    //read in image as appropriate type
    switch($imageType) {
      case 1 : $image = ImageCreateFromGIF($imagePath); break;
      case 2 : $image = ImageCreateFromJPEG($imagePath); break;
      case 3 : $image = ImageCreateFromPNG($imagePath); break;
    }
    
    switch($GLOBALS["sgConfig"]->thumbnail_software) {
    case "gd2" :
      //create blank truecolor image
      $thumb = ImageCreateTrueColor($thumbWidth,$thumbHeight);
      //resize with resampling image
      ImageCopyResampled($thumb,$image,0,0,0,0,$thumbWidth,$thumbHeight,$imageWidth,$imageHeight);
    case "gd1" :
    default :
      //create blank 256 color image
      $thumb = ImageCreate($thumbWidth,$thumbHeight);
      //resize image
      ImageCopyResized($thumb,$image,0,0,0,0,$thumbWidth,$thumbHeight,$imageWidth,$imageHeight);
      break;
    }
    
    //output image of appropriate type
    switch($imageType) {
      case 1 :
        ImagePNG($thumb); 
        ImagePNG($thumb,$thumbPath); 
        break;
      case 2 : 
        ImageJPEG($thumb,"",$thumbQuality); 
        ImageJPEG($thumb,$thumbPath,$thumbQuality); 
        break;
      case 3 :
        ImagePNG($thumb); 
        ImagePNG($thumb,$thumbPath); 
        break;
      
    }
    ImageDestroy($image);
    ImageDestroy($thumb);
  }

}

?>
