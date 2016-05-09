<?php

use Phalcon\Mvc\Controller;
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;

class PlayController extends Controller
{
    /**
     * 缓存key - value详解结构：
     * playing_id_list.cache - 当前所有正在进行游戏的房间ID列表
     * playing_id_{$play_id}_user.cache - 存储$play_id房间的红黑双方信息,value内容示例：array( 'red' => array('user_id','user_name'), 'black' => array('user_id','user_name') );
     * user_id_{$user_id}_playing_id.cache - 存储用户id所在的房间id，value内容示例：1
     *
     */
    public $int_max_play_id = 200;//创建房间上限数
    public function initialize()
    {
        $this->checkLoginInfo();

    }

    //创建房间
    public function createAction()
    {
        $this->reConnection();
        $this->setPlayId();
        $this->bindPlayIdAndRedUser();

        $this->response->redirect( 'play/chess/'.$this->getPlayID() )->sendHeaders();
    }

    //断开重连
    public function reConnection()
    {
        $user_info                  =   $this->session->get('user_info');
        $str_cache_key              =   'user_id_'.$user_info['user_id'].'_playing_id.cache';
        $int_playing_id             =   $this->common->objRedisCache()->get( $str_cache_key );
        if( $int_playing_id )
        {
            $this->response->redirect( 'play/chess/'.$int_playing_id )->sendHeaders();
            exit();
        }
    }

    //检测登录信息
    public function checkLoginInfo()
    {
        $user_info = $this->session->get('user_info');
        if( !is_array($user_info) && !$user_info['userid'] || !$user_info['user_name'] )
        {
            $this->response->redirect( 'index/' )->sendHeaders();
        }
    }

    //绑定房间ID和红方用户信息
    public function bindPlayIdAndRedUser()
    {
        $user_info                          =   $this->session->get('user_info');
        $str_cache_key                      =   'playing_id_'.$this->getPlayID().'_user.cache';
        $arr_playing_id_user['red']         =   array('user_id'=>$user_info['user_id'],'user_name'=>$user_info['user_name'],'fd'=>0,'is_ready'=>false);
        $arr_playing_id_user['black']       =   array('user_id'=>0,'user_name'=>'','fd'=>0,'is_ready'=>false);
        $arr_playing_id_user['who_turn']    =   'red';
        $this->common->objRedisCache()->save( $str_cache_key, $arr_playing_id_user );

        $str_cache_key                      =   'user_id_'.$user_info['user_id'].'_playing_id.cache';
        $int_playing_id                     =   $this->getPlayID();
        $this->common->objRedisCache()->save( $str_cache_key, $int_playing_id );
    }

    //进入大厅
    public function indexAction()
    {
        $this->reConnection();
        $user_info = $this->session->get('user_info');
        $this->view->user_info  =   json_encode( $user_info );
        $this->view->arr_online_users = $this->common->objRedisCache()->get( 'online_users.cache' );
        $playing_list = $this->common->objRedisCache()->get( 'playing_id_list.cache' );
        foreach( $playing_list as $k=>$v )
        {
            $new_playing_list[$k] = $this->common->objRedisCache()->get( 'playing_id_'.$v.'_user.cache' );
            $new_playing_list[$k]['playing_id'] = $v;
        }
        $this->view->playing_list = $new_playing_list ? $new_playing_list : array();
    }

    //对弈页面
    public function chessAction()
    {
        $user_info = $this->session->get('user_info');
        $this->view->user_info  =   json_encode( $user_info );
        $this->checkPlayId();
        $this->bindPlayIdAndBlackUser();
        $playing_user = $this->common->objRedisCache()->get('playing_id_'.$this->getPlayID().'_user.cache');
        $this->view->playing_user = json_encode( $playing_user );
        if( $user_info['user_id'] == $playing_user['red']['user_id'] )
        {
            $this->view->chess_color = 'red';
            $this->view->is_ready = $playing_user['red']['is_ready'];
        }
        if( $user_info['user_id'] == $playing_user['black']['user_id'] )
        {
            $this->view->chess_color = 'black';
            $this->view->is_ready = $playing_user['black']['is_ready'];
        }
        $this->view->redUserName = $playing_user['red']['user_name'];
        if( !$playing_user['black']['is_ready'] )
            $this->view->redUserName .= $playing_user['red']['is_ready'] ? '(已准备)' : '(未准备)';
        $this->view->blackUserName = $playing_user['black']['user_name'];
        if( !$playing_user['red']['is_ready'] )
            $this->view->blackUserName .= $playing_user['black']['is_ready'] ? '(已准备)' : '(未准备)';

        $moves = $this->common->objRedisCache()->get('playing_id_'.$this->getPlayID().'_moves.cache');
        $this->view->move = $moves ? json_encode( $moves[count($moves)-1] ) : "{}";
    }

