<?php
/**
 * Webjawns Login Form
 *
 * @package Webjawns
 * @subpackage Auth
 */
class Application_Form_Register extends Zend_Form {
 
    public function init() {
    	$email = $this->addElement('text', 'email', array(
    	    'filters' => array('StringTrim', 'StringToLower'),
    	    'validators' => array('EmailAddress'),
    	    'required' => true,
    	    'label' => 'Email Address',
    	));
    
        $username = $this->addElement('text', 'username', array(
            'filters' => array('StringTrim', 'StringToLower'),
            'validators' => array(array('StringLength', false, array(3, 250))),
            'required' => true,
            'label' => 'Username',
        ));
        
        $password = $this->addElement('password', 'password', array(
            'filters' => array('StringTrim'),
            'validators' => array( array('StringLength', false, array(5, 250))),
            'required' => true,
            'label' => 'Password',
        ));
 		
 		$login = $this->addElement('text', 'display_name', array(
            'filters' => array('StringTrim'),
 		    'required' => false,
 		    'ignore' => false,
 		    'label' => 'Real name',
 		));
 		
        $login = $this->addElement('submit', 'login', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Register',
        ));
        
        $redirect = $this->addElement('hidden', 'redirect');
 
        // Displays 'authentication failed' message if absolutely necessary
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }
 
}