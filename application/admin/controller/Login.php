<?php


namespace app\admin\controller;

use think\Controller;

use think\Db;

use think\facade\Request;

use think\facade\Session;


class Login extends Controller
{
    public function index()
    {
        $this->assign('admin', Session::get('adminname'));
        return $this->fetch("index/index");
    }

    public function login()
    {
        return view('login');
    }

    //盐值加密函数
    public function salt($pwd)
    {
        $salt  = "ssjxyz";  //定义一个salt值，程序员规定下来的随机字符串
        $spwd  = $pwd . $salt;  //把密码和salt连接
        $mspwd = md5($spwd);  //执行MD5散列
        return $mspwd;  //返回散列
    }


    public function logincheck()
    {
        $status = 0;
        if (Request::isAjax()) {
            $data      = Request::post();
            $password  = $data['password'];
            $name      = $data['name'];
            $salt      = $this->salt($password);
            $mpassword = md5($password);
            $admin     = Db::name('admin')->where('name', '=', $name)->find();
            if ($admin) {
                if (($admin['pwd'] == $mpassword) && ($admin['salt'] == $salt) && ($admin['status'] == 1)) {
                    $status = 1;
                    Session::set('adminname', $name);
                    $this->welcome();
                } else if (($admin['pwd'] == $mpassword) && ($admin['salt'] == $salt) && ($admin['status'] != 1)) {
                    $status = 2;
                } else if (($admin['pwd'] != $mpassword)) {
                    $status = 3;
                }
            } else {
                $status = 4;
            }
        }
        return $status;
    }


    public function welcome()
    {
        $mysql_version = db()->query('SELECT VERSION() AS ver');
        //运行环境
        Session::set('server_os', $_SERVER['SERVER_SOFTWARE']);
        Session::set('mysql_version', $mysql_version[0]['ver']);
        Session::set('php_way', php_sapi_name());
        Session::set('server_version', PHP_OS);

        //心理咨询师数
        $enum = Db::name('expert')->select();
        Session::set('enum', sizeof($enum));
        //学生数
        $snum = Db::name('student')->select();
        Session::set('snum', sizeof($snum));
        //订单数
        $onum = Db::name('order')->select();
        Session::set('onum', sizeof($onum));
        //留言数
        $mnum = Db::name('message')->select();
        Session::set('mnum', sizeof($mnum));
        //公告数
        $nnum = Db::name('notice')->select();
        Session::set('nnum', sizeof($nnum));
        //公告数
        $anum = Db::name('admin')->select();
        Session::set('anum', sizeof($anum));
        return $this->fetch("index/welcome");
    }
}