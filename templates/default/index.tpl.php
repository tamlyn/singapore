<?php

/**
 * Default singapore template.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot org>
 * @copyright (c)2003 Tamlyn Rhodes
 * @version 1.0
 */

//include header file
include $sg->config->pathto_current_template."header.tpl.php";

if($sg->isImage()) {
  include $sg->config->pathto_current_template."image.tpl.php";
} elseif($sg->isGallery()) {
  include $sg->config->pathto_current_template."gallery.tpl.php";
} else {
  include $sg->config->pathto_current_template."listing.tpl.php";
}

//include footer file
include $sg->config->pathto_current_template."footer.tpl.php";

?>
