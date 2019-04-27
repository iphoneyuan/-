<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2019/1/4
 * Time: 21:25
 */

namespace app\api\controller;


use think\Cache;
use think\Controller;
use think\Db;
use think\Request;


class Clock extends Controller
{
    //小程序签到页面渲染
    public function index()
    {
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);
        $name = Cache::get('name_id'.$this->wxResult["openid"]);
//        halt($name)
//        echo $name;
        $punch_time=Db::table('setting')->find();
        $result = Db::table('assignment')
                    ->alias('a')
                    ->join('classroom b', 'a.classroomId=b.id')
                    ->join('course c', 'a.courseId=c.id')
                    ->where('begindate', '<', time()+$punch_time['punch_time'])
                    ->where('enddate', '>', time())
                    ->where('delete',0)
                    ->select();
        if ($result) {
            foreach ($result as $kk => $value) {
                if ($result[$kk]['watch_teacher_one'] == $name) {
                    $db = Db::table('assignment')
                            ->alias('a')
                            ->join('classroom b', 'a.classroomId=b.id')
                            ->join('course c', 'a.courseId=c.id')
                            ->where('begindate', '<', time()+$punch_time['punch_time'])
                            ->where('enddate', '>', time())
                            ->where('watch_teacher_one', $name)
                            ->where('delete',0)
                            ->field('a.id,b.door_number,c.name,a.begindate,a.enddate,a.watch_teacher_one,a.watch_teacher_second,a.watch_teacher_third,a.watch_teacher_four,a.status_one,a.status_two,a.status_three,a.status_four')
                            ->find();
                    $db['current_state']=$db['status_one'];
                    $db['punch_time']=date('H:i',($db['begindate']-$punch_time['punch_time']));
                    $db['begintime'] = date('H:i', $db['begindate']);
                    $db['endtime'] = date('H:i', $db['enddate']);
                    return json_encode($db);
                } elseif ($result[$kk]['watch_teacher_second'] == $name) {
                    $db = Db::table('assignment')
                            ->alias('a')
                            ->join('classroom b', 'a.classroomId=b.id')
                            ->join('course c', 'a.courseId=c.id')
                            ->where('begindate', '<', time()+$punch_time['punch_time'])
                            ->where('enddate', '>', time())
                            ->where('watch_teacher_second', $name)
                            ->where('delete',0)
                            ->field('a.id,b.door_number,c.name,a.begindate,a.enddate,a.watch_teacher_one,a.watch_teacher_second,a.watch_teacher_third,a.watch_teacher_four,a.status_one,a.status_two,a.status_three,a.status_four')
                            ->find();
                    $db['current_state']=$db['status_two'];
                    $db['punch_time']=date('H:i',($db['begindate']-$punch_time['punch_time']));
                    $db['begintime'] = date('H:i', $db['begindate']);
                    $db['endtime'] = date('H:i', $db['enddate']);
                    return json_encode($db);
                } elseif ($result[$kk]['watch_teacher_third'] == $name) {
                    $db = Db::table('assignment')
                            ->alias('a')
                            ->join('classroom b', 'a.classroomId=b.id')
                            ->join('course c', 'a.courseId=c.id')
                            ->where('begindate', '<', time()+$punch_time['punch_time'])
                            ->where('enddate', '>', time())
                            ->where('watch_teacher_third', $name)
                            ->where('delete',0)
                            ->field('a.id,b.door_number,c.name,a.begindate,a.enddate,a.watch_teacher_one,a.watch_teacher_second,a.watch_teacher_third,a.watch_teacher_four,a.status_one,a.status_two,a.status_three,a.status_four')
                            ->find();
                    $db['current_state']=$db['status_three'];
                    $db['punch_time']=date('H:i',($db['begindate']-$punch_time['punch_time']));
                    $db['begintime'] = date('H:i', $db['begindate']);
                    $db['endtime'] = date('H:i', $db['enddate']);
                    return json_encode($db);
                }elseif ($result[$kk]['watch_teacher_four'] == $name) {
                    $db = Db::table('assignment')
                            ->alias('a')
                            ->join('classroom b', 'a.classroomId=b.id')
                            ->join('course c', 'a.courseId=c.id')
                            ->where('begindate', '<', time()+$punch_time['punch_time'])
                            ->where('enddate', '>', time())
                            ->where('watch_teacher_four', $name)
                            ->where('delete',0)
                            ->field('a.id,b.door_number,c.name,a.begindate,a.enddate,a.watch_teacher_one,a.watch_teacher_second,a.watch_teacher_third,a.watch_teacher_four,a.status_one,a.status_two,a.status_three,a.status_four')
                            ->find();
                    $db['current_state']=$db['status_four'];
                    $db['punch_time']=date('H:i',($db['begindate']-$punch_time['punch_time']));
                    $db['begintime'] = date('H:i', $db['begindate']);
                    $db['endtime'] = date('H:i', $db['enddate']);
                    return json_encode($db);
                }
            }
        }
    }
    //签到功能
    public function sign(){
        $code = input('code');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'),
            config('wx.appid'),  config('wx.app_secret'), $code);
        $last = curl_get($this->wxLoginUrl);
        $this->wxResult = json_decode($last, true);
        $id=input("Id");
        $s=input("S");
        $formid = input ("formid");
        $name = Cache::get('name_id'.$this->wxResult["openid"]);
        $punch_time=Db::table('setting')->find();
        //进行签到定位检查
        $radius=Db::table('theodolite')->find();
        $time=Db::table('assignment')->where('id',$id)->find();
        //考试时间检查
        if((($time['begindate']-$punch_time['punch_time'])<time())&&($time['begindate']>time())) {
            //进行签到定位检查
            if ($radius['radius'] > $s) {
                $result = Db::table('assignment')
                            ->where('id', $id)
                            ->find();
                if ($result['watch_teacher_one'] == $name) {
                    $db = Db::table('assignment')
                            ->where('id', $id)
                            ->where('watch_teacher_one', $name)
                            ->update(['status_one' => 1]);
                    if ($db) {
                        $this->template($formid,$name,$id);
                        return json_encode(['code' => 1, 'msg' => '签到成功']);
                    } else {
                        return json_encode(['code' => 0, 'msg' => '签到失败']);
                    }
                } elseif ($result['watch_teacher_second'] == $name) {
                    $db = Db::table('assignment')
                            ->where('id', $id)
                            ->where('watch_teacher_second', $name)
                            ->update(['status_two' => 1]);
                    if ($db) {
                        $this->template($formid,$name,$id);
                        return json_encode(['code' => 1, 'msg' => '签到成功']);
                    } else {
                        return json_encode(['code' => 0, 'msg' => '签到失败']);
                    }
                } elseif ($result['watch_teacher_third'] == $name) {
                    $db = Db::table('assignment')
                            ->where('id', $id)
                            ->where('watch_teacher_third', $name)
                            ->update(['status_three' => 1]);
                    if ($db) {
                        $this->template($formid,$name,$id);
                        return json_encode(['code' => 1, 'msg' => '签到成功']);
                    } else {
                        return json_encode(['code' => 0, 'msg' => '签到失败']);
                    }
                } elseif ($result['watch_teacher_four'] == $name) {
                    $db = Db::table('assignment')
                            ->where('id', $id)
                            ->where('watch_teacher_four', $name)
                            ->update(['status_four' => 1]);
                    if ($db) {
                        $this->template($formid,$name,$id);
                        return json_encode(['code' => 1, 'msg' => '签到成功']);
                    } else {
                        return json_encode(['code' => 0, 'msg' => '签到失败']);
                    }
                } else {
                    return json_encode(['code' => 0, 'msg' => '您无考试任务']);
                }
            } else {
                return json_encode(['code' => 2, 'msg' => '您不在考场范围内，无法签到']);
            }
        }else{
            return json_encode(['code' => 0, 'msg' => '请在规定时间内进行签到']);
        }
    }

    //模板信息
    public function template($formid,$name,$id){
        $name = Cache::get('name_id'.$this->wxResult["openid"]);
        $this->wxAccessUrl = sprintf(
            config('wx.access_token'),
            config('wx.appid'), config('wx.app_secret')
        );
        $result = curl_get($this->wxAccessUrl);
        $wxResult = json_decode($result, true);
        $this->wxTemplateUrl = sprintf(
            config('wx.template'),$wxResult['access_token']);
        //查询数据
        $result=Db::table('assignment')->alias('a')
                ->join('classroom b', 'a.classroomId=b.id')
                ->join('course c', 'a.courseId=c.id')
                ->where('a.id', $id)
                ->where('a.watch_teacher_one|a.watch_teacher_second|a.watch_teacher_third|a.watch_teacher_four', $name)
                ->find();
        $params = [
            'touser' => Db::table('teacher')->where('name_id',$name)->find()['openId'],
            'template_id' => 'xkqADNJ5-KBfgmJrIgER_YC49xyE4aZx284_k1MYubo',//模板ID
            'url' => $this->wxTemplateUrl, //点击详情后的URL可以动态定义
            'form_id' => $formid,
            'data' =>
                [
                    //签到时间
                    'keyword1' =>
                        [
                            'value' => date('Y-m-d H:i:s',time()),
                            'color' => '#173177'
                        ],
                    //签到人
                    'keyword2' =>
                        [
                            'value' => $name,
                            'color' => '#FF0000'
                        ],
                    //课程名称
                    'keyword3' =>
                        [
                            'value' => $result['name'],
                            'color' => '#173177'
                        ],
                    //班级
                    'keyword4' =>
                        [
                            'value' => $result['door_number'],
                            'color' => 'blue'
                        ],
                    //学校
                    'keyword5' =>
                        [
                            'value' => '广州大学华软软件学院'.Db::table('teacher')->where('name_id',$name)->find()['department'],
                            'color' => 'blue'
                        ]
                ]
        ];
        $json = json_encode($params,JSON_UNESCAPED_UNICODE);

        return curlPost($this->wxTemplateUrl, $json);
    }

}