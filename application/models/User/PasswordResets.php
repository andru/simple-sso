<?php

class Application_Model_User_PasswordResets extends Zend_Db_Table_Abstract {
 
    protected $_name = 'user_passwordresets';
    
    /*
    Adds a new password reset row and returns the generated code
    */
    public function generateCode(Zend_Db_Table_Row $user, $expiry_days){

        $date = date('Y-m-d H:i:s');
        $expires = date ( 'Y-m-d H:i:s', strtotime ( '+'.$expiry_days.' day' , time() ) );
        $code = substr(md5(uniqid()), 10, 25);
        $id = $this->insert(array('user_id'=>$user->user_id, 'created'=>$date, 'expires'=>$expires, 'code'=>$code));
        
    	if($id){
    		return $code;
    	}
    	return false;
    }

    public function getByCode($code){
        $row = $this->fetchRow(
            $this->select()
                ->where('code = ?', $code)
                ->where('expires > ?', date('Y-m-d H:i:s', time()))
                ->where('status = ?', '0')
                ->order('expires DESC')
        );
        return $row;
    }

    public function getLastValidCode(Zend_Db_Table_Row $user){
        $row = $this->fetchRow(
            $this->select()
                ->where('expires > ?', date('Y-m-d H:i:s', time()))
                ->where('user_id = ?', $user->user_id)
                ->where('status = ?', '0')
                ->order('expires DESC')
        );
        if($row)
            return $row->code;
        return false;
    }

    public function markCodeUsed($user, $code){
        $row = $this->fetchRow(
            $this->select()
                ->where('code = ?', $code)
                ->where('user_id = ?', $user->user_id)
                ->where('status = ?', '0')
                ->order('expires DESC')
        );
        $row->status = '1';
        $row->save();
        return true;
    }

    public function redeemAllForUser($user){
        $where = $this->getAdapter()->quoteInto('user_id = ?', $user->user_id);
        $this->update(array('status'=>1, 'expires'=>date('Y-m-d H:m:s')), $where);
    }

 	
  
}