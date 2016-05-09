<?php echo $this->partial('public/header'); ?>
    <title>对弈房间 - Swoole中国象棋</title>
<link href="<?php echo $this->url->getStatic('public/css/zzsc.css'); ?>" type="text/css" rel="stylesheet" />
<link href="<?php echo $this->url->getStatic('public/css/button.css'); ?>" type="text/css" rel="stylesheet" />
<script>
var SITE_URL 		= 	"<?php echo 'http://'.$_SERVER['HTTP_HOST'] ?>";
var user_info		=	eval(<?php echo $user_info; ?>);
var playing_user	=	eval(<?php echo $playing_user; ?>);
var chess_color		=	"<?php echo $chess_color; ?>";
var other_color		=	( chess_color == 'red' ) ? 'black' : 'red';
var move            =   eval(<?php echo $move; ?>);
</script>
</head>
<body>
<div class="box" id="box">
	<div class="chess_left">
		<canvas id="chess">对不起，您的浏览器不支持HTML5，请升级浏览器至IE9、firefox或者谷歌浏览器！</canvas>
		<audio src="" id="clickAudio" preload="auto"></audio>
		<audio src="" id="selectAudio" preload="auto"></audio>
		<br/ ><br />
		<div>
			<?php $arr_playing_user = 	json_decode( $playing_user, true ); ?>
			<?php $other_color		=	( $chess_color == 'red' ) ? 'black' : 'red'; ?>
			<?php if( !$arr_playing_user[$chess_color]['is_ready'] || !$arr_playing_user[$other_color]['is_ready'] ){ ?>
			<input type="button" class="button yellow larrow" value="<?php if($is_ready){ echo '已准备'; }else{ echo '准备'; } ?>" id="readyBtn" <?php if($is_ready){ echo 'disabled="disabled"'; } ?> />
			<?php } ?>
			<input type="button" class="button red" value="<?php echo $redUserName; ?>" disabled="disabled" id="redUserName" />
			<input type="button" class="button black" value="<?php echo $blackUserName; ?>" disabled="disabled" id="blackUserName" />
			<input type="button" class="button blue" value="返回大厅" id="reEnterDaTing" />
		</div>
	</div>
</div>
<script src="<?php echo $this->url->getStatic('public/js/jquery-1.8.2.min.js'); ?>"></script>
<script src="<?php echo $this->url->getStatic('public/js/'.$chess_color.'_common.js'); ?>"></script>
<script src="<?php echo $this->url->getStatic('public/js/'.$chess_color.'_play.js'); ?>"></script>
<script src="<?php echo $this->url->getStatic('public/js/websocket.js'); ?>"></script>
<script src="<?php echo $this->url->getStatic('public/js/jquery.json.js'); ?>"></script>
<script src="<?php echo $this->url->getStatic('public/js/onload.js'); ?>"></script>
</body>
</html>
