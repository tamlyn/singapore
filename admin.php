<?php

/**
 * Admin interface file.
 *
 * Checks the selected 'action', checks user permissions, calls the appropriate 
 * methods and sets the required include file. Finally it includes the admin 
 * template's index file.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @package singapore
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: admin.php,v 1.36 2006/01/27 13:31:24 tamlyn Exp $
 */

//include admin class
require_once "includes/admin.class.php";

//create the admin object
$sg = new sgAdmin();

//set session arg separator to be xml compliant
ini_set("arg_separator.output", "&amp;");

//start session
session_name($sg->config->session_name);
@session_start();

//load user details (must be done after session_start)
$sg->loadUser();

//send content-type and character encoding header
@header("Content-type: text/html; charset=".$sg->character_set);

//perform admin action
$sg->doAction();

//pass control over to template
include $sg->config->pathto_admin_template."index.tpl.php";


?>
