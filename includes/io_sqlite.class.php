<?php 

/**
 * IO class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: io_sqlite.class.php,v 1.3 2004/12/15 17:04:56 tamlyn Exp $
 */

//include the generic SQL class
require_once dirname(__FILE__)."/iosql.class.php";
 
/**
 * Class used to read and write data to and from a SQLite database.
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2004 Tamlyn Rhodes
 */
class sgIO_sqlite extends sgIOsql
{
  /**
   * Database resource pointer
   */
  var $db;
  
  /**
   * @param sgConfig pointer to a {@link sgConfig} object representing 
   *   the current script configuration
   */
  function sgIO_sqlite(&$config)
  {
    $this->config =& $config;
    $this->db = sqlite_open($this->config->base_path.$this->config->pathto_data_dir."sqlite.dat");
  }

  /**
   * Name of IO backend.
   */
  function getName()
  {
    return "SQLite";
  }

  /**
   * Version of IO backend.
   */
  function getVersion()
  {
    return "$Revision: 1.3 $";
  }

  /**
   * Author of IO backend.
   */
  function getAuthor()
  {
    return "Tamlyn Rhodes";
  }

  /**
   * Brief description of IO backend and it's requirements.
   */
  function getDescription()
  {
    return "Uses a SQLite database. Requires only the SQLite PHP extension which incorporates the database server.";
  }

  function query($query)
  {
    return sqlite_query($this->db, $query);
  }
  
  function escape_string($query)
  {
    return sqlite_escape_string($query);
  }
  
  function fetch_array($res)
  {
    return sqlite_fetch_array($res);
  }
  
  function num_rows($res)
  {
    return sqlite_num_rows($res);
  }

  function error()
  {
    return sqlite_error_string(sqlite_last_error($this->db));
  }

}

?>
