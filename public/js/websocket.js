//指令定义
/**
 //send 发送消息
 * cmd = 1  //连接服务器
 * cmd = 2  //准备
 * cmd = 3  //下一步棋
 * cmd = 4  //对局胜负已分，进行数据存储
 * cmd = 5  //重新开始一场对局
 //getmsg 接收消息
 * cmd = 1  //黑方用户进入房间，更新红方用户界面黑方用户名
 * cmd = 2  //对方已准备
 * cmd = 3  //双方都已经准备妥当，进入对局
 * cmd = 4  //下一步棋
 * cmd = 5  //数据存储完毕，显示重新开始按钮
 * cmd = 6  //双方都重新准备完毕，开始一个新的对局
 * cmd = 7  //返回大厅
 */
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
        obj.cmd = 1;
        sendCmd(obj);
    };

    //有消息到来时触发
    ws.onmessage = function (e) {
        var msg = eval("("+e.data+")");
        if( msg.cmd == 1 || msg.cmd == 2 )
        {
            if( chess_color == 'red' )
            {
                $('#blackUserName').val( msg.user_name );
            }
            if( chess_color == 'black' )
            {
                $('#redUserName').val( msg.user_name );
            }
        }
        if( msg.cmd == 3 )
        {
            $('#redUserName').val(msg.red_user_name);
            $('#blackUserName').val(msg.black_user_name);
            $('#readyBtn').hide();
            play.isPlay= ( chess_color == 'red' ) ? true : false ;
			com.createMans(com.initMap);		//生成棋子
			play.init();
        }
        if( msg.cmd == 4 )
        {
            play.AutoPlay( msg.pace );
        }
        if( msg.cmd == 5 )
        {
            location.reload();
        }
        if( msg.cmd == 6 )
        {
            if( confirm('您的对手已经返回大厅，您是否也返回大厅') )
            {
                location.href=SITE_URL+'/play';
            }
            else
            {
                location.reload();
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
    ws.send($.toJSON(obj));
    if( obj.cmd == 2 )
    {
        $('#'+chess_color+'UserName').val( user_info.user_name + '(已准备)');
    }
    if( obj.cmd == 3 )
    {
        console.log( obj );
    }
}
