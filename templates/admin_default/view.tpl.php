<?php 
  if($sg->isImagePage()) {
    include $sg->config->pathto_admin_template."viewimage.tpl.php";
  } elseif($sg->isAlbumPage()) {
    include $sg->config->pathto_admin_template."viewalbum.tpl.php";
  } else {
    include $sg->config->pathto_admin_template."viewgallery.tpl.php";
  }
 ?>