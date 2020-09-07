<?php


namespace app\admin\controller;

use PhpOffice\PhpSpreadsheet\IOFactory;
use think\App;
use think\Controller;
use think\Db;
use think\facade\Env;
use think\facade\Request;

use app\admin\model\Order as ordermodel;
use app\admin\model\Admin as adminmodel;
use app\admin\model\Expert as expertmodel;
use app\admin\model\Student as studentmodel;
use app\admin\model\Message as messagemodel;
use app\admin\model\Notice as noticemodel;
use app\admin\model\Select as selectmodel;
use app\admin\model\Authgroup as authgroupmodel;
use app\admin\model\Authrule as authrulesmodel;

use PHPExcel;
use PHPExcel_IOFactory;
use think\facade\Session;
use think\process\pipes\Windows;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class Admin extends Controller
{

//    public function load()
//    {
//        $rootpath = Env::get('ROOT_PATH');
//        $path     = $rootpath . 'application\admin\view\alert\alert.html';
//        return $path;
//    }

    //检测session
    protected function logincheck()
    {
        if (Session::get('adminname')) {
            return true;
        } else {
            return false;
        }
    }


    //预约状态与当前时间检测函数
    public function operastatus()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            //系统时间2020-08-29 08:41:06
            //预约时间2020-08-25 18:12:04
            //获取当前时间
            $time = date('Y-m-d h:i:s', time());
            //var_dump($time);
            $res = ordermodel::select();
            foreach ($res as $order) {
                if ($order['time'] <= $time) {
                    ordermodel::where('id', $order['id'])->update(['status' => 2]);
                } else {
                    ordermodel::where('id', $order['id'])->update(['status' => 1]);
                }
            }
        }
    }


    //工具类函数
    public function salt($pwd)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $salt  = "ssjxyz";  //定义一个salt值，程序员规定下来的随机字符串
            $spwd  = $pwd . $salt;  //把密码和salt连接
            $mspwd = md5($spwd);  //执行MD5散列
            return $mspwd;  //返回散列
        }
    }


    //教师//
    //教师相关函数//

    //教师添加模板加载
    public function expertadd()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $list  = selectmodel::field('sex')->select();
            $list2 = selectmodel::field('level')->select();
            $this->assign([
                'sexlist'   => $list,
                'levellist' => $list2
            ]);
            return $this->fetch("admin/expertadd");
        }
    }

    //教师修改界面模板加载
    public function expertedit()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            return $this->fetch("admin/expertedit");
        }
    }

    //教师新增
    public function expertinsert()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            if (Request::isAjax()) {
                $data     = Request::post();
                $password = $data['pwd'];
                $mpwd     = md5($password);
                $mspwd    = $this->salt($password);  //执行MD5散列
                $newdata  = [
                    'id'      => $data['id'],
                    'name'    => $data['name'],
                    'pwd'     => $mpwd,
                    'salt'    => $mspwd,
                    'sex'     => $data['sex'],
                    'mail'    => $data['mail'],
                    'age'     => $data['age'],
                    'level'   => $data['level'],
                    'address' => $data['address'],
                    'phone'   => $data['phone'],
                    'person'  => $data['person'],
                ];
                $res      = expertmodel::insert($newdata);
                if ($res) {
                    $this->success('', '', '1');
                } else {
                    $this->error('', '', '0');
                }
            }
        }
    }

    //学生新增
    public function studentinsert()
    {
        if (Request::isAjax()) {
            $data     = Request::post();
            $password = $data['pwd'];
            $mpwd     = md5($password);
            $mspwd    = $this->salt($password);  //执行MD5散列
            $newdata  = [
                'id'      => $data['id'],
                'name'    => $data['name'],
                'pwd'     => $mpwd,
                'salt'    => $mspwd,
                'sex'     => $data['sex'],
                'mail'    => $data['mail'],
                'age'     => $data['age'],
                'nation'  => $data['nation'],
                'address' => $data['address'],
                'phone'   => $data['phone'],
                'depart'  => $data['depart'],
            ];
            $res      = studentmodel::insert($newdata);
            if ($res) {
                $this->success('', '', '1');
            } else {
                $this->error('', '', '0');
            }
        }
    }

    //显示教师修改面板信息
    public function geteditexpert($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            //显示要修改的信息
            $list         = selectmodel::field('sex')->select();
            $list2        = selectmodel::field('level')->select();
            $list3        = expertmodel::where('id', '=', $id)->find();
            $arr          = array();
            $arr['list']  = $list;
            $arr['list2'] = $list2;
            $arr['list3'] = $list3;
            return json($arr);
        }
    }

    //修改教师   id未修改时
    public function expertupdate()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            if (Request::isAjax()) {
                $data = Request::post();
                //salt化
                $password = $data['pwd'];
                $mpwd     = md5($password);
                $mspwd    = $this->salt($password);  //执行MD5散列
                $newdata  = [
                    'id'      => $data['id'],
                    'name'    => $data['name'],
                    'pwd'     => $mpwd,
                    'salt'    => $mspwd,
                    'sex'     => $data['sex'],
                    'mail'    => $data['mail'],
                    'age'     => $data['age'],
                    'level'   => $data['level'],
                    'address' => $data['address'],
                    'phone'   => $data['phone'],
                    'person'  => $data['person'],
                ];
                $res      = expertmodel::update($newdata);
                if ($res) {
                    $this->success('', '', '1');
                } else {
                    $this->error('', '', '0');
                }
            }
        }
    }

    //新增教师 id唯一性检查
    public function expertidcheck($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            //只有一个值的时候可以直接传参
            $res = expertmodel::where('id', '=', $id)->find();
            if ($res) {
                $this->success('', '', '1');
            } else {
                $this->error('', '', '0');
            }
        }
    }

    //显示教师全部数据  和数据表格重载搜索
    public function expertjson()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $val   = request()->param('value');
            $type  = request()->param('type');
            $page  = input("get.page") ? input("get.page") : 1;
            $page  = intval($page);
            $limit = input("get.limit") ? input("get.limit") : 1;
            $limit = intval($limit);
            $start = $limit * ($page - 1);
            if ($val == '') {
                $acount = expertmodel::select();
                $list   = expertmodel::limit($start, $limit)->select();
                $count  = sizeof($acount);
            } else {
                if ($type == 1) {
                    $data  = expertmodel::where('id', $val)->limit($start, $limit)->select();
                    $list  = expertmodel::where('id', $val)->select();
                    $count = sizeof($list);
                }
                if ($type == 2) {
                    $data  = expertmodel::where('name', 'like', $val)->limit($start, $limit)->select();
                    $list  = expertmodel::where('name', 'like', $val)->select();
                    $count = sizeof($list);
                }
            }
            $data["total"] = $count;
            $data["msg"]   = "";
            $data["code"]  = 0;
            $data["data"]  = $list;
            $rs            = json($data);
            return $rs;
        }
    }

    //教师单个删除
    public function expertdel($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $del = expertmodel::destroy($id);
            if ($del) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }

    //教师批量删除
    public function expertdels($ids)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $dels = expertmodel::destroy($ids);
            if ($dels) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }

    //显示教师整体界面 以及传送下拉列表框的值
    public function expertlist()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $list  = selectmodel::field('sex')->select();
            $list2 = selectmodel::field('level')->select();
            $this->assign([
                'sexlist'   => $list,
                'levellist' => $list2
            ]);
            return $this->fetch("admin/expertlist");
        }
    }


