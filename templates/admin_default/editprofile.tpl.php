<h1><?php echo $sg->i18n->_g("my profile"); ?></h1>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<table class="formTable">
<input type="hidden" name="action" value="saveprofile" />
<input type="hidden" name="user" value="<?php echo $_SESSION["sgUser"]->username; ?>" />
<tr>
  <td><?php echo $sg->i18n->_g("Username"); ?></td>
  <td><strong><?php echo $_SESSION["sgUser"]->username; ?></strong></td>
</tr>
<tr>
  <td><?php echo $sg->i18n->_g("Email"); ?></td>
  <td><input type="input" name="sgEmail" value="<?php echo $_SESSION["sgUser"]->email; ?>" /></td>
</tr>
<tr>
  <td><?php echo $sg->i18n->_g("Full name"); ?></td>
  <td><input type="input" name="sgFullname" value="<?php echo $_SESSION["sgUser"]->fullname; ?>" /></td>
</tr>
  <tr><td><?php echo $sg->i18n->_g("Description"); ?></td>
  <td><input type="input" name="sgDescription" value="<?php echo $_SESSION["sgUser"]->description; ?>" /></td>
</tr>
<tr>
  <td></td>
  <td><input type="submit" class="button" value="<?php \*"*\ echo $sg->i18n->_g("Save Changes") ?>" /></td>
</tr>
</table>
</form>