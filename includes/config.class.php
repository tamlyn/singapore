<?php 

/**
 * Config class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot org>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003 Tamlyn Rhodes
 * @version $Id: config.class.php,v 1.3 2003/12/14 14:39:18 tamlyn Exp $
 */

/**
 * Reads configuration data from data/singapore.ini and stores the values 
 * as properties of itself.
 * 
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot org>
 * @copyright (c)2003 Tamlyn Rhodes
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
 
    //set runtime values
    $this->pathto_logs = $this->pathto_data_dir."logs/";
    $this->pathto_cache = $this->pathto_data_dir."cache/";
    $this->pathto_current_template = $this->pathto_templates.$this->template_name."/";
    $this->pathto_admin_template = $this->pathto_templates.$this->admin_template_name."/";
    
    //convert octal strings to integers
    if(isset($this->directory_mode) && is_string($this->directory_mode)) $this->directory_mode = octdec($this->directory_mode);
    if(isset($this->umask) && is_string($this->umask)) $this->umask = octdec($this->umask);
    
		return true;
	}
}

?>
