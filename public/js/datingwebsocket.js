//指令定义
//-1更新用户列表
//-2更新房间列表
//-3退出登录
var webchess = {
    'server' : 'ws://192.168.12.11:9501'
}

$(document).ready(function () {
    //使用原生WebSocket
    if (window.WebSocket || window.MozWebSocket)
    {
        ws = new WebSocket(webchess.server);
    }
    listenEvent();
});

function listenEvent() {
    /**
     * 连接建立时触发
     */
    ws.onopen = function (e) {
        var obj = new Object();
        obj.cmd = -1;
        sendCmd(obj);
    };

    //有消息到来时触发
    ws.onmessage = function (e) {
        var msg = eval("("+e.data+")");
        if( msg.cmd == -1 )
        {
            var html = '<tr id="user_'+msg.user_id+'" datagrid-row-index="0" class="datagrid-row">'+
                '<td field="USERNIKE">'+
                    '<div style="text-align:left;height:auto;" class="datagrid-cell datagrid-cell-c4-USERNIKE">'+
                        '<div style="line-height:22px;float:left;width:100%;" title="JunStar">'+msg.user_name+'</div>'+
                    '</div>'+
                '</td>'+
                '<td field="WINNUM">'+
                    '<div style="text-align:center;height:auto;" class="datagrid-cell datagrid-cell-c4-WINNUM">暂未统计</div>'+
                '</td>'+
                '<td field="LOSENUM">'+
                    '<div style="text-align:center;height:auto;" class="datagrid-cell datagrid-cell-c4-LOSENUM">暂未统计</div>'+
                '</td>'+
                '<td field="STATUS">'+
                    '<div style="text-align:center;height:auto;" class="datagrid-cell datagrid-cell-c4-STATUS">'+msg.status_text+'</div>'+
                '</td>'+
            '</tr>';

            if( $('#user_'+msg.user_id) !== undefined )
            {
                $('#user_'+msg.user_id).remove();
            }
            $('#user_list').append( html );
            $('#online_users_count').html( msg.user_count );
        }

        if( msg.cmd == -2 )
        {
            $('#room_list').empty();
            if( msg.playing_count > 0)
            {
                for( key in msg.playing_id_list )
                {
                    var playing_id_list = msg.playing_id_list[key];
                    var playing_status_text = (playing_id_list.playing_status == 1) ? '游戏中' : '等待中';
                    var playing_status_html = (playing_id_list.playing_status == 1) ? '暂无' : '<button class="btn2 btn2-primary btn2-xs" style="color:brown;" onclick="location.href=\''+SITE_URL+'/play/chess/'+playing_id_list.playing_id+'\'">加入游戏</button>';
                    var html = '<tr id="room_'+playing_id_list.playing_id+'" datagrid-row-index="0" class="datagrid-row">'+
                        '<td field="roomId">'+
                            '<div style="text-align:center;height:auto;" class="datagrid-cell datagrid-cell-c3-roomId">'+
                                '<div style="line-height:22px;float:left;width:100%;">'+playing_id_list.playing_id+'</div>'+
                            '</div>'+
                        '</td>'+
                        '<td field="white">'+
                            '<div style="text-align:center;height:auto;" class="datagrid-cell datagrid-cell-c3-white">'+playing_id_list.playing_user.red.user_name+'</div>'+
                        '</td>'+
                        '<td field="black">'+
                            '<div style="text-align:center;height:auto;" class="datagrid-cell datagrid-cell-c3-black">'+playing_id_list.playing_user.black.user_name+'</div>'+
                        '</td>'+
                        '<td field="status" style="color:#0072c1;">'+
                            '<div style="color:#0072c1;;text-align:center;;height:auto;" class="datagrid-cell datagrid-cell-c3-status">'+playing_status_text+'</div>'+
                        '</td>'+
                        '<td field="options">'+
                            '<div style="text-align:center;height:auto;" class="datagrid-cell datagrid-cell-c3-options">'+
                                '<div class="btn-group">'+
                                    playing_status_html +
                                '</div>'+
                            '</div>'+
                        '</td>'+
                    '</tr>';
                    $('#room_list').append( html );
                }
                $('#playing_room_count').html( msg.playing_count );
            }
            else
            {
                $('#playing_room_count').html( 0 );
            }
        }

        if( msg.cmd == -3 )
        {
            if( $('#user_'+msg.user_id) !== undefined )
            {
                $('#user_'+msg.user_id).remove();
                $('#online_users_count').html( parseInt($('#online_users_count').html()) - 1 );
            }
        }
    };

    /**
     * 连接关闭事件
     */
    ws.onclose = function (e) {
        alert('服务器已关闭');
    };

    /**
     * 异常事件
     */
    ws.onerror = function (e) {
        alert('服务器发生异常');
    };
}

function sendCmd( obj )
{
    obj.user_id = user_info.user_id;
    obj.user_name = user_info.user_name;
    ws.send($.toJSON(obj));
}

function doLogout()
{
    var obj = new Object();
    obj.cmd = -3;
    sendCmd(obj);
    location.href   =   SITE_URL+'/index/doLogout';
}
