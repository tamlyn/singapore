<h1><?php echo $confirmTitle ?></h1>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<?php 
  foreach($_REQUEST as $name => $value)
    if(is_array($value))
      foreach($value as $subname => $subvalue)
        echo "<input type=\"hidden\" name=\"{$name}[{$subname}]\" value=\"$subvalue\" />\n";
    else
      echo "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
?>
<p><?php echo $confirmMessage ?></p>
<p><input type="submit" class="button" name="confirmed" value="<?php /*"*/ echo $sg->translator->_g("confirm|OK") ?>">
<input type="submit" class="button" name="confirmed" value="<?php /*"*/ echo $sg->translator->_g("confirm|Cancel") ?>"></p>
</form>
