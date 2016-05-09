<?php
class ChesswebsocketTask extends \Phalcon\Cli\Task
{
    var $arr_cmd = array(
                        -1,//用户进入大厅
                        -2,//有用户创建了对局室，更新在大厅用户的对局列表
                        -3,//退出
                        1,//创建对局室
                        2,//准备
                        3,//走一步棋
                        4,//胜负已分，存储数据
                        5,//返回大厅
                    );
    public function mainAction()
    {
        $server = new swoole_websocket_server("0.0.0.0", 9501);

        $server->set(array(
            'daemonize' => 0
        ));
        
        $server->on('open', function (swoole_websocket_server $server, $request) {
            // echo 'this server is open';
        });

        $server->on('message', function (swoole_websocket_server $server, $frame) {
            $data = json_decode( $frame->data, true );
            if( $data['cmd'] === -1 )
                $this->enterDaTing( $server, $data, $frame->fd );

            if( $data['cmd'] === -3 )
                $this->logout( $server, $data, $frame->fd );

            //绑定fd和user_id
            if( $data['cmd'] === 1 )
                $this->enterRoom( $server, $data, $frame->fd );

            if( $data['cmd'] === 2 )
                $this->ready( $server, $data, $frame->fd );

            if( $data['cmd'] === 3 )
                $this->move( $server, $data, $frame->fd );

            if( $data['cmd'] === 4 )
                $this->chessEnd( $server, $data, $frame->fd );

            if( $data['cmd'] === 5 )
                $this->reEnterDaTing( $server, $data, $frame->fd );
        });

        $server->on('close', function ($ser, $fd) {
            // echo "client {$fd} closed\n";
            //删除此fd关联的user_id
            $this->common->objRedisCache()->delete( 'fd_id_'.$fd.'_user_id.cache' );
        });

        $server->start();
    }

    //登出
    public function logout( $server, $data, $fd )
    {
        $user_id          = $this->common->objRedisCache()->get( 'fd_id_'.$fd.'_user_id.cache' );
        $arr_online_users = $this->common->objRedisCache()->get( 'online_users.cache' );
        if( is_array( $arr_online_users ) && count( $arr_online_users ) > 0 )
        {
            foreach( $arr_online_users as $k=>$v )
            {
                if( $k == $user_id || $v['status'] ) continue;
                $return['cmd']          =   -3;
                $return['user_id']      =   $user_id;
                $user_fd = $this->common->objRedisCache()->get( 'user_id_'.$k.'_fd.cache' );
                $server->push( $user_fd, json_encode($return) );
            }
        }
    }

    //用户进入大厅
    public function enterDaTing( $server, $data, $fd )
    {
        //绑定user_id和fd
        $user_id = intval( $data['user_id'] );
        $this->common->objRedisCache()->save( 'user_id_'.$user_id.'_fd.cache', $fd );
        $this->common->objRedisCache()->save( 'fd_id_'.$fd.'_user_id.cache', $user_id );

        //向所有在大厅的用户发送消息，告知有新用户进入大厅
        $this->updateDaTingUserList( $server, $data, $fd );

        $this->updateDaTingRoomList( $server, $data, $fd );
    }

    //更新所有在大厅的用户的用户列表
    protected function updateDaTingUserList( $server, $data, $fd )
    {
        $user_id = intval( $data['user_id'] );
        $arr_online_users = $this->common->objRedisCache()->get( 'online_users.cache' );
        if( is_array( $arr_online_users ) && count( $arr_online_users ) > 0 )
        {
            foreach( $arr_online_users as $k=>$v )
            {
                if( $k == $user_id || $v['status'] ) continue;
                $return['cmd']          =   -1;
                $return['user_id']      =   $user_id;
                $return['user_name']    =   $arr_online_users[$user_id]['user_name'];
                $return['status']       =   $arr_online_users[$user_id]['status'];
                $return['status_text']  =   $arr_online_users[$user_id]['status_text'];
                $return['user_count']   =   count( $arr_online_users );
                $user_fd = $this->common->objRedisCache()->get( 'user_id_'.$k.'_fd.cache' );
                $server->push( $user_fd, json_encode($return) );
            }
        }
        return true;
    }

