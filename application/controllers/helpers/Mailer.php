<?php

class Helper_Mailer extends Zend_Controller_Action_Helper_Abstract
{
    public function getName(){
        return 'Mailer';
    }

	public function init(){
		$options = Zend_Registry::get('options');
		$this->options = $options['email'];
	}

	public function direct($email, $subject, $html, $text ){   
        $from = $this->options['from'];
        $from_name = $this->options['from_name'];

        $mail = new Zend_Mail();
        $mail->setBodyText($text);
        $mail->setBodyHtml($html);
        $mail->setFrom($from, $from_name);
        $mail->addTo($email);
        $mail->setSubject($subject);
        $mail->send();
    }
}
?>