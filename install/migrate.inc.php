<?php 

/**
 * Contains functions used during the database migration process.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: migrate.inc.php,v 1.1 2004/12/02 12:01:59 tamlyn Exp $
 */


//output functions
function setupHeader($var)
{
  echo "\n</p>\n\n<h2>{$var}</h2>\n\n<p>\n";
}

/**
 * Print an information message. Always returns true.
 * @return true
 */
function setupMessage($var)
{
  echo "{$var}.<br />\n";
  return true;
}

/**
 * Print an error message. Always returns false.
 * @return false
 */
function setupError($var)
{
  echo "<span class=\"error\">{$var}</span>.<br />\n";
  return false;
}

 ?>