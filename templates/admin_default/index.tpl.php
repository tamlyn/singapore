<?php

/**
 * Default singapore admin template.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot org>
 * @copyright (c)2003 Tamlyn Rhodes
 * @version 1.0
 */


//include header file
include $sg->config->pathto_admin_template."header.tpl.php";

//include selected file
include $sg->config->pathto_admin_template.$includeFile.".tpl.php";

//include footer file
include $sg->config->pathto_admin_template."footer.tpl.php";

?>
