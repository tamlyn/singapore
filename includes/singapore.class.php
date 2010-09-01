<?php 

/**
 * Main class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003-2006 Tamlyn Rhodes
 * @version $Id: singapore.class.php,v 1.80 2010/09/01 00:33:13 zhangweiwu Exp $
 */

//define constants for regular expressions
define('SG_REGEXP_PROTOCOLURL', '(?:http://|https://|ftp://|mailto:)(?:[a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,4}(?::[0-9]+)?(?:/[^ \n\r\"\'<]+)?');
define('SG_REGEXP_WWWURL',      'www\.(?:[a-zA-Z0-9\-]+\.)*[a-zA-Z]{2,4}(?:/[^ \n\r\"\'<]+)?');
define('SG_REGEXP_EMAILURL',    '(?:[\w][\w\.\-]+)+@(?:[\w\-]+\.)+[a-zA-Z]{2,4}');

/**
 * Provides functions for handling galleries and images
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 */
class Singapore
{
  /**
   * current script version 
   * @var string
   */
  var $version = "0.10.1";
  
  /**
   * instance of a {@link sgConfig} object representing the current 
   * script configuration
   * @var sgConfig
   */
  var $config;
  
  /**
   * instance of the currently selected IO handler object
   * @var sgIO_csv
   */
  var $io;
  
  /**
   * instance of a {@link Translator}
   * @var Translator
   */
  var $translator;
  
  /**
   * instance of a {@link sgGallery} representing the current gallery
   * @var sgGallery
   */
  var $gallery;
  
  /**
   * reference to the currently selected {@link sgImage} object in the 
   * $images array of {@link $gallery}
   * @var sgImage
   */
  var $image;
  
  /**
   * two character code of language currently in use
   * @var string
   */
  var $language = null;
  
  /**
   * name of template currently in use
   * @var string
   */
  var $template = null;
  
  /**
   * details of current user
   * @var sgUser
   */
  var $user = null;
  
  /**
   * name of action requested
   * @var string
   */
  var $action = null;
  
  /**
   * Constructor, does all init type stuff. This code is a total mess.
   * @param string the path to the base singapore directory
   */
  function Singapore($basePath = "")
  {
    //import class definitions
    //io handler class included once config is loaded
    require_once $basePath."includes/translator.class.php";
    require_once $basePath."includes/thumbnail.class.php";
    require_once $basePath."includes/gallery.class.php";
    require_once $basePath."includes/config.class.php";
    require_once $basePath."includes/image.class.php";
    require_once $basePath."includes/user.class.php";
    
    //start execution timer
    $this->scriptStartTime = microtime();
    
    //remove slashes
    if(get_magic_quotes_gpc())
      $_REQUEST = array_map(array("Singapore","arraystripslashes"), $_REQUEST);
    
    //desanitize request
    $_REQUEST = array_map("htmlentities", $_REQUEST);
    
    //load config from singapore root directory
    $this->config =& sgConfig::getInstance();
    $this->config->loadConfig($basePath."singapore.ini");
    $this->config->loadConfig($basePath."secret.ini.php");
    
    //if instantiated remotely...
    if(!empty($basePath)) {
      //...try to guess base path and relative url
      if(empty($this->config->base_path))
        $this->config->base_path = $basePath;
      if(empty($this->config->base_url))
        $this->config->base_url  = $basePath;
      //...load local config if present
      //may over-ride guessed values above
      $this->config->loadConfig("singapore.local.ini");
    }
    
    //set current gallery to root if not specified in url
    $galleryId = isset($_GET[$this->config->url_gallery]) ? $_GET[$this->config->url_gallery] : ".";
    
    //load config from gallery ini file (gallery.ini) if present
    $this->config->loadConfig($basePath.$this->config->pathto_galleries.$galleryId."/gallery.ini");
    
    //set current template from request vars or config
    //first, preset template to default one
    $this->template = $this->config->default_template;
    //then check if requested template exists
    if(!empty($_REQUEST[$this->config->url_template])) {
     $templates = Singapore::getListing($this->config->base_path.$this->config->pathto_templates);
      foreach($templates->dirs as $single) {
        if($single == $_REQUEST[$this->config->url_template]) {
          $this->template = $single;
          break;
        }
      }
    }

    $this->config->pathto_current_template = $this->config->pathto_templates.$this->template.'/';
    //load config from template ini file (template.ini) if present
    $this->config->loadConfig($basePath.$this->config->pathto_current_template."template.ini");
        
    //set runtime values
    $this->config->pathto_logs = $this->config->pathto_data_dir."logs/";
    $this->config->pathto_cache = $this->config->pathto_data_dir."cache/";
    $this->config->pathto_admin_template = $this->config->pathto_templates.$this->config->admin_template_name."/";
    
    //set current language from request vars or config
    if(!empty($_REQUEST[$this->config->url_lang]))
      $this->language = $_REQUEST[$this->config->url_lang];
    else {
      $this->language = $this->config->default_language;
      if($this->config->detect_language)
        foreach($this->getBrowserLanguages() as $lang)
          if($lang=="en" || file_exists($basePath.$this->config->pathto_locale."singapore.".$lang.".pmo")) {
            $this->language = $lang;
            break;
          }
    }
    
    //read the language file
    $this->translator =& Translator::getInstance($this->language);
    $this->translator->readLanguageFile($this->config->base_path.$this->config->pathto_locale."singapore.".$this->language.".pmo");
    
    //clear the UMASK
    umask(0);
    
    //include IO handler class and create instance
    require_once $basePath."includes/io_".$this->config->io_handler.".class.php";
    $ioClassName = "sgIO_".$this->config->io_handler;
    $this->io = new $ioClassName($this->config);
    
    //load gallery and image info
    $this->selectGallery($galleryId);
    
    //set character set
    if(!empty($this->translator->languageStrings[0]["charset"]))
      $this->character_set = $this->translator->languageStrings[0]["charset"];
    else
      $this->character_set = $this->config->default_charset;

    if (ini_get("mbstring.func_overload") == "7") {
      $this->character_set = "UTF-8";
    }
    //set action to perform
    if(empty($_REQUEST["action"])) $this->action = "view";
    else $this->action = $_REQUEST["action"];
    
    
  }
  
