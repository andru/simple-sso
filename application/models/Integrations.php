<?php
include_once(dirname(__FILE__).'/integration/MediaWiki.php');
include_once(dirname(__FILE__).'/integration/Vanilla.php');

class Application_Model_Integrations {
	
	public function __construct($opts=array()){
		$bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
		$settings = $bootstrap->getOptions();
		$this->_cookieDomain = $settings['app']['cookiedomain'];
		$this->persist = (isset($opts['persist']) && $opts['persist']===true) ? true : false;

		if(Zend_Registry::isRegistered('logger')){
		  $this->logger = Zend_Registry::get('logger');
		}
		$conf = Zend_Registry::get('integrations');
		//echo '<pre>'; print_r($conf->mediawiki->toArray()); exit;

		$this->integrations = array();

		if(isset($conf->mediawiki) && $conf->mediawiki->enabled){
			$this->integrations[] = new Application_Model_Integration_MediaWiki($conf->mediawiki->toArray());
		}
		if(isset($conf->vanilla) && $conf->vanilla->enabled){
			$this->integrations[] = new Application_Model_Integration_Vanilla($conf->vanilla->toArray());
		}
		/*if($conf->wordpress['enabled']===true){
			$this->integrations[] = new Application_Model_Integration_Wordpress($conf['wordpress']);
		}*/
		

	}
	
	protected function log($msg){
    if($this->logger && $this->logger instanceof Zend_Log){
      $this->logger->info($msg);
    }
	}
	
	public function onAuthenticate(){
		if($this->persist){
			setcookie('SSO-Persist', true, time()+31536000, '/', $this->_cookieDomain);
		}
		setcookie('SSO-Authed', true, time()+31536000, '/', $this->_cookieDomain);
		foreach($this->integrations as $i){
			$i->onAuthenticate();
		}
	}
	
	public function onDestroySession(){
		$this->log('onDestroySession, running integrations... ');
		foreach($this->integrations as $i){
			$i->onDestroySession();
		}
		$this->deleteCookies();
	}
	
	public function deleteCookies(){
		/*if($this->persist){
			setcookie('SSO-Persist', false, time()-60, '/', $this->_cookieDomain);
		}*/
		setcookie('SSO-Persist', false, time()-60, '/', $this->_cookieDomain);
		setcookie('SSO-Authed', false, time()-60, '/', $this->_cookieDomain);
	}
}