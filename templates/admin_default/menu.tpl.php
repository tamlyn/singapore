<p><?php echo $sg->translator->_g("Please choose an option:") ?></p>

<ul>
  <li><a href="<?php echo $sg->formatAdminURL("view") ?>"><?php echo $sg->translator->_g("Manage galleries and images") ?></a></li>
  <li><a href="<?php echo $sg->formatAdminURL("showgalleryhits") ?>"><?php echo $sg->translator->_g("View gallery hits") ?></a></li>
  <?php if(!$sg->user->isGuest()): ?>
  <li><a href="<?php echo $sg->formatAdminURL("editpass") ?>"><?php echo $sg->translator->_g("Change password") ?></a></li>
  <li><a href="<?php echo $sg->formatAdminURL("editprofile") ?>"><?php echo $sg->translator->_g("My profile") ?></a></li>
  <?php endif; ?>
  <?php if($sg->user->isAdmin()): ?>
  <li><a href="<?php echo $sg->formatAdminURL("manageusers") ?>"><?php echo $sg->translator->_g("Manage users") ?></a></li>
  <li><a href="<?php echo $sg->formatAdminURL("purgecache") ?>"><?php echo $sg->translator->_g("Purge cached thumbnails") ?></a></li>
  <?php endif; ?>
  <li><a href="<?php echo $sg->formatAdminURL("logout") ?>"><?php echo $sg->translator->_g("Log out of admin") ?></a></li>
</ul>
