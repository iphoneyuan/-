<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2019/1/10
 * Time: 14:10
 */

namespace app\register\controller;


use controller\BasicAdmin;
use think\Db;

class Person extends BasicAdmin
{
    //个人信息列表
    public function index(){

        $get=$this->request->get();
        $db=Db::table('teacher')->where('delete',0);
            if(!empty($get["name"])){
                $db=$db->where('name_id','like','%'.$get["name"].'%')->paginate(15);
            }else{
                $db=$db->paginate(15);
            }

     $page = preg_replace(['|href="(.*?)"|', '|pagination|'], ['data-open="$1" href="javascript:void(0);"', 'pagination pull-right'], $db->render());
     $list = $db->items();
     $this->assign('page', $page);
     $this->assign('list', $list);
     return $this->fetch('person/index');
    }

    //回收站
    public function recycle(){
        $db=Db::table('teacher')->where('delete',1)->paginate(15);
        $page = preg_replace(['|href="(.*?)"|', '|pagination|'], ['data-open="$1" href="javascript:void(0);"', 'pagination pull-right'], $db->render());
        $list = $db->items();
        $this->assign('page', $page);
        $this->assign('list', $list);
        return $this->fetch('person/recycle');
    }

    //修改密码页面渲染
    public function changepassword(){
        $id=input('id');
        $db=Db::table('teacher')->where('id',$id)->find();
        $this->assign('db', $db);
        return $this->fetch('person/changepassword');
    }
    //修改密码
    public function replacepassword(){
        $all=$this->request->post();
        $all['sure_password']=md5($all['sure_password']);
        $all['password']=md5($all['password']);
        $all['i_Protect_Answer']=md5($all['i_Protect_Answer']);
        $result=Db::table('teacher')->where('id',$all['id'])->find();

       if($result['i_Protect_Answer']==$all['i_Protect_Answer']){
           if($all['sure_password']==$all['password']){
               $db=Db::table('teacher')->where('id',$all['id'])->update(['password'=>$all['password']]);
               if($db){
                   $this->success('修改密码成功','');
               }else{
                   $this->error('抱歉，修改密码失败','');
               }
           }else{
               $this->error('抱歉，两次密码不匹配','');
           }
       } else{
           $this->error('抱歉，密保问题回答错误','');
       }
    }

    //显示修改个人信息页面
    public function showmessage(){
        $id=input('id');
        $db=Db::table('teacher')->where('id',$id)->find();
        if($db['sex']==1){
            $db['sex']='男';
        }else{
            $db['sex']='女';
        }
        $this->assign('db', $db);
        return $this->fetch('person/showmessage');
    }

    //修改个人信息
    public function changemessage(){
        $all=$this->request->post();
        if($all['sex']=='男'){
            $all['sex']='1';
        }else{
            $all['sex']='0';
        }
        $db=Db::table('teacher')->where('id',$all['id'])->update($all);
        if($db){
            $this->success('修改成功','');
        }else{
            $this->error('修改失败','');
        }
    }

    //软删除
    public function ruandel(){
        $id=input('id');
        $db=Db::table('teacher')->where('id',$id)->update(['delete'=>1]);
        if($db){
            $this->success('删除成功','');
        }else{
            $this->error('删除失败','');
        }
    }
    //批量软删除
   public function delruanpersonall(){
       $id=input('id');
       $db=Db::table('teacher')->where('id','in',$id)->update(['delete'=>1]);
       if($db){
          return ['error_code'=>1,'msg'=>'删除成功'];
       }else{
           return ['error_code'=>0,'msg'=>'删除失败'];
       }

   }
    //删除
    public function del(){
        $id=input('id');
        $db=Db::table('teacher')->where('id',$id)->delete();
        if($db){
            $this->success('删除成功','');
        }else{
            $this->error('删除失败','');
        }
    }

    //还原
   public function revice(){
       $id=input('id');
       $db=Db::table('teacher')->where('id',$id)->update(['delete'=>0]);
       if($db){
           $this->success('删除成功','');
       }else{
           $this->error('删除失败','');
       }
   }
   //添加页面
   public function add(){
        return $this->fetch('person/add');
   }

   //添加个人信息
   public function addmessage(){
       $all=$this->request->post();
       $Testing = Db::table('teacher')->where('number',$all['number'])->find();
       if($Testing){
           $this->error('该账号已存在','');
       }else{}
       if($all['sex']=='男'){
           $all['sex']='1';
       }else{
           $all['sex']='0';
       }
       $all['name_id']=$all['name'].'('.$all['number'].')';
       $db=Db::table('teacher')->insert($all);
       if($db){
           $this->success('添加成功','');
       }else{
           $this->error('添加失败','');
       }
   }
//批量删除
   public function delall(){
       $id=input('id');
       $db=Db::table('teacher')->where('id','in',$id)->delete();
       if($db){
           return ['error_code'=>1,'msg'=>'删除成功'];
       }else{
           return ['error_code'=>0,'msg'=>'删除失败'];
       }
   }
//批量还原
   public function delhuanall(){
       $id=input('id');
       $db=Db::table('teacher')->where('id','in',$id)->update(['delete'=>0]);
       if($db){
           return ['error_code'=>1,'msg'=>'还原成功'];
       }else{
           return ['error_code'=>0,'msg'=>'还原失败'];
       }
   }

}