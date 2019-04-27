<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Date: 2018/12/17
 * Time: 18:37
 */

namespace app\api\controller;

use think\Cache;
use think\Controller;
use think\Db;
use think\db\Where;
use think\Session;


class Login extends Controller
{
    //登录
    public function login(){
        $password = input("password");
        $number = input("name");
        //md5加密
        $password=md5($password);
        $code=input('code');

        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
            $result = curl_get($this->wxLoginUrl);
            $this->wxResult = json_decode($result, true);

        $result = Db::table("teacher")
            ->where("password",$password)
            ->where("number",$number)
            ->find();

        if($result){

            Cache::set('number'. $this->wxResult["openid"],$result['number']);
            Cache::set('name'. $this->wxResult["openid"],$result['name']);
            Cache::set('name_id'. $this->wxResult["openid"],$result['name_id']);

            if($result['type'] == 1){
                // 启动事务
                Db::startTrans();
                try {
                    Db::table('teacher')
                        ->where('number',$number)
                        ->update(['openId'=> $this->wxResult["openid"]]);
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                }
            }
            return json_encode(['code'=>2,'msg'=>'登录成功','type'=>$result['type']]);//正确
        }else{
            $num = Db::table("teacher")
                        ->where("number",$number)
                        ->find();
            if($num){
                return json_encode(['code'=>1,'msg'=>'密码不正确']);//密码不正确
            }else{
                return json_encode(['code'=>0,'msg'=>'账户不正确']);//账户不正确
            }
        }
    }
    //重设登录密码，提交密保问题
    public function reset(){
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);
        $i_Password=input('i_Password');
        $i_repeat_Password=input('i_repeat_Password');
        $i_Protect=input('i_Protect');
        $i_Protect_Answer=input('i_Protect_Answer');
        $i_Protect_Answer=md5($i_Protect_Answer);
        $number=Cache::get("number".$this->wxResult["openid"]);
        $pattern = "/^[A-Za-z0-9]{8,16}$/";
        if (!preg_match($pattern,$i_Password)) {
            return json_encode(['code'=>0,'msg'=>'密码不规范']);
        }
        if( $i_Password!=$i_repeat_Password){
            return json_encode(['code'=>0,'msg'=>'两次输入的密码不一致']);
        }
        $i_Password=md5($i_Password);
        $result=Db::table('teacher')
                    ->where('number',$number)
                    ->update(['password'=>$i_Password,'i_Protect'=>$i_Protect,'i_Protect_Answer'=>$i_Protect_Answer,'type'=>1]);
        if($result){
            return json_encode(['code'=>1,'msg'=>'修改成功']);
        }else{
            return json_encode(['code'=>0,'msg'=>'修改失败']);
        }
    }


    //退出
    public function exitd(){
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);
        // 启动事务
        Db::startTrans();
        try {
            Db::table('teacher')
                ->where('name_id',Cache::get('name_id'.$this->wxResult["openid"]))
                ->update(['openId'=>'']);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }
        $result=Cache::rm('name'.$this->wxResult["openid"]);
        $result1=Cache::rm('number'.$this->wxResult["openid"]);
        $result2=Cache::rm('name_id'.$this->wxResult["openid"]);

        if($result&&$result1&&$result2){
            return json_encode(['code'=>1,'msg'=>'退出成功']);
        }else{
            return json_encode(['code'=>0,'msg'=>'退出失败']);
        }
    }

    public function check_login(){
        $code=input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $result = curl_get($this->wxLoginUrl);
        $wxResult = json_decode($result, true);
        $msqcode = Db::table("teacher")->where('openId',$wxResult['openid'])->find();
        if($msqcode){
            return json_encode(['code'=>1,'msg'=>'已登录']);
        }else{
            return json_encode(['code'=>0,'msg'=>'未登录']);
        }
    }
}