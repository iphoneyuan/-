<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2019/1/19
 * Time: 11:12
 */

namespace app\register\controller;


use controller\BasicAdmin;
use think\Db;

class Evaluate extends BasicAdmin
{
    public $table = 'evaluate';
//渲染列表
    public function index(){
        $result=Db::table('teacher')->alias('a')
               ->join('evaluate b','a.id=b.teacher_id')
               ->select();
       $this->assign('list',$result);
      return $this->fetch('evaluate/index');
    }

    //删除
    public function del(){
      $id=input('id');
      $result=Db::table('evaluate')->where('id',$id)->delete();
      if($result){
          $this->success('删除成功','');
      }else{
          $this->error('删除失败','');
      }
    }
}