  /**
   * Load gallery and image info
   * @param string the id of the gallery to load (optional)
   */
  function selectGallery($galleryId = "")
  {
    if(empty($galleryId)) $galleryId = isset($_REQUEST[$this->config->url_gallery]) ? $_REQUEST[$this->config->url_gallery] : ".";
    
    //try to validate gallery id
    if(strlen($galleryId)>1 && $galleryId{1} != '/') $galleryId = './'.$galleryId;
    
    //detect back-references to avoid file-system walking
    if(strpos($galleryId,"../")!==false) $galleryId = ".";
    
    //find all ancestors to current gallery
    $this->ancestors = array();
    $ancestorNames = explode("/", $galleryId);
    $numberOfAncestors = count($ancestorNames);
    
    //construct fully qualified gallery ids
    $ancestorIds[0] = ".";
    for($i=1; $i<$numberOfAncestors; $i++)
      $ancestorIds[$i] = $ancestorIds[$i-1]."/".$ancestorNames[$i];
    
    //fetch galleries passing previous gallery as parent pointer
    for($i=0; $i<$numberOfAncestors; $i++)
      if(!$this->ancestors[$i] =& $this->io->getGallery(
              $ancestorIds[$i], $this->ancestors[$i-1],
              //only fetch children of bottom level gallery 
              ($i==$numberOfAncestors-1) ? 1 : 0
          )
        )
        break;
    
    //need to remove bogus parent of root gallery created by previous step
    unset($this->ancestors[-1]);
    
    //set reference to current gallery
    $this->gallery = &$this->ancestors[count($this->ancestors)-1];
    
    //check if gallery was successfully fetched
    if($this->gallery == null) {
      $this->gallery = new sgGallery($galleryId, $this->ancestors[0]);
      $this->gallery->name = $this->translator->_g("Gallery not found '%s'",htmlspecialchars($galleryId));
    }
    
    //sort galleries and images
    $GLOBALS["sgSortOrder"] = $this->config->gallery_sort_order;
    if($this->config->gallery_sort_order!="x") usort($this->gallery->galleries, array("Singapore","multiSort"));
    $GLOBALS["sgSortOrder"] = $this->config->image_sort_order;
    if($this->config->image_sort_order!="x") usort($this->gallery->images, array("Singapore","multiSort"));
    unset($GLOBALS["sgSortOrder"]);
    
    //if startat is set then cast to int otherwise startat 0
    $this->gallery->startat = isset($_REQUEST[$this->config->url_startat]) ? (int)$_REQUEST[$this->config->url_startat] : 0;
    $this->startat = $this->gallery->startat; //depreciated
    
    //select the image (if any)
    if(!empty($_REQUEST[$this->config->url_image]))
      $this->selectImage($_REQUEST[$this->config->url_image]);
    
    //load hit data
    if($this->config->track_views || $this->config->show_views)
      $this->io->getHits($this->gallery);
    
    //update and save hit data
    if($this->config->track_views) {
      if($this->isImagePage()) {
        $this->image->hits++;
        $this->image->lasthit = time();
      } elseif($this->gallery->startat == 0) {
        $this->gallery->hits++;
        $this->gallery->lasthit = time();
      }
      $this->io->putHits($this->gallery);
    }
  }
  
