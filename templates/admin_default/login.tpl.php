<h1><?php echo $sg->_g("log in") ?></h1>

<p><?php echo $sg->_g("Only authorised users are allowed into the admin section.\nPlease enter your admin username and password below.") ?></p>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
  <input type="hidden" name="action" value="login" />
  <p>
    <?php echo $sg->_g("Username:") ?> <input type="text" name="sgUsername" />
    <?php echo $sg->_g("Password:") ?> <input type="password" name="sgPassword" />
    <input type="submit" class="button" value="<?php echo $sg->_g('log in|Go') ?>" />
  </p>
</form>
