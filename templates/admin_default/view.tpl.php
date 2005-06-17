<?php 
  if($sg->isImagePage()) {
    include $sg->config->pathto_admin_template."image.tpl.php";
  } elseif($sg->isAlbumPage()) {
    include $sg->config->pathto_admin_template."album.tpl.php";
  } else {
    include $sg->config->pathto_admin_template."gallery.tpl.php";
  }
 ?>