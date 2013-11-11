<?php

class Application_Model_Integration_Vanilla {
	
	public function __construct($domain){
		$app_options = Zend_Registry::get('options');
		$this->_cookieDomain = isset($opts['cookie_domain']) ? $opts['cookie_domain'] : $app_options['app']['cookiedomain'];
		if(Zend_Registry::isRegistered('logger')){
		  $this->logger = Zend_Registry::get('logger');
		}
	}
	
	protected function log($msg){
        if($this->logger && $this->logger instanceof Zend_Log){
          $this->logger->info($msg);
        }
	}

	
	public function onAuthenticate(){
		$this->deleteCookies();
	}
	
	public function onShareSession(){
	
	}
	
	public function onDestroySession(){
		$this->deleteCookies();
	}
	
	public function deleteCookies(){
		$this->log("Deleting Vanilla cookies from domain ".$this->_cookieDomain);
		setcookie('Vanilla', false, -1, '/',$this->_cookieDomain);
		setcookie('Vanilla-Volatile', false, -1, '/',$this->_cookieDomain);
		setcookie('VanillaProxy', false, -1, '/',$this->_cookieDomain);
		setcookie('Vanilla-Vv', false, -1, '/',$this->_cookieDomain);
		setcookie('Vanilla', false, -1, '/');
		setcookie('Vanilla-Volatile', false, -1, '/');
		setcookie('VanillaProxy', false, -1, '/');
		setcookie('Vanilla-Vv', false, -1, '/');
	}	
}