    //更新所有在大厅的用户的房间列表
    protected function updateDaTingRoomList( $server, $data, $fd )
    {
        $user_id        = intval( $data['user_id'] );
        $arr_online_users = $this->common->objRedisCache()->get( 'online_users.cache' );
        $playing_id_list = $this->common->objRedisCache()->get( 'playing_id_list.cache' );
        $new_playing_id_list = array();
        foreach( $playing_id_list as $pk=>$pv )
        {
            $new_playing_id_list[$pk]['playing_id']         =   $pv;
            $playing_user = $this->common->objRedisCache()->get( 'playing_id_'.$pv.'_user.cache' );
            $new_playing_id_list[$pk]['playing_user']       =   $playing_user;
            $new_playing_id_list[$pk]['playing_status']     =   $playing_user['black']['user_id'] ? 1 : 0;
        }
        if( is_array( $arr_online_users ) && count( $arr_online_users ) > 0 )
        {
            foreach( $arr_online_users as $k=>$v )
            {
                if( $k == $user_id || $v['status'] ) continue;
                $return['cmd']              =   -2;
                $return['playing_id_list']  =   $new_playing_id_list;
                $return['playing_count']    =   count( $new_playing_id_list );
                $user_fd = $this->common->objRedisCache()->get( 'user_id_'.$k.'_fd.cache' );
                $server->push( $user_fd, json_encode($return) );
            }
        }
        return true;
    }

    //进入房间
    public function enterRoom( $server, $data, $fd )
    {
        //绑定信息
        $this->bindUserIdFdPlayingId( $server, $data, $fd );
        //更新在线用户信息
        $user_id            =   intval( $data['user_id'] );
        $playing_id         =   $this->common->objRedisCache()->get( 'user_id_'.$user_id.'_playing_id.cache' );
        $arr_online_users   =   $this->common->objRedisCache()->get( 'online_users.cache' );
        if( $arr_online_users === NULL )
        {
            $arr_online_users = array();
            $arr_online_users[$user_id]['status']       =   1;
            $arr_online_users[$user_id]['status_text']  =   '正在'.$playing_id.'号房间游戏';
            $arr_online_users[$user_id]['user_id']      =   $user_id;
            $user_info                                  =   Users::findFirstByUserId( $user_id );
            $arr_online_users[$user_id]['user_name']    =   $user_info->user_name;
        }
        else
        {
            $arr_online_users[$user_id]['status']       =   1;
            $arr_online_users[$user_id]['status_text']  =   '正在'.$playing_id.'号房间游戏';
        }
        $this->common->objRedisCache()->delete( 'online_users.cache' );
        $this->common->objRedisCache()->save( 'online_users.cache', $arr_online_users );
        //更新所有在大厅的用户的用户列表
        $this->updateDaTingUserList( $server, $data, $fd );
        //更新所有在大厅的用户的房间列表
        $this->updateDaTingRoomList( $server, $data, $fd );
    }

    //绑定user_id、fd和playing_id
    public function bindUserIdFdPlayingId( $server, $data, $fd )
    {
        $user_id = intval( $data['user_id'] );
        $this->common->objRedisCache()->save( 'user_id_'.$user_id.'_fd.cache', $fd );
        $this->common->objRedisCache()->save( 'fd_id_'.$fd.'_user_id.cache', $user_id );
        //绑定play_id和fd
        $playing_id     =   $this->common->objRedisCache()->get( 'user_id_'.$user_id.'_playing_id.cache' );
        if( !$playing_id ) return false;
        $playing_user   =   $this->common->objRedisCache()->get( 'playing_id_'.$playing_id.'_user.cache' );
        if( $playing_user['red']['user_id'] == $user_id )
        {
            $playing_user['red']['fd'] = $fd;
        }
        if( isset($playing_user['black']['user_id']) && $playing_user['black']['user_id'] == $user_id )
        {
            $playing_user['black']['fd'] = $fd;
            //如果绑定的是黑方用户信息，则需要向红方用户发送消息，更新红方用户界面的黑方用户user_name
            $return['cmd']          =   1;
            $return['user_name']    =   $playing_user['black']['user_name'];
            $return['user_name']    .=  $playing_user['black']['is_ready'] ? '' : '(未准备)';
            $server->push( $playing_user['red']['fd'], json_encode($return) );
        }
        $this->common->objRedisCache()->delete( 'playing_id_'.$playing_id.'_user.cache' );
        $this->common->objRedisCache()->save( 'playing_id_'.$playing_id.'_user.cache', $playing_user );
    }

