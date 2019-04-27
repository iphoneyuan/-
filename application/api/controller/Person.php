<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2019/1/9
 * Time: 9:04
 */

namespace app\api\controller;


use controller\BasicAdmin;
use think\Db;
use think\Request;
use think\Validate;
use think\Cache;

class Person extends BasicAdmin
{
    //上传图片
    public function upimg(){
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);
        $file=request()->file("imgfile");
        if($file){
            $info = $file->validate(['ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'static/image/person');
            if($info) {
                // 成功上传后 获取上传信息
                $a = $info->getSaveName();
                $imgp = str_replace("\\", "/", $a);
                $imgpath = '/image/person/' . $imgp;
                $all = Request::instance()->post();
                $all['imageurl'] = $imgpath;
                $number = Cache::get('number'.$this->wxResult["openid"]);
                $upcommoditytask =Db::table('teacher')->where('number',$number)->update(['imageurl'=>$all['imageurl']]);
                if($upcommoditytask){
                    return json_encode(['code'=>1,'msg'=>'上传成功','url'=>$imgpath]);
                }else{
                    return json_encode(['code'=>0,'msg'=>'上传失败']);
                }
            }else{
               return json_encode(['code'=>0,'msg'=>'上传失败']);
            }
        }else{
            return json_encode(['code'=>0,'msg'=>'接收失败']);
        }
    }

    //上传昵称
    public function nickname(){
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);
        $nickname=input('nickname');
        $number= Cache::get('number'.$this->wxResult["openid"]);
        $result=Db::table('teacher')
                    ->where('number',$number)
                    ->update(['nickname'=>$nickname]);
        if($result){
            return json_encode(['code'=>1,'msg'=>'修改昵称成功','nickname'=>$nickname]);
        }else{
            return json_encode(['code'=>0,'msg'=>'修改昵称失败']);
        }
    }

    //显示密保问题接口
    public function onloadquestion(){
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);
        $number= Cache::get('number'.$this->wxResult["openid"]);
        $result=Db::table('teacher')
                    ->where('number',$number)
                    ->find();
        if($result){
            return json_encode(['code'=>1,'question'=>$result['i_Protect']]);
        }else{
            return json_encode(['code'=>0,'msg'=>'账号不存在']);
        }
    }

    //修改密码
    public function replacepassword(){
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);
        $number= Cache::get('number'.$this->wxResult["openid"]);
        $i_Protect_Answer=input('i_Protect_Answer');
        $i_password=input('i_password');
        $pattern = "/^[A-Za-z0-9]{8,16}$/";
        if (!preg_match($pattern,$i_password)) {
            return json_encode(['code'=>0,'msg'=>'密码不规范']);
        }
        $i_password=md5($i_password);
        $i_Protect_Answer=md5($i_Protect_Answer);
        $result=Db::table('teacher')
                    ->where('number',$number)
                    ->find();
        if($result['i_Protect_Answer']== $i_Protect_Answer){
            $db=Db::table('teacher')
                    ->where('number',$number)
                    ->update(['password'=>$i_password]);
            if($db){
                return json_encode(['code'=>1,'msg'=>'修改密码成功']);
            }else{
                return json_encode(['code'=>0,'msg'=>'修改密码失败']);
            }
        }else{
            return json_encode(['code'=>0,'msg'=>'密保问题错误']);
        }
    }

    //显示个人头像和昵称
    public function showimg(){
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);
        $number= Cache::get('number'.$this->wxResult["openid"]);
        $result=Db::table('teacher')
                    ->where('number',$number)
                    ->find();
        if($result){
            return json_encode($result);
        }else{
            return json_encode(['code'=>0,'msg'=>'账户不存在']);
        }
    }
}