  /**
   * Selects an image from the current gallery
   * @param mixed either the filename of the image to select or the integer 
   *  index of its position in the images array
   * @return boolean true on success; false otherwise
   */
  function selectImage($image) 
  {
    if(is_string($image)) {
      foreach($this->gallery->images as $index => $img)
        if($img->id == $image) {
          $this->image =& $this->gallery->images[$index];
          return true;
        }
    } elseif(is_int($image) && $image >= 0 && $image < count($this->gallery->images)) {
      $this->image =& $this->gallery->images[$image];
      return true;
    }
    $this->image =& new sgImage("", $this->gallery);
    $this->image->name = $this->translator->_g("Image not found '%s'",htmlspecialchars($image));
    return false;
  }
  
  
  /**
   * Obfuscates the given email address by replacing "." with "dot" and "@" with "at"
   * @param string  email address to obfuscate
   * @param boolean  override the obfuscate_email config setting (optional)
   * @return string  obfuscated email address or HTML mailto link
   */
  function formatEmail($email, $forceObfuscate = false)
  {
    if($this->config->obfuscate_email || $forceObfuscate)
        return strtr($email,array("@" => ' <b>'.$this->translator->_g("email|at").'</b> ', "." => ' <b>'.$this->translator->_g("email|dot").'</b> '));
      else
        return "<a href=\"mailto:".$email."\">".$email."</a>";
  }

  
  /**
   * Returns image name for image pages and gallery name for gallery pages.
   * If either of these is empty, returns gallery_name config option.
   *
   * @return string  Title of current page
   */
  function pageTitle()
  {
    $crumbArray = $this->crumbLineArray();
    
    $ret = "";
    for($i=count($crumbArray)-1;$i>0;$i--)
      $ret .= $crumbArray[$i]->nameForce()." &lt; ";
    $ret .= $crumbArray[$i]->nameForce();
    
    return $ret;
  }
  
  /**
   * @return bool true if this is an image page; false otherwise
   */
  function isImagePage()
  {
    return !empty($this->image);
  }
  
  /**
   * @return bool true if this is a non-album gallery page; false otherwise
   */
  function isGalleryPage()
  {
    return !empty($this->gallery) && $this->gallery->galleryCount()>0;;
  }
  
  /**
   * @return bool true if this is an album page; false otherwise
   */
  function isAlbumPage()
  {
    return !$this->isGalleryPage() && !$this->isImagePage() && !empty($this->gallery);
  }
  
  
  /**
   * @return int the script execution time in seconds rounded to two decimal places
   */
  function scriptExecTime()
  {
    $scriptStartTime = $this->scriptStartTime;
    $scriptEndTime = microtime();
    
    list($usec, $sec) = explode(" ",$scriptStartTime); 
    $scriptStartTime = (float)$usec + (float)$sec; 
    list($usec, $sec) = explode(" ",$scriptEndTime); 
    $scriptEndTime = (float)$usec + (float)$sec; 
    
    $scriptExecTime = floor(($scriptEndTime - $scriptStartTime)*100)/100;
    return $scriptExecTime;
  }
  
  /**
   * Displays the script execution time if configured to do so
   * @returns string  the script execution time
   */
  function scriptExecTimeText()
  {
    if($this->config->show_execution_time)
      return $this->translator->_g("Page created in %s seconds",$this->scriptExecTime());
    else
      return "";
  }
  
