<?php

/**
 * Use this script to batch generate all main and preview thumbnails for all 
 * galleries. Galleries which contain sub-galleries are skipped as are hidden 
 * galleries.
 *
 * Currently this is a bit of a hack. Hopefully a later version of the script 
 * will be built more robustly using the singapore class to greater advantage.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2004 Tamlyn Rhodes
 * @version 0.1
 */

//relative path to the singapore base installation
$basePath = '../';

//remove the built in time limit
set_time_limit(0);

// require main class
require_once $basePath."includes/singapore.class.php";

//create singapore object
$sg = new Singapore($basePath);

//start recursive thumbnail generation 
showAllThumbnails($sg, ".");

function showAllThumbnails($sg, $galleryId)
{
  $dir = $sg->getListing($sg->config->pathto_galleries.$galleryId.'/',"images");
  
  //if contains subgalleries, recurse
  if(!empty($dir->dirs))
    foreach($dir->dirs as $subgal)
      showAllThumbnails($sg, $galleryId.'/'.$subgal);
  //otherwise display thumbnails
  else
    foreach($dir->files as $image) {
      if($sg->config->max_image_size) 
        echo '<img src="'.$sg->config->base_path.'thumb.php?gallery='.urlencode($galleryId).'&amp;image='.urlencode($image).'&size='.$sg->config->max_image_size.'" alt="" /> ';
      echo '<img src="'.$sg->config->base_path.'thumb.php?gallery='.urlencode($galleryId).'&amp;image='.urlencode($image).'&size='.$sg->config->main_thumb_size.'" alt="" /> ';
      echo '<img src="'.$sg->config->base_path.'thumb.php?gallery='.urlencode($galleryId).'&amp;image='.urlencode($image).'&size='.$sg->config->preview_thumb_size.'" alt="" /> ';
      echo "<br />\n";
      
      //trying to generate all thumbnails effectively simultaneously can cause problems
      //this function call pauses execution for a number of microseconds after each image 
      usleep(5000);
    }
  
  flush();
}

?>