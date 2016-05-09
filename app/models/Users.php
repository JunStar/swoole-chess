<?php
use Phalcon\Mvc\Model;
class Users extends Model
{
    public $user_id;

    public $user_name;

    public $user_password;

    public function addUser( $data )
    {
        return $this->save($data, array('user_name', 'user_password', 'create_time'));
    }
}