  function poweredByText()
  {
    return $this->translator->_g("Powered by").' <a href="http://www.sgal.org/">singapore</a>';
  }
  
  function allRightsReserved()
  {
    return $this->translator->_g("All rights reserved.");
  }
  
  function licenseText()
  {
    return $this->translator->_g("Images may not be reproduced in any form without the express written permission of the copyright holder.");
  }
  
  function adminURL()
  {
    return '<a href="'.$this->config->base_url.'admin.php">';
  }
  
  function adminLink()
  {
    return $this->adminURL().$this->translator->_g("Log in")."</a>";
  }
  
    
  /**
   * Checks to see if the user is currently logged in to admin mode. Also resets
   * the login timeout to the current time.
   * @returns boolean  true if the user is logged in; false otherwise
   * @static
   */
  function isLoggedIn()
  {
    if(
      isset($this->user) && 
      $_SESSION["sgUser"]["ip"] == $_SERVER["REMOTE_ADDR"] && 
      (time() - $_SESSION["sgUser"]["loginTime"] < 600)
    ) {
      //reset loginTime to current time
      $_SESSION["sgUser"]["loginTime"] = time();
      return true;
    }
    return false;
  }
  
  function loadUser($username = null)
  {
    if($username == null)
      if(isset($_SESSION["sgUser"]))
        $username = $_SESSION["sgUser"]["username"];
      else
        return false;
    
    $users = $this->io->getUsers();
    foreach($users as $user)
      if($user->username == $username) {
        $this->user = $user;
        return $user;
      }
    return false;
  }
  
  /**
   * Creates an array of objects each representing an item in the crumb line.
   * @return array  the items of the crumb line
   */
  function crumbLineArray()
  {
    $crumb = $this->ancestors;
    
    if($this->isImagePage()) $crumb[] = $this->image;
    
    return $crumb;
  }
  
  /**
   * @return string  the complete crumb line with links
   */
  function crumbLineText()
  {
    $crumbArray = $this->crumbLineArray();

    $ret = "";
    for($i=0;$i<count($crumbArray)-1;$i++)
      $ret .= $crumbArray[$i]->nameLink()." &gt;\n";
    $ret .= $crumbArray[$i]->nameForce();

    return $ret;
  }
  
  function crumbLine()
  {
    return $this->translator->_g("crumb line|You are here:")." ".$this->crumbLineText();
  }
  
  /**
   * Generates the HTML code for imagemap_navigation
   * @return string imagemap HTML code
   */
  function imageMap()
  {
    if(!$this->config->imagemap_navigation) return "";
    
    $imageWidth = $this->image->width();
    $imageHeight = $this->image->height();
    $middleX = round($imageWidth/2);
    $middleY = round($imageHeight/2);
    
    $ret  = "<map name=\"sgNavMap\" id=\"sgNavMap\">\n";
    if($this->image->hasNext()) $ret .= '<area href="'.$this->image->nextURL().'" alt="'.$this->image->nextText().'" title="'.$this->image->nextText().'" shape="poly" ';
    else $ret .= '<area href="'.$this->image->parentURL().'" alt="'.$this->image->parentText().'" title="'.$this->image->parentText().'" shape="poly" ';
    $ret .= "coords=\"$middleX,$middleY,$imageWidth,$imageHeight,$imageWidth,0,$middleX,$middleY\" />\n";
    if($this->image->hasPrev()) $ret .= '<area href="'.$this->image->prevURL().'" alt="'.$this->image->prevText().'" title="'.$this->image->prevText().'" shape="poly" ';
    else $ret .= '<area href="'.$this->image->parentURL().'" alt="'.$this->image->parentText().'" title="'.$this->image->parentText().'" shape="poly" ';
    $ret .= "coords=\"$middleX,$middleY,0,0,0,$imageHeight,$middleX,$middleY\" />\n";
    $ret .= '<area href="'.$this->image->parentURL().'" alt="'.$this->image->parentText().'" title="'.$this->image->parentText().'" shape="poly" ';
    $ret .= "coords=\"$middleX,$middleY,0,0,$imageWidth,0,$middleX,$middleY\" />\n";
    $ret .= '</map>';
    
    return $ret;
  }
  
