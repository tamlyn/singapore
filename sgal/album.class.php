<?php

class sgal_album extends sgal_item
{
	protected $_albums, $_images;
	
	protected $_tableName = 'albums',
		$_propertyNames = array('id', 'path', 'hash', 'parent_id', 'sequence', 'sort_order', 'views', 'title', 'created', 'deleted', 'albumCount', 'imageCount');
	
	public function addAlbum($subAlbum)
	{
		$this->_albums[] = $subAlbum;
	}
	
	public function addImage($image)
	{
		$this->_images[] = $image;
	}
	
	public function getAlbums()
	{
		if (!isset($this->_albums)) { //need to load albums
			$query = 'SELECT * FROM albums WHERE parent_id = ? AND deleted IS NULL';
			
			$statement = $this->db->prepare($query);
			$statement->execute(array($this->id));
			
			$this->_albums = array();
			while ($data = $statement->fetch()) {
				$this->_albums[] = new sgal_album($data);
			}
		}
		return $this->_albums;
	}

	public function getImages()
	{
		if (!isset($this->_images)) {
			$query = 'SELECT * FROM images WHERE album_id = ? AND deleted IS NULL';
			
			$statement = $this->db->prepare($query);			
			$result = $statement->execute(array($this->data['id']));
			
			$this->_images = array();
			while ($data = $statement->fetch()) {
				$this->_images[] = new sgal_image($data);
			}
		}
		
		return $this->_images;
	}
	
	public function getAlbumCount()
	{
		return $this->data['albumCount'];
	}
	
	public function getImageCount()
	{
		if (!isset($this->data['imageCount'])) {
			if (isset($this->_images)) {
				$this->data['imageCount'] = count($this->_images);
			} else {
				
			}
		}
		return $this->data['imageCount'];
	}
	
	public static function hash($albums, $images)
	{
		return count($albums) || count($images) 
			? md5('ALBUMS '.implode(' ', $albums).' IMAGES '.implode(' ', $images))
			: '';
	}
	
	public function checkHash()
	{
		$listing = sgal::getListing($this->getRealPath(), $this->config->imageExtensions);
		return $this->hash == self::hash($listing['dirs'], $listing['files']);
	}
	
	public static function parentPath($path)
	{
		return substr($path, 0, strrpos($path, '/'));
	}
	
	public function getRealPath()
	{
		return realpath($this->config->path_albums.'/'.$this->path);	
	}
	
	public function delete($hardDelete = false)
	{
		foreach ($this->albums as $album) {
			$album->delete($hardDelete);
		}
		
		foreach ($this->images as $image) {
			$image->delete($hardDelete);
		}
		
		parent::delete($hardDelete);
	}
	
}
?>