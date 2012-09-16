<?php
/**
 * User: fad
 * Date: 05.09.12
 * Time: 11:49
 */
class NewsModule extends WebModule
{
	/** @var string 'webroot/uploads/' . $uploadPath */
	public $_uploadPath = 'news';
	public $uploadAllowExt = 'jpg,jpeg,gif,bmp,png';

	public function getUploadPath()
	{
		return Yii::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $this->_uploadPath;
	}

	public function init()
	{
		parent::init();

		$this->setImport(array(
			'news.models.*',
		));
	}
}
