/*! 一叶孤舟 | qq:28701884 | 欢迎指教 */

var com = com||{};

com.init = function (stype){

	com.nowStype= "stype2";
	var stype = com.stype[com.nowStype];
	com.width			=	stype.width;		//画布宽度
	com.height			=	stype.height; 		//画布高度
	com.spaceX			=	stype.spaceX;		//着点X跨度
	com.spaceY			=	stype.spaceY;		//着点Y跨度
	com.pointStartX		=	stype.pointStartX;	//第一个着点X坐标;
	com.pointStartY		=	stype.pointStartY;	//第一个着点Y坐标;
	com.page			=	stype.page;			//图片目录

	com.get("box").style.width = com.width+130+"px";

	com.canvas			=	document.getElementById("chess"); //画布
	com.ct				=	com.canvas.getContext("2d");
	com.canvas.width	=	com.width;
	com.canvas.height	=	com.height;

	com.childList		=	com.childList||[];

	com.loadImages(com.page);		//载入图片/图片目录
	//z(com.initMap.join())
}

//样式
com.stype = {
	stype2:{
		width:530,		//画布宽度
		height:567, 		//画布高度
		spaceX:57,		//着点X跨度
		spaceY:57,		//着点Y跨度
		pointStartX:-2,		//第一个着点X坐标;
		pointStartY:0,		//第一个着点Y坐标;
		page:"stype_2"	//图片目录
	}
}
//获取ID
com.get = function (id){
	return document.getElementById(id)
}

window.onload = function(){
	com.bg=new com.class.Bg();
	com.bg.show();
}

//载入图片
com.loadImages = function(stype){
	//绘制棋盘
	com.bgImg = new Image();
	com.bgImg.src  = SITE_URL+"/public/img/"+stype+"/bg.png";
	document.getElementsByTagName("body")[0].style.background= "url("+SITE_URL+"/public/img/"+stype+"/bg.jpg)";
}

//显示列表
com.show = function (){
	com.ct.clearRect(0, 0, com.width, com.height);
	for (var i=0; i<com.childList.length ; i++){
		com.childList[i].show();
	}
}

com.class = com.class || {} //类

com.class.Bg = function (img, x, y){
	this.x = x||0;
    this.y = y||0;
	this.isShow = true;

	this.show = function (){
		if (this.isShow) com.ct.drawImage(com.bgImg, com.spaceX * this.x,com.spaceY *  this.y);
	}
}

com.init();
