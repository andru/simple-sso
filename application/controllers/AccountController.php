<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: Some license information
 *
 * @category   Zend
 * @package    Zend_
 * @subpackage Wand
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license   BSD License
 * @version    $Id:$
 * @link       http://framework.zend.com/package/PackageName
 * @since      File available since Release 1.5.0
 */
class AccountController extends Zend_Controller_Action
{
    /**
     * Default minimum number of characters for a password
     */
    const MIN_PASS_CHAR = 6;
    
    /**
     * Default maximum number of characters for a password
     */
    const MAX_PASS_CHAR = 75;
    
    /**
     * Default maximum number of characters for username and email
     */
    const MAX_EMAIL_USERNAME_CHAR = 255;

    /**
     * Base URL
     */
    private $_baseURL = '';

	public function init(){
		$bootstrap = $this->getInvokeArg('bootstrap'); 
		 $options = $bootstrap->getOptions();
		 $this->_baseURL = $options['app']['url'];

         $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');

         //$this->initView();
	}

    public function postDispatch(){
       // $this->view->messages = $this->_flashMessenger->getMessages();

    }

    /**
     * Controller's entry point
     *
     * @return void
     */
    public function indexAction()
    {
        /*$this->view->messages = $this->_helper->flashMessenger->getMessages();
        
        $session = new Zend_Session_Namespace();
        $this->view->flashMessengerClass = $session->flashMessengerClass;*/
        
        /*$params = $this->getRequest()->getParams();
        $sess = Zend_Session_Namespace('SSO');
        if(isset($sess->login_redirect)){
        
        }
        $this->view->form = $this->getForm();
        if(isset($params['redirect'])){
        	$this->view->form->redirect->setValue($params['redirect']);
        }*/
        
        //$this->_helper->redirector()->gotoRoute(array(),'home');
		$this->_forward('index','index');
	}
	
    
    /**
     * Handles the register action which displays the registration form
     *
     * @return void
     */
    public function registerAction()
    {
        //if a user session is currently still active, ask them to logout?
        
        $session = new Zend_Session_Namespace('registration');
        //if( isset( $session->usernameRegistration ) )
         //   $this->view->usernameRegistration = $session->username;
        //if( isset( $session->emailRegistration ) )
           // $this->view->emailRegistration = $session->email;
           
        $this->view->form = $this->getRegisterForm();
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
        $this->view->flashMessengerClass = $session->flashMessengerClass;
     //   Zend_Session::destroy();
    }
    
    /**
     * Handles the registration of the user, validating the user input,
     * inserting into the database, and sending the email activation, 
     *
     * @return void
     */
    public function registrationAction()
    {
        if($this->getRequest()->isPost() ) {
            
            $form = $this->getRegisterForm();
           // if($form->getValue('password') !== $form->getValue('password-conf'))
            //	$form->addError("Passwords do not match!");
            if (!$form->isValid($this->getRequest()->getPost())) {
                // Did not pass validation...
                $this->view->form = $form;
                return $this->render('register');
            }
            
            

            $users = new Application_Model_Users();           
            
            try{
            	$user = $users->createUser($form->getValues());           	
            	
            }catch(Exception $e){
            	if(!$error = $e->getMessage())
            		$error = 'Sorry, an error occurred while creating your account. Please try again.';
            	$this->view->messages = array($error);
            	//$form->markAsError();
            	//$form->isValid($this->getRequest()->getPost());
            	$this->view->form = $form;
            	return $this->render('register');
            }
          	
          	//print_r((array) $user->toArray());exit;
            
            if($user){
            	$this->sendActivationEmail($user, $form->getValue('password'));
            	
            	//return $this->_redirect()->gotoRoute(array(),'registered');
            	return $this->_forward('registered');
            }
            
        }
        $this->_redirect()->gotoRoute(array(),'register');
    }
    
    /**
     * Success or error page in registration process
     *
     * @return void
     */
    public function registeredAction()
    {
        
    }
    
