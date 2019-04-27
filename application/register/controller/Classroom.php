<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2019/1/18
 * Time: 15:19
 */

namespace app\register\controller;


use controller\BasicAdmin;
use think\Db;

class Classroom extends BasicAdmin
{
    public function index(){
        $get=$this->request->get();
        $db = Db::table('classroom')->order('door_number ASC')->paginate(20);

        $page = preg_replace(['|href="(.*?)"|', '|pagination|'], ['data-open="$1" href="javascript:void(0);"', 'pagination pull-right'], $db->render());
        $list = $db->items();
        $this->assign('page', $page);
        $this->assign('list', $list);
        return $this->fetch('classroom/index');
    }

    //删除课室信息
    public function del(){
        $id=input('id');
        $check=Db::table('assignment')->where('classroomId',$id)->find();
        if($check){
            $this->error('该课室暂无法删除','');
        }else{
            $result=Db::table('classroom')->where('id',$id)->delete();
            if($result){
                $this->success('恭喜你，成功删除该课室信息','');
            }else{
                $this->error('抱歉，删除失败','');
            }
        }
    }
    public function add()
    {
        return $this->fetch('classroom/add');
    }
    public function addclassroom(){
        $classroomname=$this->request->post();
        $classroom=Db::table('classroom')->where('door_number',$classroomname['door_number'])->find();
        if($classroom){
            $this->error('该课室已存在','');
        }else{
            $classroomname['name'] = $classroomname['name'].'('.$classroomname['door_number'].')';
            $db=Db::table('classroom')->insert($classroomname);
            if($db){
                $this->success('添加成功','');
            }else{
                $this->success('添加失败','');
            }
        }
    }
}