<?php 

/**
 * Class providing admin functions.
 * 
 * @package singapore
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003 Tamlyn Rhodes
 * @version $Id: admin.class.php,v 1.9 2004/01/06 23:01:48 tamlyn Exp $
 */

/**
 * Provides administration functions.
 * 
 * @uses Singapore
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot org>
 */
class sgAdmin extends Singapore
{
  /**
   * The text of the last error to have occurred
   * @var string
   */
  var $lastError = "";
  
  /**
   * Admin constructor. Doesn't call {@link Singapore} constructor.
   * @param string the path to the base singapore directory
   */
  function sgAdmin($basePath = "")
  {
    //import class definitions
    require_once $basePath."includes/gallery.class.php";
    require_once $basePath."includes/image.class.php";
    require_once $basePath."includes/config.class.php";
    require_once $basePath."includes/io_csv.class.php";
    
    //start execution timer
    $this->scriptStartTime = microtime();

    //remove slashes
    if(get_magic_quotes_gpc()) {
      $_REQUEST = array_map(array("Singapore","arraystripslashes"), $_REQUEST);
      
      //as if magic_quotes_gpc wasn't insane enough, php doesn't add slashes 
      //to the tmp_name variable so I have to add them manually. Grrrr.
      if(isset($_FILES["sgImageFile"]["tmp_name"])) 
        $_FILES["sgImageFile"]["tmp_name"] = addslashes($_FILES["sgImageFile"]["tmp_name"]);
      $_FILES   = array_map(array("Singapore","arraystripslashes"), $_FILES);
    }
    
    //load config from default ini file (singapore.ini)
    $this->config = new sgConfig("singapore.ini");
    //load config from admin template ini file (admin.ini) if present
    $this->config->loadConfig($this->config->pathto_admin_template."admin.ini");
    
    //do not load gallery-specific ini files

    //read the standard language file
    $this->readLanguageFile($this->config->language);
    //read extra admin language file
    $this->readLanguageFile("admin.".$this->config->language);

    //set character set
    if(!empty($this->languageStrings[0]["charset"]))
      $this->character_set = $this->languageStrings[0]["charset"];
    else
      $this->character_set = $this->config->default_charset;

    //create IO handler
    $this->io = new sgIO_csv($this->config);
    
    //set action to perform
    if(empty($_REQUEST["action"])) $this->action = "menu";
    else $this->action = $_REQUEST["action"];
    
    //set page title
    $this->pageTitle = $this->config->gallery_name;
  }
  
  /**
   * @return string the value of {@link lastError}
   */
  function getLastError()
  {
    return $this->lastError;
  }
  
  /**
   * Loads gallery details and sets values similar to that of 
   * {@link Singapore::Singapore()}
   * 
   * @param string the id of the gallery to load (optional)
   */
  function selectGallery($galleryId = "")
  {
    if(empty($galleryId))
      $galleryId = !empty($_REQUEST["gallery"]) ? $_REQUEST["gallery"] : ".";
    
    //try to validate gallery id
    if(strlen($galleryId)>1 && $galleryId{1} != '/') $galleryId = './'.$galleryId;
    
    //detect back-references to avoid file-system walking
    if(strpos($galleryId,"../")!==false) $galleryId = ".";
    
    //fetch the gallery and image info
    $this->gallery = $this->io->getGallery($galleryId);
    
    //sort galleries and images
    $GLOBALS["temp"]["gallery_sort_order"] = $this->config->gallery_sort_order;
    $GLOBALS["temp"]["image_sort_order"] = $this->config->image_sort_order;
    if($this->config->gallery_sort_order!="x") usort($this->gallery->galleries, array("Singapore","gallerySort"));
    if($this->config->image_sort_order!="x") usort($this->gallery->images, array("Singapore","imageSort"));
    
    $this->startat = isset($_REQUEST["startat"]) ? $_REQUEST["startat"] : 0;
    
    //encode the gallery name
    $bits = explode("/",$this->gallery->id);
    for($i=0;$i<count($bits);$i++)
      $bits[$i] = rawurlencode($bits[$i]);
    $this->gallery->idEncoded = implode("/",$bits);
    
    $this->gallery->idEntities = htmlspecialchars($this->gallery->id);
    
    //find the parent
    $this->gallery->parent = substr($this->gallery->idEncoded, 0, strrpos($this->gallery->idEncoded, "/"));
    
    if(isset($_REQUEST['image']))
      $this->selectImage($_REQUEST["image"]);
  }
  
