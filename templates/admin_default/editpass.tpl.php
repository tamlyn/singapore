<h1><?php echo $sg->translator->_g("change password") ?></h1>
<p><?php echo $sg->translator->_g("Please choose a new password between 6 and 16 characters in length.") ?></p>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="action" value="savepass" />
<input type="hidden" name="sgUsername" value="<?php echo $_SESSION['sgUser']->username ?>" />
<table>
<tr>
  <td><?php echo $sg->translator->_g("Current password:") ?></td>
  <td><input type="password" name="sgOldPass" size="23" /></td>
</tr>
<tr>
  <td><?php echo $sg->translator->_g("New password:") ?></td>
  <td><input type="password" name="sgNewPass1" size="23" /></td>
</tr>
<tr>
  <td><?php echo $sg->translator->_g("Confirm password:") ?></td>
  <td><input type="password" name="sgNewPass2" size="23" /></td>
</tr>
<tr>
  <td></td>
  <td><input type="submit" class="button" value="<?php /*"*/ echo $sg->translator->_g("Save Changes") ?>" /></td>
</tr>
</table>
</form>
