<?php 

/**
 * IO class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: iosql.class.php,v 1.1 2004/12/08 10:57:36 tamlyn Exp $
 */

//include the base IO class
require_once dirname(__FILE__)."/io.class.php";
 
/**
 * Class used to read and write data to and from a MySQL database.
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2004 Tamlyn Rhodes
 */
class sgIOsql extends sgIO
{
  
  /**
   * Overridden in subclasses
   */
  function query($query) { }
  function escape_string($query) { }
  function fetch_array($res) { }
  function num_rows($res) { }
  function error()
  {
    return "unknown error";
  }
  
  /**
   * Fetches gallery info for the specified gallery and immediate children.
   * @param string gallery id
   * @param string language code spec for this request (optional)
   * @param int     number of levels of child galleries to fetch (optional)
   */
  function getGallery($galleryId, $language = null, $getChildGalleries = 1) 
  {
    $gal = new sgGallery($galleryId);
    
    if($language == null) $language = $this->config->default_language;
    
    //try to open language specific gallery info
    $res = $this->query("SELECT * FROM ".$this->config->sql_prefix."galleries ".
           "WHERE id='".$this->escape_string($galleryId)."' ".
           "AND lang='".$this->escape_string($language)."'");
    
    //if fail then try to open generic gallery info
    if(!$res || !$this->num_rows($res))
      $res = $this->query("SELECT * FROM ".$this->config->sql_prefix."galleries ".
             "WHERE id='".$this->escape_string($galleryId)."' and lang=''");
    //if that succeeds then get galleries from db
    if($res && $this->num_rows($res)) {
      $galinfo = $this->fetch_array($res);
      $gal->filename = $galinfo['filename'];
      $gal->owner = $galinfo['owner'];
      $gal->groups = $galinfo['groups'];
      $gal->permissions = $galinfo['permissions'];
      $gal->categories = $galinfo['categories'];
      $gal->name = $galinfo['name'];
      $gal->artist = $galinfo['artist'];
      $gal->email = $galinfo['email'];
      $gal->copyright = $galinfo['copyright'];
      $gal->desc = $galinfo['description'];
      $gal->summary = $galinfo['summary'];
      $gal->date = $galinfo['date'];
      $gal->hits = $galinfo['hits'];
      $gal->lasthit = $galinfo['lasthit'];
      
      //try to open language specific image info
      $res = $this->query("SELECT * FROM ".$this->config->sql_prefix."images ".
             "WHERE galleryid='".$this->escape_string($galleryId)."' ".
             "AND lang='".$this->escape_string($language)."'");
      
      //if fail then try to open generic image info
      if(!$res || !$this->num_rows($res))
        $res = $this->query("SELECT * FROM ".$this->config->sql_prefix."images ".
               "WHERE galleryid='".$this->escape_string($galleryId)."' and lang=''");
      for($i=0;$i<$this->num_rows($res);$i++) {
        $gal->images[$i] = new sgImage();
        $imginfo = $this->fetch_array($res);
        $gal->images[$i]->filename = $imginfo['filename'];
        $gal->images[$i]->thumbnail = $imginfo['thumbnail'];
        $gal->images[$i]->owner = $imginfo['owner'];
        $gal->images[$i]->groups = $imginfo['groups'];
        $gal->images[$i]->permissions = $imginfo['permissions'];
        $gal->images[$i]->categories = $imginfo['categories'];
        $gal->images[$i]->name = $imginfo['name'];
        $gal->images[$i]->artist = $imginfo['artist'];
        $gal->images[$i]->email = $imginfo['email'];
        $gal->images[$i]->copyright = $imginfo['copyright'];
        $gal->images[$i]->desc = $imginfo['description'];
        $gal->images[$i]->location = $imginfo['location'];
        $gal->images[$i]->date = $imginfo['date'];
        $gal->images[$i]->camera = $imginfo['camera'];
        $gal->images[$i]->lens = $imginfo['lens'];
        $gal->images[$i]->film = $imginfo['film'];
        $gal->images[$i]->darkroom = $imginfo['darkroom'];
        $gal->images[$i]->digital = $imginfo['digital'];
        $gal->images[$i]->width = $imginfo['width'];
        $gal->images[$i]->height = $imginfo['height'];
        $gal->images[$i]->type = $imginfo['type'];
        $gal->images[$i]->hits = $imginfo['hits'];
        $gal->images[$i]->lasthit = $imginfo['lasthit'];
      }
        
    } else
      //no record found so use iifn method implemented in parent class
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
  function putGallery($gal, $language = "") {
    //insert gallery info
    $success = (bool) $this->query("REPLACE INTO ".$this->config->sql_prefix."galleries ".
           "(id,lang,filename,owner,groups,permissions,categories,name,artist,".
           "email,copyright,description,summary,date,hits,lasthit) VALUES ('".
           $this->escape_string($gal->id)."','".$language."','".
           $this->escape_string($gal->filename)."','".
           $gal->owner."','".$gal->groups."',".$gal->permissions.",'".
           $this->escape_string($gal->categories)."','".
           $this->escape_string($gal->name)."','".
           $this->escape_string($gal->artist)."','".
           $this->escape_string($gal->email)."','".
           $this->escape_string($gal->copyright)."','".
           $this->escape_string($gal->desc)."','".
           $this->escape_string($gal->summary)."','".
           $this->escape_string($gal->date)."',".
           $gal->hits.",".$gal->lasthit.")");
    //delete all image info
    $success &= (bool) $this->query("DELETE FROM ".$this->config->sql_prefix."images ".
          "WHERE galleryid='".$this->escape_string($gal->id)."' AND lang='".$language."'");
    for($i=0;$i<count($gal->images);$i++) {
      $success &= (bool) $this->query("INSERT INTO ".$this->config->sql_prefix."images ".
           "(galleryid,lang,filename,owner,groups,permissions,categories,name,artist,".
           "email,copyright,description,location,date,camera,lens,film,darkroom,digital,".
           "width,height,type,hits,lasthit) VALUES ('".
           $this->escape_string($gal->id)."','".$language."','".
           $this->escape_string($gal->images[$i]->filename)."','".
           $gal->images[$i]->owner."','".$gal->images[$i]->groups."',".
           $gal->images[$i]->permissions.",'".
           $this->escape_string($gal->images[$i]->categories)."','".
           $this->escape_string($gal->images[$i]->name)."','".
           $this->escape_string($gal->images[$i]->artist)."','".
           $this->escape_string($gal->images[$i]->email)."','".
           $this->escape_string($gal->images[$i]->copyright)."','".
           $this->escape_string($gal->images[$i]->desc)."','".
           $this->escape_string($gal->images[$i]->location)."','".
           $this->escape_string($gal->images[$i]->date)."','".
           $this->escape_string($gal->images[$i]->camera)."','".
           $this->escape_string($gal->images[$i]->lens)."','".
           $this->escape_string($gal->images[$i]->film)."','".
           $this->escape_string($gal->images[$i]->darkroom)."','".
           $this->escape_string($gal->images[$i]->digital)."',".
           $gal->images[$i]->width.",".$gal->images[$i]->height.",".
           $gal->images[$i]->type.",".$gal->images[$i]->hits.",".
           $gal->images[$i]->lasthit.")");
    }
    return $success;
  }
  
  /**
   * Fetches hit data from database.
   * @param string  id of gallery to fetch hits for
   */
  function getHits($galleryId) {
    
    $hits = new stdClass;
    
    $res = $this->query("SELECT hits,lasthit FROM ".$this->config->sql_prefix.
           "galleries WHERE id='".$this->escape_string($galleryId)."'");
    if($res && $this->num_rows($res)) {
      $galinfo = $this->fetch_array($res);
      $hits->hits = $galinfo['hits'];
      $hits->lasthit = $galinfo['lasthit'];
      
      $hits->images = array();
      
      $res = $this->query("SELECT filename,hits,lasthit FROM ".$this->config->sql_prefix.
             "images WHERE galleryid='".$this->escape_string($galleryId)."'");
      for($i=0;$i<$this->num_rows($res);$i++) {
        $imginfo = $this->fetch_array($res);
        $hits->images[$i] = new stdClass;
        $hits->images[$i]->filename = $imginfo['filename'];
        $hits->images[$i]->hits = $imginfo['hits'];
        $hits->images[$i]->lasthit = $imginfo['lasthit'];
      }
    } else {
      $hits->hits = 0;
      $hits->lasthit = 0;
      $hits->images = array();
    }
    
    return $hits;
  }
  
  /**
   * Stores gallery hits.
   * @param string      id of gallery to store hits for
   * @param sgStdClass  instance of object holding hit data
   */
  function putHits($galleryId, $hits) {
  
    $success = (bool) $this->query("UPDATE ".$this->config->sql_prefix."galleries ".
           "SET hits=".$hits->hits.", lasthit=".$hits->lasthit." ".
           "WHERE id='".$this->escape_string($galleryId)."'");
           
    foreach($hits->images as $imghit)
      $success &= (bool) $this->query("UPDATE ".$this->config->sql_prefix."images ".
             "SET hits=".$imghit->hits.", lasthit=".$imghit->lasthit." ".
             "WHERE galleryid='".$this->escape_string($galleryId)."' ".
             "AND filename='".$this->escape_string($imghit->filename)."'");

    return $success;
  }
  
  /**
   * Fetches all registered users.
   */
  function getUsers() {
    $res = $this->query("SELECT * FROM ".$this->config->sql_prefix."users");
    
    for($i=0;$i<$this->num_rows($res);$i++) {
      $usrinfo = $this->fetch_array($res);
      $users[$i] = new sgUser($usrinfo['username'],$usrinfo['userpass']);
      $users[$i]->permissions = $usrinfo['permissions'];
      $users[$i]->groups = $usrinfo['groups'];
      $users[$i]->email = $usrinfo['email'];
      $users[$i]->fullname = $usrinfo['fullname'];
      $users[$i]->description = $usrinfo['description'];
      $users[$i]->stats = $usrinfo['stats'];
    }
    
    return $users;
  }
  
  /**
   * Stores all registered users.
   * @param array  an array of sgUser objects representing the users to store
   */
  function putUsers($users) {
    //empty table
    $success = (bool) $this->query("DELETE FROM ".$this->config->sql_prefix."users");
    for($i=0;$i<count($users);$i++)
      $success &= (bool) $this->query("INSERT INTO ".$this->config->sql_prefix."users ".
           "(username,userpass,permissions,groups,email,fullname,description,stats) VALUES ('".
           $this->escape_string($users[$i]->username)."','".
           $users[$i]->userpass."',".$users[$i]->permissions.",'".
           $this->escape_string($users[$i]->groups)."','".
           $this->escape_string($users[$i]->email)."','".
           $this->escape_string($users[$i]->fullname)."','".
           $this->escape_string($users[$i]->description)."','".
           $this->escape_string($users[$i]->stats)."')");
           
    return $success;
  }
}

?>
