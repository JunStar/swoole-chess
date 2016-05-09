window.onload = function(){
	com.bg=new com.class.Bg();
	com.dot = new com.class.Dot();
	com.pane=new com.class.Pane();
	com.pane.isShow=false;
	com.childList=[com.bg,com.dot,com.pane];
	com.mans	 ={};		//棋子集合
	com.bg.show();
	if( playing_user.red.is_ready && playing_user.black.is_ready )
	{
		play.isPlay = ( playing_user.who_turn === chess_color ) ? true : false ;
		if( move[chess_color+'_map'] != undefined ){
			play.map = []
			for(var key in move[chess_color+'_map']){
				var playmapi = [];
				for(var i in move[chess_color+'_map'][key])
				{
					playmapi.push(move[chess_color+'_map'][key][i]);//往数组中放属性
				}
			   play.map.push(playmapi);
			}
			com.createMans(play.map);		//生成棋子
		}
		else
		{
			com.createMans(com.initMap);		//生成棋子
		}
	}
	else
	{
		document.getElementById('readyBtn').addEventListener("click", function(e) {
			var obj = new Object();
	        obj.cmd = 2;
			sendCmd(obj);
			$("#readyBtn").attr('disabled','disabled');
            $("#readyBtn").val('已准备');
		})
	}
	play.init();

	document.getElementById('reEnterDaTing').addEventListener("click", function(e) {
		var obj = new Object();
		obj.cmd = 5;
		sendCmd(obj);
		if( confirm('您确定退回大厅吗？') )
			location.href=SITE_URL+'/play/';
	})
}

play.AutoPlay = function ( pace ){
	var key=play.map[pace[1]][pace[0]]
	play.nowManKey = key;

	var key=play.map[pace[3]][pace[2]];
	if (key){
		play.AIclickMan(key,pace[2],pace[3]);
	}else {
		play.AIclickPoint(pace[2],pace[3]);
	}
	//自动下完之后，设置本方可以下棋
	play.isPlay = true;
}

//出现输赢后，向服务器发送指令
play.showWin = function (my){
	play.isPlay = false;
	var obj = new Object();
	obj.cmd = 4;
	sendCmd( obj );
	if (my===play.my){
		alert("恭喜你，你赢了！");
	}else{
		alert("很遗憾，你输了！");
	}
}
