<h1><?php echo $sg->translator->_g("choose thumbnail") ?></h1>
  
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="action" value="changethumbnail" />
<input type="hidden" name="gallery" value="<?php echo htmlspecialchars($sg->gallery->id) ?>" />
<p><?php echo $sg->translator->_g("Choose the filename of the image used to represent this gallery.") ?></p>
  
<p><select name="sgThumbName">
  <option value="__none__"<?php if($sg->gallery->filename == "__none__") echo ' selected="true"'; ?>><?php echo $sg->translator->_g("thumbnail|None") ?></option>
  <option value="__random__"<?php if($sg->gallery->filename == "__random__") echo ' selected="true"'; ?>><?php echo $sg->translator->_g("thumbnail|Random") ?></option>
  <?php 
    foreach($sg->gallery->images as $img) {
      echo '<option value="'.$img->id.'"';
      if($sg->gallery->filename == $img->id) echo ' selected="true"';
      echo '>'.$img->getName().' ('.$img->id.")</option>\n  ";
    }
  ?>
</select></p>
<p><input type="submit" class="button" name="confirmed" value="<?php /*"*/ echo $sg->translator->_g("confirm|OK") ?>">
<input type="submit" class="button" name="confirmed" value="<?php /*"*/ echo $sg->translator->_g("confirm|Cancel") ?>"></p>
</form>