    //绑定房间ID和黑方用户信息
    public function bindPlayIdAndBlackUser()
    {
        $user_info                      =   $this->session->get('user_info');
        $str_cache_key                  =   'playing_id_'.$this->getPlayID().'_user.cache';
        $arr_playing_id_user            =   $this->common->objRedisCache()->get( $str_cache_key );
        if( $arr_playing_id_user['red']['user_id'] !== $user_info['user_id'] && $arr_playing_id_user['black']['user_id'] !== $user_info['user_id'] )
        {
            $arr_playing_id_user['black']['user_id']    =   $user_info['user_id'];
            $arr_playing_id_user['black']['user_name']  =   $user_info['user_name'];
            $this->common->objRedisCache()->delete( $str_cache_key );
            $this->common->objRedisCache()->save( $str_cache_key, $arr_playing_id_user );
        }

        $str_cache_key              =   'user_id_'.$user_info['user_id'].'_playing_id.cache';
        $int_playing_id             =   $this->getPlayID();
        $this->common->objRedisCache()->save( $str_cache_key, $int_playing_id );
    }

    //检测playId的合法性
    public function checkPlayId()
    {
        $int_get_param_id      = $this->dispatcher->getParam( 'id' );
        if( !$int_get_param_id )
        {
            $this->response->redirect( 'play/create' )->sendHeaders();
            $this->view->disable();
            exit();
        }

        $user_info                      =   $this->session->get('user_info');
        $playing_id_by_user_id          =   $this->common->objRedisCache()->get('user_id_'.$user_info['user_id'].'_playing_id.cache');
        if( $playing_id_by_user_id && $playing_id_by_user_id != $int_get_param_id )
        {
            $this->response->redirect( 'play/chess/'.$playing_id_by_user_id )->sendHeaders();
            $this->view->disable();
            exit();
        }

        $str_cache_key                  =   'playing_id_'.$this->getPlayID().'_user.cache';
        $arr_playing_id_user            =   $this->common->objRedisCache()->get( $str_cache_key );
        if( isset( $arr_playing_id_user['red'] ) && !isset( $arr_playing_id_user['black'] ) )
        {
            return true;
        }
        //获取当前正在进行游戏的playid集合
        $str_cache_key        = 'playing_id_list.cache';
        $arr_playing_id_list   = $this->common->objRedisCache()->get( $str_cache_key );
        if( !is_array( $arr_playing_id_list ) || !in_array( $int_get_param_id, $arr_playing_id_list ) )
        {
            $this->response->redirect( 'play/create' )->sendHeaders();
            $this->view->disable();
            exit();
        }
    }

    //返回刚创建的房间ID
    public function getPlayID()
    {
        return $this->dispatcher->getParam( 'id' ) ? $this->dispatcher->getParam( 'id' ) : $this->int_play_id;
    }

    //设置已经创建了的房间ID的缓存列表，并返回刚创建的房间ID
    public function setPlayId()
    {
        //生成1-200的自然数数组，作为所有的playid的集合
        $arr_play_id_list       =   range( 1, $this->int_max_play_id );
        //获取当前正在进行游戏的playid集合
        $str_cache_key          =   'playing_id_list.cache';
        $arr_playing_id_list    =   $this->common->objRedisCache()->get( $str_cache_key );
        //如果当前正在游戏的playid集合为空，则默认设置当前正在游戏的playid为1
        if ($arr_playing_id_list === null)
        {
            $this->int_play_id      =   1;
            $arr_playing_id_list    =   array(1);
            $this->common->objRedisCache()->save( $str_cache_key, $arr_playing_id_list );
        }
        else
        {
        //否则取其他空闲的playid的集合，并取其中第一个元素为即将可以创建的playid
            $arr_free_play_id       =   array_diff( $arr_play_id_list, $arr_playing_id_list );
            $this->int_play_id      =   intval( array_shift( $arr_free_play_id ) );
            $arr_playing_id_list[]  =   $this->int_play_id;
            $this->common->objRedisCache()->save( $str_cache_key, $arr_playing_id_list );
        }
        return $this->int_play_id;
    }
}