  /**
   * Generates the HTML code for the language select box
   * @return string select box HTML code
   */
  function languageFlipper()
  {
    if(!$this->config->language_flipper) return "";
    
    $languageCache = $this->config->base_path.$this->config->pathto_data_dir."languages.cache";
    // Look for the language file
    if(!file_exists($languageCache))
      return "";
    
    // Open the file
    $fp = @fopen($languageCache, "r");
    if (!$fp) return "";
    
    // Read contents
    $str = '';
    while (!feof($fp)) $str .= fread($fp, 1024);
    // Unserialize
    $availableLanguages = @unserialize($str);
    
    $ret  = '<div class="sgLanguageFlipper">';
    $ret .= '<form method="get" action="'.$_SERVER["PHP_SELF"]."\">\n";
    //carry over current get vars
    foreach($_GET as $var => $val)
      $ret .= '<input type="hidden" name="'.$var.'" value="'.htmlspecialchars($val)."\" />\n";
    $ret .= '<select name="'.$this->config->url_lang."\">\n";
    $ret .= '  <option value="'.$this->config->default_language.'">'.$this->translator->_g("Select language...")."</option>\n";
    foreach($availableLanguages as $code => $name) {
      $ret .= '  <option value="'.$code.'"';
      if($code == $this->language && $this->language != $this->config->default_language)
        $ret .= 'selected="true" ';
      $ret .= '>'.htmlentities($name)."</option>\n";
    }
    $ret .= "</select>\n";
    $ret .= '<input type="submit" class="button" value="'.$this->translator->_g("Go")."\" />\n";
    $ret .= "</form></div>\n";
    return $ret;
  }
  
  /**
   * Generates the HTML code for the template select box
   * @return string select box HTML code
   */
  function templateFlipper()
  {
    if(!$this->config->template_flipper) return "";
    
    //get list of installed templates
    $templates = Singapore::getListing($this->config->base_path.$this->config->pathto_templates, "dirs");
    
    $ret  = '<div class="sgTemplateFlipper">';
    $ret .= '<form method="get" action="'.$_SERVER["PHP_SELF"]."\">\n";
    //carry over current get vars
    foreach($_GET as $var => $val)
      $ret .= '<input type="hidden" name="'.$var.'" value="'.htmlspecialchars($val)."\" />\n";
    $ret .= '<select name="'.$this->config->url_template."\">\n";
    $ret .= '  <option value="'.$this->config->default_template.'">'.$this->translator->_g("Select template...")."</option>\n";
    foreach($templates->dirs as $name)
      //do not list admin template(s)
      if(strpos($name, "admin_")===false) {
        $ret .= '  <option value="'.$name.'"';
        if($name == $this->template && $this->template != $this->config->default_template)
          $ret .= 'selected="true" ';
        $ret .= '>'.$name."</option>\n";
      }
    $ret .= "</select>\n";
    $ret .= '<input type="submit" class="button" value="'.$this->translator->_g("Go")."\" />\n";
    $ret .= "</form></div>\n";
    return $ret;
  }
  
  
  /**
   * @param string $seperator optional string to seperate the Gallery Tab Links
   * @return string
   */
  function galleryTab($seperator = " | ")
  {
    $showing = $this->galleryTabShowing();
    return Singapore::conditional($this->galleryTabLinks(), $showing.$seperator."%s", $showing);
  }
  
  /**
   * @return string
   */
  function galleryTabShowing()
  {
    if($this->isAlbumPage()) {
      $total = $this->gallery->imageCount();
      $perPage = $this->config->thumb_number_album;
    } else {
      $total = $this->gallery->galleryCount();
      $perPage = $this->config->thumb_number_gallery;
    }
    
    if($this->gallery->startat+$perPage > $total)
      $last = $total;
    else
      $last = $this->gallery->startat+$perPage;
    
    return $this->translator->_g("Showing %s-%s of %s",($this->gallery->startat+1),$last,$total);
  }
  
  /**
   * @return string
   */
  function galleryTabLinks()
  {
    $ret = "";
    if($this->hasPrevPage())
      $ret .= $this->prevPageLink()." ";
    
    //This is for compatibility with old templates
    //it detects if compatibility mode is on using method_exists()
    if(!$this->gallery->isRoot() && method_exists($this, 'galleryName'))
      $ret .= $this->gallery->parentLink();
    
    if($this->hasNextPage())
      $ret .= " ".$this->nextPageLink();
        
    return $ret;
  }
  
