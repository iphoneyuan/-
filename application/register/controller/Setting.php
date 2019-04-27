<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2019/1/21
 * Time: 15:12
 */

namespace app\register\controller;


use controller\BasicAdmin;
use think\Db;

class Setting extends BasicAdmin
{
    //渲染页面
    public function index(){
        $list=Db::table('setting')->find();
        $db=Db::table('theodolite')->find();
        $list['punch_time']=$list['punch_time']/3600;
        $list['walk_time']=$list['walk_time']/3600;
        $this->assign('list',$list);
        $this->assign('db',$db);
        return $this->fetch('setting/index');
    }

    //修改相关系统
    public function up(){
        $result=$this->request->post();
        $result['punch_time']=$this->replacetext($result['punch_time']);
        $result['walk_time']=$this->replacetext($result['walk_time']);
        $result['punch_time']= $result['punch_time']*3600;
        $result['walk_time']=$result['walk_time']*3600;
        $result['radius']=$this->replacetext($result['radius']);

        $punch_time=Db::table('setting')->where('id', 1)->update(['punch_time'=> $result['punch_time'],'walk_time'=>$result['walk_time']]);
        $radius=Db::table('theodolite')->where('id',1)->update(['radius'=>$result['radius']]);
        if($punch_time||$radius){
            $this->success('修改成功','');
        }else{
            $this->error('修改失败','');
        }
    }

    //正则匹配 提取括号中的
    public function replacetext($str)
    {
        $result = array();
        preg_match_all("/((0|[1-9]\d*)(\.\d+)?)/", $str, $result);
        return $result[0][0];
    }
}