<h1><?php echo $sg->i18n->_g("user management"); ?></h1>

<?php if(!$sg->isAdmin()) echo '<p>'.$sg->i18n->_g("You must be an administrator to access this area.").'</p>'; ?>

<table>
<?php 
  $users = $sg->io->getUsers();
  for($i=0; $i<count($users); $i++) {
    echo "<tr>\n  ";
    echo "<td><strong>".$sg->i18n->_g("Username:")."</strong> ";
    if($users[$i]->permissions & SG_SUSPENDED) echo "<strike>"; 
    if($users[$i]->permissions & SG_ADMIN) echo "<u>"; 
    echo $users[$i]->username;
    if($users[$i]->permissions & SG_ADMIN) echo "</u>"; 
    if($users[$i]->permissions & SG_SUSPENDED) echo "</strike>";
    echo "</td>\n  "; 
    echo "<td><strong>".$sg->i18n->_g("Email:")."</strong> ".$users[$i]->email."</td>\n  ";
    echo "<td><strong>".$sg->i18n->_g("Full name:")."</strong> ".$users[$i]->fullname."</td>\n  ";
    echo '<td><a href="'.$sg->formatAdminURL("edituser", null, null, null, "&amp;user=".$users[$i]->username).'">'.$sg->i18n->_g("edit")."</a></td>\n  ";
    echo '<td><a href="'.$sg->formatAdminURL("deleteuser", null, null, null, "&amp;user=".$users[$i]->username).'">'.$sg->i18n->_g("delete")."</a></td>\n  ";
    echo '<td><a href="'.$sg->formatAdminURL("suspenduser", null, null, null, "&amp;user=".$users[$i]->username).'">'.($users[$i]->permissions & SG_SUSPENDED ? $sg->i18n->_g("unsuspend") : $sg->i18n->_g("suspend"))."</a></td>\n";
    echo "</tr>\n";
  }

?>
</table>

<h2><?php echo $sg->i18n->_g("create new user"); ?></h2>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post"><p>
  <?php echo $sg->i18n->_g("Username:"); ?>
  <input type="hidden" name="action" value="newuser" />
  <input type="input" name="user" />
  <input type="submit" class="button" value="<?php \*"*\ echo $sg->i18n->_g("Create"); ?>" />
</p></form>
