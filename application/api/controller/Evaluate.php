<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2019/1/19
 * Time: 12:04
 */

namespace app\api\controller;


use controller\BasicAdmin;
use think\Cache;
use think\Db;

class Evaluate extends BasicAdmin
{
    //评论数据渲染接口
    public function index()
    {
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);

        $punch_time=Db::table('setting')->find();
        $name_id = Cache::get('name_id'.$this->wxResult["openid"]);
        $start_time = date('Y-m-d', time());
        $start_time = strtotime($start_time);
        $end_time = $start_time + $punch_time['walk_time'];
        $walk_teacher_one = Db::table('assignment')->where('begindate', '>', $start_time)->where('enddate', '<', $end_time)->where('walk_teacher_one', $name_id)->find();
        $walk_teacher_two = Db::table('assignment')->where('begindate', '>', $start_time)->where('enddate', '<', $end_time)->where('walk_teacher_two', $name_id)->find();
        if ($walk_teacher_one || $walk_teacher_two) {
            $result = Db::table('evaluate')->alias('a')->join('teacher b','a.teacher_id=b.id')->order('begin_time desc')->field('word,name_id,begin_time,end_time')->select();
            foreach ($result as $kk=>$value){
                $result[$kk]['date']=date('Y-m-d',$result[$kk]['begin_time']);
                $result[$kk]['begintime']=date('H:i:s',$result[$kk]['begin_time']);
                $result[$kk]['endtime']=date('H:i:s',$result[$kk]['end_time']);
                unset($result[$kk]['begin_time']);
                unset($result[$kk]['end_time']);
            }
            $db['data']=$result;
            unset($result);
            $db["status"] = 1;
            return json_encode($db);
        } else {
            $result = Db::table('evaluate')->alias('a')->join('teacher b','a.teacher_id=b.id')->order('begin_time desc')->field('word,name_id,begin_time,end_time')->select();
            foreach ($result as $kk=>$value){
                $result[$kk]['date']=date('Y-m-d',$result[$kk]['begin_time']);
                $result[$kk]['begintime']=date('H:i:s',$result[$kk]['begin_time']);
                $result[$kk]['endtime']=date('H:i:s',$result[$kk]['end_time']);
                unset($result[$kk]['begin_time']);
                unset($result[$kk]['end_time']);
            }
            $db['data']=$result;
            unset($result);
            $db["status"] = 0;
            return json_encode($db);
        }
    }
     //评论
    public function upmessage(){
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);
        $all=$this->request->post();
        $name_id=Cache::get("name_id".$this->wxResult["openid"]);
        $teacher_id=Db::table('teacher')->where('name_id',$name_id)->find();
        if($all){
            $TFPicker=explode('~',$all['TFPicker']);
            $result['begin_time']=strtotime($TFPicker[0]);
            $result['end_time']=strtotime($TFPicker[1]);
            $result['word']=$all['Textarea_Content'];
            $result['teacher_id']=$teacher_id['id'];
            $result['create_time'] = time();
            $db=Db::table('evaluate')->insert($result);
            if($db){
                return json_encode(['error_code'=>1,'msg'=>'评论成功']);
            }else{
                return json_encode(['error_code'=>0,'msg'=>'抱歉，评论失败']);
            }
        }else{
            return json_encode(['error_code'=>0,'msg'=>'您尚未传入数据']);
        }
    }

    //获取接口
    public function datatime(){
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);
        $name_id = Cache::get('name_id'.$this->wxResult["openid"]);
        $punch_time=Db::table('setting')->find();
        $start_time = date('Y-m-d', time());
        $start_time = strtotime($start_time);
        $end_time = $start_time + $punch_time['walk_time'];
        $result=Db::table('assignment')->where('walk_teacher_one|walk_teacher_two',$name_id)->where('begindate', '>', $start_time)->where('enddate', '<', $end_time)->field('begindate,enddate')->select();
        if($result){
            foreach ($result as $vo=>$value){
                $result[$vo]['datetime']=date('Y-m-d H:i:s',$result[$vo]['begindate']).'~'.date('Y-m-d H:i:s',$result[$vo]['enddate']);
                unset($result[$vo]['begindate']);
                unset($result[$vo]['enddate']);
            }
            foreach($result as $key=>$value){
                $data[] =implode(',', $value);
            }
            $res = array_unique($data);
            foreach($res as $key){
                $array[] = explode(',', $key);
            }
           return json_encode($res);
        }else{
            return json_encode(['error_code'=>0,'msg'=>'您不是巡考老师，无权限获取日期信息']);
        }

    }
}