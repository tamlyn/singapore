<?php

/**
 * Creates and caches a thumbnail of the specified size for the 
 * specified image. 
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: thumb.php,v 1.24 2004/04/23 17:28:28 tamlyn Exp $
 */

//require config class
require_once "includes/config.class.php";

//remove slashes
if(get_magic_quotes_gpc())
  showThumb(stripslashes($_REQUEST["gallery"]),stripslashes($_REQUEST["image"]), stripslashes($_REQUEST["size"]));
else
  showThumb($_REQUEST["gallery"],$_REQUEST["image"], $_REQUEST["size"]);

function showThumb($gallery, $image, $maxsize) {
  //create config object
  $config = new sgConfig();

  //check if image is remote (filename starts with 'http://')
  $isRemoteFile = substr($image,0,7)=="http://";
  
  if($isRemoteFile) $imagePath = $image;
  else $imagePath = $config->pathto_galleries."$gallery/$image";
  
  $thumbPath = $config->pathto_cache.$maxsize.strtr("-$gallery-$image",":/?\\","----");
  
  $imageModified = @filemtime($imagePath);
  $thumbModified = @filemtime($thumbPath);
  $thumbQuality = $config->thumbnail_quality;
  
  list($imageWidth, $imageHeight, $imageType) = GetImageSize($imagePath);
  
  //send appropriate headers
  switch($imageType) {
    case 1 : $config->thumbnail_software=="gd2"?header("Content-type: image/png"):header("Content-type: image/gif"); break;
    case 3 : header("Content-type: image/png"); break;
    case 6 : header("Content-type: image/x-ms-bmp"); break;
    case 7 : 
    case 8 : header("Content-type: image/tiff"); break;
    case 2 :
    default: header("Content-type: image/jpeg"); break;
  }
  
  //if thumbnail is newer than image then output cached thumbnail and exit
  if($imageModified<$thumbModified) { 
    header("Last-Modified: ".gmdate("D, d M Y H:i:s",$thumbModified)." GMT");
    readfile($thumbPath);
    exit;
  }
  //otherwise thumbnail is out of date or doesn't exist so create new one
  
  //compute width and height of thumbnail to create
  if($imageWidth < $imageHeight && ($imageWidth>$maxsize || $imageHeight>$maxsize)) {
    $thumbWidth = round($imageWidth/$imageHeight * $maxsize);
    $thumbHeight = $maxsize;
  } elseif($imageWidth>$maxsize || $imageHeight>$maxsize) {
    $thumbWidth = $maxsize;
    $thumbHeight = round($imageHeight/$imageWidth * $maxsize);
  } else {
    //image is smaller than required dimensions so output it and exit
    readfile($imagePath);
    exit;
  }

  //set default files permissions
  umask($config->umask);
  
  //if file is remote then copy locally first
  if($isRemoteFile) {
    $ip = fopen($imagePath, "rb");
    $tp = fopen($thumbPath, "wb");
    while(fwrite($tp,fread($ip, 4096)));
    fclose($tp);
    fclose($ip);
    $imagePath = $thumbPath;
  }
  
  
  switch($config->thumbnail_software) {
  case "im" : //use ImageMagick  
    $cmd  = '"'.$config->pathto_convert.'"';
    $cmd .= " -geometry {$thumbWidth}x{$thumbHeight}";
    if($imageType == 2) $cmd .= " -quality $thumbQuality";
    if($config->progressive_thumbs) $cmd .= " -interlace Plane";
    if($config->remove_jpeg_profile) $cmd .= ' +profile "*"';
    $cmd .= ' '.escapeshellarg($imagePath).' '.escapeshellarg($thumbPath);
    
    exec($cmd);
    readfile($thumbPath);
    
    break;
  case "gd2" :
  case "gd1" :
  default : //use GD by default
    //read in image as appropriate type
    switch($imageType) {
      case 1 : $image = ImageCreateFromGIF($imagePath); break;
      case 3 : $image = ImageCreateFromPNG($imagePath); break;
      case 2 : 
      default: $image = ImageCreateFromJPEG($imagePath); break;
    }
    
    switch($config->thumbnail_software) {
    case "gd2" :
      //create blank truecolor image
      $thumb = ImageCreateTrueColor($thumbWidth,$thumbHeight);
      //resize image with resampling
      ImageCopyResampled($thumb,$image,0,0,0,0,$thumbWidth,$thumbHeight,$imageWidth,$imageHeight);
      break;
    case "gd1" :
    default :
      //create blank 256 color image
      $thumb = ImageCreate($thumbWidth,$thumbHeight);
      //resize image
      ImageCopyResized($thumb,$image,0,0,0,0,$thumbWidth,$thumbHeight,$imageWidth,$imageHeight);
      break;
    }
    
    //set image interlacing
    ImageInterlace($thumb,$config->progressive_thumbs);
    
    //output image of appropriate type
    switch($imageType) {
      case 1 :
        //GIF images are output as PNG
      case 3 :
        ImagePNG($thumb); 
        ImagePNG($thumb,$thumbPath); 
        break;
      case 2 :
      default: 
        ImageJPEG($thumb,"",$thumbQuality); 
        ImageJPEG($thumb,$thumbPath,$thumbQuality); 
        break;
    }
    ImageDestroy($image);
    ImageDestroy($thumb);
  }

}

?>