  function navigationLinks() {
    $ret = "<link rel=\"Top\" title=\"".$this->config->gallery_name."\" href=\"".$this->ancestors[0]->URL()."\" />\n";
    
    if($this->isImagePage()) {
      $ret .= "<link rel=\"Up\" title=\"".$this->image->parent->name()."\" href=\"".$this->image->parent->URL()."\" />\n";
      if ($this->image->hasPrev()) {
        $first= $this->image->firstImage();
        $prev = $this->image->prevImage();
        $ret .= "<link rel=\"First\" title=\"".$first->name()."\" href=\"".$first->URL()."\" />\n";
        $ret .= "<link rel=\"Prev\" title=\"".$prev->name()."\" href=\"".$prev->URL()."\" />\n";
      }
      if ($this->image->hasNext()) {
        $next = $this->image->nextImage();
        $last = $this->image->lastImage();
        $ret .= "<link rel=\"Next\" title=\"".$next->name()."\" href=\"".$next->URL()."\" />\n";
        $ret .= "<link rel=\"Last\" title=\"".$last->name()."\" href=\"".$last->URL()."\" />\n";
        //prefetch next image
        $ret .= "<link rel=\"Prefetch\" href=\"".$next->imageURL()."\" />\n";
      }
    } else {
      if(!$this->gallery->isRoot())
        $ret .= "<link rel=\"Up\" title=\"".$this->gallery->parent->name()."\" href=\"".$this->gallery->parent->URL()."\" />\n";
      if($this->hasPrevPage()) {
        $ret .= "<link rel=\"Prev\" title=\"".$this->translator->_g("gallery|Previous")."\" href=\"".$this->prevPageURL()."\" />\n";
        $ret .= "<link rel=\"First\" title=\"".$this->translator->_g("gallery|First")."\" href=\"".$this->firstPageURL()."\" />\n";
      }
      if($this->hasNextPage()) {
        $ret .= "<link rel=\"Next\" title=\"".$this->translator->_g("gallery|Next")."\" href=\"".$this->nextPageURL()."\" />\n";
        $ret .= "<link rel=\"Last\" title=\"".$this->translator->_g("gallery|Last")."\" href=\"".$this->lastPageURL()."\" />\n";
      } 
    }
    return $ret;
  }
  
  
  /** 
   * @return int the number of 'pages' or 'screen-fulls'
   */
  function galleryPageCount() {
    if($this->isAlbumPage())
      return intval($this->gallery->imageCount()/$this->config->thumb_number_album)+1;
    else
      return intval($this->gallery->galleryCount()/$this->config->thumb_number_gallery)+1;
  }
  
  /** 
   * @return int
   */
  function lastPageIndex() {
    if($this->isAlbumPage())
      return ($this->galleryPageCount()-1)*
        ($this->isAlbumPage()?$this->config->thumb_number_album:$this->config->thumb_number_gallery);
  }
  
  /**
   * @return bool true if there is at least one more page
   */
  function hasNextPage() {
    if($this->isAlbumPage())
      return count($this->gallery->images)>$this->startat+$this->config->thumb_number_album;
    elseif($this->isGalleryPage())
      return count($this->gallery->galleries)>$this->startat+$this->config->thumb_number_gallery;
    elseif($this->isImagePage())
      return isset($this->gallery->images[$this->image->index+1]);
  }
  
  /**
   * @return bool true if there is at least one previous page
   */
  function hasPrevPage() {
    if($this->isAlbumPage() || $this->isGalleryPage())
      return $this->startat > 0;
    elseif($this->isImagePage())
      return isset($this->gallery->images[$this->image->index-1]);
  }
  
  function firstPageURL() {
    return $this->gallery->URL(0);
  }
  
  function firstPageLink() {
    return "<a href=\"".$this->firstPageURL()."\">".$this->translator->_g("First %s", $this->itemsPerPage())."</a>";
  }
  
  /**
   * @return string the URL of the previous page
   */
  function prevPageURL() {
    return $this->gallery->URL($this->startat - $this->itemsPerPage());
  }
  
