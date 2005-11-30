<?php 

/**
 * Config class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003-2005 Tamlyn Rhodes
 * @version $Id: config.class.php,v 1.8 2005/11/30 23:02:18 tamlyn Exp $
 */

/**
 * Reads configuration data from data/singapore.ini and stores the values 
 * as properties of itself.
 * 
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 */
class sgConfig
{

  /**
   * Implements the Singleton design pattern by always returning a reference
   * to the same sgConfig object. Use instead of 'new'.
   */
  function &getInstance()
  {
    static $instance;
    if(!is_object($instance))
      //note that the new config object is NOT assigned by reference as 
      //references are not stored in static variables (don't ask me...)
      $instance = new sgConfig();
    return $instance;
  }
  
  /**
	 * Parses an ini file for configuration directives and imports the values 
	 * into the current object overwriting any previous values.
	 * @param string relative or absolute path to the ini file to load
	 * @return boolean true on success; false otherwise
	 */
	function loadConfig($configFilePath)
	{
	  if(!file_exists($configFilePath)) return false;
	  
	  //get values from ini file
    $ini_values = parse_ini_file($configFilePath);

    //import values into object scope
    foreach($ini_values as $key => $value) $this->$key = $value;
 
    return true;
	}
}

?>
