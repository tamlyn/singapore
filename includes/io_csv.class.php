<?php 

/**
 * IO class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003-2005 Tamlyn Rhodes
 * @version $Id: io_csv.class.php,v 1.29 2005/12/04 04:39:46 tamlyn Exp $
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
    return "$Revision: 1.29 $";
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
   * @return sgGallery  the gallery object created
   */
  function &getGallery($galleryId, &$parent, $getChildGalleries = 1, $language = null) 
  {
    $gal =& new sgGallery($galleryId, $parent);

    if($language == null) {
      $translator =& Translator::getInstance();
      $language = $translator->language;
    }
    
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
      
      
      //only fetch individual images if child galleries are required
      if($getChildGalleries) {
        for($i=0;$i<count($temp)-3;$i++) {
          $gal->images[$i] =& new sgImage($temp[$i+2][0], $gal, $this->config);
          list(,
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
          ) = @GetImageSize($gal->images[$i]->realPath());
        }
      //otherwise just fill in empty images
      } else if(count($temp) > 3) {
        for($i=0;$i<count($temp)-3;$i++)
          $gal->images[$i] =& new sgImage($temp[$i+2][0], $gal);
      }
        
    } else
      //no metadata found so use iifn method implemented in superclass
      return parent::getGallery($galleryId, $parent, $getChildGalleries, $language);
    
    //discover child galleries
    $dir = Singapore::getListing($this->config->base_path.$this->config->pathto_galleries.$galleryId."/");
    if($getChildGalleries)
      //but only fetch their info if required too
      foreach($dir->dirs as $gallery) 
        $gal->galleries[] = $this->getGallery($galleryId."/".$gallery, $gal, $getChildGalleries-1, $language);
    else
      //otherwise just copy their names in so they can be counted
      $gal->galleries = $dir->dirs;
    
    return $gal;
  }
  
  /**
   * Stores gallery information.
   * @param sgGallery  instance of gallery object to be stored
   */
  function putGallery($gal) {
    $dataFile = $this->config->base_path.$this->config->pathto_galleries.$gal->id."/metadata.csv";
    @chmod($dataFile, octdec($this->config->file_mode));
    $fp = @fopen($dataFile,"w");
    if(!$fp)
      return false;
    
    $success  = (bool) fwrite($fp,"filename,thumbnail,owner,group(s),permissions,catergories,image name,artist name,artist email,copyright,image description,image location,date taken,camera info,lens info,film info,darkroom manipulation,digital manipulation");
    $success &= (bool) fwrite($fp,"\n\"".
      $gal->filename.'",,'.
      $gal->owner.','.
      $gal->groups.','.
      $gal->permissions.','.
      $gal->categories.',"'.
      str_replace('"','""',$gal->name).'","'.
      str_replace('"','""',$gal->artist).'","'.
      str_replace('"','""',$gal->email).'","'.
      str_replace('"','""',$gal->copyright).'","'.
      str_replace('"','""',$gal->desc).'","'.
      str_replace('"','""',$gal->summary).'","'.
      str_replace('"','""',$gal->date).'"'
    );
    
    for($i=0;$i<count($gal->images);$i++)
      $success &= (bool) fwrite($fp,"\n\"".
        $gal->images[$i]->id.'",,'.
        //$gal->images[$i]->thumbnail.','.
        $gal->images[$i]->owner.','.
        $gal->images[$i]->groups.','.
        $gal->images[$i]->permissions.','.
        $gal->images[$i]->categories.',"'.
        str_replace('"','""',$gal->images[$i]->name).'","'.
        str_replace('"','""',$gal->images[$i]->artist).'","'.
        str_replace('"','""',$gal->images[$i]->email).'","'.
        str_replace('"','""',$gal->images[$i]->copyright).'","'.
        str_replace('"','""',$gal->images[$i]->desc).'","'.
        str_replace('"','""',$gal->images[$i]->location).'","'.
        str_replace('"','""',$gal->images[$i]->date).'","'.
        str_replace('"','""',$gal->images[$i]->camera).'","'.
        str_replace('"','""',$gal->images[$i]->lens).'","'.
        str_replace('"','""',$gal->images[$i]->film).'","'.
        str_replace('"','""',$gal->images[$i]->darkroom).'","'.
        str_replace('"','""',$gal->images[$i]->digital).'"'
      );
    $success &= (bool) fclose($fp);
  
    return $success;
  }
  
  /**
   * Fetches hit data from file.
   * @param sgGallery  gallery object to load hits into
   */
  function getHits(&$gal) {
    
    $fp = @fopen($this->config->base_path.$this->config->pathto_galleries.$gal->id."/hits.csv","r");
    
    if($fp) {
      while($temp[] = fgetcsv($fp,255));
      fclose($fp);
    } else $temp = array();
    
    if(isset($temp[0]))
      list(
        ,
        $gal->hits,
        $gal->lasthit
      ) = $temp[0];
    
    for($i=0;$i<count($temp)-2;$i++) {
      if($temp[$i+1][0] == $gal->images[$i]->id) 
        list(
          ,
          $gal->images[$i]->hits,
          $gal->images[$i]->lasthit
        ) = $temp[$i+1];
      else
        foreach($gal->images as $key => $img)
          if($temp[$i+1][0] == $img->id) 
            list(
              ,
              $gal->images[$key]->hits,
              $gal->images[$key]->lasthit
            ) = $temp[$i+1];
        
    }
    
    return true;
  }
  
  /**
   * Stores gallery hits.
   * @param string      id of gallery to store hits for
   * @param sgStdClass  instance of object holding hit data
   */
  function putHits($gal) {
    $logfile = $this->config->base_path.$this->config->pathto_galleries.$gal->id."/hits.csv";
    @chmod($logfile, octdec($this->config->file_mode));
    $fp = @fopen($logfile,"w");
    if(!$fp) return false;
    
    fwrite($fp, '"'.
      $gal->id.'",'.
      $gal->hits.','.
      $gal->lasthit
    );
      
    for($i=0;$i<count($gal->images);$i++)
      fwrite($fp, "\n\"".
        $gal->images[$i]->id.'",'.
        $gal->images[$i]->hits.','.
        $gal->images[$i]->lasthit
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
