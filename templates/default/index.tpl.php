<?php

/**
 * Default singapore template.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version 1.0
 */

//include header file
include $sg->config->base_path.$sg->config->pathto_current_template."header.tpl.php";

switch($sg->action) {
case "addcomment" :
  include $sg->config->base_path.$sg->config->pathto_current_template."addcomment.tpl.php";
default :
  if($sg->isImage()) { 
    //this is an 'image' page so include the 'image' template file
    include $sg->config->base_path.$sg->config->pathto_current_template."image.tpl.php";
  } elseif($sg->isAlbum()) {
    //this is an 'album' page so include the 'album' template file
    include $sg->config->base_path.$sg->config->pathto_current_template."album.tpl.php";
  } else {
    //this is a 'gallery' page so include the 'gallery' template file
    include $sg->config->base_path.$sg->config->pathto_current_template."gallery.tpl.php";
  }
}
//include footer file
include $sg->config->base_path.$sg->config->pathto_current_template."footer.tpl.php";

?>
