<?php

/**
 * Creates and caches a thumbnail of the specified size for the 
 * specified image. 
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: thumb.php,v 1.33 2004/12/02 12:02:49 tamlyn Exp $
 */

//require config class
require_once "includes/config.class.php";

//remove slashes
if(get_magic_quotes_gpc())
  showThumb(stripslashes($_REQUEST["gallery"]),stripslashes($_REQUEST["image"]), $_REQUEST["width"], $_REQUEST["height"], isset($_REQUEST["force"]));
else
  showThumb($_REQUEST["gallery"],$_REQUEST["image"], $_REQUEST["width"], $_REQUEST["height"], isset($_REQUEST["force"]));

function showThumb($gallery, $image, $maxWidth, $maxHeight, $forceSize) {
  //create config object
  $config = new sgConfig();

  //check if image is remote (filename starts with 'http://')
  $isRemoteFile = substr($image,0,7)=="http://";
  
  //security check: make sure requested file is in galleries directory
  $galPath = realpath($config->pathto_galleries);
  $imgPath = realpath($config->pathto_galleries.$gallery."/".$image);
  if(substr($imgPath,0,strlen($galPath)) != $galPath && !$isRemoteFile)
    die("Requested image does not exist or is outside allowed directory");
  
  //security check: make sure $image has a valid extension
  if(!$isRemoteFile && !preg_match("/.+\.(".$config->recognised_extensions.")$/i",$image))
    die("Specified file is not a recognised image file");

  if($isRemoteFile) $imagePath = $image;
  else $imagePath = $config->pathto_galleries."$gallery/$image";
  
  $thumbPath = $config->pathto_data_dir."cache/".$maxWidth."x".$maxHeight.($forceSize?"f":"").strtr("-$gallery-$image",":/?\\","----");
  
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
  
  
  
  //if aspect ratio is to be constrained set crop size
  if($forceSize) {
    $newAspect = $maxWidth/$maxHeight;
    $oldAspect = $imageWidth/$imageHeight;
    if($newAspect > $oldAspect) {
      $cropWidth = $imageWidth;
      $cropHeight = round($oldAspect/$newAspect * $imageHeight);
    } else {
      $cropWidth = round($newAspect/$oldAspect * $imageWidth);
      $cropHeight = $imageHeight;
    }
  //else crop size is image size
  } else {
    $cropWidth = $imageWidth;
    $cropHeight = $imageHeight;
  }
  
  //set cropping offset
  $cropX = floor(($imageWidth-$cropWidth)/2);
  $cropY = floor(($imageHeight-$cropHeight)/2);
    
  //compute width and height of thumbnail to create
  if($cropWidth > $maxWidth && ($cropHeight < $maxHeight || ($cropHeight > $maxHeight && round($cropWidth/$cropHeight * $maxHeight) > $maxWidth))) {
    $thumbWidth = $maxWidth;
    $thumbHeight = round($cropHeight/$cropWidth * $maxWidth);
  } elseif($cropHeight > $maxHeight) {
    $thumbWidth = round($cropWidth/$cropHeight * $maxHeight);
    $thumbHeight = $maxHeight;
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
    while(fwrite($tp,fread($ip, 4095)));
    fclose($tp);
    fclose($ip);
    $imagePath = $thumbPath;
  }
  
  
  switch($config->thumbnail_software) {
  case "im" : //use ImageMagick  
    $cmd  = '"'.$config->pathto_convert.'"';
    if($forceSize) $cmd .= " -crop {$cropWidth}x{$cropHeight}+$cropX+$cropY";
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
      ImageCopyResampled(
        $thumb,                    $image,
        0,           0,            $cropX,     $cropY,
        $thumbWidth, $thumbHeight, $cropWidth, $cropHeight);
      break;
    case "gd1" :
    default :
      //create blank 256 color image
      $thumb = ImageCreate($thumbWidth,$thumbHeight);
      //resize image
      ImageCopyResized(
        $thumb,                    $image,
        0,           0,            $cropX,     $cropY,
        $thumbWidth, $thumbHeight, $cropWidth, $cropHeight);
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