  /**
   * Loads hits info into the galleries object.
   */
  function loadGalleryHits()
  {
    for($i=$max=0;$i<count($this->gallery->galleries);$i++) {
      $this->gallery->galleries[$i]->hits = $this->io->getHits($this->gallery->galleries[$i]->id);
      if($this->gallery->galleries[$i]->hits->hits > $max) $max = $this->gallery->galleries[$i]->hits->hits;
    }
    $this->gallery->maxhits = $max;
  }
  
  /**
   * Loads hits info into the images object.
   */
  function loadImageHits()
  {
    $hits = $this->io->getHits($this->gallery->id);
    for($i=$max=0;$i<count($this->gallery->images);$i++) {
      for($j=0;$j<count($hits->images);$j++)
        if($hits->images[$j]->filename == $this->gallery->images[$i]->filename)
          $this->gallery->images[$i]->hits = $hits->images[$j];
      if(isset($this->gallery->images[$i]->hits->hits) && $this->gallery->images[$i]->hits->hits > $max) $max = $this->gallery->images[$i]->hits->hits;
    }
    $this->gallery->maxhits = $max;
  }
  
  /**
   * @return string
   */
  function galleryHitsTab()
  {
    $showing = $this->galleryTabShowing();
    if($this->gallery->id != ".") $links = "<a href=\"admin.php?action=showgalleryhits&amp;gallery=".$this->gallery->parent."\" title=\"".$this->__g("gallery|Up one level")."\">".$this->__g("gallery|Up")."</a>";
    if(empty($links)) return $showing;
    else return $showing." | ".$links;
  }
  
  /**
   * Returns a two-dimensional array of links for the admin bar.
   * 
   * @returns string
   */
  function adminLinksArray()
  {
    if(!$this->isLoggedIn()) return array(0 => array($this->__g("admin bar|Back") => "."));
    
    $ret[0][$this->__g("admin bar|Admin")] = "admin.php";
    $ret[0][$this->__g("admin bar|Galleries")] = "admin.php?action=view".(isset($this->gallery) ? "&amp;gallery=".$this->gallery->idEncoded : "");
    $ret[0][$this->__g("admin bar|Log out")] = "admin.php?action=logout";
    if(isset($this->gallery)) {
      $ret[1][$this->__g("admin bar|Edit gallery")] = "admin.php?action=editgallery&amp;gallery=".$this->gallery->idEncoded;
      $ret[1][$this->__g("admin bar|Delete gallery")] = "admin.php?action=deletegallery&amp;gallery=".$this->gallery->idEncoded;
      $ret[1][$this->__g("admin bar|New subgallery")] = "admin.php?action=newgallery&amp;gallery=".$this->gallery->idEncoded;
      if($this->isImage()) {
        $ret[2][$this->__g("admin bar|Edit image")] = "admin.php?action=editimage&amp;gallery=".$this->gallery->idEncoded."&amp;image=".$this->image->filename;
        $ret[2][$this->__g("admin bar|Delete image")] = "admin.php?action=deleteimage&amp;gallery=".$this->gallery->idEncoded."&amp;image=".$this->image->filename;
      }
      $ret[2][$this->__g("admin bar|New image")] = "admin.php?action=newimage&amp;gallery=".$this->gallery->idEncoded;
    }
    return $ret;
  }
  
  
  /**
   * Saves the new password if it is correctly specified.
   * 
   * @return boolean true on success; false otherwise
   */
  function savePass()
  {
    $users = $this->io->getUsers();
    
    $found = false;
    for($i=1;$i < count($users);$i++)
      if($_POST["sgUsername"] == $users[$i]->username) {
        $found = true;
        if(md5($_POST["sgOldPass"]) == $users[$i]->userpass)
          if($_POST["sgNewPass1"]==$_POST["sgNewPass2"])
            if(strlen($_POST["sgNewPass1"]) >= 6 && strlen($_POST["sgNewPass1"]) <= 16) { 
              $users[$i]->userpass = md5($_POST["sgNewPass1"]);
              if($this->io->putUsers($users)) return true;
              else $this->lastError = $this->_g("There was an error saving the new password.");
            }
            else 
              $this->lastError = $this->_g("New password must be between 6 and 16 characters long.");
          else 
            $this->lastError = $this->_g("The new passwords you entered do not match.");
        else 
          $this->lastError = $this->_g("The current password you entered does not match the one in the database.");
  		}
    
    if(!$found) $this->lastError = $this->_g("The username specified was not found in the database.");
    
    //some sort of error occurred so:
    return false;
  }
  
  /**
   * @return boolean true if user is logged in; false otherwise
   */
  function isLoggedIn()
  {
    if(isset($_SESSION["sgUser"]) && $_SESSION["sgUser"]->check == md5($_SERVER["REMOTE_ADDR"]) && (time() - $_SESSION["sgUser"]->loginTime < 1800)) {
  		$_SESSION["sgUser"]->loginTime = time();
  	  return true;
    }
    return false;
  }

