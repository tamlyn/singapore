<?php 

/**
 * Translation class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003-2005 Tamlyn Rhodes
 * @version $Id: translator.class.php,v 1.6 2010/08/28 07:03:37 zhangweiwu Exp $
 */
 
/**
 * Provides functions for translating strings using GNU Gettext PO files
 * @package singapore
 * @author Joel Sjögren <joel dot sjogren at nonea dot se>
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 */
class Translator
{
  /**
   * Array of language strings in the form
   *   "english string" => "foreign string"
   * @private
   * @var array
   */
  var $languageStrings = array();
  
  var $language = "en";

  /**
   * Constructor
   * @param string  language code
   * @private
   */
  function Translator($language)
  {
    $this->language = $language;
  }
  
  /**
   * Implements a version of the Singleton design pattern by always returning 
   * a reference to the same Translator object for each language. If no 
   * language is specified then the first loaded Translator is returned.
   * @param string  language code (optional)
   * @static
   */
  function &getInstance($language = 0)
  {
    static $instances = array();
    
    $key = empty($instances) ? 0 : $language;
    
    if(!isset($instances[$key]))
      //note that the new object is NOT assigned by reference as 
      //references are not stored in static variables (don't ask me...)
      $instances[$key] = new Translator($language);
    return $instances[$key];
  }
  
  /**
   * Reads a language file and saves the strings in an array.
   * Note that the language code is used in the filename for the
   * datafile, and is case sensitive.
   *
   * @author   Joel Sjögren <joel dot sjogren at nonea dot se>
   * @param    string    file to load
   * @return   bool      success
   */
  function readLanguageFile($languageFile)
  {
      // Look for the language file
      if(!file_exists($languageFile))
        return false;
      
      // Open the file
      $fp = @fopen($languageFile, "r");
      if (!$fp) return false;
      
      // Read contents
      $str = '';
      while (!feof($fp)) $str .= fread($fp, 1024);
      // Unserialize
      $newStrings = @unserialize($str);
      if (ini_get("mbstring.func_overload") == "7") {
        $encoding = $newStrings[0]["charset"] == 'gb2312' ? "CP936" : $newStrings[0]["charset"];
        foreach ($newStrings as $key => $value) {
          if (is_string($value))
            $newStrings[$key] = mb_convert_encoding($value, "UTF-8", $encoding);
          if (is_array($value))
            foreach ($value as $subkey => $subvalue) 
              $newStrings[$key][$subkey] = mb_convert_encoding($subvalue, "UTF-8", $encoding);
        }
      }
      //Append new strings to current languageStrings array
      $this->languageStrings = array_merge($this->languageStrings, $newStrings);
      
      // Return successful
      return (bool) $newStrings;
  }
  
  /**
   * Returns a translated string, or the same if no language is chosen.
   * You can pass more arguments to use for replacement within the
   * string - just like sprintf(). It also removes anything before 
   * the first | in the text to translate. This is used to distinguish 
   * strings with different meanings, but with the same spelling.
   * Examples:
   * _g("Text");
   * _g("Use a %s to drink %s", _g("glass"), "water");
   *
   * @author   Joel Sjögren <joel dot sjogren at nonea dot se>
   * @param    string    text to translate
   * @return   string    translated string
   */
  function _g ($text)
  {
      // String exists and is not empty?
      if(!empty($this->languageStrings[$text])) {
        $text = $this->languageStrings[$text];
      } else {
        $text = preg_replace("/^[^\|]*\|/", "", $text);
      }
      
      // More arguments were passed? sprintf() them...
      if (func_num_args() > 1) {
          $args = func_get_args();
          array_shift($args);
          //preg_match_all("/%((\d+\\\$)|.)/", str_replace("%%", "", $text), $m);
          //while (count($args) < count($m[0])) $args[] = '';
          $text = vsprintf($text, $args);
      }
      return $text;
  }
  
  /**
   * Plural form of _g().
   *
   * @param    string  singular form of text to translate
   * @param    string  plural form of text to translate
   * @param    string  number
   * @return   string  translated string
   */
  function _ng ($msgid1, $msgid2, $n)
  {
      //calculate which plural to use
      if(!empty($this->languageStrings[0]["plural"]))
        eval($this->languageStrings[0]["plural"]);
      else 
        $plural = $n==1?0:1;
      
      // String exists and is not empty?
      if (!empty($this->languageStrings[$msgid1][$plural])) {
        $text = $this->languageStrings[$msgid1][$plural];
      } else {
        $text = preg_replace("/^[^\|]*\|/", "", ($n == 1 ? $msgid1 : $msgid2));
      }
      
      if (func_num_args() > 3) {
          $args = func_get_args();
          array_shift($args);
          array_shift($args);
          return vsprintf($text, $args);
      }
      
      return sprintf($text, $n);
  }
  
}

?>
