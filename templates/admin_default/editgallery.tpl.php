<h1><?php echo $sg->i18n->_g("edit gallery") ?></h1>
  
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="action" value="savegallery" />
<input type="hidden" name="gallery" value="<?php echo $sg->gallery->idEntities ?>" />
<input type="hidden" name="sgOwner" value="<?php echo $sg->gallery->owner ?>" />
<input type="hidden" name="sgGroups" value="<?php echo $sg->gallery->groups ?>" />
<input type="hidden" name="sgPermissions" value="<?php echo $sg->gallery->permissions ?>" />
<input type="hidden" name="sgCategories" value="<?php echo $sg->gallery->categories ?>" />

  
<table class="formTable">
<tr>
  <td><?php echo $sg->i18n->_g("Gallery thumbnail:") ?></td>
  <td><div class="inputbox sgImageInput">
  <?php 
    if($sg->gallery->filename == "__random__")
      echo nl2br($sg->i18n->_g("Random\nthumbnail"));
    else
      echo $sg->galleryThumbnailImage(); 
  ?>
  <br />
  <a href="<?php echo $sg->formatAdminURL("changethumbnail",$sg->gallery->idEncoded) ?>"><?php echo $sg->i18n->_g("thumbnail|Change...") ?></a>
</div></td>
</tr>
<tr>
  <td><?php echo $sg->i18n->_g("Gallery name") ?></td>
  <td><input type="text" name="sgGalleryName" value="<?php echo $sg->gallery->name ?>" size="40" /></td>
</tr>
<tr>
  <td><?php echo $sg->i18n->_g("Artist name") ?></td>
  <td><input type="text" name="sgArtistName" value="<?php echo $sg->gallery->artist ?>" size="40" /></td>
</tr>
<tr>
  <td><?php echo $sg->i18n->_g("Email") ?></td>
  <td><input type="text" name="sgArtistEmail" value="<?php echo $sg->gallery->email ?>" size="40" /></td>
</tr>
<tr>
  <td><?php echo $sg->i18n->_g("Copyright") ?></td>
  <td><input type="text" name="sgCopyright" value="<?php echo $sg->gallery->copyright ?>" size="40" /></td>
</tr>
<tr>
  <td><?php echo $sg->i18n->_g("Description") ?></td>
  <td><textarea name="sgGalleryDesc" cols="70" rows="8"><?php echo $sg->galleryDescriptionStripped() ?></textarea></td>
</tr>
<tr>
  <td></td>
  <td><input type="submit" class="button" value="<?php echo $sg->i18n->_g("Save Changes") ?>" /></td>
</tr>
</table>
  
</form>