    /**
     * Handles the activation of a new user account
     * 
     * @return void
     */
    public function activateAction()
    {
    	
        $id = $this->getRequest()->getParam('id');
        $url_code = $this->getRequest()->getParam('code');
        
        $users = new Application_Model_Users();
        if($user = $users->getUserBy('id',$id)){
        	if(substr(md5($user->register_time.$user->register_ip),10,5) === $url_code)
        		$result = $users->activateUser($id,$url_code);
        }else{
        	$result = false;
        }
        
        
        
        if(!$result) {
            $this->render('activation-failure');
        } else {
        
        	//log user in
        	$auth = Zend_Auth::getInstance();
        	$auth->getStorage()->write($user->email);
        	
        	$authSess = new Zend_Session_Namespace('Auth');
        	$authSess->identity = $user->email;
        	$authSess->user = $user->toArray();
        	
        	$integrations = new Application_Model_Integrations();
        	$integrations->onAuthenticate();
        	
            $this->render('activation-success');
        }
    }


    public function unconfirmedAction(){
        $this->render('awaiting-activation');
    }

    public function resendActivationAction(){
        $form = new Application_Form_ResendActivation(array(
            'action' => '/sso/account/resend-activation',
            'method' => 'post'
        ));
        $this->view->form = $form;
        if($this->getRequest()->isPost() ) {

            if (!$form->isValid($this->getRequest()->getPost())) {
                // Did not pass validation...
                $this->view->form = $form;
                return $this->render('resend-activation');
            }
            
            $users = new Application_Model_Users();           
            
            try{
                $user = $users->getUserBy('email',$form->getValue('email'));               
            }catch(Exception $e){
                if(!$error = $e->getMessage())
                    $error = 'Sorry, an error occurred while finding your account. Please try again.';
                $this->view->messages = array($error);
                return $this->render('resend-activation');
            }
            
            //print_r((array) $user->toArray());exit;
            
            if($user){
                if($user->email_confirmed){
                    $form->addError('Your account has already been activated.');
                }else{
                   $this->sendActivationEmail($user);
                    return $this->render('resent-activation'); 
                }
            }else{
                $form->addError('There is no account with this email address.');
            }
            return $this->render('resend-activation');
        }else{
            $this->render('resend-activation');  
        }
    }
    
    /**
     * Displays the form for the user to reset their password
     *
     * @return void
     */
    public function forgotPasswordAction() 
    {
        //simply displays messages returned from activateAction()
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
        $session = new Zend_Session_Namespace();
        $this->view->flashMessengerClass = $session->flashMessengerClass;
    }
    
    /**
     * Emails user URL to change password
     *
     * @return void
     */
    public function forgotPasswordProcessAction() 
    {   
        if( $this->getRequest()->isPost() ) {
            $email = $this->getRequest()->getPost('email');           
            
            $users = new Application_Model_Users();
            $user = $users->getUserBy('email', $email);

            if( $user != false ) {
                $code = $user->generatePasswordResetCode(7); //allow 7 days to reset password
                if($code){
                    $html = "<p>To reset your password, click <a href=\"".$this->_baseURL."/reset-password/$code\">here</a>.</p>";
                    $text = "Go to the following link to reset your password ".$this->_baseURL."/reset-password/$code\n";
                    $this->_helper->mailer($user->email, Zend_Registry::get('sitename').' Password Reset', $html, $text);
                    $session = new Zend_Session_Namespace();
                    $session->flashMessengerClass = 'flashMessagesGreen';
                    $this->_helper->flashMessenger->addMessage('An email has been sent to you with instructions on how to reset your password.');
                }else{
                    $this->_helper->flashMessenger->addMessage('Something went wrong! Failed to generate a password reset code. Please try again.');
                }
            } else { //return with error
                $session = new Zend_Session_Namespace();
                $session->flashMessengerClass = 'flashMessagesRed';
                $this->_helper->flashMessenger->addMessage('We have no record of that email address.');
            }
        }else{
            $this->_helper->flashMessenger->addMessage('Invalid request.');
        }
        $this->_redirect('/forgot-password');
    }
    
