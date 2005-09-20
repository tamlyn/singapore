<h1><?php echo $sg->translator->_g("Log In") ?></h1>

<p><?php echo $sg->translator->_g("Please enter your admin username and password below.") ?></p>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
  <input type="hidden" name="action" value="login" />
  <p>
    <?php echo $sg->translator->_g("Username:") ?> <input type="text" name="sgUsername" />
    <?php echo $sg->translator->_g("Password:") ?> <input type="password" name="sgPassword" />
    <input type="submit" class="button" value="<?php /*"*/ echo $sg->translator->_g("Go") ?>" />
  </p>
</form>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
  <input type="hidden" name="action" value="login" />
  <input type="hidden" name="sgUsername" value="guest" />
  <input type="hidden" name="sgPassword" value="password" />
  <p>
    <?php echo $sg->translator->_g("If you do not have a username then you may log in as a guest."); ?> 
    <input type="submit" class="button" value="<?php /*"*/ echo $sg->translator->_g("Log in as guest"); ?>" />
  </p>
</form>
