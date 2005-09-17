<h1><?php echo $sg->translator->_g("new gallery") ?></h1>
  
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="action" value="addgallery" />
<input type="hidden" name="gallery" value="<?php echo $sg->gallery->idEntities ?>" />

<table class="formTable">
  <tr>
    <td><?php echo $sg->translator->_g("Identifier:") ?></td>
    <td><input type="text" name="newgallery" value="" size="40" /></td>
  </tr>
  <tr>
    <td></td>
    <td><input type="submit" class="button" value="<?php /*"*/ echo $sg->translator->_g("Create") ?>" /></td>
  </tr>
</table>
  
</form>
