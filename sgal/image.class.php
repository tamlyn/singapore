<?php

class sgal_image extends sgal_item
{
	protected $_album;
	
	protected $_tableName = 'images',
		$_propertyNames = array('id', 'filename', 'hash', 'album_id', 'sequence', 'views', 'title', 'created', 'deleted', 'width', 'height');

	public function getAlbum()
	{
		if (!isset($this->_album)) {
			$this->_album = sgal::getInstance()->loadAlbum($this->album_id);
		}
		return $this->_album;
	}
		
	public function getRealPath()
	{
		return $this->album->realPath.'/'.$this->filename;
	}
}
?>