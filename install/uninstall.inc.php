<?php 

/**
 * Contains functions used during the uninstall process.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: uninstall.inc.php,v 1.1 2004/12/02 12:01:59 tamlyn Exp $
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
      if ($file == '.' || $file == '..') continue;
      $fullfile = $d->path . $file;
      if(!is_writable($fullfile) && @chmod($fullfile,0777))
        setupMessage("Made $fullfile writable");
      if (is_dir($fullfile))
        makeWritable($fullfile."/");
    }
  }
}


/**
 * Drops all tables created by singapore.
 * @param sgIO_sql  pointer to a singapore SQL backend object
 */
function sqlDropTables($io) {
  $success = true;
  setupHeader("Deleting tables");
  if(!$io->query("SELECT * FROM ".$io->config->sql_prefix."galleries"))
    setupMessage("'".$io->config->sql_prefix."galleries' table not found - skipped");
  elseif($io->query("DROP TABLE ".$io->config->sql_prefix."galleries")) 
    setupMessage("'".$io->config->sql_prefix."galleries' table deleted");
  else
    $success = setupError("Unable to delete '".$io->config->sql_prefix."galleries' table:".$io->error());
    
  if(!$io->query("SELECT * FROM ".$io->config->sql_prefix."images"))
    setupMessage("'".$io->config->sql_prefix."images' table not found - skipped");
  elseif($io->query("DROP TABLE ".$io->config->sql_prefix."images")) 
    setupMessage("'".$io->config->sql_prefix."images' table deleted");
  else
    $success = setupError("Unable to delete '".$io->config->sql_prefix."images' table:".$io->error());
    
  if(!$io->query("SELECT * FROM ".$io->config->sql_prefix."users"))
    setupMessage("'".$io->config->sql_prefix."users' table not found - skipped");
  elseif($io->query("DROP TABLE ".$io->config->sql_prefix."users")) 
    setupMessage("'".$io->config->sql_prefix."users' table deleted");
  else
    $success = setupError("Unable to delete '".$io->config->sql_prefix."users' table:".$io->error());
    
  return $success;
}


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