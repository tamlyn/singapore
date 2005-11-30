<?php 

/**
 * IO class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: io_mysql.class.php,v 1.7 2005/11/30 23:02:18 tamlyn Exp $
 */

//include the base IO class and generic SQL class
require_once dirname(__FILE__)."/iosql.class.php";
 
/**
 * Class used to read and write data to and from a MySQL database.
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2004 Tamlyn Rhodes
 */
class sgIO_mysql extends sgIOsql
{
  /**
   * @param sgConfig pointer to a {@link sgConfig} object representing 
   *   the current script configuration
   */
  function sgIO_mysql()
  {
    $this->config =& sgConfig::getInstance();
    mysql_connect($this->config->sql_host, $this->config->sql_user, $this->config->sql_pass);
    mysql_select_db($this->config->sql_database);
  }

  /**
   * Name of IO backend.
   */
  function getName()
  {
    return "MySQL";
  }

  /**
   * Version of IO backend.
   */
  function getVersion()
  {
    return "$Revision: 1.7 $";
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
    return "Uses a MySQL database. Requires a MySQL database server and the MySQL PHP extension.";
  }

  function query($query)
  {
    return mysql_query($query);
  }
  
  function escape_string($query)
  {
    return mysql_escape_string($query);
  }
  
  function fetch_array($res)
  {
    return mysql_fetch_array($res);
  }
  
  function num_rows($res)
  {
    return mysql_num_rows($res);
  }

  function error()
  {
    return mysql_error();
  }

}

?>
