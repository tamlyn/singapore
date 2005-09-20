<h1><?php echo $sg->translator->_g("New Image") ?></h1>

<?php if($sg->gallery->hasChildGalleries()) echo "<p>".$sg->translator->_g("This image will not be visible because this gallery is not an album: it contains child galleries.")."</p>"; ?>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data" method="post">
<input type="hidden" name="action" value="addimage" />
<input type="hidden" name="gallery" value="<?php echo $sg->gallery->idEntities() ?>" />

  
<table class="formTable">
<tr>
  <td><input type="radio" class="radio" id="sgLocationChoiceSingle" name="sgLocationChoice" value="single" checked="true" /></td>
  <td colspan="2"><label for="sgLocationChoiceSingle"><?php echo $sg->translator->_g("Upload single file") ?></label></td>
<tr>
<tr>
  <td></td>
  <td><?php echo $sg->translator->_g("Image file to upload:") ?></td>
  <td><input type="file" name="sgImageFile" value="" size="40" /></td>
</tr>
<tr>
  <td></td>
  <td><?php echo $sg->translator->_g("Identifier:") ?></td>
  <td>
    <label for="sgNameChoiceSame"><input type="radio" class="radio" id="sgNameChoiceSame" name="sgNameChoice" value="same" checked="true" /> <?php echo $sg->translator->_g("Use filename of uploaded file.") ?></label><br />
    <label for="sgNameChoiceNew"><input type="radio" class="radio" id="sgNameChoiceNew" name="sgNameChoice" value="new" /> <?php echo $sg->translator->_g("Specify different filename:") ?></label><br />
    <input type="text" name="sgFileName" value="" size="40" /></td>
</tr>
<tr>
  <td><input type="radio" class="radio" id="sgLocationChoiceMulti" name="sgLocationChoice" value="multi" /></td>
  <td colspan="2"><label for="sgLocationChoiceMulti"><?php echo $sg->translator->_g("Upload multiple files") ?></label></td>
<tr>
<tr>
  <td></td>
  <td><?php echo $sg->translator->_g("ZIP file to upload:") ?></td>
  <td><input type="file" name="sgArchiveFile" value="" size="40" /></td>
</tr>
<tr>
  <td><input type="radio" class="radio" id="sgLocationChoiceRemote" name="sgLocationChoice" value="remote"></td>
  <td colspan="2"><label for="sgLocationChoiceRemote"><?php echo $sg->translator->_g("Add remote file") ?></label></td>
<tr>
<tr>
  <td></td>
  <td><?php echo $sg->translator->_g("URL of image:") ?></td>
  <td><input type="text" name="sgImageURL" value="http://" size="40" /></td>
</tr>
<tr>
  <td colspan="2"></td>
  <td><input type="submit" class="button" value="<?php /*"*/ echo $sg->translator->_g("Create"); ?>" /></td>
</tr>
</table>
  
</form>
