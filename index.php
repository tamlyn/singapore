<?php

/**
 * Main file drives the gallery.
 *
 * This file may be included into another but be sure to check the consequences 
 * of this in the readme.
 * 
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: index.php,v 1.17 2004/10/11 05:24:11 tamlyn Exp $
 */

//you may leave this blank as it will be automatically detected
$relativePath = "";

//check whether this file has been included by another and if 
//so calculate the relative path and instantiate $sg with it 
if(isset($_SERVER["SCRIPT_FILENAME"]) && realpath(__FILE__) != realpath($_SERVER["SCRIPT_FILENAME"])) {
  //split each path into chunks
  $you = explode(DIRECTORY_SEPARATOR,dirname(realpath($_SERVER["SCRIPT_FILENAME"])));
  $me  = explode(DIRECTORY_SEPARATOR,dirname(realpath(__FILE__)));
  $i=0;
  //find at what level paths first differ
  while($i<count($you) && $i<count($me) && $you[$i] == $me[$i]) $i++;
  //travel up appropriate number of directories
  for($j=$i;$j<count($you);$j++) $relativePath .= "../";
  //travel down appropriate directories
  for($j=$i;$j<count($me); $j++) $relativePath .= $me[$j]."/";
}
  

//include main class
require_once $relativePath."includes/singapore.class.php";

//create a wrapper
$sg = new Singapore($relativePath);

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
include $sg->config->base_path.$sg->config->pathto_current_template."index.tpl.php";

?>
