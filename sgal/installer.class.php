<?php

class sgal_installer
{
	public static function install()
	{
		$queries = array(
			'CREATE TABLE albums ( id INTEGER PRIMARY KEY, path VARCHAR(255) NOT NULL, hash VARCHAR(32), parent_id INTEGER NOT NULL, sequence INTEGER, views INTEGER, title TEXT, created DATETIME, deleted DATETIME, sort_order INTEGER, albumCount INTEGER, imageCount INTEGER);',
			'CREATE TABLE images ( id INTEGER PRIMARY KEY, filename VARCHAR(64) NOT NULL, hash VARCHAR(32), album_id INTEGER NOT NULL, sequence INTEGER, views INTEGER, title TEXT, created DATETIME, deleted DATETIME, width INTEGER , height INTEGER);',
			'CREATE TABLE comments ( id INTEGER PRIMARY KEY, album_id INTEGER, image_id INTEGER, time DATETIME NOT NULL, name VARCHAR(255) , email VARCHAR(255) , comment TEXT );',
			'CREATE TABLE config ( key VARCHAR(64) PRIMARY KEY, value TEXT );',
			'CREATE INDEX albums_path ON albums(path);',
			'CREATE INDEX albums_parent_id ON albums(parent_id);',
			'CREATE INDEX comments_album_id ON comments(album_id);',
			'CREATE INDEX comments_image_id ON comments(image_id);',
			'CREATE INDEX images_filename ON images(filename);',
			'CREATE INDEX images_album_id ON images(album_id);'
		);
		
		$db = sgal::db();
		foreach ($queries as $query) {
			try {
				$result = $db->exec($query);
			} catch (PDOException $e) {
				echo 'Exception executing query: '.$query;
			}
		}
		
		self::updateDefaultConfig();
	}
	
	public static function updateDefaultConfig()
	{
		$config = sgal_config::getInstance();
		$defaultConfigValues = array(
			'version' => sgal::version,
			'path_albums' => SGAL_BASE.'/albums',
			'path_templates' => SGAL_BASE.'/templates',
			'template' => 'simple',
			'imageExtensions' => 'jpg|jpeg|png|gif'
		);
		
		foreach ($defaultConfigValues as $key => $value) {
			if ($config->$key === null) {
				$config->$key = $value;
			}
		}
		
		$config->save();
	}
	
	public static function upgrade()
	{
		$db = sgal::db();
		$config = sgal_config::getInstance();
		
		$oldVersion = $config->version;
		
		//run each schema update as required
		switch ($oldVersion) {
		case '2.0alpha1' :
			//$db->exec('ALTER TABLE ...');
		case '2.0alpha2' :
		case '2.0alpha3' :
			break;
		default :
			//upgrading from unsupported version
		}
		
		//update versions
		$config->version = sgal::version;
		$config->save();
		
		//add any new config options
		self::updateDefaultConfig();
	}
}
?>


