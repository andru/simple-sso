<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	
	public function _initRoutes(){
		$this->bootstrap('FrontController');
		$router = $this->frontController->getRouter();
		
		//$router->removeDefaultRoutes();
		
		//default
		/*$router->addRoute('default',  			
			new Zend_Controller_Router_Route('/:controller/:action', array('controller' => 'index', 'action' => 'index'))
		);*/
		$router->addRoute('home',  			
			new Zend_Controller_Router_Route('/', array('controller' => 'index', 'action' => 'index'))
		);
		$router->addRoute('login',  			
			new Zend_Controller_Router_Route('/login', array('controller' => 'user', 'action' => 'login'))
		);
		$router->addRoute('authenticate',  			
			new Zend_Controller_Router_Route('/authenticate', array('controller' => 'user', 'action' => 'authenticate'))
		);
		$router->addRoute('authenticate-external',  			
			new Zend_Controller_Router_Route('/authenticate/external', array('controller' => 'user', 'action' => 'authenticate-external'))
		);
		$router->addRoute('logout',  			
			new Zend_Controller_Router_Route('/logout', array( 'controller' => 'user', 'action' => 'logout'))
		);
        $router->addRoute('goodbye',           
            new Zend_Controller_Router_Route('/goodbye', array('controller' => 'index', 'action' => 'goodbye'))
        );

		$router->addRoute('create-from-external',  			
			new Zend_Controller_Router_Route('/register/external', array( 'controller' => 'user', 'action' => 'create-from-external'))
		);
		$router->addRoute('associate-provider',  			
			new Zend_Controller_Router_Route('/associate-provider', array( 'controller' => 'user', 'action' => 'associate-provider'))
		);
		$router->addRoute('register',  			
			new Zend_Controller_Router_Route('/register', array( 'controller' => 'account', 'action' => 'register'))
		);
		$router->addRoute('registration',  			
			new Zend_Controller_Router_Route('/registration', array( 'controller' => 'account', 'action' => 'registration'))
		);
		$router->addRoute('registered',  			
			new Zend_Controller_Router_Route('/registered', array( 'controller' => 'account', 'action' => 'registered'))
		);
		$router->addRoute('activate',  			
			new Zend_Controller_Router_Route('/activate/:id/:code', array( 'controller' => 'account', 'action' => 'activate'))
		);
        $router->addRoute('forgot-password',        
          new Zend_Controller_Router_Route('/forgot-password', array( 'controller' => 'account', 'action' => 'forgot-password'))
        );
        $router->addRoute('forgot-password-process',        
          new Zend_Controller_Router_Route('/forgot-password/process', array( 'controller' => 'account', 'action' => 'forgot-password-process'))
        );

        $router->addRoute('reset-password',        
          new Zend_Controller_Router_Route('/reset-password/:code', array( 'controller' => 'account', 'action' => 'reset-password'))
        );
        $router->addRoute('reset-password-process',        
          new Zend_Controller_Router_Route('/reset-password/process', array( 'controller' => 'account', 'action' => 'reset-password-process'))
        );
		
		$router->addRoute('integration-application',  			
			new Zend_Controller_Router_Route('/integration/:application/:action', array( 'controller' => 'integration', 'action' => 'index'))
		);
		
		$router->addRoute('integration',  			
			new Zend_Controller_Router_Route('/integration/:action', array( 'controller' => 'integration', 'action' => 'index'))
		);
		
	}
	
    protected function _initDoctype() {
        $view = $this->bootstrap('view')->getResource('view');
        $view->doctype('HTML5');
    }

    protected function _initHasher(){
        Zend_Registry::set('hasher', new PasswordHash_PasswordHash(8, true));
    }
    
    protected function _initView() {
        $view = new Zend_View();
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');

        $options = $this->getOptions();

        $view->headTitle( $options['app']['name'] )->setSeparator(' - ');
        $view->assign('app_name', $options['app']['name']);
        $view->assign('support_email', $options['app']['support_email']);
        $viewRenderer->setView($view);
        return $view;
    }
    
    protected function _initMasthead(){
    	require(realpath(APPLICATION_PATH.'/../../common').'/Layout.php');
    }

    protected function _initSiteName(){
        $options = $this->getOptions();
        Zend_Registry::set('sitename', $options['app']['name'] );
    }

    protected function _initHelperPath() {
        $view = $this->bootstrap('view')->getResource('view');
        $view->setHelperPath(APPLICATION_PATH . '/views/helpers', 'My_View_Helper');

        Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/controllers/helpers', 'Helper');
    }

    protected function _initPlugins(){
        $loader = new Zend_Loader_PluginLoader();
        $loader->addPrefixPath('Validator', APPLICATION_PATH.'/forms/validators/');
    }

    
    protected function _initApplicationAutoloading(){
    	$autoLoader = Zend_Loader_Autoloader::getInstance();
    	
        $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
            'basePath' => APPLICATION_PATH,
            'namespace' => 'Application_',
        ));
                
    	$resourceLoader->addResourceType('form', 'forms', 'Form');
       	$resourceLoader->addResourceType('model', 'models', 'Model');
    }

    protected function _initAttributeExOpenIDPath() {
        $autoLoader = Zend_Loader_Autoloader::getInstance();

        $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
                    'basePath' => APPLICATION_PATH,
                    'namespace' => 'My_',
                ));

        $resourceLoader->addResourceType('openidextension', 'openid/extension/', 'OpenId_Extension');
        $resourceLoader->addResourceType('authAdapter', 'auth/adapter', 'Auth_Adapter');

        $autoLoader->pushAutoloader($resourceLoader);
    }

     protected function _initAppKeysToRegistry() {
         $appkeys = new Zend_Config_Ini(APPLICATION_PATH . '/configs/appkeys.ini');
         Zend_Registry::set('keys', $appkeys);
     }

    protected function _initIntegrationConfig() {
         $conf = new Zend_Config_Ini(APPLICATION_PATH . '/configs/integrations.ini');
         Zend_Registry::set('integrations', $conf);
     }
     
	/*protected function _initConfig(){
		$config = new Zend_Config($this->getOptions());
		Zend_Registry::set('config', $config);
		return $config;
	}*/
     public function _initLog(){
      $options = $this->getOptions();
      if($options['logging']['enabled']==true){
        $writer = new Zend_Log_Writer_Stream($options['logging']['path'].'/info.log');
        $logger = new Zend_Log($writer);
        Zend_Registry::set('logger', $logger);
      }
     }
     
     public function _initSession(){
     	$options = $this->getOptions();
     	ini_set('session.gc_maxlifetime', $options['resources']['session']['remember_me_seconds']); //set server session timeout to same as cookie
     	$opts = array_merge(array(
     		'name'=>'SSO-Session',
     		'cookie_domain'=>$options['app']['cookiedomain'],
     		'cookie_path'=>'/',
     	),$options['resources']['session']);
     	Zend_Session::start($opts);	
     }

     public function _initMail(){
       $options = $this->getOptions();

       if(isset($options['email'])){
        if(isset($options['email']['transport']) && $options['email']['transport']==='smtp'){
            $config = array('auth' => 'login',
                      'username' => $options['email']['username'],
                      'password' => $options['email']['password']);

            $tr = new Zend_Mail_Transport_Smtp($options['email']['server'], $config);
        }else{
            $tr = new Zend_Mail_Transport_Sendmail();
        }
        Zend_Mail::setDefaultTransport($tr);
      }
     }
     
     public function _initDatabase(){
     	$db = $this->getPluginResource('db')->getDbAdapter();
     	Zend_Registry::set('dbAdapter', $db);
     }

     public function _initOptionsStore(){
        $options = $this->getOptions();
        Zend_Registry::set('options', $options);
     }
     

}