  /**
   * Attempts to log a registered user into admin.
   * 
   * @return boolean true on success; false otherwise
   */
  function login() 
  {
    if(isset($_POST["sgUsername"]) && isset($_POST["sgPassword"])) {
  	  $users = $this->io->getUsers();
  		for($i=1;$i < count($users);$i++)
        if($_POST["sgUsername"] == $users[$i]->username && md5($_POST["sgPassword"]) == $users[$i]->userpass){
          $_SESSION["sgUser"] = $users[$i];
          $_SESSION["sgUser"]->check = md5($_SERVER["REMOTE_ADDR"]);
  			  $_SESSION["sgUser"]->ip = $_SERVER["REMOTE_ADDR"];
          $_SESSION["sgUser"]->loginTime = time();
          return true;
  			}
  		$this->logout();
      $this->lastError = $this->_g("Username and/or password incorrect");
      return false;
  	}
    $this->lastError = $this->_g("You must enter a username and password");
    return false;
  }
  
  /**
   * Cancels a users admin session.
   * 
   * @return true
   */
  function logout()
  {
    $_SESSION["sgUser"] = null;
  	return true;
  }
  
  /**
   * Creates a directory.
   *
   * @return boolean true on success; false otherwise
   */
  function addGallery()
  {
    $path = $this->config->pathto_galleries.$this->gallery->id."/".$_REQUEST["newgallery"];
    
    if(file_exists($path)) {
      $this->lastError = $this->_g("Gallery already exists");
      return false;
    }
    
    if(mkdir($path, $this->config->directory_mode))
      return true;
    
    $this->lastError = $this->_g("Could not create directory");
    return false;
  }
  
  /**
   * Saves gallery info to the database.
   *
   * @return boolean true on success; false otherwise
   */
  function saveGallery()
  {
    $this->gallery->owner = $_REQUEST["sgOwner"];
    $this->gallery->groups = $_REQUEST["sgGroups"];
    $this->gallery->permissions = $_REQUEST["sgPermissions"];
    $this->gallery->categories = $_REQUEST["sgCategories"];
    $this->gallery->name = stripslashes($_REQUEST["sgGalleryName"]);
    $this->gallery->artist = stripslashes($_REQUEST["sgArtistName"]);
    $this->gallery->email = stripslashes($_REQUEST["sgArtistEmail"]);
    $this->gallery->copyright = stripslashes($_REQUEST["sgCopyright"]);
    $this->gallery->desc = str_replace(array("\n","\r"),array("<br />",""),stripslashes($_REQUEST["sgGalleryDesc"]));
    
    if($this->config->enable_clickable_urls) {
      //recognise URLs and htmlise them
      $this->gallery->desc = preg_replace('{(?<!href="|href=)\b('.$this->regexps['genericURL'].')\b(?!</a>)}', '<a href="$1">$1</a>', $this->gallery->desc);  //general protocol match
      $this->gallery->desc = preg_replace('{(?<!://)\b('.$this->regexps['wwwURL'].')\b(?!</a>)}', '<a href="http://$1">$1</a>', $this->gallery->desc);  //web addresses starting www. without path info
      $this->gallery->desc = preg_replace('{(?<!mailto:|\.)\b('.$this->regexps['emailURL'].')\b(?!</a>)}', '<a href="mailto:$1">$1</a>', $this->gallery->desc);  //email addresses *@*.*
    }
    
    if($this->io->putGallery($this->gallery))
      return true;
      
    $this->lastError = $this->_g("Could not save gallery info");
    return false;
  }
  
  /**
   * Deletes a gallery and everything contained within it.
   *
   * @return boolean true on success; false otherwise
   */
  function deleteGallery()
  {
    //check that there are no "../" references in the gallery name
    //this ensures that the gallery being deleted really is a 
    //subdirectory of the galleries directory 
    if(strpos($_REQUEST['gallery'],"../") !== false) return false;
    
    //check that the gallery to delete is not the top level directory
    if(realpath($this->config->pathto_galleries.$_REQUEST['gallery']) == realpath($this->config->pathto_galleries)) {
      $this->lastError = $this->_g("Cannot delete the top level directory");
      return false;
    }
    
    //remove the offending directory and all contained therein
    return $this->rmdir_all($this->config->pathto_galleries.$_REQUEST['gallery']);
  }
  
