<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2019/3/19
 * Time: 18:51
 */

namespace app\api\controller;


use think\Controller;
use think\Db;
use think\Cache;

class Notice extends Controller
{
    //页面列表
    public function index(){
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);
        $result=Db::table('notice')->select();
        foreach($result as $kk=>$value){
            $result[$kk]['time']=date('Y-m-d h:i:s',$result[$kk]['time']);
        }
        return json_encode($result);
    }
    //页面详情
    public function detail(){
        $id=input('id');
        $result=Db::table('notice')->where('id',$id)->find();
        $result['time']=date('Y-m-d h:i:s',$result['time']);
        return json_encode($result);
    }
   //判断通知是否访问
    public function visit(){
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);
       $number=Cache::get('number'.$this->wxResult["openid"]);
       $id=input('id');
       if(!Db::table('not_tea')->where('teacher_number',$number)->where('notice_id',$id)->count()){
           $result=Db::table('not_tea')->insert(['teacher_number'=>$number,'notice_id'=>$id]);
        if($result){
            return json_encode(['code'=>1,'msg'=>'插入成功']);
        }else{
            return json_encode(['code'=>0,'msg'=>'插入失败']);
        }
       }
    }

    //
    public function isvisit(){
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);
        $number=Cache::get('number'.$this->wxResult["openid"]);
        $before=Db::table('not_tea')->where('teacher_number',$number)->count();
        $end=Db::table('notice')->count();
        if($before!=$end){
            return json_encode(['code'=>0,'msg'=>'未查看','num'=>$end-$before]);
        }else{
            return json_encode(['code'=>1,'msg'=>'已查看']);
        }
    }

}