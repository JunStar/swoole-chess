<?php

use Phalcon\Mvc\Controller;

class IndexController extends Controller
{
    //登录界面
    public function indexAction()
    {
        $arr_user_info = $this->session->get('user_info');
        if( is_array($arr_user_info) && $arr_user_info['user_id'] && $arr_user_info['user_name'] )
        {
            $this->response->redirect('play/');
        }
    }

    //登录操作
    public function doLoginAction()
    {
        $str_user_name          =   trim( $this->request->getPost('username') );
        $int_user_name_length   =   mb_strlen( $str_user_name );
        if( $int_user_name_length < 2 || $int_user_name_length > 12 )
        {
            $this->common->ajaxError('用户名或密码错误');
        }
        $obj_user_info  =   Users::findFirstByUserName( $str_user_name );
        $str_password   =   trim( $this->request->getPost('password') );
        if( !password_verify( $str_password, @$obj_user_info->user_password ) )
        {
            $this->common->ajaxError('用户名或密码错误');
        }
        $this->setLoginInfo( $obj_user_info );
        $this->setInDaTingUsers( $obj_user_info );
        $this->common->ajaxSuccess('登录成功');
    }

    //设置登录信息
    protected function setLoginInfo( $obj_user_info )
    {
        $arr_user_session_info['user_id']   = $obj_user_info->user_id;
        $arr_user_session_info['user_name'] = $obj_user_info->user_name;
        $this->session->set('user_info',$arr_user_session_info);
        return true;
    }

    //设置在线用户信息
    protected function setInDaTingUsers( $obj_user_info )
    {
        $user_id = $obj_user_info->user_id;
        //如果用户已经和某房间ID绑定，说明用户是重连的，则不需要将其设置进在大厅的用户列表中
        $playing_id = $this->common->objRedisCache()->get( 'user_id_'.$user_id.'_playing_id.cache' );
        if( $playing_id ) return false;

        $arr_online_users = $this->common->objRedisCache()->get( 'online_users.cache' );
        if( $arr_online_users === NULL )
        {
            $arr_online_users = array();
            $arr_online_users[$user_id] = array( 'user_id' => $user_id, 'user_name' => $obj_user_info->user_name, 'status' => 0, 'status_text' => '等待' );
            $this->common->objRedisCache()->save( 'online_users.cache', $arr_online_users );
        }
        else
        {
            $arr_online_users[$user_id] = array( 'user_id' => $user_id, 'user_name' => $obj_user_info->user_name, 'status' => 0, 'status_text' => '等待' );
            $this->common->objRedisCache()->delete( 'online_users.cache' );
            $this->common->objRedisCache()->save( 'online_users.cache', $arr_online_users );
        }
        return true;
    }

    //登出操作
    public function doLogoutAction()
    {
        //重设在线用户缓存列表
        $user_info = $this->session->get('user_info');
        $arr_online_users = $this->common->objRedisCache()->get( 'online_users.cache' );
        unset($arr_online_users[$user_info['user_id']]);
        $this->common->objRedisCache()->delete( 'online_users.cache' );
        $this->common->objRedisCache()->save( 'online_users.cache', $arr_online_users );
        $this->session->destroy();
        $this->response->redirect('/');
    }

    //注册页面
    public function registerAction()
    {

    }

    //注册操作
    public function doRegAction()
    {
        $arr_data['user_name']  =   trim( $this->request->getPost('user_name') );
        $int_user_name_length   =   mb_strlen( $arr_data['user_name'] );
        if( $int_user_name_length < 2 || $int_user_name_length > 12 )
        {
            $this->common->ajaxError('用户名长度2-12个字符',array('field'=>'user_name'));
        }
        $obj_user_info = Users::findFirstByUserName( $arr_data['user_name'] );
        if( FALSE !== $obj_user_info )
        {
            $this->common->ajaxError('用户名已存在',array('field'=>'user_name'));
        }
        $str_password   =   trim( $this->request->getPost('password') );
        $str_repassword =   trim( $this->request->getPost('repassword') );
        if( $str_password !== $str_repassword )
        {
            $this->common->ajaxError('两次密码输入不相同',array('field'=>'password'));
        }
        $int_password_length    =   mb_strlen( $str_password );
        if( $int_password_length < 6 || $int_password_length > 20 )
        {
            $this->common->ajaxError('密码长度6-20个字符',array('field'=>'password'));
        }
        $arr_data['user_password']  =   password_hash( $str_password, PASSWORD_DEFAULT );
        $arr_data['create_time']    =   time();
        $model_users = new Users();
        $boolen_reg_user = $model_users->addUser( $arr_data );
        if( $boolen_reg_user )
        {
            $this->common->ajaxSuccess('注册成功');
        }
        else
        {
            // foreach ($users->getMessages() as $message) {
            //     echo $message->getMessage(), "<br/>";
            // }
            $this->common->ajaxError('注册失败');
        }
    }
}
