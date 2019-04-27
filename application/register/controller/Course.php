<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2019/1/18
 * Time: 12:09
 */

namespace app\register\controller;


use controller\BasicAdmin;
use think\Db;

class Course extends BasicAdmin
{
    //显示课程信息
     public function index(){
         $get=$this->request->get();
         $db = Db::table('course')->order('courseId ASC')->paginate(20);
         $page = preg_replace(['|href="(.*?)"|', '|pagination|'], ['data-open="$1" href="javascript:void(0);"', 'pagination pull-right'], $db->render());
         $list = $db->items();
         foreach ($list as $kk => $value) {
             $result = array();
             preg_match_all("/^([^\(]*)\(.*$/", $list[$kk]['name'], $result);
             $list[$kk]['name'] = $result[1][0];
         }
         $this->assign('page', $page);
         $this->assign('list', $list);
         return $this->fetch('course/index');
     }

     //删除课程信息
    public function del(){
         $id=input('id');
         $check=Db::table('assignment')->where('courseId',$id)->find();
         if($check){
             $this->error('该课程暂无法删除','');
         }else{
             $result=Db::table('course')->where('id',$id)->delete();
             if($result){
                 $this->success('恭喜你，成功删除该课程信息','');
             }else{
                 $this->error('抱歉，删除失败','');
             }
         }
    }
    public function add()
    {
        return $this->fetch('course/add');
    }
    public function addcourse(){
        $coursename=$this->request->post();
        $course=Db::table('course')->where('courseId',$coursename['courseId'])->find();
        if($course){
            $this->error('该课程已存在','');
        }else{
            $coursename['name'] = $coursename['name'].'('.$coursename['courseId'].')-本';
            $db=Db::table('course')->insert($coursename);
            if($db){
                $this->success('添加成功','');
            }else{
                $this->success('添加失败','');
            }
        }
    }
}