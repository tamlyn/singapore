<?php

if (!defined('SGAL_BASE')) {
	define('SGAL_BASE', realpath(dirname(__FILE__)));
}

if (!defined('SGAL_DSN')) {
	define('SGAL_DSN', 'sqlite:' . SGAL_BASE . '/data/sgal.sqlite');
}

require_once SGAL_BASE . '/config.class.php';
require_once SGAL_BASE . '/installer.class.php';
require_once SGAL_BASE . '/item.class.php';
require_once SGAL_BASE . '/album.class.php';
require_once SGAL_BASE . '/image.class.php';
require_once SGAL_BASE . '/template.class.php';

class sgal
{
	const version = "2.0alpha1";
	
	private static $instance, $pdo;
	
	private $config, $db, $_currentAlbum, $_currentImage, $_template;
	
	private function __construct()
	{
		$this->db = self::db();
		$this->config = sgal_config::getInstance();
		
		//do transparent install & upgrade
		if (!$this->config->version) {
			sgal_installer::install();
		} elseif ($this->config->version != self::version) {
			sgal_installer::upgrade();
		}
	}
	
	public function __get($name)
	{
		$method = 'get'.ucfirst($name);
		if (method_exists($this, $method)) {
			return $this->$method();
		} else {
			throw new OutOfBoundsException('Invalid property: '.$name);
		}
	}
	
	public static function getInstance()
	{
		if (!isset(self::$instance)) {
            $classname = __CLASS__;
            self::$instance = new $classname;
        }

        return self::$instance;
	}
	