    //准备
    public function ready( $server, $data, $fd )
    {
        $user_id        =   $this->common->objRedisCache()->get( 'fd_id_'.$fd.'_user_id.cache' );
        $playing_id     =   $this->common->objRedisCache()->get( 'user_id_'.$user_id.'_playing_id.cache' );
        $playing_user   =   $this->common->objRedisCache()->get( 'playing_id_'.$playing_id.'_user.cache' );
        if( $user_id == $playing_user['red']['user_id'] )
        {
            $playing_user['red']['is_ready'] = true;
            $return['cmd']          =   2;
            $return['user_name']    =   $playing_user['red']['user_name'].'(已准备)';
            $server->push( $playing_user['black']['fd'], json_encode($return) );
        }
        if( $user_id == $playing_user['black']['user_id'] )
        {
            $playing_user['black']['is_ready'] = true;
            $return['cmd']          =   2;
            $return['user_name']    =   $playing_user['black']['user_name'].'(已准备)';
            $server->push( $playing_user['red']['fd'], json_encode($return) );
        }
        $this->common->objRedisCache()->delete( 'playing_id_'.$playing_id.'_user.cache' );
        $this->common->objRedisCache()->save( 'playing_id_'.$playing_id.'_user.cache', $playing_user );

        //检测是否双方都已经准备妥当，如果双方都已经准备妥当，则发送消息，让双方都进入对局
        if( $playing_user['red']['is_ready'] && $playing_user['black']['is_ready'] )
        {
            $return['cmd']              =   3;
            $return['black_user_name']  =   $playing_user['black']['user_name'];
            $return['red_user_name']    =   $playing_user['red']['user_name'];
            $server->push( $playing_user['black']['fd'], json_encode($return) );
            $server->push( $playing_user['red']['fd'], json_encode($return) );
        }
    }

    //走一步棋
    public function move( $server, $data, $fd )
    {
        $user_id    =   $this->common->objRedisCache()->get( 'fd_id_'.$fd.'_user_id.cache' );
        $playing_id =   $this->common->objRedisCache()->get( 'user_id_'.$user_id.'_playing_id.cache' );
        $playing_user  =   $this->common->objRedisCache()->get( 'playing_id_'.$playing_id.'_user.cache' );
        if( $playing_user['red']['user_id'] == $user_id )
        {
            $now_move = 'red';
            $wait_move = 'black';
        }
        if( $playing_user['black']['user_id'] == $user_id )
        {
            $now_move = 'black';
            $wait_move = 'red';
        }

        if( $playing_user['who_turn'] !== $now_move )
            return false;

        //记录着法
        //定义走的这着的相关数据
        $arr_playing_id_move["user_id"]                =   $user_id;            //这步棋是谁下的
        $arr_playing_id_move["camp"]                   =   $now_move;           //下这步棋的人在这场对局中的阵营,red、black
        $arr_playing_id_move["move"]                   =   $data['move'];       //着法的中文
        $arr_playing_id_move["{$now_move}_pace"]       =   $data['pace'];       //下这步棋的阵营方在下方的着法坐标
        $arr_playing_id_move["{$wait_move}_pace"]      =   $this->transPace( $data['pace'] ); //下这步棋的阵营方的反方方在下方的着法坐标
        $arr_playing_id_move["{$now_move}_map"]        =   $data['map'];        //下这步棋的阵营方在下方的所有子力布局
        $arr_playing_id_move["{$wait_move}_map"]       =   $this->transMap( $data['map'] ); //下这步棋的阵营方的反方在下方的所有子力布局

        //取出已有着法
        $arr_playing_id_moves  =   $this->common->objRedisCache()->get( 'playing_id_'.$playing_id.'_moves.cache' );

        //检测当前走的这一步是否正常
        $this->checkMove( $arr_playing_id_move, $arr_playing_id_moves );

        //如果已有缓存，save之前需要删除缓存
        if( $arr_playing_id_moves )
            $this->common->objRedisCache()->delete( 'playing_id_'.$playing_id.'_moves.cache' );

        $arr_playing_id_moves[] = $arr_playing_id_move;
        $this->common->objRedisCache()->save( 'playing_id_'.$playing_id.'_moves.cache', $arr_playing_id_moves );

        //修改play_user的who_turn
        $playing_user['who_turn']  =   $wait_move;
        $this->common->objRedisCache()->delete( 'playing_id_'.$playing_id.'_user.cache' );
        $this->common->objRedisCache()->save( 'playing_id_'.$playing_id.'_user.cache', $playing_user );

        //向客户端发送消息
        $return['cmd']   =   4;
        $return['pace']  =   $this->transPace( $data['pace'] );
        $server->push( $playing_user[$wait_move]['fd'], json_encode($return) );
    }

