<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2019/1/8
 * Time: 12:18
 */

namespace app\api\controller;


use controller\BasicAdmin;
use think\Db;

class Forgetpassword extends BasicAdmin
{
    //检查账号是否存在
    public function check_number(){
        $i_number=input('i_number');
        $result=Db::table('teacher')->where('number',$i_number)->find();

        if($result){
            if($result['i_Protect']==''){
                return json_encode(['code'=>2,'msg'=>'该账号未登录过，请使用默认密码']);
            }else{
                return json_encode(['code'=>1,'msg'=>'账号存在']);
            }
        }else{
            return json_encode(['code'=>0,'msg'=>'账号不存在']);
        }
    }

    //显示密保问题接口
    public function onloadquestion(){
        $i_number=input('i_number');
        $result=Db::table('teacher')->where('number',$i_number)->find();
        if($result){
            return json_encode(['code'=>1,'question'=>$result['i_Protect']]);
        }else{
            return json_encode(['code'=>0,'msg'=>'账号不存在']);
        }
    }

    //密保问题核验
    public function checkquestion(){
        $i_number=input('i_number');
        $i_Protect_Answer=input('i_Protect_Answer');
        $i_Protect_Answer=md5($i_Protect_Answer);
        $result=Db::table('teacher')->where('number',$i_number)->find();
        if($result['i_Protect_Answer']== $i_Protect_Answer){
            return json_encode(['code'=>1,'msg'=>'恭喜你，核验成功']);
        }else{
            return json_encode(['code'=>0,'msg'=>'抱歉，核验失败']);
        }
    }

    //重设密码
    public function replacepassword(){
        $i_number=input('i_number');
        $i_password=input('i_password');
        $pattern = "/^[A-Za-z0-9]{8,16}$/";
        if (!preg_match($pattern,$i_password)) {
            return json_encode(['code'=>0,'msg'=>'密码不规范']);
        }
        $i_password=md5($i_password);
        $result=Db::table('teacher')->where('number',$i_number)->update(['password'=>$i_password]);
        if($result){
            return json_encode(['code'=>1,'msg'=>'恭喜你，重设密码成功']);
        }else{
            return json_encode(['code'=>0,'msg'=>'抱歉，重设密码失败']);
        }
    }
}