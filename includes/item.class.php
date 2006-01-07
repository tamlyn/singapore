<?php 

/**
 * Singapore gallery item class.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2005 Tamlyn Rhodes
 * @version $Id: item.class.php,v 1.7 2006/01/07 16:24:14 tamlyn Exp $
 */

//permissions bit flags
define("SG_GRP_READ",   1);
define("SG_GRP_EDIT",   2);
define("SG_GRP_ADD",    4);
define("SG_GRP_DELETE", 8);
define("SG_WLD_READ",   16);
define("SG_WLD_EDIT",   32);
define("SG_WLD_ADD",    64);
define("SG_WLD_DELETE", 128);
define("SG_IHR_READ",   17);
define("SG_IHR_EDIT",   34);
define("SG_IHR_ADD",    68);
define("SG_IHR_DELETE", 136);

/**
 * Abstract class from which sgImage and sgGallery are derived.
 * 
 * @abstract
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2005 Tamlyn Rhodes
 */
class sgItem
{
  /**
   * The id of the item. In the case of galleries this is the path to the 
   * gallery from root and must be unique. For images it is the image file
   * name (or URL for remote images).
   * @var string
   */
  var $id;
   
  /**
   * Username of the user to which the item belongs
   * @var string
   */
  var $owner = "__nobody__";
  
  /**
   * Space-separated list of groups to which the item belongs
   * @var string
   */
  var $groups = "";
  
  /**
   * Bit-field of permissions
   * @var int
   */
  var $permissions = 0;
  
  /**
   * Space-separated list of categories to which the item belongs (not used)
   * @var string
   */
  var $categories = "";
  
  /**
   * The name or title of the item
   * @var string
   */
  var $name = "";
  
  /**
   * The name of the original item creator (or anyone else)
   * @var string
   */
  var $artist = "";
  
  /**
   * Email of the original item creator (or anyone else)
   * @var string
   */
  var $email = "";
  
  /**
   * Optional copyright information
   * @var string
   */
  var $copyright = "";
  
  /**
   * Multiline description of the item
   * @var string
   */
  var $desc = "";
  
  /**
   * Date associated with item
   * @var string
   */
  var $date = "";
  
  var $location = "";
  
  /**
   * Number of times item has been viewed
   * @var int
   */
  var $hits = 0;
  
  /**
   * Unix timestamp of last time item was viewed
   * @var int
   */
  var $lasthit = 0;
  
  /**
   * Pointer to the parent sgItem
   * @var sgItem
   */
  var $parent;
  
  
  /**
   * Reference to the current config object
   * @var sgConfig
   */
  var $config;
  
  /**
   * Reference to the current translator object
   * @var Translator
   */
  var $translator;
  
  /**
   * Array in which the various sized thumbnails representing this item are stored
   * @var array
   */
  var $thumbnails = array();
  
  /** Accessor methods */
  function name()        { return $this->name; }
  function artist()      { return $this->artist; }
  function date()        { return $this->date; }
  function location()    { return $this->location; }
  function description() { return $this->desc; }
  
  function canEdit()     { return false; }
  
  function idEntities()  { return htmlspecialchars($this->id); }
  
  /**
   * Removes script-generated HTML (BRs and URLs) but leaves any other HTML
   * @return string the description of the item
   */
  function descriptionStripped()
  {
    $ret = str_replace("<br />","\n",$this->description());
    
    if($this->config->enable_clickable_urls) {
      //strip off html from autodetected URLs
      $ret = preg_replace('{<a href="('.SG_REGEXP_PROTOCOLURL.')\">\1</a>}', '\1', $ret);
      $ret = preg_replace('{<a href="http://('.SG_REGEXP_WWWURL.')">\1</a>}', '\1', $ret);
      $ret = preg_replace('{<a href="mailto:('.SG_REGEXP_EMAILURL.')">\1</a>}', '\1', $ret);
    }
    
    return $ret;
  }
  
  /**
   * If the current item has an artist specified, returns " by " followed 
   * by the artist's name. Otherwise returns an empty string.
   * @return string 
   */
  function byArtistText() 
  {
    if(empty($this->artist))
      return "";
    else
      return " ".$this->translator->_g("artist name|by %s",$this->artist);
  }
  