    /**
     * 转换步法，开局布局时，红棋在下方和黑棋在下方的两种情况下，步法之间的对换
     * @param $pace 步法
     * @return 返回$pace
     */
    public function transPace( $pace )
    {
        $arr_pace = explode(',', $pace);
        foreach( $arr_pace as $k=>$v )
        {
            $new_arr_pace[$k] = (8-$v['0']).(9-$v['1']).(8-$v['2']).(9-$v['3']);
        }
        return implode(',', $new_arr_pace);
    }

    /**
     * 转换布局
     * @param $map 布局
     * @return $new_map
     */
    public function transMap( $map )
    {
        foreach( $map as $k=>$v )
        {
            foreach( $v as $vk=>$vv )
            {
                $new_map[ 9-$k ][ 8-$vk ] = $vv;
            }
        }
        return $new_map;
    }

    /**
     * 检测当前走的着法是否正常
     * @param $move     当前的着法
     * @param $moves    已下的所有着法
     */
    public function checkMove( $move, $moves )
    {
        return true;
    }

    //胜负已分，进行数据存储
    public function chessEnd( $server, $data, $fd )
    {
        //需要等两个用户都发送了cmd=4的指令才进行数据存储
        $user_id        =   $this->common->objRedisCache()->get( 'fd_id_'.$fd.'_user_id.cache' );
        $playing_id     =   $this->common->objRedisCache()->get( 'user_id_'.$user_id.'_playing_id.cache' );
        $playing_user   =   $this->common->objRedisCache()->get( 'playing_id_'.$playing_id.'_user.cache' );

        //设置锁，否则有可能入库两次
        $is_locked = $this->common->objRedisCache()->get( 'playing_id_'.$playing_id.'_is_locked.cache' );
        if( $is_locked === NULL )
        {
            $this->common->objRedisCache()->save( 'playing_id_'.$playing_id.'_is_locked.cache', true );
        }

        if( $is_locked )
        {
            //向用户发送消息，显示重新开始按钮
            $return['cmd'] = 5;
            $server->push( $fd, json_encode($return) );
            return false;
        }

        //判断胜负是否已分，查看最后一个map，看是否没有个将或帅
        $moves      =   $this->common->objRedisCache()->get( 'playing_id_'.$playing_id.'_moves.cache' );
        $last_map   =   $moves[count($moves)-1]['red_map'];
        $allMan     =   array();
        foreach( $last_map as $k=>$v )
        {
            foreach( $v as $vk=>$vv )
            {
                $vv && $allMan[] = $vv;
            }
        }
        if( in_array( 'j0', $allMan ) && in_array( 'J0', $allMan ) )    return false;
        if( !in_array( 'j0', $allMan ) )    $victory    =   'black';
        if( !in_array( 'J0', $allMan ) )    $victory    =   'red';

        $playing_user = $this->common->objRedisCache()->get( 'playing_id_'.$playing_id.'_user.cache' );
        $chess_save_data = new Chess();
        $chess_save_data->red_user_id     =   $playing_user['red']['user_id'];
        $chess_save_data->black_user_id   =   $playing_user['black']['user_id'];
        $chess_save_data->victory         =   $victory;
        $chess_save_data->save();

        foreach( $moves as $k=>$v )
        {
            $chess_moves[$k] = new ChessMoves();
            $chess_moves[$k]->chess_id          =   1;
            $chess_moves[$k]->user_id           =   $v['user_id'];
            $chess_moves[$k]->camp              =   $v['camp'];
            $chess_moves[$k]->move              =   $v['move'];
            $chess_moves[$k]->red_pace          =   $v['red_pace'];
            $chess_moves[$k]->black_pace        =   $v['black_pace'];
            $chess_moves[$k]->red_map           =   json_encode( $v['red_map'] );
            $chess_moves[$k]->black_map         =   json_encode( $v['black_map'] );
            $chess_moves[$k]->save();
        }

        //删除/更新缓存
        $this->common->objRedisCache()->delete( 'playing_id_'.$playing_id.'_is_locked.cache' );
        $this->common->objRedisCache()->delete( 'playing_id_'.$playing_id.'_moves.cache' );
        $this->common->objRedisCache()->delete( 'playing_id_'.$playing_id.'_user.cache' );
        $new_playing_user['red']                =   $playing_user['red'];
        $new_playing_user['red']['is_ready']    =   false;
        $new_playing_user['black']              =   $playing_user['black'];
        $new_playing_user['black']['is_ready']  =   false;
        $new_playing_user['who_turn']           =   'red';
        $this->common->objRedisCache()->save( 'playing_id_'.$playing_id.'_user.cache', $new_playing_user );

        //向用户发送消息，显示准备按钮
        $return['cmd'] = 5;
        $server->push( $fd, json_encode($return) );
    }

