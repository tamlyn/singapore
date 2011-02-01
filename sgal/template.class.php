<?php

class sgal_template
{
	protected $mainView;
	
	function __construct()
	{
		$this->sgal = sgal::getInstance();
		$this->config = sgal_config::getInstance();
	}
	
	public function run()
	{
		$this->mainView = 'album';
		
		$this->render();
	}
	
	protected function render()
	{
		$templatePath = $this->getTemplatePath();
		
		ob_start();
		try {
			include $templatePath.'/header.phtml';
			include $templatePath.'/'.$this->mainView.'.phtml';
			include $templatePath.'/footer.phtml';
		} catch (Exception $exception) {
			//ob_clean();
			include $templatePath.'/error.phtml';
		}
		ob_end_flush();
		
	}
	
	public function getTemplatePath()
	{
		return $this->config->path_templates.'/'.$this->config->template;
	}
}
?>