<?php 

/**
 * Config class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: config.class.php,v 1.5 2004/09/06 16:30:23 tamlyn Exp $
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
   * Tries to load the default configuration
   */
  function sgConfig($configFilePath = "singapore.ini")
  {
    $this->loadConfig($configFilePath);
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
