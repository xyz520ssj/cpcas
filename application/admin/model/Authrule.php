<?php


namespace app\admin\model;


use think\Model;

class Authrule extends Model
{
    public function authRuleTree()
    {
        $authRuleres = $this->order('sort desc')->select();
        return $this->sort($authRuleres);
    }

    public function sort($data, $pid = 0)
    {
        static $arr = array();
        foreach ($data as $k => $v) {
            if ($v['pid'] == $pid) {
                $arr[] = $v;
                $this->sort($data, $v['id']);
            }
        }
        return $arr;
    }

}