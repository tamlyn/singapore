<h1><?php echo $sg->i18n->_g("edit permissions") ?></h1>

<?php 
  $obj = $sg->isImage() ? $sg->image : $sg->gallery;
?>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="action" value="savepermissions" />
<input type="hidden" name="gallery" value="<?php echo $sg->gallery->idEntities ?>" />

<table class="formTable">
<tr>
  <td><?php echo $sg->i18n->_g("Owner") ?></td>
  <td><?php 
    if($sg->isAdmin())
      echo '<input type="text" name="sgOwner" value="'.$sg->gallery->owner.'" />';
    else 
      echo '<strong>'.$sg->gallery->owner.'</strong>';
  ?></td>
</tr>
<tr>
  <td><?php echo $sg->i18n->_g("Groups") ?></td>
  <td><input type="text" name="sgGroups" value="<?php echo $sg->gallery->groups ?>" /></td>
</tr>
<tr>
  <td><?php echo $sg->i18n->_g("Group permissions") ?></td>
  <td><div class="inputbox">
    <label for="sgGrpRead"><input type="checkbox" class="checkbox" id="sgGrpRead" name="sgGrpRead" <?php if($obj->permissions & SG_GRP_READ) echo 'checked="true" '; ?>/> <?php echo $sg->i18n->_g("permissions|Read") ?></label>
    <label for="sgGrpEdit"><input type="checkbox" class="checkbox" id="sgGrpEdit" name="sgGrpEdit" <?php if($obj->permissions & SG_GRP_EDIT) echo 'checked="true" '; ?>/> <?php echo $sg->i18n->_g("permissions|Edit") ?></label>
    <label for="sgGrpAdd"><input type="checkbox" class="checkbox" id="sgGrpAdd" name="sgGrpAdd" <?php if($obj->permissions & SG_GRP_ADD) echo 'checked="true" '; ?>/> <?php echo $sg->i18n->_g("permissions|Add") ?></label>
    <label for="sgGrpDelete"><input type="checkbox" class="checkbox" id="sgGrpDelete" name="sgGrpDelete" <?php if($obj->permissions & SG_GRP_DELETE) echo 'checked="true" '; ?>/> <?php echo $sg->i18n->_g("permissions|Delete") ?></label>
  </div></td>
</tr>
<tr>
  <td><?php echo $sg->i18n->_g("World permissions") ?></td>
  <td><div class="inputbox">
    <label for="sgWldRead"><input type="checkbox" class="checkbox" id="sgWldRead" name="sgWldRead" <?php if($obj->permissions & SG_WLD_READ) echo 'checked="true" '; ?>/> <?php echo $sg->i18n->_g("permissions|Read") ?></label>
    <label for="sgWldEdit"><input type="checkbox" class="checkbox" id="sgWldEdit" name="sgWldEdit" <?php if($obj->permissions & SG_WLD_EDIT) echo 'checked="true" '; ?>/> <?php echo $sg->i18n->_g("permissions|Edit") ?></label>
    <label for="sgWldAdd"><input type="checkbox" class="checkbox" id="sgWldAdd" name="sgWldAdd" <?php if($obj->permissions & SG_WLD_ADD) echo 'checked="true" '; ?>/> <?php echo $sg->i18n->_g("permissions|Add") ?></label>
    <label for="sgWldDelete"><input type="checkbox" class="checkbox" id="sgWldDelete" name="sgWldDelete" <?php if($obj->permissions & SG_WLD_DELETE) echo 'checked="true" '; ?>/> <?php echo $sg->i18n->_g("permissions|Delete") ?></label>
  </div></td>
</tr>
<tr>
  <td></td>
  <td><input type="submit" class="button" value="<?php /*"*/ echo $sg->i18n->_g("Save Changes") ?>" /></td>
</tr>
</table>
  
</form>
