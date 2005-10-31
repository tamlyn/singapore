<h1><?php echo $sg->translator->_g("Move or Copy Items") ?></h1>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="action" value="multimove" />
<input type="hidden" name="gallery" value="<?php echo $sg->gallery->idEntities() ?>" />
<?php 
  if(isset($_REQUEST["sgGalleries"]))
    foreach($_REQUEST["sgGalleries"] as $name => $value)
      echo "<input type=\"hidden\" name=\"sgGalleries[$name]\" value=\"$value\" />\n";
  elseif(isset($_REQUEST["sgImages"]))
    foreach($_REQUEST["sgImages"] as $name => $value)
      echo "<input type=\"hidden\" name=\"sgImages[$name]\" value=\"$value\" />\n";
?>
<p>Select the gallery to which you wish to move or copy the selected items.</p>

<p><select name="sgMoveTarget">
<?php
  foreach($sg->allGalleriesArray() as $gal)
    //if(strpos($gal->id, $sg->gallery->id) !== 0)
      echo '<option value="'.$gal->idEntities().'">'.$gal->idEntities()." (".$gal->name().")</option>\n";
?>
</select></p>
<p>
  <label><input type="radio" name="sgMoveType" value="move" /> Move</label>
  <label><input type="radio" name="sgMoveType" value="copy" /> Copy</label>
</p>
<p><input type="submit" class="button" name="confirmed" value="<?php /*"*/ echo $sg->translator->_g("confirm|OK") ?>">
<input type="submit" class="button" name="confirmed" value="<?php /*"*/ echo $sg->translator->_g("confirm|Cancel") ?>"></p>
</form>
