<?php 

/**
 * IO class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: io_mysql.class.php,v 1.3 2004/11/01 08:58:22 tamlyn Exp $
 */

/**
 * Class used to read and write data to and from a MySQL database.
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2004 Tamlyn Rhodes
 */
class sgIO_mysql extends sgIO
{
  
  /**
   * @param sgConfig pointer to a {@link sgConfig} object representing 
   *   the current script configuration
   */
  function sgIO_mysql(&$config)
  {
    $this->config =& $config;
    mysql_connect($this->config->mysql_host, $this->config->mysql_user, $this->config->mysql_pass);
    mysql_select_db($this->config->mysql_database);
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
    $res = mysql_query("SELECT * FROM ".$this->config->mysql_prefix."galleries ".
           "WHERE id='".mysql_escape_string($galleryId)."' ".
           "AND lang='".mysql_escape_string($language)."'");
    
    //if fail then try to open generic gallery info
    if(!$res || !mysql_num_rows($res))
      $res = mysql_query("SELECT * FROM ".$this->config->mysql_prefix."galleries ".
             "WHERE id='".mysql_escape_string($galleryId)."' and lang=''");
    //if that succeeds then get galleries from db
    if($res && mysql_num_rows($res)) {
      $galinfo = mysql_fetch_array($res);
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
      $res = mysql_query("SELECT * FROM ".$this->config->mysql_prefix."images ".
             "WHERE galleryid='".mysql_escape_string($galleryId)."' ".
             "AND lang='".mysql_escape_string($language)."'");
      
      //if fail then try to open generic image info
      if(!$res || !mysql_num_rows($res))
        $res = mysql_query("SELECT * FROM ".$this->config->mysql_prefix."images ".
               "WHERE galleryid='".mysql_escape_string($galleryId)."' and lang=''");
      for($i=0;$i<mysql_num_rows($res);$i++) {
        $gal->images[$i] = new sgImage();
        $imginfo = mysql_fetch_array($res);
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
      return parent::getGallery($galleryId, $language, $getChildGalleries-1);
    
    
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
    $success = mysql_query("REPLACE INTO ".$this->config->mysql_prefix."galleries ".
           "(id,lang,filename,owner,groups,permissions,categories,name,artist,".
           "email,copyright,description,summary,date,hits,lasthit) VALUES ('".
           mysql_escape_string($gal->id)."','".$language."','".
           mysql_escape_string($gal->filename)."','".
           $gal->owner."','".$gal->groups."',".$gal->permissions.",'".
           mysql_escape_string($gal->categories)."','".
           mysql_escape_string($gal->name)."','".
           mysql_escape_string($gal->artist)."','".
           mysql_escape_string($gal->email)."','".
           mysql_escape_string($gal->copyright)."','".
           mysql_escape_string($gal->desc)."','".
           mysql_escape_string($gal->summary)."','".
           mysql_escape_string($gal->date)."',".
           $gal->hits.",".$gal->lasthit.")");
    //delete all image info
    $success &= mysql_query("DELETE FROM ".$this->config->mysql_prefix."images ".
          "WHERE galleryid='".$gal->id."' AND lang='".$language."'");
    //insert image info
           echo mysql_error();
    for($i=0;$i<count($gal->images);$i++) {
      $success &= mysql_query("INSERT INTO ".$this->config->mysql_prefix."images ".
           "(galleryid,lang,filename,owner,groups,permissions,categories,name,artist,".
           "email,copyright,description,location,date,camera,lens,film,darkroom,digital,".
           "width,height,type,hits,lasthit) VALUES ('".
           mysql_escape_string($gal->id)."','".$language."','".
           mysql_escape_string($gal->images[$i]->filename)."','".
           $gal->images[$i]->owner."','".$gal->images[$i]->groups."',".
           $gal->images[$i]->permissions.",'".
           mysql_escape_string($gal->images[$i]->categories)."','".
           mysql_escape_string($gal->images[$i]->name)."','".
           mysql_escape_string($gal->images[$i]->artist)."','".
           mysql_escape_string($gal->images[$i]->email)."','".
           mysql_escape_string($gal->images[$i]->copyright)."','".
           mysql_escape_string($gal->images[$i]->desc)."','".
           mysql_escape_string($gal->images[$i]->location)."','".
           mysql_escape_string($gal->images[$i]->date)."','".
           mysql_escape_string($gal->images[$i]->camera)."','".
           mysql_escape_string($gal->images[$i]->lens)."','".
           mysql_escape_string($gal->images[$i]->film)."','".
           mysql_escape_string($gal->images[$i]->darkroom)."','".
           mysql_escape_string($gal->images[$i]->digital)."',".
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
    
    $res = mysql_query("SELECT hits,lasthit FROM ".$this->config->mysql_prefix.
           "galleries WHERE id='".mysql_escape_string($galleryId)."'");
    if($res && mysql_num_rows($res)) {
      $galinfo = mysql_fetch_array($res);
      $hits->hits = $galinfo['hits'];
      $hits->lasthit = $galinfo['lasthit'];
      
      $hits->images = array();
      
      $res = mysql_query("SELECT filename,hits,lasthit FROM ".$this->config->mysql_prefix.
             "images WHERE galleryid='".mysql_escape_string($galleryId)."'");
      for($i=0;$i<mysql_num_rows($res);$i++) {
        $imginfo = mysql_fetch_array($res);
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
  
    $success = mysql_query("UPDATE ".$this->config->mysql_prefix."galleries ".
           "SET hits=".$hits->hits.", lasthit=".$hits->lasthit." ".
           "WHERE id='".mysql_escape_string($galleryId)."'");
           
    foreach($hits->images as $imghit)
      $success &= mysql_query("UPDATE ".$this->config->mysql_prefix."images ".
             "SET hits=".$imghit->hits.", lasthit=".$imghit->lasthit." ".
             "WHERE galleryid='".mysql_escape_string($galleryId)."' ".
             "AND filename='".mysql_escape_string($imghit->filename)."'");

    return $success;
  }
  
  /**
   * Fetches all registered users.
   */
  function getUsers() {
    $res = mysql_query("SELECT * FROM ".$this->config->mysql_prefix."users");
    
    for($i=0;$i<mysql_num_rows($res);$i++) {
      $usrinfo = mysql_fetch_array($res);
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
    $success  = mysql_query("TRUNCATE TABLE ".$this->config->mysql_prefix."users");
    for($i=0;$i<count($users);$i++)
      $success &= mysql_query("INSERT INTO ".$this->config->mysql_prefix."users ".
           "(username,userpass,permissions,groups,email,fullname,description,stats) VALUES ('".
           mysql_escape_string($users[$i]->username)."','".
           $users[$i]->userpass."',".$users[$i]->permissions.",'".
           mysql_escape_string($users[$i]->groups)."','".
           mysql_escape_string($users[$i]->email)."','".
           mysql_escape_string($users[$i]->fullname)."','".
           mysql_escape_string($users[$i]->description)."','".
           mysql_escape_string($users[$i]->stats)."')");
           
    return $success;
  }
}

?>
