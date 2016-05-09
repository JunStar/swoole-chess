<?php
class common
{
    /**
     * 浏览器友好的变量输出
     * @param mixed $var 变量
     * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
     * @param string $label 标签 默认为空
     * @param boolean $strict 是否严谨 默认为true
     * @return void|string
     */
    function dump($var, $echo=true, $label=null, $strict=true){
        $label = ($label === null) ? '' : rtrim($label) . ' ';
        if (!$strict) {
            if (ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            } else {
                $output = $label . print_r($var, true);
            }
        } else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            if (!extension_loaded('xdebug')) {
                $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            }
        }
        if ($echo) {
            echo($output);
            return null;
        }else
            return $output;
    }

    function ajaxSuccess( $info, $data=array() )
    {
        $return['status']   =   1;
        $return['msg']      =   $info;
        $return['data']     =   $data;
        exit( json_encode( $return ) );
    }

    function ajaxError( $info, $data=array() )
    {
        $return['status']   =   0;
        $return['msg']      =   $info;
        $return['data']     =   $data;
        exit( json_encode( $return ) );
    }

    function objFileCache()
    {
        $front_cache = new Phalcon\Cache\Frontend\Data(
            array(
                "lifetime" => 24*60*60
            )
        );

        $cache = new Phalcon\Cache\Backend\File(
            $front_cache,
            array(
                "cacheDir" => "app/cache/"
            )
        );
        return $cache;
    }

    function objRedisCache()
    {
        // Cache data for 1 days
        $frontCache = new \Phalcon\Cache\Frontend\Data(array(
            "lifetime" => 24*60*60
        ));

        //Create the Cache setting redis connection options
        $cache = new Phalcon\Cache\Backend\Redis($frontCache, array(
            "prefix"        =>  'chess_',       //前缀
            'host'          =>  '127.0.0.1',    //服务器地址
            'port'          =>  6379,           //端口
            'persistent'    =>  false           //是否长连接
        ));
        return $cache;
    }
}
