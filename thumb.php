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

require "includes/config.php";

showThumb($_REQUEST["gallery"],$_REQUEST["image"], $_REQUEST["size"]);

function showThumb($gallery, $image, $maxsize) {
  header("Content-type: image/jpeg");
  
  //if image is local (filename does not start with 'http://')
  //then prepend filename with path to current gallery
  if(substr($img->filename,0,7)!="http://") $imagePath = sgGetConfig("pathto_galleries")."$gallery/$image";
  else $imagePath = $image;
  $thumbPath = sgGetConfig("pathto_cache").$maxsize.strtr("-$gallery-$image",":/?\\","----");
  $imageModified = @filemtime($imagePath);
  $thumbModified = @filemtime($thumbPath);
  $thumbQuality = sgGetConfig("thumbnail_quality");
  
  if($imageModified<$thumbModified) { //if thumbnail is newer than image output cached thumbnail
    header("Last-Modified: ".gmdate("D, d M Y H:i:s",$thumbModified)." GMT");
    readfile($thumbPath);
  } else { //thumbnail is out of date or doesn't exist so create new one
    
    $imgSize = GetImageSize($imagePath);
  
    if($imgSize[0]<$imgSize[1]){
      $y = $maxsize;
      $x = $imgSize[0]/$imgSize[1] * $maxsize; 
    }else{
      $x = $maxsize;
      $y = $imgSize[1]/$imgSize[0] * $maxsize;
    }
  
    $image = ImageCreateFromJPEG($imagePath);
    
    /* If you have PHP >= 4.0.6 and GD >= 2.0.1 then you will
     * get nicer thumbnails by uncommenting the two lines commented
     * out below. See Readme for more information.
     */
    
    $thumb = ImageCreate($x,$y);
    //$thumb = ImageCreateTrueColor($x,$y);
  
    ImageCopyResized($thumb,$image,0,0,0,0,$x,$y,$imgSize[0],$imgSize[1]);
    //ImageCopyResampled($thumb,$image,0,0,0,0,$x,$y,$imgSize[0],$imgSize[1]);
    
    
    ImageJPEG($thumb,"",$thumbQuality);
    ImageJPEG($thumb,$thumbPath,$thumbQuality);
    ImageDestroy($image);
    ImageDestroy($thumb);
  }
}

?>
