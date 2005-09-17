<h1><?php echo $sg->translator->_g("access control") ?></h1>

<?php 
  $obj = $sg->isImage() ? $sg->image : $sg->gallery;
?>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="action" value="savepermissions" />
<input type="hidden" name="gallery" value="<?php echo $sg->gallery->idEntities ?>" />

<table class="formTable">
<tr>
  <td><?php echo $sg->translator->_g("Owner") ?></td>
  <td><?php 
    if($sg->isAdmin())
      echo '<input type="text" name="sgOwner" value="'.$sg->gallery->owner.'" />';
    else 
      echo '<strong>'.$sg->gallery->owner.'</strong>';
  ?></td>
</tr>
<tr>
  <td><?php echo $sg->translator->_g("Groups") ?></td>
  <td><input type="text" name="sgGroups" value="<?php echo $sg->gallery->groups ?>" /></td>
</tr>
<tr>
  <td><?php echo $sg->translator->_g("access control|Read") ?></td>
  <td><div class="inputbox">
<?php 
  if(($obj->permissions & SG_IHR_READ) == SG_IHR_READ) $checked = 0;
  elseif($obj->permissions & SG_GRP_READ) $checked = 1;
  elseif($obj->permissions & SG_WLD_READ) $checked = 2;
  else $checked = 3;
?>
    <label for="sgOwnRead"><input type="radio" class="radio" id="sgOwnRead" name="sgRead" value="owner" <?php if($checked == 3) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|Owner") ?></label>
    <label for="sgGrpRead"><input type="radio" class="radio" id="sgGrpRead" name="sgRead" value="group" <?php if($checked == 1) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|Group") ?></label>
    <label for="sgWldRead"><input type="radio" class="radio" id="sgWldRead" name="sgRead" value="world" <?php if($checked == 2) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|World") ?></label>
<?php if(!$sg->galleryIsRoot()): ?>
    <label for="sgIhrRead"><input type="radio" class="radio" id="sgIhrRead" name="sgRead" value="inherit" <?php if($checked == 0) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|Inherit") ?></label>
<?php endif; ?>
  </div></td>
</tr>
<tr>
  <td><?php echo $sg->translator->_g("access control|Edit") ?></td>
  <td><div class="inputbox">
<?php 
  if(($obj->permissions & SG_IHR_EDIT) == SG_IHR_EDIT) $checked = 0;
  elseif($obj->permissions & SG_GRP_EDIT) $checked = 1;
  elseif($obj->permissions & SG_WLD_EDIT) $checked = 2;
  else $checked = 3;
?>
    <label for="sgOwnEdit"><input type="radio" class="radio" id="sgOwnEdit" name="sgEdit" value="owner" <?php if($checked == 3) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|Owner") ?></label>
    <label for="sgGrpEdit"><input type="radio" class="radio" id="sgGrpEdit" name="sgEdit" value="group" <?php if($checked == 1) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|Group") ?></label>
    <label for="sgWldEdit"><input type="radio" class="radio" id="sgWldEdit" name="sgEdit" value="world" <?php if($checked == 2) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|World") ?></label>
<?php if(!$sg->galleryIsRoot()): ?>
    <label for="sgIhrEdit"><input type="radio" class="radio" id="sgIhrEdit" name="sgEdit" value="inherit" <?php if($checked == 0) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|Inherit") ?></label>
<?php endif; ?>
  </div></td>
</tr>
<tr>
  <td><?php echo $sg->translator->_g("access control|Add") ?></td>
  <td><div class="inputbox">
<?php 
  if(($obj->permissions & SG_IHR_ADD) == SG_IHR_ADD) $checked = 0;
  elseif($obj->permissions & SG_GRP_ADD) $checked = 1;
  elseif($obj->permissions & SG_WLD_ADD) $checked = 2;
  else $checked = 3;
?>
    <label for="sgOwnAdd"><input type="radio" class="radio" id="sgOwnAdd" name="sgAdd" value="owner" <?php if($checked == 3) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|Owner") ?></label>
    <label for="sgGrpAdd"><input type="radio" class="radio" id="sgGrpAdd" name="sgAdd" value="group" <?php if($checked == 1) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|Group") ?></label>
    <label for="sgWldAdd"><input type="radio" class="radio" id="sgWldAdd" name="sgAdd" value="world" <?php if($checked == 2) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|World") ?></label>
<?php if(!$sg->galleryIsRoot()): ?>
    <label for="sgIhrAdd"><input type="radio" class="radio" id="sgIhrAdd" name="sgAdd" value="inherit" <?php if($checked == 0) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|Inherit") ?></label>
<?php endif; ?>
  </div></td>
</tr>
<tr>
  <td><?php echo $sg->translator->_g("access control|Delete") ?></td>
  <td><div class="inputbox">
<?php 
  if(($obj->permissions & SG_IHR_DELETE) == SG_IHR_DELETE) $checked = 0;
  elseif($obj->permissions & SG_GRP_DELETE) $checked = 1;
  elseif($obj->permissions & SG_WLD_DELETE) $checked = 2;
  else $checked = 3;
?>
    <label for="sgOwnDelete"><input type="radio" class="radio" id="sgOwnDelete" name="sgDelete" value="owner" <?php if($checked == 3) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|Owner") ?></label>
    <label for="sgGrpDelete"><input type="radio" class="radio" id="sgGrpDelete" name="sgDelete" value="group" <?php if($checked == 1) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|Group") ?></label>
    <label for="sgWldDelete"><input type="radio" class="radio" id="sgWldDelete" name="sgDelete" value="world" <?php if($checked == 2) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|World") ?></label>
<?php if(!$sg->galleryIsRoot()): ?>
    <label for="sgIhrDelete"><input type="radio" class="radio" id="sgIhrDelete" name="sgDelete" value="inherit" <?php if($checked == 0) echo 'checked="true" '; ?>/> <?php echo $sg->translator->_g("permissions|Inherit") ?></label>
<?php endif; ?>
  </div></td>
</tr>
<tr>
  <td></td>
  <td><input type="submit" class="button" value="<?php /*"*/ echo $sg->translator->_g("Save Changes") ?>" /></td>
</tr>
</table>
  
</form>
