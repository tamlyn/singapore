<?php 

/**
 * Thumbnail class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003-2005 Tamlyn Rhodes
 * @version $Id: thumbnail.class.php,v 1.6 2005/12/01 00:10:45 tamlyn Exp $
 */


/**
 * Creates and manages image thumbnails
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003-2005 Tamlyn Rhodes
 */
class sgThumbnail
{
  var $config;
  var $maxWidth = 0;
  var $maxHeight = 0;
  var $thumbWidth = 0;
  var $thumbHeight = 0;
  var $cropWidth = 0;
  var $cropHeight = 0;
  var $forceSize = false;
  var $imagePath = "";
  var $thumbPath = "";
  
  function sgThumbnail(&$img, $type)
  {
    $this->config =& sgConfig::getInstance();
    $this->image  =& $img; 
    
    $widthVar  = "thumb_width_".$type;
    $heightVar = "thumb_height_".$type;
    $cropVar   = "thumb_crop_".$type;
    $this->maxWidth  = $this->config->$widthVar;
    $this->maxHeight = $this->config->$heightVar;
    if(isset($this->$cropVar))
      $this->forceSize = $this->config->$cropVar;
    
    if($this->image == null) return;
    
    $this->imagePath = $this->image->realPath();
    $this->thumbPath = Singapore::thumbnailPath($this->image->parent->id, $this->image->id, $this->maxWidth, $this->maxHeight, $this->forceSize);
    
    //security check: make sure requested file is in galleries directory
    if(!Singapore::isSubPath($this->config->pathto_galleries, $this->imagePath) && !$this->image->isRemote())
      return;
    
    //security check: make sure $image has a valid extension
    if(!$this->image->isRemote() && !preg_match("/.+\.(".$this->config->recognised_extensions.")$/i",$this->image->id))
      return;
  
    $this->calculateDimensions();
    
    //link straight to image if it smaller than required size
    if($this->image->width <= $this->thumbWidth && $this->image->height <= $this->thumbHeight)
      $this->thumbPath = $this->image->imageURL();
    
    $imageModified = @filemtime($this->imagePath);
    $thumbModified = @filemtime($this->thumbPath);
    $thumbQuality = $this->config->thumbnail_quality;
    
    if($imageModified > $thumbModified || !$imageModified)
      $this->buildThumbnail();
  }
  
  /** Accessor methods */
  function width()  { return $this->thumbWidth; }
  function height() { return $this->thumbHeight; }
  function URL()    { return $this->thumbPath; }
  
  /** Private methods */
  
  /**
   * Calculates thumbnail dimensions.
   */
  function calculateDimensions()
  {
    //if aspect ratio is to be constrained set crop size
    if($this->forceSize) {
      $newAspect = $this->maxWidth/$this->maxHeight;
      $oldAspect = $this->image->realWidth()/$this->image->realHeight();
      if($newAspect > $oldAspect) {
        $this->cropWidth  = $this->image->realWidth();
        $this->cropHeight = round($this->image->realHeight()*($oldAspect/$newAspect));
      } else {
        $this->cropWidth  = round($this->image->realWidth()*($newAspect/$oldAspect));
        $this->cropHeight = $this->image->realHeight();
      }
    //else crop size is image size
    } else {
      $this->cropWidth = $this->image->realWidth();
      $this->cropHeight = $this->image->realHeight();
    }
    
    if($this->cropHeight > $this->maxHeight && ($this->cropWidth <= $this->maxWidth 
       || ($this->cropWidth > $this->maxWidth && round($this->cropHeight/$this->cropWidth * $this->maxWidth) > $this->maxHeight))) {
      $this->thumbWidth  = round($this->cropWidth/$this->cropHeight * $this->maxHeight);
      $this->thumbHeight = $this->maxHeight; 
    } elseif($this->cropWidth > $this->maxWidth) {
      $this->thumbWidth  = $this->maxWidth;
      $this->thumbHeight = round($this->cropHeight/$this->cropWidth * $this->maxWidth);
    } else {
      $this->thumbWidth  = $this->image->realWidth();
      $this->thumbHeight = $this->image->realHeight();
    }
  }
  