    //返回大厅
    public function reEnterDaTing( $server, $data, $fd )
    {
        //清空必要的缓存
        $user_id        =   $this->common->objRedisCache()->get( 'fd_id_'.$fd.'_user_id.cache' );
        $playing_id     =   $this->common->objRedisCache()->get( 'user_id_'.$user_id.'_playing_id.cache' );
        $playing_user   =   $this->common->objRedisCache()->get( 'playing_id_'.$playing_id.'_user.cache' );
        $this->common->objRedisCache()->delete( 'playing_id_'.$playing_id.'_user.cache' );
        $this->common->objRedisCache()->delete( 'playing_id_'.$playing_id.'_moves.cache' );

        $playing_id_list = $this->common->objRedisCache()->get( 'playing_id_list.cache' );
        foreach( $playing_id_list as $k=>$v )
        {
            if( $v == $playing_id )
                unset($playing_id_list[$k]);
        }

        if( count($playing_id_list) )
        {
            $this->common->objRedisCache()->save( 'playing_id_list.cache', $playing_id_list );
        }
        else
        {
            $this->common->objRedisCache()->delete( 'playing_id_list.cache' );
        }

        $this->common->objRedisCache()->delete( 'user_id_'.$playing_user['red']['user_id'].'_playing_id.cache' );
        $this->common->objRedisCache()->delete( 'user_id_'.$playing_user['black']['user_id'].'_playing_id.cache' );
        $fd = 0;
        if( $playing_user['red']['user_id'] == $user_id )
            $fd = $this->common->objRedisCache()->get( 'user_id_'.$playing_user['black']['user_id'].'_fd.cache' );
        if( $playing_user['black']['user_id'] == $user_id )
            $fd = $this->common->objRedisCache()->get( 'user_id_'.$playing_user['red']['user_id'].'_fd.cache' );

        //向用户发送消息，询问是否退回到大厅
        if( $fd )
        {
            $return['cmd'] = 6;
            $server->push( $fd, json_encode($return) );
        }

        $online_users = $this->common->objRedisCache()->get( 'online_users.cache' );
        if( $playing_user['red']['user_id'] )
        {
            $online_users[$playing_user['red']['user_id']]['status'] = 0;
            $online_users[$playing_user['red']['user_id']]['status_text'] = '等待';
        }
        if( $playing_user['black']['user_id'] )
        {
            $online_users[$playing_user['black']['user_id']]['status'] = 0;
            $online_users[$playing_user['black']['user_id']]['status_text'] = '等待';
        }
        $this->common->objRedisCache()->delete( 'online_users.cache' );
        $this->common->objRedisCache()->save( 'online_users.cache', $online_users );
    }
}
