<?php

/**
 * Default singapore admin template.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version 1.0
 */


//include header file
include $sg->config->base_path.$sg->config->pathto_admin_template."header.tpl.php";

//include selected file
include $sg->config->base_path.$sg->config->pathto_admin_template.$includeFile.".tpl.php";

//include footer file
include $sg->config->base_path.$sg->config->pathto_admin_template."footer.tpl.php";

?>
