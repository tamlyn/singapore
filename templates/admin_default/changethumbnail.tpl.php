<h1><?php echo $sg->i18n->_g("choose thumbnail") ?></h1>
  
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="action" value="changethumbnail" />
<input type="hidden" name="gallery" value="<?php echo htmlspecialchars($sg->gallery->id) ?>" />
<p><?php echo $sg->i18n->_g("Choose the filename of the image used to represent this gallery.") ?></p>
  
<p><select name="sgThumbName">
  <option value="__none__"><?php echo $sg->i18n->_g("thumbnail|None") ?></option>
  <option value="__random__"><?php echo $sg->i18n->_g("thumbnail|Random") ?></option>
  <?php 
    foreach($sg->gallery->images as $img)
      if($sg->gallery->filename == $img->filename)
        echo "<option value=\"$img->filename\" selected=\"true\">$img->name ($img->filename)</option>\n  ";
      else
        echo "<option value=\"$img->filename\">$img->name ($img->filename)</option>\n  ";
  ?>
</select></p>
<p><input type="submit" class="button" name="confirmed" value="<?php \*"*\ echo $sg->i18n->_g("confirm|OK") ?>">
<input type="submit" class="button" name="confirmed" value="<?php \*"*\ echo $sg->i18n->_g("confirm|Cancel") ?>"></p>
</form>
