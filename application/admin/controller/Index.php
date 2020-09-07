<?php

namespace app\admin\controller;

use think\Controller;
use think\facade\Session;

class Index extends Controller
{
    //检测session
    public function logincheck()
    {
        if (Session::get('adminname')) {
            return true;
        } else {
            return false;
        }
    }

    public function index()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            return view('index');
        }
    }

    public function welcome()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            return view('welcome');
        }
    }

}