  /**
   * Saves changes to the gallery thumbnail to the database.
   *
   * @return boolean true on success; false otherwise
   */
  function saveGalleryThumbnail()
  {
    $this->gallery->filename = $_REQUEST['sgThumbName'];
    return $this->io->putGallery($this->gallery);
  }
  
  
  /**
   * Adds an image to the database.
   *
   * @return boolean true on success; false otherwise
   */
  function addImage()
  {
    if($_REQUEST["sgLocationChoice"] == "remote") {
      $image = $_REQUEST["sgImageURL"];
      $path = $image;
    } elseif($_REQUEST["sgLocationChoice"] == "local") {
      //set filename as requested
      if($_REQUEST["sgNameChoice"] == "same") $image = $_FILES["sgImageFile"]["name"];
      else $image = $_REQUEST["sgFileName"];
      
      //make sure file has a recognised extension
      if(!preg_match("/(jpe?g)|(png)|(gif)|(tiff?)|(bmp)$/i",$image)) $image .= ".jpeg";
      
      $path = $this->config->pathto_galleries.$this->gallery->id."/".$image;
      
      if(file_exists($path)) {
        $this->lastError = $this->_g("File already exists"); 
        return false;
      }
      
      if(!move_uploaded_file($_FILES["sgImageFile"]["tmp_name"],$path)) {
        $this->lastError = $this->_g("Could not upload file"); 
        return false;
      }
      
    } else {
      $this->lastError = $this->_g("Invalid location choice");
      return false;
    }
    
    $i = count($this->gallery->images);
    
    $img = new sgImage();
    
    $img->filename = $image;
    $img->name = strtr(substr($image, strrpos($image,"/"), strrpos($image,".")-strlen($image)), "_", " ");
    list($img->width, $img->height) = GetImageSize($path);
    
    $this->gallery->images[count($this->gallery->images)] = $img;
    
    if($this->io->putGallery($this->gallery)) {
      $this->selectImage($image);
      return true;
    }
    
    $this->lastError = $this->_g("Could not add image to gallery");
    @unlink($path);
    return false;
  }
  
  /**
   * Saves image info to the database.
   *
   * @return boolean true on success; false otherwise
   */
  function saveImage()
  {
    $this->image->filename = $_REQUEST['image'];
    $this->image->thumbnail = $_REQUEST["sgThumbnail"];
    $this->image->owner = $_REQUEST["sgOwner"];
    $this->image->groups = $_REQUEST["sgGroups"];
    $this->image->permissions = $_REQUEST["sgPermissions"];
    $this->image->categories = $_REQUEST["sgCategories"];
    $this->image->name = stripslashes($_REQUEST["sgImageName"]);
    $this->image->artist = stripslashes($_REQUEST["sgArtistName"]);
    $this->image->email = stripslashes($_REQUEST["sgArtistEmail"]);
    $this->image->location = stripslashes($_REQUEST["sgLocation"]);
    $this->image->date = stripslashes($_REQUEST["sgDate"]);
    $this->image->copyright = stripslashes($_REQUEST["sgCopyright"]);
    $this->image->desc = str_replace(array("\n","\r"),array("<br />",""),stripslashes($_REQUEST["sgImageDesc"]));
    $this->image->camera = stripslashes($_REQUEST["sgField01"]);
    $this->image->lens = stripslashes($_REQUEST["sgField02"]);
    $this->image->film = stripslashes($_REQUEST["sgField03"]);
    $this->image->darkroom = stripslashes($_REQUEST["sgField04"]);
    $this->image->digital = stripslashes($_REQUEST["sgField05"]);
    
    if($this->io->putGallery($this->gallery)) return true;
    
    $this->lastError = $this->_g("Could not save image information");
    return false;    
  }
  
  /**
   * Deletes an image from the current gallery.
   *
   * @return boolean true on success; false otherwise
   */
  function deleteImage()
  {
    for($i=0;$i<count($this->gallery->images);$i++)
      if($this->gallery->images[$i]->filename == $this->image->filename)
        array_splice($this->gallery->images,$i,1);
    
    if(file_exists($this->config->pathto_galleries.$this->gallery->id."/".$this->image->filename))
      unlink($this->config->pathto_galleries.$this->gallery->id."/".$this->image->filename);
    
    if($this->io->putGallery($this->gallery)) {
      $this->image = null;
      return true;
    } else {
      $this->lastError = $this->_g("Could not delete image");
      return false;
    }
  }
  
  /**
   * Deletes the contents of the cache directory.
   *
   * @return boolean true on success; false otherwise
   */
  function purgeCache()
  {
    $dir = $this->getListing($this->config->pathto_cache,"all");
    
    $success = true;
    for($i=0;$i<count($dir->files);$i++) {
      $success &= unlink($dir->path.$dir->files[$i]);
    }
    
    return $success;
  }

}


?>
