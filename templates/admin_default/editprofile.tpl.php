<h1><?php echo $sg->translator->_g("Edit Profile"); ?></h1>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<table class="formTable">
<input type="hidden" name="action" value="saveprofile" />
<input type="hidden" name="user" value="<?php echo $sg->user->username; ?>" />
<tr>
  <td><?php echo $sg->translator->_g("Username"); ?></td>
  <td><strong><?php echo $sg->user->username; ?></strong></td>
</tr>
<tr>
  <td><?php echo $sg->translator->_g("Email"); ?></td>
  <td><input type="input" name="sgEmail" value="<?php echo $sg->user->email; ?>" /></td>
</tr>
<tr>
  <td><?php echo $sg->translator->_g("Full name"); ?></td>
  <td><input type="input" name="sgFullname" value="<?php echo $sg->user->fullname; ?>" /></td>
</tr>
  <tr><td><?php echo $sg->translator->_g("Description"); ?></td>
  <td><input type="input" name="sgDescription" value="<?php echo $sg->user->description; ?>" /></td>
</tr>
<tr>
  <td></td>
  <td><input type="submit" class="button" value="<?php /*"*/ echo $sg->translator->_g("Save Changes") ?>" /></td>
</tr>
</table>
</form>
