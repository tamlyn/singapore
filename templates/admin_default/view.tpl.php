<?php 
  if($sg->isImage()) {
    include $sg->config->pathto_admin_template."image.tpl.php";
  } elseif($sg->isGallery()) {
    include $sg->config->pathto_admin_template."gallery.tpl.php";
  } else {
    include $sg->config->pathto_admin_template."listing.tpl.php";
  }
 ?>