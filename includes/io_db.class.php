<?php 

require_once('DB.php');

/**
 * IO class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: io_db.class.php,v 1.2 2004/05/23 16:57:30 tamlyn Exp $
 */

/**
 * Class used to read and write data to and from a database using PEAR::DB files.
 * @see sgIO_csv
 * @package singapore
 * @author Joel Sjögren <joel dot sjogren at nonea dot se>
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 */
class sgIO_db {

    /**
     * instance of a {@link sgConfig} object representing the current 
     * script configuration
     * @var sgConfig
     */
    var $config;

    var $db;

    var $tableUsers     = 'users';
    var $tableGalleries = 'galleries';
    var $tableImageInfo = 'imageinfo';
    
    var $tablesChecked = false;
    
    function sgIO_db(&$config)
    {
        $this->config =& $config;
        $this->init();
    }

    /**
     * creates a database object, opens a database connection, and initializes
     */
    function init ()
    {
        // Init
        $dsn = '';
        // Create DSN
        $dsn .= $this->config->db_api . '://';
        if ($this->config->db_user) {
            $dsn .= $this->config->db_user;
            if (!empty($this->config->db_password)) $dsn .= ':' . $this->config->db_password;
            $dsn .= '@';
        }
        if (!empty($this->config->db_protocol)) $dsn .= $this->config->db_protocol . '+';
        $dsn .= $this->config->db_host;
        if ($this->config->db_port) $dsn .= ':' . $this->config->db_port;
        if ($this->config->db_database) $dsn .= '/' . $this->config->db_database;
        
        // Establish connection
        $this->db = DB::connect($dsn);
        // An error?
        if (DB::isError($this->db)) die($this->db->getMessage());
        // Set default fetching mode
        $this->db->setFetchMode(DB_FETCHMODE_ASSOC);
        // Set tables
        if (@$this->config->db_table_users)     $this->tableUsers     = $this->config->db_table_users;
        if (@$this->config->db_table_galleries) $this->tableGalleries = $this->config->db_table_galleries;
        if (@$this->config->db_table_imageinfo) $this->tableImageInfo = $this->config->db_table_imageinfo;
    }

    function checkTables ($die = true)
    {
        if ($this->tablesChecked) return true;
        // Make sure all tables exists
        $error = "The table for storing %s doesn't exists. Please run the setup first, or create the table manually.";
        $sql = "SELECT COUNT(*) FROM !";
        $res = $this->db->getOne($sql, $this->tableUsers);
        if (DB::isError($res)) if ($die) die(sprintf($error, "users")); else return false;
        $res = $this->db->getOne($sql, $this->tableGalleries);
        if (DB::isError($res)) if ($die) die(sprintf($error, "galleries")); else return false;
        $res = $this->db->getOne($sql, $this->tableImageInfo);
        if (DB::isError($res)) if ($die) die(sprintf($error, "image info")); else return false;
        // Cache checked = make sure only one check per request
        $this->tablesChecked = true;
        return true;
    }

    function executeFile ($file)
    {
        if (!file_exists($file)) die(sprintf("File %s doesn't exists.", $file));
        if (!is_readable($file)) die(sprintf("Can't read file %s.", $file));
        // Read the file
        if (function_exists('file_get_contents')) $contents = file_get_contents($file);
        else $contents = implode('', file($file));
        // Replace with table names
        $contents = preg_replace("/([\s])[\$]TABLE_USERS([\s$])/m", "\\1" . $this->tableUsers . "\\2", $contents);
        $contents = preg_replace("/([\s])[\$]TABLE_GALLERIES([\s$])/m", "\\1" . $this->tableGalleries . "\\2", $contents);
        $contents = preg_replace("/([\s])[\$]TABLE_IMAGEINFO([\s$])/m", "\\1" . $this->tableImageInfo . "\\2", $contents);
        // Separate contents
        $contents = explode(';', $contents);
        // Execute all commands
        foreach ($contents as $command) {
            // Don't run empty commands
            if (!trim($command)) continue;
            // execute
            $res = $this->db->query($command);
            // An error occured?
            if (DB::isError($res)) die(nl2br(sprintf("Couldn't run all the SQL contents of %s.\n The file probably contains errors.\n\nThe following command failed:\n%s", $file, '<code>' . str_replace(' ', '&nbsp;', $command) . '</code>')));
        }
    }

