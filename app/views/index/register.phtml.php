<?php echo $this->partial('public/header'); ?>
<title>注册 - Swoole中国象棋</title>
        <!-- CSS -->
        <link rel="stylesheet" href="<?php echo $this->url->getStatic('public/css/reg_login/reset.css'); ?>">
        <link rel="stylesheet" href="<?php echo $this->url->getStatic('public/css/reg_login/supersized.css'); ?>">
        <link rel="stylesheet" href="<?php echo $this->url->getStatic('public/css/reg_login/style.css'); ?>">
        <link rel="stylesheet" href="<?php echo $this->url->getStatic('public/css/reg_login/tipso.min.css'); ?>">
    </head>
    <body>
        <div class="page-container">
            <h1>注册</h1>
            <form id="register_form">
                <?php echo $this->tag->textField(array('name'=>'user_name','id'=>'user_name','class'=>'username','placeholder'=>'用户名','minlength'=>2,'maxlength'=>12)); ?><span id="error_user_name"></span>
                <?php echo $this->tag->passwordField(array('name'=>'password','id'=>'password','class'=>'password','placeholder'=>'密码','minlength'=>6,'maxlength'=>20)); ?><span id="error_password"></span>
                <?php echo $this->tag->passwordField(array('name'=>'repassword','id'=>'repassword','class'=>'password','placeholder'=>'重复密码','minlength'=>6,'maxlength'=>20)); ?><span id="error_repassword"></span>
                <button type="button" onclick="doReg();" id="submitBtn">提交</button>
            </form>
            <div class="connect">
                <p>已有账号？<?= $this->tag->linkTo("index/index", "点击登录") ?></p>
            </div>
        </div>
        <!-- Javascript -->
        <script src="<?php echo $this->url->getStatic('public/js/jquery-1.8.2.min.js'); ?>"></script>
        <script src="<?php echo $this->url->getStatic('public/js/supersized.3.2.7.min.js'); ?>"></script>
        <script src="<?php echo $this->url->getStatic('public/js/supersized-init.js'); ?>"></script>
        <script src="<?php echo $this->url->getStatic('public/js/scripts.js'); ?>"></script>
        <script src="<?php echo $this->url->getStatic('public/js/tipso.min.js'); ?>"></script>
        <script>
        $("#repassword").on("focus", function()
        {
            $(document).on("keydown",function(event)
            {
                if( event.keyCode == 13 )
                {
                    $('#submitBtn').click();
                }
            });
        });

        function doReg()
        {
            $('#submitBtn').attr('disabled','disabled');
            $('#submitBtn').html('注册中，请稍后...');
            var post_url = "<?php echo $this->url->get('index/doReg'); ?>";
            $.post(post_url, $('#register_form').serialize(), function(data){
                data = eval("("+data+")");
                if( data.status == 0 )
                {
                    if( data.data.field == 'user_name' )
                    {
                        $('#error_user_name').tipso({background: 'tomato',useTitle:false,position: 'right',content:data.msg});
                        $('#error_user_name').tipso('update', 'content', data.msg);
                        $('#error_user_name').tipso('show');
                    }
                    else if( data.data.field == 'password' )
                    {
                        $('#error_password').tipso({background: 'tomato',useTitle:false,position: 'right',content:data.msg});
                        $('#error_password').tipso('update', 'content', data.msg);
                        $('#error_password').tipso('show');
                        $('#error_repassword').tipso({background: 'tomato',useTitle:false,position: 'right',content:data.msg});
                        $('#error_repassword').tipso('update', 'content', data.msg);
                        $('#error_repassword').tipso('show');
                    }
                    $('#submitBtn').removeAttr('disabled');
                    $('#submitBtn').html('提交');
                }
                else if( data.status == 1 )
                {
                    $('#submitBtn').html('注册成功，正在进入登录页...');
                    setTimeout( skipLogin, 2000 );
                }
            });
        }

        function skipLogin()
        {
            location.href = "<?php echo $this->url->get('/'); ?>";
        }
        </script>
    </body>
</html>
