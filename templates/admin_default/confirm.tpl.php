<h1><?php echo $confirmTitle ?></h1>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<?php 
  foreach($_REQUEST as $name => $value)
    echo "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
?>
<p><?php echo $confirmMessage ?></p>
<p><input type="submit" class="button" name="confirmed" value="<?php echo $sg->_g("confirm|OK") ?>">
<input type="submit" class="button" name="confirmed" value="<?php echo $sg->_g("confirm|Cancel") ?>"></p>
</form>