    function &getGallery ($galleryId, $getImages = true, $getSubGalleries = 2)
    {
        // Make sure database tables are correct
        $this->checkTables();
        
        // New gallery object
        $gal = new sgGallery($galleryId);

        // Get gallery info
        $sql = "SELECT * FROM ! WHERE id = ?";
        $res = $this->db->getRow($sql, array($this->tableGalleries, $galleryId));
        
        // Gallery found in database
        if (is_array($res)) {

            $gal->numid        = $res['numid'];
            $gal->filename     = $res['thumbnail'];
            $gal->owner        = $res['owner'];
            $gal->groups       = $res['groups'];
            $gal->permissions  = $res['permissions'];
            $gal->categories   = $res['categories'];
            $gal->name         = $res['name'];
            $gal->artist       = $res['artist'];
            $gal->email        = $res['artist_email'];
            $gal->copyright    = $res['copyright'];
            $gal->desc         = $res['description'];
      
            if ($getImages) {
                $sql = "SELECT * FROM ! WHERE gallery = ? ORDER BY numorder";
                $imageRes = $this->db->query($sql, array($this->tableImageInfo, $res['numid']));
                if (!DB::isError($imageRes)) {
                    $i = 0;
                    while ($row = $imageRes->fetchRow()) {
                        $row['datafields'] = @unserialize($row['datafields']);
                        $gal->images[$i] = new sgImage();
                        $gal->images[$i]->numid        = $row['numid'];
                        $gal->images[$i]->filename     = $row['filename'];
                        $gal->images[$i]->thumbnail    = $row['thumbnail'];
                        $gal->images[$i]->owner        = $row['owner'];
                        $gal->images[$i]->groups       = $row['groups'];
                        $gal->images[$i]->permissions  = $row['permissions'];
                        $gal->images[$i]->categories   = $row['categories'];
                        $gal->images[$i]->name         = $row['imagename'];
                        $gal->images[$i]->artist       = $row['artist'];
                        $gal->images[$i]->email        = $row['artist_email'];
                        $gal->images[$i]->copyright    = $row['copyright'];
                        $gal->images[$i]->desc         = $row['description'];
                        $gal->images[$i]->location     = @$row['datafields']['location'];
                        $gal->images[$i]->date         = @$row['datafields']['date'];
                        $gal->images[$i]->camera       = @$row['datafields']['camera'];
                        $gal->images[$i]->lens         = @$row['datafields']['lens'];
                        $gal->images[$i]->film         = @$row['datafields']['film'];
                        $gal->images[$i]->darkroom     = @$row['datafields']['darkroom'];
                        $gal->images[$i]->digital      = @$row['datafields']['digital'];
                        // get image size and type
                        list(
                            $gal->images[$i]->width, 
                            $gal->images[$i]->height, 
                            $gal->images[$i]->type
                        ) = substr($gal->images[$i]->filename, 0, 7) == "http://"
                            ? GetImageSize($gal->images[$i]->filename)
                            : GetImageSize($this->config->pathto_galleries.$gal->id."/".$gal->images[$i]->filename);
                        $i++;
                    }
                }
            }
        } else {
            // Gallery couldn't be found in database. Use fallback IO
            $exists = sgIO_fallback::getGallery($gal, $this->config->pathto_galleries.$gal->id);
            // If gallery doesn't exists, don't bother getting subgalleries
            if (!$exists) $getSubGalleries = 0;
            // Root gallery not in database - force addition
            if ($galleryId == '.') {
                $this->putGallery($gal);
            }
        }
    
        // Get subgalleries
        if ($getSubGalleries) {
            $subGalleries = sgIO_fallback::getSubGalleries($gal->id, $this->config->pathto_galleries);
            foreach ($subGalleries as $gallery) $gal->galleries[] = $this->getGallery($gallery, true, $getSubGalleries-1);
        }
    
        // Return
        return $gal;
    }