  function prevPageLink() {
    return "<a href=\"".$this->prevPageURL()."\">".$this->translator->_g("Previous %s", $this->itemsPerPage())."</a>";
  }
  
  /**
   * @return string the URL of the next page
   */
  function nextPageURL() {
    return $this->gallery->URL($this->startat + $this->itemsPerPage());
  }
  
  function nextPageLink() {
    return "<a href=\"".$this->nextPageURL()."\">".$this->translator->_g("Next %s", $this->itemsPerPage())."</a>";
  }
  
  function lastPageURL() {
    $perpage = $this->isAlbumPage() ? $this->config->thumb_number_album : $this->config->thumb_number_gallery;
    return $this->gallery->URL(floor($this->gallery->itemCount() / $perpage) * $perpage);
  }
  
  function lastPageLink() {
    return "<a href=\"".$this->lastPageURL()."\">".$this->translator->_g("Last %s", $this->itemsPerPage())."</a>";
  }
  
  function itemsPerPage()
  {
    return $this->isAlbumPage() ? $this->config->thumb_number_album : $this->config->thumb_number_gallery;
  }
  
  /**
   * @return string link for adding a comment to image
   */
  function imageCommentLink()
  {
    return "<a href=\"".
      $this->formatURL($this->gallery->idEncoded(), $this->image->id, null, "addcomment").
      "\">".$this->translator->_g("Add a comment")."</a>";
  }
  
    /**
   * @return array  array of sgImage objects
   */
  function &previewThumbnailsArray()
  {
    $ret = array();
    $index = $this->image->index();
    $start = ceil($index - $this->config->thumb_number_preview/2);
    
    for($i = $start; $i < $start + $this->config->thumb_number_preview; $i++)
      if(isset($this->image->parent->images[$i]))
        $ret[$i] =& $this->image->parent->images[$i];
      
    return $ret;
  }
  
  function previewThumbnails()
  {
    $thumbs =& $this->previewThumbnailsArray();
    $index = $this->image->index();
    $ret = "";
    foreach($thumbs as $key => $thumb) {
      $thumbClass = "sgThumbnailPreview";
      if($key==$index-1) $thumbClass .= " sgThumbnailPreviewPrev";
      elseif($key==$index) $thumbClass .= " sgThumbnailPreviewCurrent";
      elseif($key==$index+1) $thumbClass .= " sgThumbnailPreviewNext";
      $ret .= $thumb->thumbnailLink($thumbClass, "preview")."\n";
    }
      
    return $ret;
  }
  
  //////////////////////////////
  //////ex-sgUtils methods//////
  //////////////////////////////
  
  /**
   * Callback function for sorting things
   * @static
   */
  function multiSort($a, $b) {
    switch($GLOBALS["sgSortOrder"]) {
      case "f" :
      case "p" : return strcmp($a->id, $b->id); //path
      case "F" :
      case "P" : return strcmp($b->id, $a->id); //path (reverse)
      case "n" : return strcmp($a->name, $b->name); //name
      case "N" : return strcmp($b->name, $a->name); //name (reverse)
      case "i" : return strcasecmp($a->name, $b->name); //case-insensitive name
      case "I" : return strcasecmp($b->name, $a->name); //case-insensitive name (reverse)
      case "a" : return strcmp($a->artist, $b->artist); //artist
      case "A" : return strcmp($b->artist, $a->artist); //artist (reverse)
      case "d" : return strcmp($a->date, $b->date); //date
      case "D" : return strcmp($b->date, $a->date); //date (reverse)
      case "l" : return strcmp($a->location, $b->location); //location
      case "L" : return strcmp($b->location, $a->location); //location (reverse)
    }
  }
  
  /**
   * Slightly pointless method
   */
  function conditional($conditional, $iftrue, $iffalse = null)
  {
    if($conditional) return sprintf($iftrue, $conditional);
    elseif($iffalse != null) return sprintf($iffalse, $conditional);
    else return "";
  }
  
  /**
   * Callback function for recursively stripping slashes
   * @static
   */
  function arraystripslashes($toStrip)
  {
    if(is_array($toStrip))
      return array_map(array("Singapore","arraystripslashes"), $toStrip);
    else
      return stripslashes($toStrip);
  }
  
