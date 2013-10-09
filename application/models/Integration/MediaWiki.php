<?php

class Application_Model_Integration_MediaWiki {
	
	public function __construct($opts){
		$app_options = Zend_Registry::get('options');
		$this->_cookieDomain = isset($opts['cookie_domain']) ? $opts['cookie_domain'] : $app_options['app']['cookiedomain'];
		$this->_cookieName = $opts['session_name'];
		$this->_wikiURL = $opts['wiki_url'];

		if(Zend_Registry::isRegistered('logger')){
		  $this->logger = Zend_Registry::get('logger');
		}

		$this->log('MediaWiki opts '.print_r($opts, true) );
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
		/*
	There's a pretty serious bug that needs fixing before this can be used....

	If the user has logged in, but not yet visited the wiki then their account has not yet been retrieved and
	cached over there.

	When we curl the logout page on the user's behalf, mediawiki tries to load the user for the first time and
	somehow we end up with the page load halting and loading forever....

		*/


		/*$this->log('Logging out of MediaWiki: '.$this->_wikiURL.'/Special:UserLogout');
		//fake a request to UserLogout action on the wiki
		$cookies = array();
		foreach($_COOKIE as $k=>$v){
			$cookies[] = $k.'='.$v;
		}
		$ch = curl_init($this->_wikiURL.'/Special:UserLogout');
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_COOKIESESSION, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIE, implode("; ",$cookies) ); 
		$res = curl_exec($ch);

		$this->log('CURL Result:'.$res);
		//echo $res; exit;
		curl_close($ch);*/
	}
	
	public function deleteCookies(){
		//set the cookie on the current domain (this seems to be the ONLY way to get setcookie to not add a . to the start of the domain!)
		setcookie($this->_cookieName, false, -1, '/'); 
		setcookie($this->_cookieName, false, -1, '/',$this->_cookieDomain);
		if(strpos($this->_cookieDomain, '.')===0){
			$dotless = substr($this->_cookieDomain, 1);
			setcookie($this->_cookieName, false, -1, '/', $dotless);
		}
	}
}