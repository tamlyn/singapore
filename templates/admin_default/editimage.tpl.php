<h1><?php echo $sg->_g("edit image") ?></h1>
  
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="action" value="saveimage" />
<input type="hidden" name="gallery" value="<?php echo htmlentities($sg->gallery->id) ?>" />
<input type="hidden" name="image" value="<?php echo htmlentities($sg->image->filename) ?>" />
<input type="hidden" name="sgThumbnail" value="<?php echo $sg->image->thumbnail ?>" />
<input type="hidden" name="sgOwner" value="<?php echo $sg->image->owner ?>" />
<input type="hidden" name="sgGroups" value="<?php echo $sg->image->groups ?>" />
<input type="hidden" name="sgPermissions" value="<?php echo $sg->image->permissions ?>" />
<input type="hidden" name="sgCategories" value="<?php echo $sg->image->categories ?>" />
<table class="formTable">
<tr>
  <td><?php echo $sg->_g("Image") ?></td>
  <td><div class="inputbox sgImageInput"><?php echo $sg->imageThumbnailImage() ?></div></td>
</tr>
<tr>
  <td><?php echo $sg->_g("Image name") ?></td>
  <td><input type="text" name="sgImageName" value="<?php echo $sg->image->name ?>" size="40" /></td>
</tr>
<tr>
  <td><?php echo $sg->_g("Artist name") ?></td>
  <td><input type="text" name="sgArtistName" value="<?php echo $sg->image->artist ?>" size="40" /></td>
</tr>
<tr>
  <td><?php echo $sg->_g("Email") ?></td>
  <td><input type="text" name="sgArtistEmail" value="<?php echo $sg->image->email ?>" size="40" /></td>
</tr>
<tr>
  <td><?php echo $sg->_g("Location") ?></td>
  <td><input type="text" name="sgLocation" value="<?php echo $sg->image->location ?>" size="40" /></td>
</tr>
<tr>
  <td><?php echo $sg->_g("Date") ?></td>
  <td><input type="text" name="sgDate" value="<?php echo $sg->image->date ?>" size="40" /></td>
</tr>
<tr>
  <td><?php echo $sg->_g("Copyright") ?></td>
  <td><input type="text" name="sgCopyright" value="<?php echo $sg->image->copyright ?>" size="40" /></td>
</tr>
<tr>
  <td><?php echo $sg->_g("Description") ?></td>
  <td><textarea name="sgImageDesc" cols="70" rows="8"><?php echo str_replace("<br />","\n",$sg->image->desc) ?></textarea></td>
</tr>
<tr>
  <td><?php echo $sg->_g("Camera") ?></td>
  <td><input type="text" name="sgField01" value="<?php echo $sg->image->camera ?>" size="40" /></td>
</tr>
<tr>
  <td><?php echo $sg->_g("Lens") ?></td>
  <td><input type="text" name="sgField02" value="<?php echo $sg->image->lens ?>" size="40" /></td>
</tr>
<tr>
  <td><?php echo $sg->_g("Film") ?></td>
  <td><input type="text" name="sgField03" value="<?php echo $sg->image->film ?>" size="40" /></td>
</tr>
<tr>
  <td><?php echo $sg->_g("Darkroom manipulation") ?></td>
  <td><input type="text" name="sgField04" value="<?php echo $sg->image->darkroom ?>" size="40" /></td>
</tr>
<tr>
  <td><?php echo $sg->_g("Digital manipulation") ?></td>
  <td><input type="text" name="sgField05" value="<?php echo $sg->image->digital ?>" size="40" /></td>
</tr>
<tr>
  <td></td>
  <td><input type="submit" class="button" value="<?php echo $sg->_g("Save Changes") ?>" /></td>
</tr>
</table>
</form>
