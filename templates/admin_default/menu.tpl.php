<p><?php echo $sg->i18n->_g("Please choose an option:") ?></p>

<ul>
  <li><a href="<?php echo $sg->formatAdminURL("view") ?>"><?php echo $sg->i18n->_g("Manage galleries and images") ?></a></li>
  <li><a href="<?php echo $sg->formatAdminURL("showgalleryhits") ?>"><?php echo $sg->i18n->_g("View gallery hits") ?></a></li>
  <li><a href="<?php echo $sg->formatAdminURL("purgecache") ?>"><?php echo $sg->i18n->_g("Purge cached thumbnails") ?></a></li>
  <li><a href="<?php echo $sg->formatAdminURL("editpass") ?>"><?php echo $sg->i18n->_g("Change password") ?></a></li>
  <?php if($sg->isAdmin()): ?>
  <li><a href="<?php echo $sg->formatAdminURL("manageusers") ?>"><?php echo $sg->i18n->_g("Manage users") ?></a></li>
  <?php endif; ?>
  <li><a href="<?php echo $sg->formatAdminURL("logout") ?>"><?php echo $sg->i18n->_g("Log out of admin") ?></a></li>
</ul>
