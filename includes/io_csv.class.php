<?php 

/**
 * IO class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: io_csv.class.php,v 1.18 2004/12/15 17:04:56 tamlyn Exp $
 */

//include the base IO class
require_once dirname(__FILE__)."/io.class.php";
 
 /**
 * Class used to read and write data to and from CSV files.
 * @see sgIO_iifn
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 */
class sgIO_csv extends sgIO
{
  //constructor provided by parent class
  
  /**
   * Name of IO backend.
   */
  function getName()
  {
    return "CSV";
  }

  /**
   * Version of IO backend.
   */
  function getVersion()
  {
    return "$Revision: 1.18 $";
  }

  /**
   * Author of IO backend.
   */
  function getAuthor()
  {
    return "Tamlyn Rhodes";
  }

  /**
   * Brief description of IO backend and it's requirements.
   */
  function getDescription()
  {
    return "Uses comma separated value files. Does not require a database.";
  }

  /**
   * Fetches gallery info for the specified gallery and immediate children.
   * @param string  gallery id
   * @param string  language code spec for this request (optional)
   * @param int     number of levels of child galleries to fetch (optional)
   */
  function getGallery($galleryId, $language = null, $getChildGalleries = 1) 
  {
    $gal = new sgGallery($galleryId);

    if($language == null) $language = $this->config->default_language;
    
    //try to open language specific metadata
    $fp = @fopen($this->config->base_path.$this->config->pathto_galleries.$galleryId."/metadata.$language.csv","r");
    
    //if fail then try to open generic metadata
    if(!$fp) 
      $fp = @fopen($this->config->base_path.$this->config->pathto_galleries.$galleryId."/metadata.csv","r");
    if($fp) {

      while($temp[] = fgetcsv($fp,2048));
      fclose($fp);
      
      list(
        $gal->filename,
        $gal->thumbnail,
        $gal->owner,
        $gal->groups,
        $gal->permissions,
        $gal->categories,
        $gal->name,
        $gal->artist,
        $gal->email,
        $gal->copyright,
        $gal->desc,
        $gal->summary,
        $gal->date
      ) = $temp[1];
      
      
      for($i=0;$i<count($temp)-3;$i++) {
        $gal->images[$i] = new sgImage();
        list(
          $gal->images[$i]->filename,
          $gal->images[$i]->thumbnail,
          $gal->images[$i]->owner,
          $gal->images[$i]->groups,
          $gal->images[$i]->permissions,
          $gal->images[$i]->categories,
          $gal->images[$i]->name,
          $gal->images[$i]->artist,
          $gal->images[$i]->email,
          $gal->images[$i]->copyright,
          $gal->images[$i]->desc,
          $gal->images[$i]->location,
          $gal->images[$i]->date,
          $gal->images[$i]->camera,
          $gal->images[$i]->lens,
          $gal->images[$i]->film,
          $gal->images[$i]->darkroom,
          $gal->images[$i]->digital
        ) = $temp[$i+2];
      
        //get image size and type
        list(
          $gal->images[$i]->width, 
          $gal->images[$i]->height, 
          $gal->images[$i]->type
        ) = substr($gal->images[$i]->filename, 0, 7)=="http://"
            ? @GetImageSize($gal->images[$i]->filename)
            : @GetImageSize($this->config->base_path.$this->config->pathto_galleries.$gal->id."/".$gal->images[$i]->filename);
      }
        
    } else
      //no metadata found so use iifn method implemented in parent class
      return parent::getGallery($galleryId, $language, $getChildGalleries);
    
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
   * Stores gallery information.
   * @param sgGallery  instance of gallery object to be stored
   */
  function putGallery($gallery) {
    
    $fp = fopen($this->config->base_path.$this->config->pathto_galleries.$gallery->id."/metadata.csv","w");
    
    if(!$fp)
      return false;
    
    $success  = (bool) fwrite($fp,"filename,thumbnail,owner,group(s),permissions,catergories,image name,artist name,artist email,copyright,image description,image location,date taken,camera info,lens info,film info,darkroom manipulation,digital manipulation");
    $success &= (bool) fwrite($fp,"\n\"".
      $gallery->filename."\",,".
      $gallery->owner.",".
      $gallery->groups.",".
      $gallery->permissions.",".
      $gallery->categories.',"'.
      str_replace('"','""',$gallery->name).'","'.
      str_replace('"','""',$gallery->artist).'","'.
      str_replace('"','""',$gallery->email).'","'.
      str_replace('"','""',$gallery->copyright).'","'.
      str_replace('"','""',$gallery->desc).'","'.
      str_replace('"','""',$gallery->summary).'","'.
      str_replace('"','""',$gallery->date).'"'
    );
    
    for($i=0;$i<count($gallery->images);$i++)
      $success &= (bool) fwrite($fp,"\n\"".
        $gallery->images[$i]->filename."\",".
        $gallery->images[$i]->thumbnail.",".
        $gallery->images[$i]->owner.",".
        $gallery->images[$i]->groups.",".
        $gallery->images[$i]->permissions.",".
        $gallery->images[$i]->categories.',"'.
        str_replace('"','""',$gallery->images[$i]->name).'","'.
        str_replace('"','""',$gallery->images[$i]->artist).'","'.
        str_replace('"','""',$gallery->images[$i]->email).'","'.
        str_replace('"','""',$gallery->images[$i]->copyright).'","'.
        str_replace('"','""',$gallery->images[$i]->desc).'","'.
        str_replace('"','""',$gallery->images[$i]->location).'","'.
        str_replace('"','""',$gallery->images[$i]->date).'","'.
        str_replace('"','""',$gallery->images[$i]->camera).'","'.
        str_replace('"','""',$gallery->images[$i]->lens).'","'.
        str_replace('"','""',$gallery->images[$i]->film).'","'.
        str_replace('"','""',$gallery->images[$i]->darkroom).'","'.
        str_replace('"','""',$gallery->images[$i]->digital).'"'
      );
    $success &= (bool) fclose($fp);
  
    return $success;
  }
  
  /**
   * Fetches hit data from file.
   * @param string  id of gallery to fetch hits for
   */
  function getHits($galleryId) {
    
    $fp = @fopen($this->config->base_path.$this->config->pathto_logs.strtr("views".$galleryId,":/?\\","----").".log","r");
    
    if($fp) {
      while($temp[] = fgetcsv($fp,255));
      fclose($fp);
    } else $temp = array();
    
    $hits = new stdClass;
    
    if(isset($temp[0]))
      list(
        ,
        $hits->hits,
        $hits->lasthit
      ) = $temp[0];
    else {
      $hits->hits = 0;
      $hits->lasthit = 0;
    }
    
    $hits->images = array();
    
    for($i=0;$i<count($temp)-2;$i++) {
      $hits->images[$i] = new stdClass;
      list(
        $hits->images[$i]->filename,
        $hits->images[$i]->hits,
        $hits->images[$i]->lasthit
      ) = $temp[$i+1];
    }
    
    return $hits;
  }
  
  /**
   * Stores gallery hits.
   * @param string      id of gallery to store hits for
   * @param sgStdClass  instance of object holding hit data
   */
  function putHits($galleryId, $hits) {
  
    $fp = fopen($this->config->base_path.$this->config->pathto_logs.strtr("views".$galleryId,":/?\\","----").".log","w");
    if(!$fp) return false;
    
    fwrite($fp, ",".
      $hits->hits.",".
      $hits->lasthit
    );
      
    if(isset($hits->images)) 
      for($i=0;$i<count($hits->images);$i++)
        if(!empty($hits->images[$i]->filename)) fwrite($fp, "\n".
          $hits->images[$i]->filename.",".
          $hits->images[$i]->hits.",".
          $hits->images[$i]->lasthit
        );
      
    return true;
  }
  
  /**
   * Fetches all registered users.
   */
  function getUsers() {
    $fp = fopen($this->config->base_path.$this->config->pathto_data_dir."users.csv.php","r");
    
    //strip off description line
    fgetcsv($fp,1024);
    
    for($i=0;$entry = fgetcsv($fp,1000,",");$i++) {
      $users[$i] = new sgUser(null,null);
      list(
        $users[$i]->username,
        $users[$i]->userpass,
        $users[$i]->permissions,
        $users[$i]->groups,
        $users[$i]->email,
        $users[$i]->fullname,
        $users[$i]->description,
        $users[$i]->stats
      ) = $entry;
    }
    
    fclose($fp);
    return $users;
  }
  
  /**
   * Stores all registered users.
   * @param array  an array of sgUser objects representing the users to store
   */
  function putUsers($users) {
    $fp = fopen($this->config->base_path.$this->config->pathto_data_dir."users.csv.php","w");
    if(!$fp) return false;
    
    $success = (bool) fwrite($fp,"<?php die(\"The contents of this file are hidden\"); ?>username,md5(pass),permissions,group(s),email,name,description,stats\n");
    for($i=0;$i<count($users);$i++) 
      $success &= (bool) fwrite($fp,$users[$i]->username.",".$users[$i]->userpass.",".$users[$i]->permissions.",\"".$users[$i]->groups."\",\"".$users[$i]->email."\",\"".$users[$i]->fullname."\",\"".$users[$i]->description."\",\"".$users[$i]->stats."\"\n");
    
    fclose($fp);
    return $success;
  }
  
}

?>
