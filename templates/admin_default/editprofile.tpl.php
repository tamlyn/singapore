<h1><?php echo $sg->i18n->_g("my profile"); ?></h1>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<table class="formTable">
<input type="hidden" name="action" value="saveprofile" />
<input type="hidden" name="user" value="<?php echo $_SESSION["sgUser"]->username; ?>" />
<?php 
  $users = $sg->io->getUsers();
  for($i=0; $i<count($users); $i++)
    if($users[$i]->username == $_SESSION["sgUser"]->username) {
      $user = $users[$i];
      break;
    }
?>  
<tr>
  <td><?php echo $sg->i18n->_g("Username"); ?></td>
  <td><strong><?php echo $user->username; ?></strong></td>
</tr>
<tr>
  <td><?php echo $sg->i18n->_g("Email"); ?></td>
  <td><input type="input" name="sgEmail" value="<?php echo $user->email; ?>" /></td>
</tr>
<tr>
  <td><?php echo $sg->i18n->_g("Full name"); ?></td>
  <td><input type="input" name="sgFullname" value="<?php echo $user->fullname; ?>" /></td>
</tr>
  <tr><td><?php echo $sg->i18n->_g("Description"); ?></td>
  <td><input type="input" name="sgDescription" value="<?php echo $user->description; ?>" /></td>
</tr>
<tr>
  <td></td>
  <td><input type="submit" class="button" value="<?php /*"*/ echo $sg->i18n->_g("Save Changes") ?>" /></td>
</tr>
</table>
</form>
