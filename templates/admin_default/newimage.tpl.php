<h1><?php echo $sg->i18n->_g("new image") ?></h1>

<?php if($sg->galleryHasSubGalleries()) echo "<p>".$sg->i18n->_g("This image will not be visible because this gallery contains subgalleries.")."</p>"; ?>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data" method="post">
<input type="hidden" name="action" value="addimage" />
<input type="hidden" name="gallery" value="<?php echo $sg->gallery->idEntities ?>" />

  
<table class="formTable">
<tr>
  <td><input type="radio" class="radio" name="sgLocationChoice" value="remote"> <?php echo $sg->i18n->_g("Remote file") ?></td>
  <td></td>
<tr>
<tr>
  <td><?php echo $sg->i18n->_g("URL of image:") ?></td>
  <td><input type="text" name="sgImageURL" value="http://" size="40" /></td>
</tr>
<tr>
  <td><input type="radio" class="radio" name="sgLocationChoice" value="local" checked="true"> <?php echo $sg->i18n->_g("Local file") ?></td>
  <td></td>
<tr>
<tr>
  <td><?php echo $sg->i18n->_g("Image file to upload:") ?></td>
  <td><input type="file" name="sgImageFile" value="" size="40" /></td>
</tr>
<tr>
  <td><?php echo $sg->i18n->_g("Identifier:") ?></td>
  <td>
    <input type="radio" class="radio" name="sgNameChoice" value="same" checked="true"> <?php echo $sg->i18n->_g("Use filename of uploaded file.") ?><br />
    <input type="radio" class="radio" name="sgNameChoice" value="new"> <?php echo $sg->i18n->_g("Specify different filename:") ?><br />
    <input type="text" name="sgFileName" value="" size="40" /></td>
</tr>
<tr>
  <td></td>
  <td><input type="submit" class="button" value="<?php echo $sg->i18n->_g("Create") ?>" /></td>
</tr>
</table>
  
</form>
