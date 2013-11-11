<?php


/* NOT IN USE */
class Application_Model_User{
    
    protected $row;
    protected $_resetPassword;

    public function __construct($row){

    	$this->row = $row;
    	$this->_resetPassword = new Application_Model_User_PasswordResets();
    }

    public function __get($p){
        return $this->row->{$p};
    }

    public function __set($p, $v){
        //if(isset($this->{$p}))
          //  $this->
        if(isset($this->row->{$p})){
            $this->row->{$p} = $v;
        }else{
            throw new Exception('Property '.$p.' is not a valid property of the User model.');
        }
    }
    
    public function save(){
        return $this->row->save();
    }

    public function generatePasswordResetCode($days){
        return $this->_resetPassword->generateCode($this->row, $days);
    }

    public function getPasswordResetCode(){
        return $this->_resetPassword->getLastValidCode($this->row);
    }

    public function setPassword($newPassword){
        $hasher = Zend_Registry::get('hasher');
        $this->row->password = $hasher->HashPassword($newPassword);
        $this->row->save();
    }

    public function toArray(){
        return $this->row->toArray();
    }

    /*
    Same as setPassword, except it also expires any resetpassword rows
    */
    public function resetPassword($newPassword){
        $this->setPassword($newPassword);
        $this->_resetPassword->redeemAllForUser($this->row);
    }

    public function authenticate(){

    }
    
    public function associateProvider(){
        
    }
    
    //force authentication
    public function setAuthenticated(){
    	
    }
 	
 	public function authenticateLocal($identity, $password){
 		
 	}
 	
 	public function authenticateExternal($provider){
 		
 	}
 	
 	public function createLocalUser(){
 		
 	}
 	
}