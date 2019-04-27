<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2019/1/18
 * Time: 15:51
 */

namespace app\register\controller;


use controller\BasicAdmin;
use think\Db;

class Position extends BasicAdmin
{
    //地图页面
    public function index(){
        $result=Db::table('theodolite')->find();
        $result1=Db::table('theodolite')->find();
        $this->assign('list',$result);
        $this->assign('radius',$result1);
        return $this->fetch('position/index');
    }

    //上传
    public function upposition(){
        $result=$this->request->post();
        $result=Db::table('theodolite')->where('id',1)->update(['latitude'=>$result['latitude'],'longitude'=>$result['longitude']]);
        if($result){
          return ['error_code'=>1,'msg'=>'恭喜，修改成功'];
        }else{
            return ['error_code'=>0,'msg'=>'抱歉，修改失败'];
        }
    }

}