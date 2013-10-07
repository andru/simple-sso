<?php
/**
 * Webjawns Login Form
 *
 * @package Webjawns
 * @subpackage Auth
 */
class Application_Form_ResendActivation extends Zend_Form {
 
    public function init() {
        $username = $this->addElement('text', 'email', array(
            'filters' => array('StringTrim', 'StringToLower'),
            'validators' => array('EmailAddress', array('StringLength', false, array(3, 255))),
            'required' => true,
            'label' => 'Email',
        ));

        $submit = $this->addElement('submit', 'resend', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Resend Activation Email',
        ));
 
        // Displays 'authentication failed' message if absolutely necessary
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        $this->addDecorator( 'Errors', array( 'placement' => Zend_Form_Decorator_Abstract::PREPEND ) );
    }
 
}