    /**
     * Asks the user 
     *
     * @return void
     */
    public function resetPasswordAction()
    {
        $code = $this->getRequest()->getParam('code');
        if( strlen( $code ) == 0 ) {//if reset code empty, return back
            $this->_forward('forgot-password');
        } else {//success changed password
            $this->view->messages = $this->_helper->flashMessenger->getMessages();
            $session = new Zend_Session_Namespace();
            $this->view->flashMessengerClass = $session->flashMessengerClass;
            
            $form = $this->getResetPasswordForm();
            $form->code->setValue($code);
            //print_r($form); exot
            $this->view->form = $form;


        }
    }
    
    /**
     * Processes the new password and stores in DB
     *
     * @return void
     */
    public function resetPasswordProcessAction()
    {
        if( $this->getRequest()->isPost() ) {
            $password = $this->getRequest()->getPost('password');
            $passwordConfirm = $this->getRequest()->getPost('passwordConfirm');
            
            $form = $this->getResetPasswordForm();

            
            if(!$form->isValid($this->getRequest()->getPost())){
                $this->view->form = $form; //$form->setValues($this->getRequest()->getPost());
                $this->render('reset-password');
            } else {
                $code = $form->code->getValue();

                //register use and redirect to success page
                $options= $this->getInvokeArg('bootstrap')->getOptions();
                $users = new Application_Model_Users();
                $user = $users->getUserByPasswordResetCode($code);

                if( $user ) {
                    $user->resetPassword($password);
                    $session = new Zend_Session_Namespace();
                    $session->flashMessengerClass = 'flashMessagesGreen';
                    $this->_helper->flashMessenger->addMessage('Your password has been successfully reset.');
                    $this->_redirect('/index/');
                } else {
                    $session = new Zend_Session_Namespace();
                    $session->flashMessengerClass = 'flashMessagesRed';
                    $this->_helper->flashMessenger->addMessage('The link you followed has expired. Please try resetting your password again.');
                    $this->_helper->redirector->gotoRoute(array(),'forgot-password');
                }
            }
        } else {
            $this->_helper->redirector->gotoRoute(array(),'forgot-password');
        }
    }


    protected function sendActivationEmail(Zend_Db_Table_Row $user, $password = null){
        //$username = $form->getProperty('username');
        if(!isset($user->email))
            throw 'Invalid user object for sendActivationEmail';
        $email = $user->email;
        $email_to = $user->display_name ?: $user->username;
        
        $hash = substr(md5($user->register_time.$user->register_ip),10,5);
        
        $sitename = Zend_Registry::get('sitename');

        //echo $hash;exit;
        $url = $this->_baseURL.'/activate/'.$user->user_id.'/'.$hash;
        $html = '<h1>Your account at '.$sitename.'</h1>' 
              . '<p><a href="'.$url.'">Click here</a> or enter this url into your browser to activate your account: '
              . $url.'</p>';
        $text = "Activate your account on '.$sitename.' here: "
              . $url." \n";

        //add password to activation email on initial registration - we can only do this when the form has just been
        //submitted. After that it's cryped.
        if($password!==null){
            $html.=- '<p>Your password is: ' . $password . '</p>';
            $text .= "Your password is: $password\n";
        }

        $this->sendMail($email_to, $email, $html, $text, 'Account Activation');
    }
    
    /**
     * Sends an email
     * 
     * @param string $html
     * @param string $text
     * @param string $title
     * @return void
     */
    protected function sendMail($name, $email, $html, $text, $title)
    {    	
        $options = Zend_Registry::get('options');
        $from = $options['email']['from'];
        $from_name = $options['email']['from_name'];
        $mail = new Zend_Mail();
        $mail->setBodyText($text);
        $mail->setBodyHtml($html);
        $mail->setFrom($from, $from_name);
        $mail->addTo($email);
        $mail->setSubject($title);
        $mail->send();
    }
    
    protected function getRegisterForm() {
        return new Application_Form_Register(array(
            'action' => '/sso/registration',
            'method' => 'post'
        ));
    }

    protected function getResetPasswordForm(){
         return new Application_Form_ResetPassword(array(
            'action' => '/sso/reset-password/process',
            'method' => 'post'
        ));
    }

}