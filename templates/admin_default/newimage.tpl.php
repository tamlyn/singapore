<h1><?php echo $sg->i18n->_g("new image") ?></h1>

<?php if($sg->galleryHasSubGalleries()) echo "<p>".$sg->i18n->_g("This image will not be visible because this gallery contains subgalleries.")."</p>"; ?>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data" method="post">
<input type="hidden" name="action" value="addimage" />
<input type="hidden" name="gallery" value="<?php echo $sg->gallery->idEntities ?>" />

  
<table class="formTable">
<tr>
  <td><input type="radio" class="radio" name="sgLocationChoice" value="single" checked="true" /></td>
  <td colspan="2"><?php echo $sg->i18n->_g("Upload single file") ?></td>
<tr>
<tr>
  <td></td>
  <td><?php echo $sg->i18n->_g("Image file to upload:") ?></td>
  <td><input type="file" name="sgImageFile" value="" size="40" /></td>
</tr>
<tr>
  <td></td>
  <td><?php echo $sg->i18n->_g("Identifier:") ?></td>
  <td>
    <input type="radio" class="radio" name="sgNameChoice" value="same" checked="true" /> <?php echo $sg->i18n->_g("Use filename of uploaded file.") ?><br />
    <input type="radio" class="radio" name="sgNameChoice" value="new" /> <?php echo $sg->i18n->_g("Specify different filename:") ?><br />
    <input type="text" name="sgFileName" value="" size="40" /></td>
</tr>
<tr>
  <td><input type="radio" class="radio" name="sgLocationChoice" value="multi" /></td>
  <td colspan="2"><?php echo $sg->i18n->_g("Upload multiple files") ?></td>
<tr>
<tr>
  <td></td>
  <td><?php echo $sg->i18n->_g("ZIP file to upload:") ?></td>
  <td><input type="file" name="sgArchiveFile" value="" size="40" /></td>
</tr>
<tr>
  <td><input type="radio" class="radio" name="sgLocationChoice" value="remote"></td>
  <td colspan="2"><?php echo $sg->i18n->_g("Add remote file") ?></td>
<tr>
<tr>
  <td></td>
  <td><?php echo $sg->i18n->_g("URL of image:") ?></td>
  <td><input type="text" name="sgImageURL" value="http://" size="40" /></td>
</tr>
<tr>
  <td colspan="2"></td>
  <td><input type="submit" class="button" value="<?php echo $sg->i18n->_g("Create") ?>" /></td>
</tr>
</table>
  
</form>
