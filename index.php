<?php

/**
 * Main file drives the gallery
 * 
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot org>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003 Tamlyn Rhodes
 * @version $Id: index.php,v 1.14 2003/12/14 14:39:16 tamlyn Exp $
 */

//include main class
require_once "includes/singapore.class.php";

//create a wrapper
$sg = new Singapore;

//only start session if session is already registered
if(isset($_REQUEST[$sg->config->session_name])) {
  //set session arg separator to be xml compliant
  ini_set("arg_separator.output", "&amp;");
  
  //start session
  session_name($sg->config->session_name);
  session_start();
}

//send content-type and character encoding header
header("Content-type: text/html; charset=".$sg->character_set);


//pass control over to template
include $sg->config->pathto_current_template."index.tpl.php";

?>
