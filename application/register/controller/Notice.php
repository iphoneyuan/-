<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2019/3/21
 * Time: 20:34
 */

namespace app\register\controller;


use controller\BasicAdmin;
use think\Db;

class Notice extends BasicAdmin
{
    //渲染页面
    public function index(){
        return $this->fetch();
    }

    public function notice_index(){
        $result=Db::table('notice')->select();
        $this->assign('list',$result);
        return $this->fetch('notice/notice_index');
    }

    
    //上传
    public function upload(){
        $result=$this->request->param();
        $uploadUrl =  "static/notice/";
        $tagUrl = base64_image_content($result['image'],$uploadUrl);
        // 启动事务
        Db::startTrans();
        try{
            $result=Db::table('notice')->insert(['word'=>$result['word'],'title'=>$result['title'],'time'=>time(),'img'=>$tagUrl]);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }
        if($result){
            return ['code'=>'1','msg'=>'发布成功'];
        }
    }

    public function del(){
        $id=input('id');
        $result=Db::table('notice')->where('id',$id)->delete();
        if($result){
            $this->success('删除成功','');
        }else{
            $this->error('删除失败','');
        }
    }
}