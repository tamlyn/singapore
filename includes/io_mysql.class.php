<?php 

/**
 * IO class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: io_mysql.class.php,v 1.4 2004/12/01 23:55:27 tamlyn Exp $
 */

/**
 * Class used to read and write data to and from a MySQL database.
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2004 Tamlyn Rhodes
 */
class sgIO_mysql extends sgIO_sql
{
  /**
   * @param sgConfig pointer to a {@link sgConfig} object representing 
   *   the current script configuration
   */
  function sgIO_mysql(&$config)
  {
    $this->config =& $config;
    mysql_connect($this->config->sql_host, $this->config->sql_user, $this->config->sql_pass);
    mysql_select_db($this->config->sql_database);
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