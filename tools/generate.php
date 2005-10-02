<?php

/**
 * Use this script to batch generate all main and preview thumbnails for all 
 * galleries. Galleries which contain sub-galleries are skipped as are hidden 
 * galleries.
 *
 * Currently this is a bit of a hack. Hopefully a later version of the script 
 * will be built more robustly using the singapore class to greater advantage.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2004-2005 Tamlyn Rhodes
 * @version 0.1
 */

//relative path to the singapore base installation
$basePath = '../';

//remove the built in time limit
set_time_limit(0);

// require main class
require_once $basePath."includes/singapore.class.php";

//create singapore object
$sg = new Singapore($basePath);

function showAllThumbnails(&$sg, &$gal)
{
  echo "<li>Entering <code>".$gal->name()."</code></li>\n";
  echo "<ul>\n";
  echo "<li>".$gal->thumbnailHTML()."</li>\n";
  
  if($gal->isGallery()) {
    foreach($gal->galleries as $subgal)
      showAllThumbnails($sg, $sg->io->getGallery($subgal->id, $gal));
  } else
    foreach($gal->images as $img)
      echo "<li>".$img->thumbnailHTML().$img->thumbnailHTML("","preview")."</li>\n";

  echo "</ul>\n";
  
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>batch thumbnail generator</title>
<link rel="stylesheet" type="text/css" href="tools.css" />
</head>

<body>

<h1>Generating thumbnails</h1>

<?php
  //start recursive thumbnail generation 
  showAllThumbnails($sg, $sg->gallery);
?>

<p>All done! <a href="index.html">Return</a> to tools.</p>

</body>
</html>