  /**
   * Obfuscates the given email address by replacing "." with "dot" and "@" with "at"
   * @param boolean  override the obfuscate_email config setting (optional)
   * @return string  obfuscated email address or HTML mailto link
   */
  function emailLink($forceObfuscate = false)
  {
    if($this->config->obfuscate_email || $forceObfuscate)
        return strtr($this->email,array("@" => ' <b>'.$this->translator->_g("email|at").'</b> ', "." => ' <b>'.$this->translator->_g("email|dot").'</b> '));
      else
        return "<a href=\"mailto:".$this->email."\">".$this->email."</a>";
  }

  function nameLink($action = null)
  {
    return '<a href="'.$this->URL(0, $action).'">'.$this->nameForce().'</a>';
  }
  
  function parentLink($action = null)
  {
    $perpage = $this->parent->isAlbum() ? $this->config->thumb_number_album : $this->config->thumb_number_gallery;
    return '<a href="'.$this->parent->URL(floor($this->index() / $perpage) * $perpage, $action).'">'.$this->parentText().'</a>';
  }
  
  function parentText()
  {
    return $this->translator->_g("gallery|Up");
  }
  
  /**
   * @return array  associative array of item properties in the form "name" => "value"
   */
  function detailsArray() 
  {
    $ret = array();
    
    //generic properties
    if(!empty($this->date))      $ret[$this->translator->_g("Date")] = $this->date;
    if(!empty($this->location))  $ret[$this->translator->_g("Location")] = $this->location;
    if(!empty($this->desc))      $ret[$this->translator->_g("Description")] = $this->desc;
    if(!empty($this->email))     $ret[$this->translator->_g("Email")] = $this->emailLink();
    
    //image properties
    if(!empty($this->camera))    $ret[$this->translator->_g("Camera")] = $this->camera;
    if(!empty($this->lens))      $ret[$this->translator->_g("Lens")] = $this->lens;
    if(!empty($this->film))      $ret[$this->translator->_g("Film")] = $this->film;
    if(!empty($this->darkroom))  $ret[$this->translator->_g("Darkroom manipulation")] = $this->darkroom;
    if(!empty($this->digital))   $ret[$this->translator->_g("Digital manipulation")] = $this->digital;
    
    //special properties
    if(!empty($this->copyright)) $ret[$this->translator->_g("Copyright")] = $this->copyright;
    elseif(!empty($this->artist))$ret[$this->translator->_g("Copyright")] = $this->artist;
    if($this->config->show_views)
      $ret[$this->translator->_g("Viewed")] = $this->translator->_ng("viewed|%s time", "viewed|%s times",$this->hits);
    
    return $ret;
  }
  
  function isAlbum()   { return false; }
  function isGallery() { return false; }
  function isImage()   { return false; }

  /**
   * Returns a link to the image or gallery with the correct formatting and path
   *
   * @param int  page offset (optional)
   * @param string  action to perform (optional)
   * @return string formatted URL
   */
  function URL($startat = null, $action = null)
  {
    $query = array();
    if($this->config->use_mod_rewrite) { //format url for use with mod_rewrite
      $ret  = $this->config->base_url;
      $ret .= $this->isImage() ? $this->parent->idEncoded() : $this->idEncoded();
      if($startat) $ret .= ','.$startat;
      $ret .= '/';
      if($this->isImage())   $ret .= $this->idEncoded();
      
      if($action)  $query[] = $this->config->url_action."=".$action;
      if($this->translator->language != $this->config->default_language) $query[] = $this->config->url_lang.'='.$this->translator->language;
      if($GLOBALS["sg"]->template != $this->config->default_template) $query[] = $this->config->url_template.'='.$GLOBALS["sg"]->template;
      
      if(!empty($query))
        $ret .= '?'.implode(ini_get('arg_separator.output'), $query);
    
    } else { //format plain url
      
      $query[] = $this->config->url_gallery."=".($this->isImage() ? $this->parent->idEncoded() : $this->idEncoded());
      if($this->isImage()) $query[] = $this->config->url_image."=".$this->idEncoded();
      if($startat)         $query[] = $this->config->url_startat."=".$startat;
      if($action)          $query[] = $this->config->url_action."=".$action;
      if($this->translator->language != $this->config->default_language)
                           $query[] = $this->config->url_lang.'='.$this->translator->language;
      if($GLOBALS["sg"]->template != $this->config->default_template)
                           $query[] = $this->config->url_template.'='.$GLOBALS["sg"]->template;
                           
      $ret = $this->config->index_file_url.implode(ini_get('arg_separator.output'), $query);
    }
    
    return $ret;
  }
  
}


?>
