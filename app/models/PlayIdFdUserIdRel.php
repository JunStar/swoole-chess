<?php
//房间id、fd、user三者的关系
class PlayIdFdUserIdRel
{
    //根据fd获取用户id
    function getUserIdByFd(){}
    //根据房间id获取红黑双方的用户信息
    function getUsersByPlayId(){}
    //根据用户id获取fd
    function getFdByUserId(){}
    //根据房间ID获取fds
    function getFdsByPlayId(){}
    //根据用户id获取房间id
    function getPlayIdByUserId(){}

    //绑定user_id、fd和playing_id
    function bindUserIdFdPlayingId( $fd, $user_id )
    {
        //绑定user_id和fd
        $this->common->objRedisCache()->save( 'user_id_'.$user_id.'_fd.cache', $fd );
        $this->common->objRedisCache()->save( 'fd_id_'.$fd.'_user_id.cache', $user_id );
        //绑定play_id和fd
        $playing_id     =   $this->common->objRedisCache()->get( 'user_id_'.$user_id.'_playing_id.cache' );
        $playing_user   =   $this->common->objRedisCache()->get( 'playing_id'.$playing_id.'_user.cache' );
        if( $playing_user['red']['user_id'] == $user_id )
        {
            $playing_user['red']['fd'] = $fd;
        }
        if( $playing_user['black']['user_id'] == $user_id )
        {
            $playing_user['black']['fd'] = $fd;
        }
        $this->common->objRedisCache()->save( 'playing_id'.$playing_id.'_user.cache', $playing_user );
    }
}