  function buildThumbnail() {
    //set cropping offset
    $cropX = floor(($this->image->width-$this->cropWidth)/2);
    $cropY = floor(($this->image->height-$this->cropHeight)/2);
    
    //check thumbs directory exists and create it if not
    if(!file_exists(dirname($this->thumbPath)))
      Singapore::mkdir(dirname($this->thumbPath));
      
    //if file is remote then copy locally first
    if($this->image->isRemote()) {
      $ip = fopen($this->imagePath, "rb");
      $tp = fopen($this->thumbPath, "wb");
      while(fwrite($tp,fread($ip, 4095)));
      fclose($tp);
      fclose($ip);
      $this->imagePath = $this->thumbPath;
    }
    
    switch($this->config->thumbnail_software) {
      case "im" : //use ImageMagick v5.x
        $cmd  = '"'.$this->config->pathto_convert.'"';
        if($this->forceSize) $cmd .= " -crop {$this->cropWidth}x{$this->cropHeight}+{$this->cropX}+{$this->cropY}";
        $cmd .= " -geometry {$this->thumbWidth}x{$this->thumbHeight}";
        if($this->image->type == 2) $cmd .= " -quality {$this->thumbQuality}";
        if($this->config->progressive_thumbs) $cmd .= " -interlace Plane";
        if($this->config->remove_jpeg_profile) $cmd .= ' +profile "*"';
        $cmd .= ' '.escapeshellarg($this->imagePath).' '.escapeshellarg($this->thumbPath);
        
        exec($cmd);
        
        break;
      case "im6" : //use ImageMagick v6.x  
        $cmd  = '"'.$this->config->pathto_convert.'"';
        $cmd .= ' '.escapeshellarg($this->imagePath);
        if($this->config->progressive_thumbs) $cmd .= " -interlace Plane";
        if($this->image->type == 2) $cmd .= " -quality ".$this->config->thumbnail_quality;
        if($this->forceSize) $cmd .= " -crop {$this->cropWidth}x{$this->cropHeight}+{$this->cropX}+{$this->cropY}";
        $cmd .= " -resize {$this->thumbWidth}x{$this->thumbHeight}";
        if($this->config->remove_jpeg_profile) $cmd .= ' +profile "*"';
        $cmd .= ' '.escapeshellarg($this->thumbPath);
        
        exec($cmd);
        
        break;
      case "gd2" :
      case "gd1" :
      default : //use GD by default
        //read in image as appropriate type
        switch($this->image->type) {
          case 1 : $image = ImageCreateFromGIF($this->imagePath); break;
          case 3 : $image = ImageCreateFromPNG($this->imagePath); break;
          case 2 : 
          default: $image = ImageCreateFromJPEG($this->imagePath); break;
        }
        
        switch($this->config->thumbnail_software) {
        case "gd2" :
          //create blank truecolor image
          $thumb = ImageCreateTrueColor($this->thumbWidth,$this->thumbHeight);
          //resize image with resampling
          ImageCopyResampled(
            $thumb,                                $image,
            0,                 0,                  $cropX,     $cropY,
            $this->thumbWidth, $this->thumbHeight, $this->cropWidth, $this->cropHeight);
          break;
        case "gd1" :
        default :
          //create blank 256 color image
          $thumb = ImageCreate($this->thumbWidth,$this->thumbHeight);
          //resize image
          ImageCopyResized(
            $thumb,                                $image,
            0,                 0,                  $cropX,     $cropY,
            $this->thumbWidth, $this->thumbHeight, $this->cropWidth, $this->cropHeight);
          break;
        }
        
        //set image interlacing
        ImageInterlace($thumb,$this->config->progressive_thumbs);
        
        //output image of appropriate type
        switch($this->image->type) {
          case 1 :
            //GIF images are saved as PNG
          case 3 :
            ImagePNG($thumb,$this->thumbPath); 
            break;
          case 2 :
          default: 
            ImageJPEG($thumb,$this->thumbPath,$this->config->thumbnail_quality); 
            break;
        }
        ImageDestroy($image);
        ImageDestroy($thumb);
    }
    
    //set file permissions on newly created thumbnail
    @chmod($this->thumbPath, octdec($this->config->file_mode));
  }

}

?>