  function thumbnailPath($gallery, $image, $width, $height, $forceSize, $mode = 1)
  {
    $config =& sgConfig::getInstance();
    switch($mode) {
      case 0 :
        return $config->pathto_data_dir."cache/".$width."x".$height.($forceSize?"f":"").strtr("-$gallery-$image",":/?\\","----");
      case 1 :
        return $config->pathto_galleries.$gallery."/_thumbs/".$width."x".$height.($forceSize?"f":"").strtr("-$image",":/?\\","----");
    }
  }
  
  /**
   * @param string  relative or absolute path to directory
   * @param string  regular expression of files to return (optional)
   * @param bool    true to get hidden directories too
   * @returns stdClass|false  a data object representing the directory and its contents
   * @static
   */
  function getListing($wd, $mask = null, $getHidden = false)
  {
    $dir = new stdClass;
    $dir->path = realpath($wd)."/";
    $dir->files = array();
    $dir->dirs = array();
    
    $dp = opendir($dir->path);
    if(!$dp) return false;

    while(false !== ($entry = readdir($dp)))
      if(is_dir($dir->path.$entry)) {
        if($entry    != "CVS" && $entry    != ".svn" && 
         (($entry{0} != '.'   && $entry{0} != '_')   || $getHidden))
          $dir->dirs[] = $entry;
      } else {
        if($mask == null || preg_match("/\.($mask)$/i",$entry))
          $dir->files[] = $entry;
      }
    
    sort($dir->files);
    sort($dir->dirs);
    closedir($dp);
    return $dir;
  }
  
  /**
   * Recursively deletes all directories and files in the specified directory.
   * USE WITH EXTREME CAUTION!!
   * @returns boolean true on success; false otherwise
   * @static
   */
  function rmdir_all($wd)
  {
    if(!$dp = opendir($wd)) return false;
    $success = true;
    while(false !== ($entry = readdir($dp))) {
      if($entry == "." || $entry == "..") continue;
      if(is_dir("$wd/$entry")) $success &= Singapore::rmdir_all("$wd/$entry");
      else $success &= unlink("$wd/$entry");
    }
    closedir($dp);
    $success &= rmdir($wd);
    return $success;
  }
  
  /**
   * Returns an array of language codes specified in the Accept-Language HHTP
   * header field of the user's browser. q= components are ignored and removed.
   * hyphens (-) are converted to underscores (_).
   * @return array  accepted language codes
   * @static
   */
  function getBrowserLanguages()
  {
    $langs = array();
    foreach(explode(",",$_SERVER["HTTP_ACCEPT_LANGUAGE"]) as $bit)
      if($pos = strpos($bit,";"))
        $langs[] = strtr(substr($bit,0,$pos),"-","_");
      else
        $langs[] = strtr($bit,"-","_");
    return $langs;
  }
  
  /**
   * Wrapper for mkdir() implementing the safe-mode hack
   */
  function mkdir($path)
  {
    $config =& sgConfig::getInstance();
    if($config->safe_mode_hack) {
      $connection = ftp_connect($config->ftp_server);
      // login to ftp server
      $result = ftp_login($connection, $config->ftp_user, $config->ftp_pass);
   
      // check if connection was made
      if ((!$connection) || (!$result))
        return false;
  
      ftp_chdir($connection, $config->ftp_base_path); // go to destination dir
      if(!ftp_mkdir($connection, $path)) // create directory
        return false;
      
      ftp_site($connection, "CHMOD ".$config->directory_mode." ".$path);
      ftp_close($connection); // close connection
      return true;
    } else 
      return mkdir($path, octdec($config->directory_mode));
  }
  
  /**
   * Tests if $child is within or is the same path as $parent. 
   *
   * @param string  path to parent directory
   * @param string  path to child directory or file
   * @param bool    set false to prevent canonicalisation of paths (optional)
   * @return bool   true if $child is contained within or is $parent 
   */
  function isSubPath($parent, $child, $canonicalise = true) 
  {
    $parentPath = $canonicalise ? realpath($parent) : $parent;
    $childPath = $canonicalise ? realpath($child) : $child;
    return $parentPath && $childPath && substr($childPath,0,strlen($parentPath)) == $parentPath;
  }


  function isInGroup($groups1,$groups2) 
  {
    return (bool) array_intersect(explode(" ",$groups1),explode(" ",$groups2)); 
  }
  
}


?>