    function putGallery (&$gallery)
    {
        // Make sure database tables are correct
        $this->checkTables();

        // Does gallery already exists?
        $sql = "SELECT numid FROM ! WHERE id = ?";
        $numID = $this->db->getOne($sql, array($this->tableGalleries, $gallery->id));
        $galleryExists = (!DB::isError($numID) && $numID);

        // Create new gallery
        if (!$galleryExists) {
            $numID = $this->db->nextID($this->tableGalleries);
            $sql = "INSERT INTO ! (numid, id, thumbnail, owner, groups, permissions, categories, name, artist, artist_email, copyright, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $res = $this->db->query($sql, array(
                $this->tableGalleries,
                $numID,
                $gallery->id,
                $gallery->filename,
                $gallery->owner,
                $gallery->groups,
                $gallery->permissions,
                $gallery->categories,
                $gallery->name,
                $gallery->artist,
                $gallery->email,
                $gallery->copyright,
                $gallery->desc,
            ));
        // Update gallery
        } else {
            $sql = "UPDATE ! SET thumbnail = ?, owner = ?, groups = ?, permissions = ?, categories = ?, name = ?, artist = ?, artist_email = ?, copyright = ?, description = ? WHERE numid = ?";
            $res = $this->db->query($sql, array(
                $this->tableGalleries,
                $gallery->filename,
                $gallery->owner,
                $gallery->groups,
                $gallery->permissions,
                $gallery->categories,
                $gallery->name,
                $gallery->artist,
                $gallery->email,
                $gallery->copyright,
                $gallery->desc,
                $numID,
            ));
        }

        // Don't go further if save wasn't OK
        if (DB::isError($res)) return false;

        // Save images
        $success = true;
        if (is_array($gallery->images)) {
            // Create SQL-queries
            $sqlD = "DELETE FROM ! WHERE gallery = ? !";
            $sqlI = "INSERT INTO ! (numid, gallery, numorder, filename, thumbnail, owner, groups, permissions, categories, imagename, artist, artist_email, copyright, description, datafields) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $sqlU = "UPDATE ! SET gallery = ?, numorder = ?, filename = ?, thumbnail = ?, owner = ?, groups = ?, permissions = ?, categories = ?, imagename = ?, artist = ?, artist_email = ?, copyright = ?, description = ?, datafields = ? WHERE numid = ?";
            // Get the numeric ids of the already existing images
            $existingImages = array();
            foreach ($gallery->images as $i => $tempImage) {
                if (!@$tempImage->numid) continue;
                $existingImages[] = "numid != " . $this->db->quote($tempImage->numid);
            }
            $existingImages = (count($existingImages) ? ' AND ' . implode(' AND ', $existingImages) : '');
            // Delete all images that are no longer with us
            $this->db->query($sqlD, array($this->tableImageInfo, $numID, $existingImages));
            // Loop through all images and save them to database
            for ($i = 0; $i < count($gallery->images); $i++) {
                $datafields = serialize(array(
                    'location'  => $gallery->images[$i]->location,
                    'date'      => $gallery->images[$i]->date,
                    'camera'    => $gallery->images[$i]->camera,
                    'lens'      => $gallery->images[$i]->lens,
                    'film'      => $gallery->images[$i]->film,
                    'darkroom'  => $gallery->images[$i]->darkroom,
                    'digital'   => $gallery->images[$i]->digital,
                ));
                // Update image
                if (@$gallery->images[$i]->numid) {
                    $res = $this->db->query($sqlU, array(
                        $this->tableImageInfo,
                        $numID,
                        $i,
                        $gallery->images[$i]->filename,
                        $gallery->images[$i]->thumbnail,
                        $gallery->images[$i]->owner,
                        $gallery->images[$i]->groups,
                        $gallery->images[$i]->permissions,
                        $gallery->images[$i]->categories,
                        $gallery->images[$i]->name,
                        $gallery->images[$i]->artist,
                        $gallery->images[$i]->email,
                        $gallery->images[$i]->copyright,
                        $gallery->images[$i]->desc,
                        $datafields,
                        $gallery->images[$i]->numid,
                    ));
                // Insert new image
                } else {
                    $newImageID = $this->db->nextID($this->tableImageInfo);
                    $res = $this->db->query($sqlI, array(
                        $this->tableImageInfo,
                        $newImageID,
                        $numID,
                        $i,
                        $gallery->images[$i]->filename,
                        $gallery->images[$i]->thumbnail,
                        $gallery->images[$i]->owner,
                        $gallery->images[$i]->groups,
                        $gallery->images[$i]->permissions,
                        $gallery->images[$i]->categories,
                        $gallery->images[$i]->name,
                        $gallery->images[$i]->artist,
                        $gallery->images[$i]->email,
                        $gallery->images[$i]->copyright,
                        $gallery->images[$i]->desc,
                        $datafields,
                    ));
                }
                $success &= !DB::isError($res);
            }
        }
        // Return
        return $success;
    }
  
    function &getHits ($galleryId)
    {
        // Create SQL-query
        $sql = "SELECT stats FROM ! WHERE id = ?";
        // Get stats
        $stats = $this->db->getOne($sql, array($this->tableGalleries, $galleryId));
        // Valid result?
        $stats = !DB::isError($stats) ? unserialize($stats) : false;
        if (!is_object($stats)) {
            $stats->hits = 0;
            $stats->lasthit = 0;
        }
        // Valid images?
        if (!@$stats->images || !is_array($stats->images)) $stats->images = array();
        foreach ($stats->images as $i => $img) {
            if (!@$stats->images[$i]->filename) unset($stats->images[$i]);
        }
        // Return
        return $stats;
    }
  
    function putHits ($galleryId, &$hits)
    {
        // Create SQL-query
        $sql = "UPDATE ! SET stats = ? WHERE id = ?";
        // Set gallery statistics
        $this->db->query($sql, array($this->tableGalleries, serialize($hits), $galleryId));
        // Return
        return true;
    }
  
    function getUsers ()
    {
        // Init
        $users = array();
        // Get result
        $sql = "SELECT * FROM !";
        $res = $this->db->query($sql, $this->tableUsers);
        // Loop
        if (!DB::isError($res)) {
            while ($row = $res->fetchRow()) {
                $tmp->username     = $row['username'];
                $tmp->userpass     = $row['userpass'];
                $tmp->permissions  = $row['permissions'];
                $tmp->fullname     = $row['fullname'];
                $tmp->description  = $row['description'];
                $tmp->stats        = $row['stats'];
                $users[] = &$tmp;
            }
        }
        // No users? Get default admin user from IO fallback
        if (!count($users)) $users = sgIO_fallback::getUsers();
        // Return
        return $users;
    }
  
    function putUsers ($users)
    {
        // Init
        $success = true;
        // Clear database
        $this->db->query("DELETE FROM !", $this->tableUsers);
        // Create query
        $sql = "INSERT INTO ! (username, userpass, permissions, fullnamn, description, stats) VALUES (?, ?, ?, ?, ?, ?)";
        // Loop
        for ($i = 0; $i < count($users); $i++) {
            $res = $this->db->query($sql, array(
                $this->tableUsers,
                $users[$i]->username,
                $users[$i]->userpass,
                $users[$i]->permissions,
                $users[$i]->fullname,
                $users[$i]->description,
                $users[$i]->stats,
            ));
            $success &= !DB::isError($res);
        }
        // Return
        return $success;
    }
}

?>
