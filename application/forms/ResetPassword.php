<?php
/**
 * Webjawns Login Form
 *
 * @package Webjawns
 * @subpackage Auth
 */
class Application_Form_ResetPassword extends Zend_Form {
 
    public function init() {

        $password = $this->addElement('password', 'password', array(
            'filters' => array('StringTrim'),
            'validators' => array( 
                array('StringLength', false, array(5, 20)),
            ),
            'required' => true,
            'label' => 'New Password',
        ));
        
        $password_conf = $this->addElement('password', 'passwordconf', array(
            'filters' => array('StringTrim'),
            'validators' => array( 
                array('StringLength', false, array(5, 20))
            ),
            'required' => true,
            'label' => 'Re-type Password',
        ));

        $this->passwordconf->addPrefixPath('Validator', APPLICATION_PATH.'/forms/validators', 'validate');
 		$this->passwordconf->addValidator('Matches', false, array('password'));
 		
        $login = $this->addElement('submit', 'reset', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Reset Password',
        ));
        
        $redirect = $this->addElement('hidden', 'code');
 
        // Displays 'authentication failed' message if absolutely necessary
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }
 
}