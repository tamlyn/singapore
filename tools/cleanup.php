<?php

/**
 * This file attempts to recursively make all files in the parent directory
 * (i.e. the singapore root directory) writable. This will, in general, only
 * succeed on server-owned content hence making it deletable by FTP users.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2004 Tamlyn Rhodes
 * @version $Id: cleanup.php,v 1.4 2006/03/02 16:14:03 tamlyn Exp $
 */

/**
 * Recursively attempts to make all files and directories in $dir writable 
 *
 * @param string  full directory name (must end with /)
 */
function makeWritable($dir)
{
  if (is_dir($dir)) {
    $d = dir($dir);
    while (($file = $d->read()) !== false) {
      //ignore current and parent dirs and php files
      if ($file == '.' || $file == '..' || substr($file, strlen($file)-4)=='.php') continue;
      $fullfile = $d->path . $file;
      if(@chmod($fullfile,0777))
        echo "Made $fullfile writable.<br />";
      if (is_dir($fullfile))
        makeWritable($fullfile."/");
    }
  }
}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>cleanup script</title>
<link rel="stylesheet" type="text/css" href="tools.css" />
</head>

<body>

<h1>Fixing file permissions</h1>

<p><?php 
  //start with parent directory (singapore root)
  makeWritable("../");
?></p>

<p>All done! <a href="index.html">Return</a> to tools.</p>

</body>
</html>
