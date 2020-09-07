<?php

namespace app\index\controller;

use app\admin\model\Admin as adminmodel;
use think\Collection;
use think\facade\Env;

class Index
{
    public function index()
    {
        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> ThinkPHP V5.1<br/><span style="font-size:30px">12载初心不改（2006-2018） - 你值得信赖的PHP框架</span></p></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=64890268" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="eab4b9f840753f8e7"></think>';
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }


    public function test()
    {
        $list = adminmodel::select();
        foreach ($list as $admin) {
            if ($admin['id'] == 1708190147) {
                echo $admin;
            }
        }
        var_dump($list);
//        foreach ($list as $k => $v) {
//            if ($data[$k]['id'] == 1708190147) {
//                echo $data[$k]['id'];
//            }
//        }
    }

    public function pass()
    {
        $pwd   = 123456;
        $mpwd  = md5($pwd);
        $salt  = "ssjxyz";  //定义一个salt值，程序员规定下来的随机字符串
        $spwd  = $pwd . $salt;  //把密码和salt连接
        $mspwd = md5($spwd);  //执行MD5散列
        return $mpwd;  //返回散列
    }

    public function rootpath()
    {
        $path='../public/static/admin/';
        echo $path;
        echo '<br/>';
        echo Env::get("ROOT_PATH");
    }
}
