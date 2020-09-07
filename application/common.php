<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

//自定义消息提示页面

/**
 * $msg 待提示的消息
 * $url 待跳转的链接
 * $icon 这里主要有两个，5和6，代表两种表情（哭和笑）
 */
function alert($msg = '', $url = '', $time = 3)
{
    $str = '<script type="text/javascript" src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"></script> <script type="text/javascript" src="__ADMIN__/lib/layui/layui.js"></script>';//加载jquery和layer
    $str .= '<script>
        $(function(){
            layer.msg("' . $msg . '",{icon:"6",time:' . ($time * 1000) . '});
            setTimeout(function(){
                   self.parent.location.href="' . $url . '"
            },2000)
        });
    </script>';//主要方法
    return $str;
}
