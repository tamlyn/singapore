<h1><?php echo $sg->i18n->_g("user management"); ?></h1>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<table class="formTable">
<input type="hidden" name="action" value="saveuser" />
<input type="hidden" name="user" value="<?php echo $_REQUEST["user"]; ?>" />
<?php 
  $users = $sg->io->getUsers();
  for($i=0; $i<count($users); $i++)
    if($users[$i]->username == $_REQUEST["user"]) {
      $user = $users[$i];
      break;
    }
  
  echo "<tr><td>".$sg->i18n->_g("Username")."</td><td><strong>".$user->username."</strong></td></tr>\n";
  if($sg->isAdmin())
    echo "<tr><td>".$sg->i18n->_g("Type").'</td><td>'.
         '<input type="radio" class="radio" name="sgType" value="admin"'.($user->permissions & SG_ADMIN ? ' checked="true"' : "").' />'.$sg->i18n->_g("Administrator")."<br />\n".
         '<input type="radio" class="radio" name="sgType" value="user"'. ($user->permissions & SG_ADMIN ? "" : ' checked="true"').' />'.$sg->i18n->_g("User")."</td></tr>\n";
  if($sg->isAdmin())
    echo "<tr><td>".$sg->i18n->_g("Groups").'</td><td><input type="input" name="sgGroups" value="'.$user->groups."\" /></td></tr>\n";
  echo "<tr><td>".$sg->i18n->_g("Email").'</td><td><input type="input" name="sgEmail" value="'.$user->email."\" /></td></tr>\n";
  echo "<tr><td>".$sg->i18n->_g("Full name").'</td><td><input type="input" name="sgFullname" value="'.$user->fullname."\" /></td></tr>\n";
  echo "<tr><td>".$sg->i18n->_g("Description").'</td><td><input type="input" name="sgDescription" value="'.$user->description."\" /></td></tr>\n";
  if($sg->isAdmin())
    echo "<tr><td>".$sg->i18n->_g("Password").'</td><td><input type="input" name="sgPassword" value="**********" /></td></tr>'."\n";
  
?>
<tr>
  <td></td>
  <td><input type="submit" class="button" value="<?php echo $sg->i18n->_g("Save Changes") ?>" /></td>
</tr>
</table>
</form>