//    //教师excel导入
//    public function expertexcelimport()
//    {
//        if ($this->logincheck() == false) {
//            return $this->fetch("alert/alert");
//        } else {
//            $status = 0;
//            $file   = $this->request->file('excel');
//            $info   = $file->move('../public/excel');
//            if ($info) {
//                $rootpath    = Env::get('ROOT_PATH');
//                $path        = $rootpath . 'public\excel\\' . $info->getSaveName();
//                $objphpexcel = PHPExcel_IOFactory::load($path);
//                $excelarray  = $objphpexcel->getSheet(0)->toArray();
//                array_shift($excelarray);
//                $data = [];
//                foreach ($excelarray as $k => $v) {
//                    //$res = Db::name('expert')->where('id', $v[0])->find();
//                    $res = expertmodel::where('id', $v[0])->find();
//                    if (empty($res)) {
//                        $data[] = [
//                            'id'      => $v[0],
//                            'name'    => $v[1],
//                            'pwd'     => md5($v[2]),
//                            'salt'    => $this->salt($v[2]),
//                            'sex'     => $v[3],
//                            'mail'    => $v[4],
//                            'age'     => $v[5],
//                            'level'   => $v[6],
//                            'address' => $v[7],
//                            'phone'   => $v[8],
//                            'person'  => $v[9]
//                        ];
//                    }
//                }
//                if (empty($data)) {
//                    $status = 0;
//                } else {
//                    $status = 1;
//                }
//                $count = sizeof($data);
//                $res   = Db::name('expert')->insertAll($data);
//                if ($res) {
//                    if ($status == 1) {
//                        $this->success('数据导入成功！', '', $count);
//                    }
//                } else {
//                    if ($status == 0) {
//                        $this->error('数据导入失败！ 全为重复数据！ ', '', '0');
//                    }
//                }
//            } else {
//                return $file->getError();
//            }
//        }
//    }


    //教师excel导入
    public function expertexcelimport()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $status = 0;
            $file   = $this->request->file('excel');
            $info   = $file->move('../public/excel');
            if ($info) {
                $rootpath = Env::get('ROOT_PATH');
                $path     = $rootpath . 'public\excel\\' . $info->getSaveName();
                $excel    = IOFactory::load($path);
                $sheet    = $excel->getSheet(0);
                $row      = $sheet->getHighestRow();
                $col      = $sheet->getHighestColumn();
                $data     = [];
                $flag     = false;
                for ($i = 2; $i <= $row; $i++) {
                    $res = expertmodel::where('id', $excel->getActiveSheet()->getCell("A" . $i)->getValue())->find();
                    if (empty($res)) {
                        $data[] = [
                            'id'      => $excel->getActiveSheet()->getCell("A" . $i)->getValue(),
                            'name'    => $excel->getActiveSheet()->getCell("B" . $i)->getValue(),
                            'pwd'     => md5($excel->getActiveSheet()->getCell("C" . $i)->getValue()),
                            'salt'    => $this->salt($excel->getActiveSheet()->getCell("C" . $i)->getValue()),
                            'sex'     => $excel->getActiveSheet()->getCell("D" . $i)->getValue(),
                            'mail'    => $excel->getActiveSheet()->getCell("E" . $i)->getValue(),
                            'age'     => $excel->getActiveSheet()->getCell("F" . $i)->getValue(),
                            'level'   => $excel->getActiveSheet()->getCell("G" . $i)->getValue(),
                            'address' => $excel->getActiveSheet()->getCell("H" . $i)->getValue(),
                            'phone'   => $excel->getActiveSheet()->getCell("I" . $i)->getValue(),
                            'person'  => $excel->getActiveSheet()->getCell("J" . $i)->getValue()
                        ];
                    }
                }

                if (empty($data)) {
                    $status = 0;
                } else {
                    $status = 1;
                }
                $count = sizeof($data);
                $res   = Db::name('expert')->insertAll($data);
                if ($res) {
                    if ($status == 1) {
                        $this->success('数据导入成功！', '', $count);
                    }
                } else {
                    if ($status == 0) {
                        $this->error('数据导入失败！ 全为重复数据！ ', '', '0');
                    }
                }
            } else {
                return $file->getError();
            }
        }
    }



    //学生//
    //学生相关函数//


    //学生添加模板加载
    public function studentadd()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $list  = selectmodel::field('sex')->select();
            $list2 = selectmodel::field('depart')->select();
            $this->assign([
                'sexlist'    => $list,
                'departlist' => $list2
            ]);
            return $this->fetch("admin/studentadd");
        }
    }


    //显示学生修改面板信息
    public function geteditstudent($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            //显示要修改的信息
            $list         = selectmodel::field('sex')->select();
            $list2        = selectmodel::field('depart')->select();
            $list3        = studentmodel::where('id', '=', $id)->find();
            $arr          = array();
            $arr['list']  = $list;
            $arr['list2'] = $list2;
            $arr['list3'] = $list3;
            return json($arr);
        }

    }

    //修改学生   id未修改时
    public function studentupdate()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            if (Request::isAjax()) {
                $data = Request::post();
                //salt化
                $password = $data['pwd'];
                $mpwd     = md5($password);
                $mspwd    = $this->salt($password);  //执行MD5散列
                $newdata  = [
                    'id'      => $data['id'],
                    'name'    => $data['name'],
                    'pwd'     => $mpwd,
                    'salt'    => $mspwd,
                    'sex'     => $data['sex'],
                    'mail'    => $data['mail'],
                    'age'     => $data['age'],
                    'depart'  => $data['depart'],
                    'address' => $data['address'],
                    'phone'   => $data['phone'],
                    'nation'  => $data['nation'],
                ];
                $res      = studentmodel::update($newdata);
                if ($res) {
                    $this->success('', '', '1');
                } else {
                    $this->error('', '', '0');
                }
            }
        }
    }


    //新增学生 id唯一性检查
    public function studentidcheck($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            //只有一个值的时候可以直接传参
            $res = studentmodel::where('id', '=', $id)->find();
            if ($res) {
                $this->success('', '', '1');
            } else {
                $this->error('', '', '0');
            }
        }
    }


    //学生单个删除
    public function studentdel($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $del = studentmodel::destroy($id);
            if ($del) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }

    //学生批量删除
    public function studentdels($ids)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $dels = studentmodel::destroy($ids);
            if ($dels) {
                $this->success('删除成功', '', '1');
            } else {
                $this->error('删除失败', '', '0');
            }
        }
    }

    //显示学生全部数据  和数据表格重载搜索
    public function studentjson()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $val   = request()->param('value');
            $type  = request()->param('type');
            $page  = input("get.page") ? input("get.page") : 1;
            $page  = intval($page);
            $limit = input("get.limit") ? input("get.limit") : 1;
            $limit = intval($limit);
            $start = $limit * ($page - 1);
            if ($val == '') {
                $acount = studentmodel::select();
                $list   = studentmodel::limit($start, $limit)->select();
                $count  = sizeof($acount);
            } else {
                if ($type == 1) {
                    $data  = studentmodel::where('id', $val)->limit($start, $limit)->select();
                    $list  = studentmodel::where('id', $val)->select();
                    $count = sizeof($list);
                }
                if ($type == 2) {
                    $data  = studentmodel::where('name', 'like', $val)->limit($start, $limit)->select();
                    $list  = studentmodel::where('name', 'like', $val)->select();
                    $count = sizeof($list);
                }
            }
            $data["total"] = $count;
            $data["msg"]   = "";
            $data["code"]  = 0;
            $data["data"]  = $list;
            $rs            = json($data);
            return $rs;
        }
    }

    //显示学生整体界面 以及传送下拉列表框的值
    public function studentlist()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $list  = selectmodel::field('sex')->select();
            $list2 = selectmodel::field('depart')->select();
            $this->assign([
                'sexlist'    => $list,
                'departlist' => $list2
            ]);
            return $this->fetch("admin/studentlist");
        }
    }

    //学生excel导入
    public function studentexcelimport()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $status = 0;
            $file   = $this->request->file('excel');
            $info   = $file->move('../public/excel');
            if ($info) {
                $rootpath = Env::get('ROOT_PATH');
                $path     = $rootpath . 'public\excel\\' . $info->getSaveName();
                $excel    = IOFactory::load($path);
                $sheet    = $excel->getSheet(0);
                $row      = $sheet->getHighestRow();
                $col      = $sheet->getHighestColumn();
                $data     = [];
                for ($i = 2; $i <= $row; $i++) {
                    $res = studentmodel::where('id', $excel->getActiveSheet()->getCell("A" . $i)->getValue())->find();
                    if (empty($res)) {
                        $data[] = [
                            'id'      => $excel->getActiveSheet()->getCell("A" . $i)->getValue(),
                            'name'    => $excel->getActiveSheet()->getCell("B" . $i)->getValue(),
                            'pwd'     => md5($excel->getActiveSheet()->getCell("C" . $i)->getValue()),
                            'salt'    => $this->salt($excel->getActiveSheet()->getCell("C" . $i)->getValue()),
                            'sex'     => $excel->getActiveSheet()->getCell("D" . $i)->getValue(),
                            'mail'    => $excel->getActiveSheet()->getCell("E" . $i)->getValue(),
                            'age'     => $excel->getActiveSheet()->getCell("F" . $i)->getValue(),
                            'nation'  => $excel->getActiveSheet()->getCell("G" . $i)->getValue(),
                            'address' => $excel->getActiveSheet()->getCell("H" . $i)->getValue(),
                            'phone'   => $excel->getActiveSheet()->getCell("I" . $i)->getValue(),
                            'depart'  => $excel->getActiveSheet()->getCell("J" . $i)->getValue()
                        ];
                    }
                }

                if (empty($data)) {
                    $status = 0;
                } else {
                    $status = 1;
                }
                $count = sizeof($data);
                $res   = Db::name('student')->insertAll($data);
                if ($res) {
                    if ($status == 1) {
                        $this->success('数据导入成功！', '', $count);
                    }
                } else {
                    if ($status == 0) {
                        $this->error('数据导入失败！ 全为重复数据！ ', '', '0');
                    }
                }
            } else {
                return $file->getError();
            }
        }
    }


    //预约//
    //预约函数//

    //预约模板加载
    public function orderlist()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $this->operastatus();
            $list  = selectmodel::field('sex')->select();
            $list2 = selectmodel::field('depart')->select();
            $list3 = selectmodel::field('slevel')->select();
            $list4 = expertmodel::field('name')->select();
            $list5 = selectmodel::field('roomnum')->select();

            $this->assign([
                'sexlist'     => $list,
                'departlist'  => $list2,
                'slevellist'  => $list3,
                'enamelist'   => $list4,
                'roomnumlist' => $list5
            ]);
            return $this->fetch("admin/orderlist");
        }
    }

    //显示预约全部数据  和数据表格重载搜索
    public function orderjson()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $val       = request()->param('value');
            $type      = request()->param('type');
            $starttime = request()->param('start');
            $endtime   = request()->param('end');

            $page  = input("get.page") ? input("get.page") : 1;
            $page  = intval($page);
            $limit = input("get.limit") ? input("get.limit") : 1;
            $limit = intval($limit);
            $start = $limit * ($page - 1);
            if ($val == '' && $type == '') {
                $acount = ordermodel::select();
                $list   = ordermodel::limit($start, $limit)->select();
                $count  = sizeof($acount);
            } else if ($val != '' && $type != '') {
                if ($type == 1) {
                    $data  = ordermodel::where('id', $val)->limit($start, $limit)->select();
                    $list  = ordermodel::where('id', $val)->select();
                    $count = sizeof($list);
                }
                if ($type == 2) {
                    $data  = ordermodel::where('sname', 'like', $val)->limit($start, $limit)->select();
                    $list  = ordermodel::where('sname', 'like', $val)->select();
                    $count = sizeof($list);
                }
                if ($type == 3) {
                    $data  = ordermodel::where('ename', 'like', $val)->limit($start, $limit)->select();
                    $list  = ordermodel::where('ename', 'like', $val)->select();
                    $count = sizeof($list);
                }
            } else if ($starttime != '' && $endtime != '') {
                if ($type == 4) {
                    $data  = Db::name('order')->whereTime('time', [$starttime, $endtime])->limit($start, $limit)->select();
                    $list  = Db::name('order')->whereTime('time', [$starttime, $endtime])->select();
                    $count = sizeof($list);;
                }
            }
            $data["total"] = $count;
            $data["msg"]   = "";
            $data["code"]  = 0;
            $data["data"]  = $list;
            $rs            = json($data);
            return $rs;
        }
    }

    //显示预约修改面板信息
    public function geteditorder($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            //显示要修改的信息
            $list         = selectmodel::field('sex')->select();
            $list2        = selectmodel::field('depart')->select();
            $list3        = selectmodel::field('slevel')->select();
            $list4        = ordermodel::where('id', '=', $id)->find();
            $list5        = expertmodel::field('name')->select();
            $arr          = array();
            $arr['list']  = $list;
            $arr['list2'] = $list2;
            $arr['list3'] = $list3;
            $arr['list4'] = $list4;
            $arr['list5'] = $list5;
            return json($arr);
        }
    }


    //修改预约   id未修改时
    public function orderupdate()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            if (Request::isAjax()) {
                $data    = Request::post();
                $newdata = [
                    "id"      => $data['id'],
                    "sname"   => $data['sname'],
                    "sid"     => $data['sid'],
                    "age"     => $data['age'],
                    "sex"     => $data['sex'],
                    "depart"  => $data['depart'],
                    "slevel"  => $data['slevel'],
                    "nation"  => $data['nation'],
                    "phone"   => $data['phone'],
                    "time"    => $data['time'],
                    "address" => $data['address'],
                    "ename"   => $data['ename'],
                    "problem" => $data['problem']
                ];
                $res     = ordermodel::update($newdata);
                if ($res) {
                    $this->success('', '', '1');
                } else {
                    $this->error('', '', '0');
                }
            }
        }
    }


    //预约单个删除
    public function orderdel($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $del = ordermodel::destroy($id);
            if ($del) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }

    //预约批量删除
    public function orderdels($ids)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $dels = ordermodel::destroy($ids);
            if ($dels) {
                $this->success('删除成功', '', '1');
            } else {
                $this->error('删除失败', '', '0');
            }
        }
    }

    //预约修改id
    public function orderinsert()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            if (Request::isAjax()) {
                $data    = Request::post();
                $newdata = [
                    "id"      => $data['id'],
                    "sname"   => $data['sname'],
                    "sid"     => $data['sid'],
                    "age"     => $data['age'],
                    "sex"     => $data['sex'],
                    "depart"  => $data['depart'],
                    "slevel"  => $data['slevel'],
                    "nation"  => $data['nation'],
                    "phone"   => $data['phone'],
                    "time"    => $data['time'],
                    "address" => $data['address'],
                    "ename"   => $data['ename'],
                    "problem" => $data['problem']
                ];
                $res     = ordermodel::insert($newdata);
                if ($res) {
                    $this->success('', '', '1');
                } else {
                    $this->error('', '', '0');
                }
            }
        }

    }


    //修改id唯一性检查
    //新增公告 id唯一性检查
    public function orderidcheck($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            //只有一个值的时候可以直接传参
            $res = ordermodel::where('id', '=', $id)->find();
            if ($res) {
                $this->success('', '', '1');
            } else {
                $this->error('', '', '0');
            }
        }
    }


    //公告//
    //公告函数//

    //公告模板加载
    public function noticelist()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            return $this->fetch("admin/noticelist");
        }
    }

    //显示公告全部数据  和数据表格重载搜索
    public function noticejson()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $val       = request()->param('value');
            $type      = request()->param('type');
            $starttime = request()->param('start');
            $endtime   = request()->param('end');

            $page  = input("get.page") ? input("get.page") : 1;
            $page  = intval($page);
            $limit = input("get.limit") ? input("get.limit") : 1;
            $limit = intval($limit);
            $start = $limit * ($page - 1);
            if ($val == '' && $type == '') {
                $acount = noticemodel::select();
                $list   = noticemodel::limit($start, $limit)->select();
                $count  = sizeof($acount);
            } else if ($val != '' && $type != '') {
                if ($type == 1) {
                    $data  = noticemodel::where('id', $val)->limit($start, $limit)->select();
                    $list  = noticemodel::where('id', $val)->select();
                    $count = sizeof($list);
                }
                if ($type == 2) {
                    $data  = noticemodel::where('content', 'like', '%' . $val . '%')->limit($start, $limit)->select();
                    $list  = noticemodel::where('content', 'like', '%' . $val . '%')->select();
                    $count = sizeof($list);
                }
            } else if ($starttime != '' && $endtime != '') {
                if ($type == 3) {
                    $data  = Db::name('notice')->whereTime('time', [$starttime, $endtime])->limit($start, $limit)->select();
                    $list  = Db::name('notice')->whereTime('time', [$starttime, $endtime])->select();
                    $count = sizeof($list);;
                }
            }
            $data["total"] = $count;
            $data["msg"]   = "";
            $data["code"]  = 0;
            $data["data"]  = $list;
            $rs            = json($data);
            return $rs;
        }
    }

    //公告添加模板加载
    public function noticeadd()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            return $this->fetch("admin/noticeadd");
        }
    }

    //修改公告信息
    public function geteditnotice($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            //显示要修改的信息
            $list        = noticemodel::where('id', '=', $id)->find();
            $arr         = array();
            $arr['list'] = $list;
            return json($arr);
        }
    }


    //新增公告 id唯一性检查
    public function noticeidcheck($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            //只有一个值的时候可以直接传参
            $res = noticemodel::where('id', '=', $id)->find();
            if ($res) {
                $this->success('', '', '1');
            } else {
                $this->error('', '', '0');
            }
        }
    }

    //公告新增
    public function noticeinsert()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            if (Request::isAjax()) {
                $data    = Request::post();
                $newdata = [
                    'id'      => $data['id'],
                    'content' => $data['content'],
                    'time'    => $data['time']
                ];
                $res     = noticemodel::insert($newdata);
                if ($res) {
                    $this->success('', '', '1');
                } else {
                    $this->error('', '', '0');
                }
            }
        }
    }


    //公告单个删除
    public function noticedel($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $del = noticemodel::destroy($id);
            if ($del) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }

    //公告批量删除
    public function noticedels($ids)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $dels = noticemodel::destroy($ids);
            if ($dels) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }


    //修改公告   id未修改时
    public function noticeupdate()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            if (Request::isAjax()) {
                $data    = Request::post();
                $newdata = [
                    'id'      => $data['id'],
                    'content' => $data['content'],
                    'time'    => $data['time']
                ];
                $res     = noticemodel::update($newdata);
                if ($res) {
                    $this->success('', '', '1');
                } else {
                    $this->error('', '', '0');
                }
            }
        }
    }

    //留言板//
    //留言板函数//

    //留言板模板加载
    public function messagelist()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            return $this->fetch("admin/messagelist");
        }
    }


    //显示留言板全部数据  和数据表格重载搜索
    public function messagejson()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $val       = request()->param('value');
            $type      = request()->param('type');
            $starttime = request()->param('start');
            $endtime   = request()->param('end');

            $page  = input("get.page") ? input("get.page") : 1;
            $page  = intval($page);
            $limit = input("get.limit") ? input("get.limit") : 1;
            $limit = intval($limit);
            $start = $limit * ($page - 1);
            if ($val == '' && $type == '') {
                $acount = messagemodel::select();
                $list   = messagemodel::limit($start, $limit)->select();
                $count  = sizeof($acount);
            } else if ($val != '' && $type != '') {
                if ($type == 1) {
                    $data  = messagemodel::where('id', $val)->limit($start, $limit)->select();
                    $list  = messagemodel::where('id', $val)->select();
                    $count = sizeof($list);
                }
                if ($type == 2) {
                    $data  = messagemodel::where('content', 'like', '%' . $val . '%')->limit($start, $limit)->select();
                    $list  = messagemodel::where('content', 'like', '%' . $val . '%')->select();
                    $count = sizeof($list);
                }
            } else if ($starttime != '' && $endtime != '') {
                if ($type == 3) {
                    $data  = Db::name('message')->whereTime('time', [$starttime, $endtime])->limit($start, $limit)->select();
                    $list  = Db::name('message')->whereTime('time', [$starttime, $endtime])->select();
                    $count = sizeof($list);;
                }
            }
            $data["total"] = $count;
            $data["msg"]   = "";
            $data["code"]  = 0;
            $data["data"]  = $list;
            $rs            = json($data);
            return $rs;
        }
    }


    //管理员//
    //管理员函数//
    //加载管理员模板

    public function adminlist()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $list = selectmodel::field('status')->select();
            $this->assign([
                'statuslist' => $list,
            ]);
            return $this->fetch('admin/adminlist');
        }
    }


    //管理员信息//
    //管理员信息函数//


    //管理员添加模板加载
    public function adminadd()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            return $this->fetch("admin/adminadd");
        }
    }

    //显示管理员全部数据  和数据表格重载搜索
    public function adminlistjson()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $val   = request()->param('value');
            $type  = request()->param('type');
            $page  = input("get.page") ? input("get.page") : 1;
            $page  = intval($page);
            $limit = input("get.limit") ? input("get.limit") : 1;
            $limit = intval($limit);
            $start = $limit * ($page - 1);
            if ($val == '' && $type == '') {
                $acount = adminmodel::select();
                $list   = adminmodel::limit($start, $limit)->select();
                $count  = sizeof($acount);
            } else if ($val != '' && $type != '') {
                if ($type == 1) {
                    $data  = adminmodel::where('id', $val)->limit($start, $limit)->select();
                    $list  = adminmodel::where('id', $val)->select();
                    $count = sizeof($list);
                }
                if ($type == 2) {
                    $data  = adminmodel::where('name', 'like', $val)->limit($start, $limit)->select();
                    $list  = adminmodel::where('name', 'like', $val)->select();
                    $count = sizeof($list);
                }
            }
            $data["total"] = $count;
            $data["msg"]   = "";
            $data["code"]  = 0;
            $data["data"]  = $list;
            $rs            = json($data);
            return $rs;
        }
    }

    //新增管理员 id唯一性检查
    public function adminidcheck($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            //只有一个值的时候可以直接传参
            $res = adminmodel::where('id', '=', $id)->find();
            if ($res) {
                $this->success('', '', '1');
            } else {
                $this->error('', '', '0');
            }
        }
    }


    //管理员新增
    public function admininsert()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            if (Request::isAjax()) {
                $data     = Request::post();
                $password = $data['pwd'];
                $mpwd     = md5($password);
                $mspwd    = $this->salt($password);  //执行MD5散列
                $newdata  = [
                    'id'     => $data['id'],
                    'name'   => $data['name'],
                    'pwd'    => $mpwd,
                    'salt'   => $mspwd,
                    'status' => $data['status'],
                ];
                $res      = adminmodel::insert($newdata);
                if ($res) {
                    $this->success('', '', '1');
                } else {
                    $this->error('', '', '0');
                }
            }
        }

    }


    //显示管理员修改面板信息
    public function geteditadmin($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            //显示要修改的信息
            $list         = selectmodel::field('status')->select();
            $list1        = adminmodel::where('id', '=', $id)->find();
            $arr          = array();
            $arr['list']  = $list;
            $arr['list1'] = $list1;
            return json($arr);
        }
    }

    //修改管理员   id未修改时
    public function adminupdate()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            if (Request::isAjax()) {
                $data = Request::post();
                //salt化
                $password = $data['pwd'];
                $mpwd     = md5($password);
                $mspwd    = $this->salt($password);  //执行MD5散列
                $newdata  = [
                    'id'     => $data['id'],
                    'name'   => $data['name'],
                    'pwd'    => $mpwd,
                    'salt'   => $mspwd,
                    'status' => $data['status'],
                ];
                $res      = adminmodel::update($newdata);
                if ($res) {
                    $this->success('', '', '1');
                } else {
                    $this->error('', '', '0');
                }
            }
        }
    }


    //管理员批量删除
    public function admindels($ids)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $dels = adminmodel::destroy($ids);
            if ($dels) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }

    //管理员单个删除
    public function admindel($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $del = adminmodel::destroy($id);
            if ($del) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }

    //退出
    public function logout()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            Session::delete('adminname');
            Session::delete('enum');
            Session::delete('snum');
            Session::delete('onum');
            Session::delete('mnum');
            Session::delete('nnum');
            Session::delete('anum');
            return $this->fetch('login/login');
        }
    }


    ////权限管理部分////
    ///
    //用户组表
    public function adminrole()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $list = selectmodel::field('status')->select();
            $this->assign([
                'statuslist' => $list,
            ]);
            return $this->fetch('admin/adminrole');
        }
    }


    //显示用户组全部数据  和数据表格重载搜索
    public function adminrolejson()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $val   = request()->param('value');
            $type  = request()->param('type');
            $page  = input("get.page") ? input("get.page") : 1;
            $page  = intval($page);
            $limit = input("get.limit") ? input("get.limit") : 1;
            $limit = intval($limit);
            $start = $limit * ($page - 1);
            if ($val == '' && $type == '') {
                $acount = authgroupmodel::select();
                $list   = authgroupmodel::limit($start, $limit)->select();
                $count  = sizeof($acount);
            } else if ($val != '' && $type != '') {
                if ($type == 1) {
                    $data  = authgroupmodel::where('id', $val)->limit($start, $limit)->select();
                    $list  = authgroupmodel::where('id', $val)->select();
                    $count = sizeof($list);
                }
                if ($type == 2) {
                    $data  = authgroupmodel::where('title', 'like', $val)->limit($start, $limit)->select();
                    $list  = authgroupmodel::where('title', 'like', $val)->select();
                    $count = sizeof($list);
                }
            }
            $data["total"] = $count;
            $data["msg"]   = "";
            $data["code"]  = 0;
            $data["data"]  = $list;
            $rs            = json($data);
            return $rs;
        }
    }

    //用户组添加模板
    public function adminroleadd()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            return $this->fetch("admin/adminroleadd");
        }
    }

    //新增用户组 id唯一性检查
    public function adminroleidcheck($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            //只有一个值的时候可以直接传参
            $res = authgroupmodel::where('id', '=', $id)->find();
            if ($res) {
                $this->success('', '', '1');
            } else {
                $this->error('', '', '0');
            }
        }
    }

    //用户组新增
    public function adminroleinsert()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            if (Request::isAjax()) {
                $data    = Request::post();
                $newdata = [
                    'id'     => $data['id'],
                    'title'  => $data['title'],
                    'rules'  => $data['rules'],
                    'status' => $data['status'],
                ];
                $res     = authgroupmodel::insert($newdata);
                if ($res) {
                    $this->success('', '', '1');
                } else {
                    $this->error('', '', '0');
                }
            }
        }
    }

    //用户组批量删除
    public function adminroledels($ids)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            // $dels = adminmodel::destroy($ids);
            $dels = authgroupmodel::destroy($ids);
            if ($dels) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }

    //用户组单个删除
    public function adminroledel($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $del = authgroupmodel::destroy($id);
            if ($del) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }

    //显示管理员修改面板信息
    public function geteditroleadmin($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            //显示要修改的信息
            $list = selectmodel::field('status')->select();
            // $list1        = adminmodel::where('id', '=', $id)->find();
            $list1        = authgroupmodel::where('id', '=', $id)->find();
            $arr          = array();
            $arr['list']  = $list;
            $arr['list1'] = $list1;
            return json($arr);
        }
    }

    //修改管理员   id未修改时
    public function adminroleupdate()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            if (Request::isAjax()) {
                $data    = Request::post();
                $newdata = [
                    'id'     => $data['id'],
                    'title'  => $data['title'],
                    'rules'  => $data['rules'],
                    'status' => $data['status'],
                ];
                $res     = authgroupmodel::update($newdata);
                if ($res) {
                    $this->success('', '', '1');
                } else {
                    $this->error('', '', '0');
                }
            }
        }
    }

    //权限管理

    //权限表
    public function adminrules()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $list = selectmodel::field('status')->select();
            $this->assign([
                'statuslist' => $list,
            ]);
            return $this->fetch('admin/adminrules');
        }
    }


    //显示权限全部数据  和数据表格重载搜索
    public function adminrulesjson()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $val   = request()->param('value');
            $type  = request()->param('type');
            $page  = input("get.page") ? input("get.page") : 1;
            $page  = intval($page);
            $limit = input("get.limit") ? input("get.limit") : 1;
            $limit = intval($limit);
            $start = $limit * ($page - 1);
            if ($val == '' && $type == '') {
                $acount = authrulesmodel::select();
                $list   = authrulesmodel::limit($start, $limit)->select();
                $count  = sizeof($acount);
            } else if ($val != '' && $type != '') {
                if ($type == 1) {
                    $data  = authrulesmodel::where('name', $val)->limit($start, $limit)->select();
                    $list  = authrulesmodel::where('name', $val)->limit($start, $limit)->select();
                    $count = sizeof($list);
                }
                if ($type == 2) {
                    $data  = authrulesmodel::where('title', $val)->limit($start, $limit)->select();
                    $list  = authrulesmodel::where('title', $val)->limit($start, $limit)->select();
                    $count = sizeof($list);
                }
            }

            $data["total"] = $count;
            $data["msg"]   = "";
            $data["code"]  = 0;
            $data["data"]  = $list;
            $rs            = json($data);
            return $rs;
        }
    }

    //权限添加模板
    public function adminrulesadd()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $list = authrulesmodel::select();
            $this->assign([
                'titlelist' => $list,
            ]);
            return $this->fetch("admin/adminrulesadd");
        }
    }

    //新增用户组 id唯一性检查
    public function adminrulesidcheck($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            //只有一个值的时候可以直接传参
            $res = authgroupmodel::where('id', '=', $id)->find();
            if ($res) {
                $this->success('', '', '1');
            } else {
                $this->error('', '', '0');
            }
        }
    }

    //用户组新增
    public function adminrulesinsert()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            if (Request::isAjax()) {
                $data   = Request::post();
                $plevel = authrulesmodel::where('id', $data['pid'])->find();
                if ($plevel) {
                    $data['level'] = $plevel['level'] + 1;
                } else {
                    $plevel['level'] = 0;
                }
                $res = authrulesmodel::insert($data);
                if ($res) {
                    $this->success('', '', '1');
                } else {
                    $this->error('', '', '0');
                }
            }
        }
    }

    //用户组批量删除
    public function adminrulesdels($ids)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            // $dels = adminmodel::destroy($ids);
            $dels = authgroupmodel::destroy($ids);
            if ($dels) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }

    //用户组单个删除
    public function adminrulesdel($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            $del = authgroupmodel::destroy($id);
            if ($del) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }

    //显示管理员修改面板信息
    public function geteditrulesadmin($id)
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            //显示要修改的信息
            $list = selectmodel::field('status')->select();
            // $list1        = adminmodel::where('id', '=', $id)->find();
            $list1        = authgroupmodel::where('id', '=', $id)->find();
            $arr          = array();
            $arr['list']  = $list;
            $arr['list1'] = $list1;
            return json($arr);
        }
    }

    //修改管理员   id未修改时
    public function adminrulesupdate()
    {
        if ($this->logincheck() == false) {
            return $this->fetch("alert/alert");
        } else {
            if (Request::isAjax()) {
                $data    = Request::post();
                $newdata = [
                    'id'     => $data['id'],
                    'title'  => $data['title'],
                    'rules'  => $data['rules'],
                    'status' => $data['status'],
                ];
                $res     = authgroupmodel::update($newdata);
                if ($res) {
                    $this->success('', '', '1');
                } else {
                    $this->error('', '', '0');
                }
            }
        }
    }


}