<?php 

/**
 * IO class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: io.class.php,v 1.4 2004/12/08 10:57:36 tamlyn Exp $
 */

/**
 * Abstract superclass of all IO classes. Also implements iifn code.
 * @package singapore
 * @abstract
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 */
class sgIO
{
  
  /**
   * Reference to a {@link sgConfig} object representing the current 
   * script configuration
   * @var sgConfig
   */
  var $config;
  
  /**
   * Constructor. Can be over-ridden by subclass but does not need to be.
   * @param sgConfig  pointer to current script configuration object
   */
  function sgIO(&$config)
  {
    $this->config =& $config;
  }
  
  /**
   * Pseudo-abstract method to be over-ridden in subclasses.
   */
  function getName()
  {
    return "undefined";
  }

  /**
   * Pseudo-abstract method to be over-ridden in subclasses.
   */
  function getVersion()
  {
    return "undefined";
  }

  /**
   * Pseudo-abstract method to be over-ridden in subclasses.
   */
  function getAuthor()
  {
    return "undefined";
  }

  /**
   * Pseudo-abstract method to be over-ridden in subclasses.
   */
  function getDescription()
  {
    return "undefined";
  }

  /**
   * Fetches gallery info for the specified gallery (and immediate children).
   * @param string  gallery id
   * @param string  language code spec for this request (optional, ignored)
   * @param int     number of levels of child galleries to fetch (optional)
   */
  function getGallery($galleryId, $language = null, $getChildGalleries = 1) 
  {
    $gal = new sgGallery($galleryId);
    
    if(file_exists($this->config->base_path.$this->config->pathto_galleries.$galleryId)) { 
    
      $bits = explode("/",$gal->id);
      $temp = strtr($bits[count($bits)-1], "_", " ");
      if($temp == ".")
        $gal->name = $this->config->gallery_name;
      elseif($this->config->enable_iifn && strpos($temp, " - ")) 
        list($gal->artist,$gal->name) = explode(" - ", $temp);
      else
        $gal->name = $temp;
      
      $dir = Singapore::getListing($this->config->base_path.$this->config->pathto_galleries.$gal->id."/", "images");
      
      //set gallery thumbnail to first image in gallery (if any)
      if(isset($dir->files[0])) $gal->filename = $dir->files[0];
      
      for($i=0;$i<count($dir->files);$i++) {
        $gal->images[$i] = new sgImage();
        $gal->images[$i]->filename = $dir->files[$i];
        //trim off file extension and replace underscores with spaces
        $temp = strtr(substr($gal->images[$i]->filename, 0, strrpos($gal->images[$i]->filename,".")-strlen($gal->images[$i]->filename)), "_", " ");
        //split string in two on " - " delimiter
        if($this->config->enable_iifn && strpos($temp, " - ")) 
          list($gal->images[$i]->artist,$gal->images[$i]->name) = explode(" - ", $temp);
        else
          $gal->images[$i]->name = $temp;
      
        //get image size and type
        list(
          $gal->images[$i]->width, 
          $gal->images[$i]->height, 
          $gal->images[$i]->type
        ) = @GetImageSize($this->config->base_path.$this->config->pathto_galleries.$gal->id."/".$gal->images[$i]->filename);
      }
    } else {
      //selected gallery does not exist
      return null;
    }
    
    //discover child galleries
    $dir = Singapore::getListing($this->config->base_path.$this->config->pathto_galleries.$galleryId."/", "dirs");
    if($getChildGalleries)
      //but only fetch their info if required too
      foreach($dir->dirs as $gallery) 
        $gal->galleries[] = $this->getGallery($galleryId."/".$gallery, $language, $getChildGalleries-1);
    else
      //otherwise just copy their names in so they can be counted
      $gal->galleries = $dir->dirs;
    
    return $gal;
  }
  
  /**
   * Pseudo-abstract method to be over-ridden in subclasses.
   */
  function putGallery($gallery) {
    return false;
  }
  
  /**
   * Pseudo-abstract method to be over-ridden in subclasses.
   */
  function getHits($galleryId) {
    return false;
  }
  
  /**
   * Pseudo-abstract method to be over-ridden in subclasses.
   */
  function putHits($galleryId, $hits) {
    return false;
  }
  
  /**
   * Pseudo-abstract method to be over-ridden in subclasses.
   */
  function getUsers() {
    return array();
  }
  
  /**
   * Pseudo-abstract method to be over-ridden in subclasses.
   */
  function putUsers($users) {
    return false;
  }
}

?>
