<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Date: 2018/12/21
 * Time: 19:08
 */

namespace app\api\controller;


use think\Cache;
use think\Controller;
use think\Db;
use think\Session;

class Invigilate extends Controller
{
    //任务信息
    public function index(){
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);
        $name=Cache::get('name_id'.$this->wxResult["openid"]);
        $result=Db::table('assignment')->alias('a')
            ->join('classroom b','a.classroomId=b.id')
            ->join('course c','a.courseId=c.id')
            ->where('a.delete',0)
            ->where('main_teacher|second_teacher|walk_teacher_one|walk_teacher_two|watch_teacher_one|watch_teacher_second|watch_teacher_third|watch_teacher_four',$name)
            ->select();

        foreach ($result as $kk=>$value ){

        if($result[$kk]['begindate']>time()){
            $result[$kk]['type']=1;
        }elseif ($result[$kk]['enddate']<time()){
            $result[$kk]['type']=2;
        }else{
            $result[$kk]['type']=0;
        }

        $result[$kk]['begintime']=date('Y-m-d H:i', $result[$kk]['begindate']);
        $result[$kk]['endtime']=date('Y-m-d H:i', $result[$kk]['enddate']);
        }

        return json_encode($result);
    }
}