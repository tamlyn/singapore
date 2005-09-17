<?php 

/**
 * IO class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003-2005 Tamlyn Rhodes
 * @version $Id: io.class.php,v 1.8 2005/09/17 14:57:46 tamlyn Exp $
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
   * @param sgItem  reference to parent gallery
   * @param int     number of levels of child galleries to fetch (optional)
   * @param string  language code spec for this request (optional, ignored)
   */
  function &getGallery($galleryId, &$parent, $getChildGalleries = 1, $language = null) 
  {
    $gal =& new sgGallery($galleryId, $parent);
    
    if(file_exists($this->config->base_path.$this->config->pathto_galleries.$galleryId)) { 
    
      $bits = explode("/",$gal->id);
      $temp = strtr($bits[count($bits)-1], "_", " ");
      if($temp == ".")
        $gal->name = $this->config->gallery_name;
      elseif($this->config->enable_iifn && strpos($temp, " - ")) 
        list($gal->artist,$gal->name) = explode(" - ", $temp);
      else
        $gal->name = $temp;
      
      $dir = sgUtils::getListing($this->config->base_path.$this->config->pathto_galleries.$gal->id."/", $this->config->recognised_extensions);
      
      //set gallery thumbnail to first image in gallery (if any)
      if(isset($dir->files[0])) $gal->filename = $dir->files[0];
      
      for($i=0; $i<count($dir->files); $i++)
        //always get the first image for the gallery thumbnail 
        //but only get the rest if child galleries are requested
        if($getChildGalleries || $i==0) {
          $gal->images[$i] =& new sgImage($dir->files[$i], $gal);
        
          //trim off file extension and replace underscores with spaces
          $temp = strtr(substr($gal->images[$i]->id, 0, strrpos($gal->images[$i]->id,".")-strlen($gal->images[$i]->id)), "_", " ");
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
          ) = @GetImageSize($this->config->base_path.$this->config->pathto_galleries.$gal->id."/".$gal->images[$i]->id);
          
          //set parent link
          $gal->images[$i]->parent =& $gal;
        } else
          //otherwise just create an empty array of the appropriate length
          $gal->images[$i] = $dir->files[$i];
    } else {
      //selected gallery does not exist
      return null;
    }
    
    //discover child galleries
    $dir = sgUtils::getListing($this->config->base_path.$this->config->pathto_galleries.$galleryId."/");
    if($getChildGalleries)
      //but only fetch their info if required too
      foreach($dir->dirs as $gallery) 
        $gal->galleries[] =& $this->getGallery($galleryId."/".$gallery, $gal, $getChildGalleries-1, $language);
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
