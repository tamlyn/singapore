<?php 

/**
 * Class providing admin functions.
 * 
 * @package singapore
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: admin.class.php,v 1.12 2004/04/09 17:53:43 tamlyn Exp $
 */

/**
 * Provides administration functions.
 * 
 * @uses Singapore
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
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
    require_once $basePath."includes/translator.class.php";
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
      if(isset($_FILES["sgArchiveFile"]["tmp_name"])) 
        $_FILES["sgArchiveFile"]["tmp_name"] = addslashes($_FILES["sgArchiveFile"]["tmp_name"]);
      $_FILES   = array_map(array("Singapore","arraystripslashes"), $_FILES);
    }
    
    if(empty($galleryId)) $galleryId = isset($_REQUEST["gallery"]) ? $_REQUEST["gallery"] : ".";
    
    //load config from default ini file (singapore.ini)
    $this->config = new sgConfig("singapore.ini");
    //load config from admin template ini file (admin.ini) if present
    $this->config->loadConfig($this->config->pathto_admin_template."admin.ini");
    
    //do not load gallery-specific ini files

    //read the standard language file
    $this->i18n = new Translator($this->config->pathto_locale."singapore.".$this->config->language.".pmo");
    //read extra admin language file
    $this->i18n->readLanguageFile($this->config->pathto_locale."singapore.admin.".$this->config->language.".pmo");

    //create IO handler
    $this->io = new sgIO_csv($this->config);
    
    //set character set
    if(!empty($this->i18n->languageStrings[0]["charset"]))
      $this->character_set = $this->i18n->languageStrings[0]["charset"];
    else
      $this->character_set = $this->config->default_charset;
    
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
   * Returns a link to the image or gallery with the correct formatting and path
   * NOTE: This takes its arguments in a different order to {@link Singapore::formatURL()}
   *
   * @author   Adam Sissman <adam at bluebinary dot com>
   */
  function formatAdminURL($action, $gallery = null, $image = null, $startat = null)
  {
    $ret  = $this->config->base_url."admin.php?";
    $ret .= "action=".$action;
    if($gallery != null)
      $ret .= "&amp;gallery=".$gallery;
    if($image != null)
      $ret .= "&amp;image=".$image;
    if($startat != null)
      $ret .= "&amp;startat=".$startat;
    
    return $ret;
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
    if($this->gallery->id != ".") $links = "<a href=\"".$this->formatAdminURL("showgalleryhits",$this->gallery->parent)."\" title=\"".$this->i18n->_g("gallery|Up one level")."\">".$this->i18n->_g("gallery|Up")."</a>";
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
    if(!$this->isLoggedIn()) return array(0 => array($this->i18n->_g("admin bar|Back") => "."));
    
    $ret[0][$this->i18n->_g("admin bar|Admin")] = $this->formatAdminURL("menu");
    $ret[0][$this->i18n->_g("admin bar|Galleries")] = $this->formatAdminURL("view", isset($this->gallery) ? $this->gallery->idEncoded : null);
    $ret[0][$this->i18n->_g("admin bar|Log out")] = $this->formatAdminURL("logout");
    if(isset($this->gallery)) {
      $ret[1][$this->i18n->_g("admin bar|Edit gallery")] = $this->formatAdminURL("editgallery",$this->gallery->idEncoded);
      $ret[1][$this->i18n->_g("admin bar|Delete gallery")] = $this->formatAdminURL("deletegallery",$this->gallery->idEncoded);
      $ret[1][$this->i18n->_g("admin bar|New subgallery")] = $this->formatAdminURL("newgallery",$this->gallery->idEncoded);
      if($this->isImage()) {
        $ret[2][$this->i18n->_g("admin bar|Edit image")] = $this->formatAdminURL("editimage",$this->gallery->idEncoded,$this->image->filename);
        $ret[2][$this->i18n->_g("admin bar|Delete image")] = $this->formatAdminURL("deleteimage",$this->gallery->idEncoded,$this->image->filename);
      }
      $ret[2][$this->i18n->_g("admin bar|New image")] = $this->formatAdminURL("newimage",$this->gallery->idEncoded);
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
              else $this->lastError = $this->i18n->_g("There was an error saving the new password.");
            }
            else 
              $this->lastError = $this->i18n->_g("New password must be between 6 and 16 characters long.");
          else 
            $this->lastError = $this->i18n->_g("The new passwords you entered do not match.");
        else 
          $this->lastError = $this->i18n->_g("The current password you entered does not match the one in the database.");
  		}
    
    if(!$found) $this->lastError = $this->i18n->_g("The username specified was not found in the database.");
    
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
      $this->lastError = $this->i18n->_g("Username and/or password incorrect");
      return false;
  	}
    $this->lastError = $this->i18n->_g("You must enter a username and password");
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
      $this->lastError = $this->i18n->_g("Gallery already exists");
      return false;
    }
    
    if(mkdir($path, $this->config->directory_mode))
      return true;
    
    $this->lastError = $this->i18n->_g("Could not create directory");
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
      
    $this->lastError = $this->i18n->_g("Could not save gallery info");
    return false;
  }
  
  /**
   * Deletes a gallery and everything contained within it.
   *
   * @return boolean true on success; false otherwise
   */
  function deleteGallery($galleryId = null)
  {
    if($galleryId ===null)
      $galleryId = $_REQUEST['gallery'];
  
    //check that there are no "../" references in the gallery name
    //this ensures that the gallery being deleted really is a 
    //subdirectory of the galleries directory 
    if(strpos($galleryId,"../") !== false) return false;
    
    //check that the gallery to delete is not the top level directory
    if(realpath($this->config->pathto_galleries.$galleryId) == realpath($this->config->pathto_galleries)) {
      $this->lastError = $this->i18n->_g("Cannot delete the top level directory");
      return false;
    }
    
    //remove the offending directory and all contained therein
    return $this->rmdir_all($this->config->pathto_galleries.$galleryId);
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
    } elseif($_REQUEST["sgLocationChoice"] == "single") {
      //set filename as requested
      if($_REQUEST["sgNameChoice"] == "same") $image = $_FILES["sgImageFile"]["name"];
      else $image = $_REQUEST["sgFileName"];
      
      //make sure file has a recognised extension
      if(!preg_match("/\.(".$this->config->recognised_extensions.")$/i",$image)) $image .= ".jpeg";
      
      $path = $this->config->pathto_galleries.$this->gallery->id."/".$image;
      $srcImage = $image;
      
      if(file_exists($path))
        switch($this->config->upload_overwrite) {
          case 1 : //overwrite
            $this->deleteImage($image);
            break;
          case 2 : //generate unique
            for($i=0;file_exists($path);$i++) {
              $pivot = strrpos($srcImage,".");
              $image = substr($srcImage, 0, $pivot).'-'.$i.substr($srcImage, $pivot,strlen($srcImage)-$pivot);
              $path = $this->config->pathto_galleries.$this->gallery->id."/".$image;
            }
            break;
          case 0 : //raise error
          default :
            $this->lastError = $this->i18n->_g("File already exists");
            return false;
        }
            
      if(!move_uploaded_file($_FILES["sgImageFile"]["tmp_name"],$path)) {
        $this->lastError = $this->i18n->_g("Could not upload file"); 
        return false;
      }
      
    }
    
    $img = new sgImage();
    
    $img->filename = $image;
    $img->name = strtr(substr($image, strrpos($image,"/"), strrpos($image,".")-strlen($image)), "_", " ");
    list($img->width, $img->height) = GetImageSize($path);
    
    $this->gallery->images[count($this->gallery->images)] = $img;
    
    if($this->io->putGallery($this->gallery)) {
      $this->selectImage($image);
      return true;
    }
    
    $this->lastError = $this->i18n->_g("Could not add image to gallery");
    @unlink($path);
    return false;
  }
  
  /**
   * Adds the contents of an uploaded archive to the database.
   *
   * @return boolean true on success; false otherwise
   */
  function addMultipleImages()
  {
    //create temp directory
    while(!mkdir($tmpdir = $_ENV["TMP"]."/".uniqid("sg")));
    
    $archive = $_FILES["sgArchiveFile"]["tmp_name"];
  
    if(!is_uploaded_file($archive)) {
      $this->lastError = $this->i18n->_g("Could not upload file"); 
      return false;
    }
    
    //decompress archive to temp
    $cmd  = escapeshellcmd($this->config->pathto_unzip);
    $cmd .= ' -d '.escapeshellarg(realpath($tmpdir));
    $cmd .= ' '.escapeshellarg(realpath($archive));
    
    if(!exec($cmd)) {
      $this->lastError = $this->i18n->_g("Could not decompress archive"); 
      return false;
    }
    
    //remove archive file
    unlink($archive);
    
    //start processing archive contents
    $wd = $tmpdir;
    $contents = $this->getListing($wd,"images");
    
    //cope with archives contained within a directory
    if(empty($contents->files) && count($contents->dirs) == 1)
      $contents = $this->getListing($wd .= '/'.$contents->dirs[0],"images");
      
    $success = true;

    //add any images to current gallery
    foreach($contents->files as $image) {
    
      //make sure file has a recognised extension
      if(!preg_match("/\.(".$this->config->recognised_extensions.")$/i",$image)) $image .= ".jpeg";
      
      $path = $this->config->pathto_galleries.$this->gallery->id."/".$image;
      $srcImage = $image;
      
      if(file_exists($path))
        switch($this->config->upload_overwrite) {
          case 1 : //overwrite
            $this->deleteImage($image);
            break;
          case 2 : //generate unique
            for($i=0;file_exists($path);$i++) {
              $pivot = strrpos($srcImage,".");
              $image = substr($srcImage, 0, $pivot).'-'.$i.substr($srcImage, $pivot,strlen($srcImage)-$pivot);
              $path = $this->config->pathto_galleries.$this->gallery->id."/".$image;
            }
            break;
          case 0 : //raise error
          default :
            $this->lastError = $this->i18n->_g("File already exists");
            $success = false;
            continue;
        }
      
      copy($wd.'/'.$srcImage,$path);
      
      $img = new sgImage();
      
      $img->filename = $image;
      $img->name = strtr(substr($image, strrpos($image,"/"), strrpos($image,".")-strlen($image)), "_", " ");
      list($img->width, $img->height) = GetImageSize($path);
      
      $this->gallery->images[count($this->gallery->images)] = $img;
    }
    
    //add any directories as subgalleries
    foreach($contents->dirs as $gallery) {
      $path = $this->config->pathto_galleries.$this->gallery->id."/".$gallery;

      if(file_exists($path))
        switch($this->config->upload_overwrite) {
          case 1 : //overwrite
            $this->deleteGallery($this->gallery->id.'/'.$gallery);
            break;
          case 2 : //generate unique
            for($i=0;file_exists($path);$i++)
              $path = $this->config->pathto_galleries.$this->gallery->id."/".$gallery.'-'.$i;
            break;
          case 0 : //raise error
          default :
            $this->lastError = $this->i18n->_g("File already exists");
            $success = false;
            continue;
        }

      rename($wd.'/'.$gallery,$path);
    }
    
    //if images were added save metadata
    if(!empty($contents->files))
      $success &= $this->io->putGallery($this->gallery);
    
    //if subgalleries were added reload gallery data
    if(!empty($contents->dirs))
      $this->selectGallery();
    
    //remove temporary directory
    $this->rmdir_all($tmpdir);
    
    if(!$success)
      $this->lastError = $this->i18n->_g("Some archive contents could not be added");
      
    return $success;
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
    
    $this->lastError = $this->i18n->_g("Could not save image information");
    return false;    
  }
  
  /**
   * Deletes an image from the current gallery.
   *
   * @param string the filename of the image to delete (optional)
   * @return boolean true on success; false otherwise
   */
  function deleteImage($image = null)
  {
    if($image === null)
      $image = $this->image->filename;
  
    for($i=0;$i<count($this->gallery->images);$i++)
      if($this->gallery->images[$i]->filename == $image)
        array_splice($this->gallery->images,$i,1);
    
    if(file_exists($this->config->pathto_galleries.$this->gallery->id."/".$image))
      unlink($this->config->pathto_galleries.$this->gallery->id."/".$image);
    
    if($this->io->putGallery($this->gallery)) {
      $this->image = null;
      return true;
    } else {
      $this->lastError = $this->i18n->_g("Could not delete image");
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