	public static function db()
	{
		if (!isset(self::$pdo)) {
            self::$pdo = new PDO(SGAL_DSN);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$pdo;
	}
	
	public function loadAlbum($idOrPath)
	{
		$album = $this->loadAlbumFromDb($idOrPath);
		if ($album) {
			if ($album->checkHash()) {
				return $album;
			} //hash check failed so continue and do a sync
		} elseif (is_numeric($idOrPath)) {
			throw new Exception('Album ID not found');
		}
		
		//check if album exists on disk
		$fullPath = $this->config->path_albums.$idOrPath;
		if (file_exists($fullPath) && is_dir($fullPath)) {
			//album exists on disk but not in db so
			$this->sync();
			//try again
			$album = $this->loadAlbumFromDb($idOrPath);
			if ($album) {
				//no need to check hash this time
				return $album;
			} else {
				throw new Exception('Album could not be loaded');
			}
		} else {
			throw new Exception('Album not found at '.$idOrPath);
		}
	}
	
	private function loadAlbumFromDb($key) {
		if (is_numeric($key)) {
			$query = 'SELECT * FROM albums WHERE id = ? AND deleted IS NULL';
		} else {
			$query = 'SELECT * FROM albums WHERE path = ? AND deleted IS NULL';
		}
		
		$statement = $this->db->prepare($query);
		$statement->execute(array($key));		

		if ($data = $statement->fetch()) {
			return new sgal_album($data);
		} else {
			return false;
		}
	}
	
	public function loadImage($idOrPath)
	{
		
	}
	
	public function getTemplate()
	{
		//TODO: check for custom template
		
		if (!isset($this->_template)) {
			$this->_template = new sgal_template();
		}
		return $this->_template;
	}
	
	public function getCurrentAlbum()
	{
		if (!isset($this->_currentAlbum)) {
			if (isset($_GET['album'])) {
				//format path properly
				$pathBits = explode('/', $_GET['album']);
				$newBits = array('');
				foreach($pathBits as $bit) {
					if (!empty($bit) && $bit != '.' && $bit != '..') {
						$newBits[] = $bit;
					}
				}
				$path = implode('/', $newBits);
			} else {
				$path = '';
			}
		
			$this->_currentAlbum = $this->loadAlbum($path);
		}
		
		return $this->_currentAlbum;
	}
	
	public function sync()
	{
		//load all albums from disk
		$diskAlbums = $this->getAlbumsFromDisk();
		
		//load all albums from db
		$dbAlbums = array();
		$albumStatement = $this->db->query('SELECT * FROM albums WHERE deleted IS NULL');
		while ($row = $albumStatement->fetch()) {
			$dbAlbums[$row['path']] = $row;
		}

		//loop through disk albums
		foreach ($diskAlbums as $path => $diskAlbum) {
			//check if current disk album exists in db
			if (isset($dbAlbums[$path])) {
				$album = new sgal_album($dbAlbums[$path]);
				//mark this album as found
				$dbAlbums[$path]['_found'] = true;
			} else {
				//create new database record for album
				//parent album should always exist cos albums are created in order from top down 
				$parentId = empty($path) ? '0' : $dbAlbums[sgal_album::parentPath($path)]['id'];
				$albumData = array(
					'path' => $path,
					'parent_id' => $parentId,
					'created' => date('Y-M-D H:i:s'),
					'title' => ucwords(str_replace('_', ' ', substr($path, strrpos($path, '/') + 1)))
				);

				$album = new sgal_album($albumData);
				$album->save();
				$dbAlbums[$path] = $album->toArray();
				$dbAlbums[$path]['_found'] = true;
			}
			
			//check the hash of the current album is up to date
			if (!$album->checkHash()) {
				
				$album->hash = sgal_album::hash($diskAlbum['dirs'], $diskAlbum['files']);
				$album->albumCount = count($diskAlbum['dirs']);
				$album->imageCount = count($diskAlbum['files']);
				$album->save();
				
				$albumImages = array();
				foreach ($album->images as $image) {
					$albumImages[$image->filename] = $image;
				}

				foreach ($diskAlbum['files'] as $filename) {
					//if image is not in db then add it
					if (!isset($albumImages[$filename])) {
						$imageData = array(
							'filename' => $filename,
							'album_id' => $album->id,
							'created' => date('Y-M-D H:i:s'),
							'title' => ucwords(str_replace('_', ' ', substr($filename, 0, strrpos($filename, '.')))) 
						);
						$image = new sgal_image($imageData);
						/*$imageSize = getimagesize($image->realPath);
						$image->width = $imageSize[0];
						$image->height = $imageSize[1];*/
						$image->save();
					}
					//remove found images from array
					unset($albumImages[$filename]);
				}
				
				//delete remaining db image data that was not matched on disk
				foreach ($albumImages as $image) {
					$image->delete();
				}
			}
			
		}
		
		//delete remaining db album data that was not matched on disk
		foreach ($dbAlbums as $albumData) {
			if (!$albumData['_found']) {
				$album = new sgal_album($albumData);
				$album->delete();
			}
		}
	}
	
	public function getAlbumsFromDisk($path = '')
	{
		$listing = self::getListing($this->config->path_albums.'/'.$path, $this->config->imageExtensions);
		$pathArray = array($path => $listing);
		foreach($listing['dirs'] as $dir) {
			$pathArray = array_merge($pathArray, $this->getAlbumsFromDisk($path.'/'.$dir));
		}
		return $pathArray;
	}
	
	
	/**
	 * @param string  relative or absolute path to directory
	 * @param string  regular expression of files to return (optional)
	 * @param bool    true to get hidden directories too (optional)
	 * @returns array|false  an array representing the directory and its contents
	 * @static
	 */
	public static function getListing($path, $mask = null, $getHidden = false)
	{
		$dir = array();
		$dir['path'] = realpath($path);
		$dir['files'] = array();
		$dir['dirs'] = array();

		$dp = dir($dir['path']);
		if(!$dp) return false;

		while(false !== ($entry = $dp->read())) {
			if(is_dir($dir['path'].'/'.$entry)) {
				if(($entry{0} != '.') || $getHidden)
					$dir['dirs'][] = $entry;
			} else {
				if($mask == null || preg_match("/\.($mask)$/i",$entry)) {
					$dir['files'][] = $entry;
				}
			}
		}

		sort($dir['files']);
		sort($dir['dirs']);
		$dp->close();
		return $dir;
	}
	
	
}

?>