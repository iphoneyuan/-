<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2019/1/7
 * Time: 10:00
 */

namespace app\register\controller;

use controller\BasicAdmin;
use think\Controller;
use think\Db;
use think\Request;

class Assignment extends Controller
{
    //页面
    public function index()
    {
        $get=$this->request->get();
        $db = Db::table('assignment')
                ->alias('a')
                ->join('course b', 'a.courseId=b.id')
                ->join('classroom c', 'a.classroomId=c.id')
                ->where('delete', '0')
                ->field('a.id,b.courseId,a.week,a.day,b.name,c.door_number,a.main_teacher,a.second_teacher,a.walk_teacher_one,a.walk_teacher_two,a.watch_teacher_one,a.watch_teacher_second,a.watch_teacher_four,a.watch_teacher_third,a.begindate,a.enddate');

        if(!empty($get['coursename'])&&!empty($get['teachername'])){
            $db=$db->where('b.name','like','%'.$get['coursename'].'%')
                ->where('a.main_teacher|a.second_teacher|a.walk_teacher_one|a.walk_teacher_two|a.watch_teacher_one|a.watch_teacher_second|a.watch_teacher_third|a.watch_teacher_four','like','%'.$get['teachername'].'%')
                ->paginate(200);
        }elseif(!empty($get['coursename'])){
            $db=$db->where('b.name','like','%'.$get['coursename'].'%')->paginate(200);
        }elseif(!empty($get['teachername'])){
            $db=$db ->where('main_teacher|second_teacher|walk_teacher_one|walk_teacher_two|watch_teacher_one|watch_teacher_second|watch_teacher_third|watch_teacher_four','like','%'.$get['teachername'].'%')->paginate(200);
        }else{
            $db=$db->paginate(20);
        }
        $page = preg_replace(['|href="(.*?)"|', '|pagination|'], ['data-open="$1" href="javascript:void(0);"', 'pagination pull-right'], $db->render());
        $list = $db->items();
        foreach ($list as $kk => $value) {
            $list[$kk]["begindate"] = date('Y-m-d H:i:s', $list[$kk]["begindate"]);
            $list[$kk]["enddate"] = date('Y-m-d H:i:s', $list[$kk]["enddate"]);
        }
        $this->assign('page', $page);
        $this->assign('list', $list);
        return $this->fetch('assignment/index');
    }

    //回收站页面
    public function recycle()
    {
        $db = Db::table('assignment')
                ->alias('a')
                ->join('course b', 'a.courseId=b.id')
                ->join('classroom c', 'a.classroomId=c.id')
                ->where('delete', '1')
                ->field('a.id,a.week,a.day,b.name,c.door_number,a.main_teacher,a.second_teacher,walk_teacher_one,walk_teacher_two,watch_teacher_one,watch_teacher_second,watch_teacher_third,watch_teacher_four,a.begindate,a.enddate')
                ->paginate(15);

        $page = preg_replace(['|href="(.*?)"|', '|pagination|'], ['data-open="$1" href="javascript:void(0);"', 'pagination pull-right'], $db->render());
        $list = $db->items();
        foreach ($list as $kk => $value) {
            $list[$kk]["begindate"] = date('Y-m-d H:i:s', $list[$kk]["begindate"]);
            $list[$kk]["enddate"] = date('Y-m-d H:i:s', $list[$kk]["enddate"]);
        }

        $this->assign('page', $page);
        $this->assign('list', $list);
        return $this->fetch('assignment/recycle');
    }


    //修改页面
    public function edit()
    {
        $id = input('id');
        $list = Db::table('assignment')
                    ->alias('a')
                    ->join('course b', 'a.courseId=b.id')
                    ->join('classroom c', 'a.classroomId=c.id')
                    ->where('a.id', $id)
                    ->field('a.id,b.courseId,a.week,a.day,b.name,c.door_number,a.main_teacher,a.second_teacher,walk_teacher_one,walk_teacher_two,watch_teacher_one,watch_teacher_second,watch_teacher_third,watch_teacher_four,a.begindate,a.enddate')
                    ->find();

        $list["begindate"] = date('Y-m-d H:i:s', $list["begindate"]);
        $list["enddate"] = date('Y-m-d H:i:s', $list["enddate"]);

        $this->assign('list', $list);
        return $this->fetch('edit');
    }

    //提交修改
    public function replace()
    {
        $result = $this->request->post();
        $result['begindate'] = strtotime($result['begindate']);
        $result['enddate'] = strtotime($result['enddate']);
        $course = Db::table('course')
                    ->where('name', $result['name'])
                    ->find();
        $door_number = Db::table('classroom')
                        ->where('door_number', $result['door_number'])
                        ->find();
        $result['courseId'] = $course['id'];
        $result['classroomId'] = $door_number['id'];
        unset($result['name']);
        unset($result['door_number']);
        $result = Db::table('assignment')
                    ->where('id', $result['id'])
                    ->update($result);
        if ($result) {
            $this->success('恭喜你，修改成功', '');
        } else {
            $this->error('抱歉，修改失败', '');
        }

    }

    //软删除
    public function ruandel()
    {
        $id = input('id');
        $result = Db::table('assignment')
                    ->where('id', $id)
                    ->update(['delete' => 1]);
        if ($result) {
            $this->success('恭喜你，删除成功', '');
        } else {
            $this->error('抱歉，删除失败', '');
        }
    }

    //删除
    public function del()
    {
        $id = input('id');
        $result = Db::table('assignment')->where('id', $id)->delete();
        if ($result) {
            $this->success('恭喜你，删除成功', '');
        } else {
            $this->error('抱歉，删除失败', '');
        }
    }

    //还原
    public function reset()
    {
        $id = input('id');
        $result = Db::table('assignment')->where('id', $id)->update(['delete' => 0]);
        if ($result) {
            $this->success('恭喜你，还原成功', '');
        } else {
            $this->error('抱歉，还原失败', '');
        }
    }

    //渲染页面
    public function add()
    {
        $teacher = Db::table('teacher')
            ->where('delete','0')
            ->order('number ASC')
            ->select();
        $classroom = Db::table('classroom')
            ->order('door_number ASC')
            ->field('door_number')
            ->select();
        $course =  Db::table('course')
            ->order('courseId ASC')
            ->select();
        $this->assign('teacher', $teacher);
        $this->assign('classroom', $classroom);
        $this->assign('course', $course);
        return $this->fetch('assignment/add');
    }

    //上传信息
    public function upmessage()
    {
        $all = $this->request->post();
        $all['begindate'] = strtotime($all['begindate']);
        $all['enddate'] = strtotime($all['enddate']);
        $courseId = Db::table('course')->where('courseId', $all['courseId'])->find();
        $door_number = Db::table('classroom')->where('door_number', $all['door_number'])->find();
        $main_teacher = Db::table('teacher')->where('number', $all['main_teacher'])->find();
        $second_teacher = Db::table('teacher')->where('number', $all['second_teacher'])->find();
        $walk_teacher_one = Db::table('teacher')->where('number', $all['walk_teacher_one'])->find();
        $walk_teacher_two = Db::table('teacher')->where('number', $all['walk_teacher_two'])->find();
        $watch_teacher_one = Db::table('teacher')->where('number', $all['watch_teacher_one'])->find();
        $watch_teacher_second = Db::table('teacher')->where('number', $all['watch_teacher_second'])->find();
        $watch_teacher_third = Db::table('teacher')->where('number', $all['watch_teacher_third'])->find();
        $watch_teacher_four = Db::table('teacher')->where('number', $all['watch_teacher_four'])->find();

        if($all['courseId']){}else{
            $this->error('请选择课程', '');
        }

        if($all['door_number']){}else{
            $this->error('请选择考场', '');
        }

        if($all['week']){}else{
            $this->error('请选择周数', '');
        }

        if($all['watch_teacher_one']){
            if(($all['watch_teacher_one'] == $all['watch_teacher_second'])||($all['watch_teacher_one'] == $all['watch_teacher_third'])||($all['watch_teacher_one'] == $all['watch_teacher_four'])){
                $this->error('同场任务不能出现同个老师', '');
            }else{}
        }else{
            $this->error('请选择监考老师1', '');
        }

        if($all['watch_teacher_second']){
            if(($all['watch_teacher_second'] == $all['watch_teacher_third'])||($all['watch_teacher_second'] == $all['watch_teacher_four'])){
                $this->error('同场任务不能出现同个老师', '');
            }else{}
        }else{
            $this->error('请选择监考老师2', '');
        }

        if($all['begindate']||$all['enddate']){}else{
            $this->error('请选择考试时间', '');
        }

        if($all['day']){}else{
            $this->error('请选择星期', '');
        }

        if($courseId){
//            $all['name'] = $courseId['name'];
        }else{
            $this->error('该课程不存在', '');
        }

        if($door_number){}else{
            $this->error('该考场不存在', '');
        }

        if($all['main_teacher']){
            if(($all['main_teacher'] == $all['second_teacher'])||($all['main_teacher'] == $all['walk_teacher_one'])||($all['main_teacher'] == $all['walk_teacher_two'])||($all['main_teacher'] == $all['watch_teacher_one'])||($all['main_teacher'] == $all['watch_teacher_second'])||($all['main_teacher'] == $all['watch_teacher_third'])||($all['main_teacher'] == $all['watch_teacher_four'])){
                $this->error('同场任务不能出现同个老师', '');
            }else{
                if($main_teacher){
                    $all['main_teacher'] = $main_teacher['name_id'];
                }else{
                    $this->error('主考老师不存在', '');
                }
            }
        }else{}
        if($all['second_teacher']){
            if(($all['second_teacher'] == $all['walk_teacher_one'])||($all['second_teacher'] == $all['walk_teacher_two'])||($all['second_teacher'] == $all['watch_teacher_one'])||($all['second_teacher'] == $all['watch_teacher_second'])||($all['second_teacher'] == $all['watch_teacher_third'])||($all['second_teacher'] == $all['watch_teacher_four'])){
                $this->error('同场任务不能出现同个老师', '');
            }else{
                if($second_teacher){
                    $all['second_teacher'] = $second_teacher['name_id'];
                }else{
                    $this->error('副考老师不存在', '');
                }
            }
        }else{}
        if($all['walk_teacher_one']){
            if(($all['walk_teacher_one'] == $all['walk_teacher_two'])||($all['walk_teacher_one'] == $all['watch_teacher_one'])||($all['walk_teacher_one'] == $all['watch_teacher_second'])||($all['walk_teacher_one'] == $all['watch_teacher_third'])||($all['walk_teacher_one'] == $all['watch_teacher_four'])){
                $this->error('同场任务不能出现同个老师', '');
            }else{
                if($walk_teacher_one){
                    $all['walk_teacher_one'] = $walk_teacher_one['name_id'];
                }else{
                    $this->error('巡考老师1不存在', '');
                }
            }
        }else{}
        if($all['walk_teacher_two']){
            if(($all['walk_teacher_two'] == $all['watch_teacher_one'])||($all['walk_teacher_two'] == $all['watch_teacher_second'])||($all['walk_teacher_two'] == $all['watch_teacher_third'])||($all['walk_teacher_two'] == $all['watch_teacher_four'])){
                $this->error('同场任务不能出现同个老师', '');
            }else{
                if($walk_teacher_two){
                    $all['walk_teacher_two'] = $walk_teacher_two['name_id'];
                }else{
                    $this->error('巡考老师2不存在', '');
                }
            }
        }else{}
        if($watch_teacher_one){
            $all['watch_teacher_one'] = $watch_teacher_one['name_id'];
        }else{
            $this->error('监考老师1不存在', '');
        }
        if($watch_teacher_second){
            $all['watch_teacher_second'] = $watch_teacher_second['name_id'];
        }else{
            $this->error('监考老师2不存在', '');
        }
        if($all['watch_teacher_third']){
            if($all['watch_teacher_third'] == $all['watch_teacher_four']){
                $this->error('同场任务不能出现同个老师', '');
            }else{
                if($watch_teacher_third){
                    $all['watch_teacher_third'] = $watch_teacher_third['name_id'];
                }else{
                    $this->error('监考老师3不存在', '');
                }
            }
        }else{}
        if($all['watch_teacher_four']){
            if($watch_teacher_four){
                $all['watch_teacher_four'] = $watch_teacher_four['name_id'];
            }else{
                $this->error('监考老师4不存在', '');
            }
        }else{}
//        halt($all);
        unset($all['name']);
        unset($all['door_number']);
        $all['courseId'] = $courseId['id'];
        $all['classroomId'] = $door_number['id'];
        $result = Db::table('assignment')->insert($all);
        if ($result) {
            $this->success('录入成功', '');
        } else {
            $this->error('录入失败', '');
        }

        //查询该门课程是否存在，存在
//        if ($course) {
//            //查询这个教室是否存在，
//            $classroom = Db::table('classroom')->where('door_number', $all['door_number'])->find();
//            if ($classroom) { //教室存在
//                unset($all['name']);
//                unset($all['door_number']);
//                $all['courseId'] = $course['id'];
//                $all['classroomId'] = $classroom['id'];
//                $result = Db::table('assignment')->insert($all);
//                if ($result) {
//                    $this->success('录入成功', '');
//                } else {
//                    $this->error('录入失败', '');
//                }
//            } else {  //教室不存在
//                $classroomId = Db::table('classroom')->insertGetId(['door_number' => $all['door_number']]);
//                if ($classroomId) {
//                    unset($all['name']);
//                    unset($all['door_number']);
//                    $all['courseId'] = $course['id'];
//                    $all['classroomId'] = $classroomId;
//                    $result = Db::table('assignment')->insert($all);
//                    if ($result) {
//                        $this->success('录入成功', '');
//                    } else {
//                        $this->error('录入失败', '');
//                    }
//                } else {
//                    $this->error('新建课室编号失败', '');
//                }
//            }
//        } else {
//            //课程不存在
//            $courseId = Db::table('course')->insertGetId(['courseId' => $all['courseId'], 'name' => $all['name']]);
//            if ($courseId) {  //课室存在
//                $classroom = Db::table('classroom')->where('door_number', $all['door_number'])->find();
//                if ($classroom) {
//                    unset($all['name']);
//                    unset($all['door_number']);
//                    $all['courseId'] = $courseId;
//                    $all['classroomId'] = $classroom['id'];
//                    $result = Db::table('assignment')->insert($all);
//                    if ($result) {
//                        $this->success('录入成功', '');
//                    } else {
//                        $this->error('录入失败', '');
//                    }
//                } else {
//                    $classroomId = Db::table('classroom')->insertGetId(['door_number' => $all['door_number']]);
//                    if ($classroomId) { //课室不存在
//                        unset($all['name']);
//                        unset($all['door_number']);
//                        $all['courseId'] = $courseId;
//                        $all['classroomId'] = $classroomId;
//                        $result = Db::table('assignment')->insert($all);
//                        if ($result) {
//                            $this->success('录入成功', '');
//                        } else {
//                            $this->error('录入失败', '');
//                        }
//                    } else {
//                        $this->error('新建课室编号失败', '');
//                    }
//                }
//            } else {
//                $this->error('新建课程编号失败', '');
//            }
//        }
    }

    //excle表格导出
    public function output()
    {
        //引入PHPExcel库文件
        vendor('PHPExcel/Classes/PHPExcel');
        //创建对象
        $phpexcel = new \PHPExcel();
        $data = Db::table('assignment')->alias('a')
            ->join('course b', 'a.courseId=b.id')
            ->join('classroom c', 'a.classroomId=c.id')
            ->where('delete', '0')
            ->field('a.id,b.courseId,a.week,a.day,b.name,c.door_number,a.main_teacher,a.second_teacher,walk_teacher_one,walk_teacher_two,watch_teacher_one,watch_teacher_second,watch_teacher_third,watch_teacher_four,a.begindate,a.enddate')
            ->select();
        foreach ($data as $kk =>$val){
            //主考老师
            if($data[$kk]['main_teacher']){
                $data[$kk]['main_teacher'] = '主:'.$data[$kk]['main_teacher'];
            }else{}
            //考试日期
            $data[$kk]['begindate'] = date('Y-m-d', $val['begindate']);
            $data[$kk]['enddate'] = date('Y-m-d', $val['enddate']);
            if ($data[$kk]['begindate'] == $data[$kk]['enddate']){
                $data[$kk]['date'] = $data[$kk]['begindate'];
                $data[$kk]['date'] = str_replace('-0', '/', $data[$kk]['date']);
                $data[$kk]['date'] = str_replace('-', '/', $data[$kk]['date']);
            }else{}
            //考试时间
            $data[$kk]['begindate'] = date('H:i', $val['begindate']);
            $data[$kk]['enddate'] = date('H:i', $val['enddate']);
            $data[$kk]['time'] = $data[$kk]['begindate'].'-'.$data[$kk]['enddate'];
            //考场名称(编号)
            $data[$kk]['door_number'] = $data[$kk]['door_number'].'('.$data[$kk]['door_number'].')';
        }
        $date = date("Y-m-d", time());
        $filename = "考试任务表" . $date . '.xls';
        $phpexcel->getActiveSheet()->setTitle($filename);
        $phpexcel->getActiveSheet()
            ->setCellValue('A1', '考试课程')
            ->setCellValue('B1', '教学周')
            ->setCellValue('C1', '星期')
            ->setCellValue('D1', '考试日期')
            ->setCellValue('E1', '时间')
            ->setCellValue('F1', '主/副考老师')
            ->setCellValue('G1', '巡考老师')
            ->setCellValue('H1', '考场名称(编号)')
            ->setCellValue('I1', '监考老师');
        $i = 2;
        foreach ($data as $k => $val) {
            $kk = $k;
            $kk1 = $kk-1;
            $kkA=0;

            //2019-4-3
            $j = $i + 1;
            $n = $i + 2;
            $m = $i + 3;
            if($data[$k]['watch_teacher_one']!="" && $data[$k]['watch_teacher_second']!=""&&$data[$k]['watch_teacher_third']==""&&$data[$k]['watch_teacher_four']==""){
                $phpexcel->getActiveSheet()
                    ->setCellValue('A' . $i, $val['name'])
                    ->setCellValue('A' . $j, $val['name'])
                    ->setCellValue('B' . $i, $val['week'])
                    ->setCellValue('B' . $j, $val['week'])
                    ->setCellValue('C' . $i, $val['day'])
                    ->setCellValue('C' . $j, $val['day'])
                    ->setCellValue('D' . $i, $val['date'])
                    ->setCellValue('D' . $j, $val['date'])
                    ->setCellValue('E' . $i, $val['time'])
                    ->setCellValue('E' . $j, $val['time'])
                    ->setCellValue('F' . $i, $val['main_teacher'])
                    ->setCellValue('F' . $j, $val['main_teacher'])
                    ->setCellValue('G' . $i, $val['walk_teacher_two'])
                    ->setCellValue('G' . $j, $val['walk_teacher_one'])
                    ->setCellValue('H' . $i, $val['door_number'])
                    ->setCellValue('H' . $j, $val['door_number'])
                    ->setCellValue('I' . $i, $val['watch_teacher_one'])
                    ->setCellValue('I' . $j, $val['watch_teacher_second']);
                $phpexcel->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $phpexcel->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $phpexcel->getActiveSheet()->getStyle('I'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $phpexcel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $phpexcel->getActiveSheet()->getStyle('I'.$i)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $phpexcel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $i = $i+2;
            }elseif($data[$k]['watch_teacher_one']!="" && $data[$k]['watch_teacher_second']!="" &&$data[$k]['watch_teacher_third']!="" &&$data[$k]['watch_teacher_four']==""){
                $phpexcel->getActiveSheet()
                    ->setCellValue('A' . $i, $val['name'])
                    ->setCellValue('A' . $j, $val['name'])
                    ->setCellValue('A' . $n, $val['name'])
                    ->setCellValue('B' . $i, $val['week'])
                    ->setCellValue('B' . $j, $val['week'])
                    ->setCellValue('B' . $n, $val['week'])
                    ->setCellValue('C' . $i, $val['day'])
                    ->setCellValue('C' . $j, $val['day'])
                    ->setCellValue('C' . $n, $val['day'])
                    ->setCellValue('D' . $i, $val['date'])
                    ->setCellValue('D' . $j, $val['date'])
                    ->setCellValue('D' . $n, $val['date'])
                    ->setCellValue('E' . $i, $val['time'])
                    ->setCellValue('E' . $j, $val['time'])
                    ->setCellValue('E' . $n, $val['time'])
                    ->setCellValue('F' . $i, $val['main_teacher'])
                    ->setCellValue('F' . $j, $val['main_teacher'])
                    ->setCellValue('F' . $n, $val['main_teacher'])
                    ->setCellValue('G' . $i, $val['walk_teacher_two'])
                    ->setCellValue('G' . $j, $val['walk_teacher_one'])
                    ->setCellValue('H' . $i, $val['door_number'])
                    ->setCellValue('H' . $j, $val['door_number'])
                    ->setCellValue('H' . $n, $val['door_number'])
                    ->setCellValue('I' . $i, $val['watch_teacher_one'])
                    ->setCellValue('I' . $j, $val['watch_teacher_second'])
                    ->setCellValue('I' . $n, $val['watch_teacher_third']);
                $phpexcel->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $phpexcel->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $phpexcel->getActiveSheet()->getStyle('I'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $phpexcel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $phpexcel->getActiveSheet()->getStyle('I'.$n)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $phpexcel->getActiveSheet()->getStyle('I'.$i)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $phpexcel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $phpexcel->getActiveSheet()->getStyle('I'.$n)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $i = $i+3;
            }elseif($data[$k]['watch_teacher_one'] !="" && $data[$k]['watch_teacher_second']!="" &&$data[$k]['watch_teacher_third']!="" &&$data[$k]['watch_teacher_four'] !=""){
                $phpexcel->getActiveSheet()
                    ->setCellValue('A' . $i, $val['name'])
                    ->setCellValue('A' . $j, $val['name'])
                    ->setCellValue('A' . $n, $val['name'])
                    ->setCellValue('A' . $m, $val['name'])
                    ->setCellValue('B' . $i, $val['week'])
                    ->setCellValue('B' . $j, $val['week'])
                    ->setCellValue('B' . $n, $val['week'])
                    ->setCellValue('B' . $m, $val['week'])
                    ->setCellValue('C' . $i, $val['day'])
                    ->setCellValue('C' . $j, $val['day'])
                    ->setCellValue('C' . $n, $val['day'])
                    ->setCellValue('C' . $m, $val['day'])
                    ->setCellValue('D' . $i, $val['date'])
                    ->setCellValue('D' . $j, $val['date'])
                    ->setCellValue('D' . $n, $val['date'])
                    ->setCellValue('D' . $m, $val['date'])
                    ->setCellValue('E' . $i, $val['time'])
                    ->setCellValue('E' . $j, $val['time'])
                    ->setCellValue('E' . $n, $val['time'])
                    ->setCellValue('E' . $m, $val['time'])
                    ->setCellValue('F' . $i, $val['main_teacher'])
                    ->setCellValue('F' . $j, $val['main_teacher'])
                    ->setCellValue('F' . $n, $val['main_teacher'])
                    ->setCellValue('F' . $m, $val['main_teacher'])
                    ->setCellValue('G' . $i, $val['walk_teacher_two'])
                    ->setCellValue('G' . $j, $val['walk_teacher_one'])
                    ->setCellValue('H' . $i, $val['door_number'])
                    ->setCellValue('H' . $j, $val['door_number'])
                    ->setCellValue('H' . $n, $val['door_number'])
                    ->setCellValue('H' . $m, $val['door_number'])
                    ->setCellValue('I' . $i, $val['watch_teacher_one'])
                    ->setCellValue('I' . $j, $val['watch_teacher_second'])
                    ->setCellValue('I' . $n, $val['watch_teacher_third'])
                    ->setCellValue('I' . $m, $val['watch_teacher_four']);
                $phpexcel->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $phpexcel->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $phpexcel->getActiveSheet()->getStyle('I'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $phpexcel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $phpexcel->getActiveSheet()->getStyle('I'.$n)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $phpexcel->getActiveSheet()->getStyle('I'.$m)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $phpexcel->getActiveSheet()->getStyle('I'.$i)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $phpexcel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $phpexcel->getActiveSheet()->getStyle('I'.$n)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $phpexcel->getActiveSheet()->getStyle('I'.$m)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $i = $i+4;
            }
        }
        $phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(47);
        $phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $phpexcel->getActiveSheet()->getColumnDimension('H')->setWidth(16);
        $phpexcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $phpexcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpexcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $rowCount = $phpexcel->getActiveSheet()->getHighestRow();
        $list=1;
        $li = 1;
        for($list;$list<$rowCount;$list++){
            $next = $list + 1;
            if($list == 1){
            }else{
                if($phpexcel->getActiveSheet()->getCell('A'.$list)->getValue()){
                    while ($phpexcel->getActiveSheet()->getCell('A'.$list)->getValue() == $phpexcel->getActiveSheet()->getCell('A'.$next)->getValue()){
                        $phpexcel->getActiveSheet()->setCellValue('A'.$next,'');
                        $next = $next + 1;
                    }
                    $A = $list;     //9    17
                    $B = $next - 1; //12   24
                    $walkbegin = $A + 2;
                    $count = $B - $A;    //7
                    $walk = 1;
                    $teachernext = $list + 1;
                    if($count<2){
                        if($phpexcel->getActiveSheet()->getCell('G'.$list)->getValue() != "" &&$phpexcel->getActiveSheet()->getCell('G'.$teachernext)->getValue()==""){
                            $phpexcel->getActiveSheet()->mergeCells('G'.$list.':'.'G'.$teachernext);
                        }else{
                        }
                    }else{
                        if($phpexcel->getActiveSheet()->getCell('G'.$list)->getValue() != "" &&$phpexcel->getActiveSheet()->getCell('G'.$teachernext)->getValue()==""){
                            $phpexcel->getActiveSheet()->mergeCells('G'.$list.':'.'G'.$B);
                        }else{
                            for($walk;$walk<$count;$walk++){
                                $phpexcel->getActiveSheet()->setCellValue('G'.$walkbegin,'');
                                $walkbegin = $walkbegin + 1;
                            }
                        }
                    }
                    $phpexcel->getActiveSheet()->mergeCells('A'.$A.':'.'A'.$B); // 5 -6
                    $phpexcel->getActiveSheet()->mergeCells('B'.$A.':'.'B'.$B);
                    $phpexcel->getActiveSheet()->mergeCells('C'.$A.':'.'C'.$B);
                    $phpexcel->getActiveSheet()->mergeCells('D'.$A.':'.'D'.$B);
                    $phpexcel->getActiveSheet()->mergeCells('E'.$A.':'.'E'.$B);
                    $phpexcel->getActiveSheet()->mergeCells('F'.$A.':'.'F'.$B);
                }else{
                }
            }
        }
        //单独抽取课室
        for($li;$li<$rowCount;$li++){
            $next = $li + 1;
            if($li == 1){
            }else{
                if($phpexcel->getActiveSheet()->getCell('H'.$li)->getValue()){
                    while ($phpexcel->getActiveSheet()->getCell('H'.$li)->getValue() == $phpexcel->getActiveSheet()->getCell('H'.$next)->getValue()){
                        $phpexcel->getActiveSheet()->setCellValue('H'.$next,'');
                        $next = $next + 1;
                    }
                    $A = $li;
                    $B = $next - 1;
                    $phpexcel->getActiveSheet()->mergeCells('H'.$A.':'.'H'.$B); // 5 -6
                }else{
                }
            }
        }
        //创建Excel输入对象
        $write = new \PHPExcel_Writer_Excel2007($phpexcel);
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header('Content-Type: application/vnd.ms-excel');
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="考试任务表.xlsx"');
        //header('Content-Disposition: attachment; filename*="utf8"' . $filename . '"');
        header("Content-Transfer-Encoding:binary");
        //ob_clean();
        $write->save('php://output');
    }

    //删除全部数据
    public function RuanDelAll()
    {
     $result=Db::table('assignment')->delete(true);
     if($result){
         $this->success('批量删除成功', '');
     }else{
         $this->error('批量删除失败', '');
     }
    }

    //显示导出页面
    public function showinput()
    {
        return $this->fetch('assignment/showinput');
    }


    //导入
    public function input()
    {
        $file = request()->file('input');
        //引入PHPExcel库文件
        vendor('PHPExcel/Classes/PHPExcel');
        //创建对象
        $phpexcel = new \PHPExcel();
        $info = $file->validate(['ext' => 'xlsx,xls,csv'])->move(ROOT_PATH . 'static' . DS . 'excel');
        if ($info) {
            $exclePath = $info->getSaveName();  //获取文件名
            $file_name = ROOT_PATH . 'static' . DS . 'excel' . DS . $exclePath;   //上传文件的地址
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
            $obj_PHPExcel = $objReader->load($file_name, $encode = 'utf-8');  //加载文件内容,编码utf-8
            echo "<pre>";
            $excel_array = $obj_PHPExcel->getsheet(0)->toArray();
            array_shift($excel_array);  //删除第一个数组(标题);

            $begincourse = $excel_array[0][0];
            $beginweek = $excel_array[0][1];
            $beginday = $excel_array[0][2];
            $begindata = $excel_array[0][3];
            $begintime = $excel_array[0][4];
            $walk_teacher_one = '';
            $count = '';
            $main_teacher = $excel_array[0][5];
            $walk_teacher_two = $excel_array[0][6];
            $beginclassroom = $excel_array[0][7];
            $watch_teacher_one = '';
            $watch_teacher_second = '';
            $watch_teacher_third = '';
            $watch_teacher_four = '';

            foreach ($excel_array as $kk => $value) {
                $excel_array[$kk]['name'] = $excel_array[$kk][0];    //考试课程
                $excel_array[$kk]['week'] = $excel_array[$kk][1];    //教学周
                $excel_array[$kk]['day'] = $excel_array[$kk][2];     //星期
                $excel_array[$kk]['data'] = $excel_array[$kk][3];   //考试日期
                $excel_array[$kk]['time'] = $excel_array[$kk][4];   //考试时间
                $excel_array[$kk]['main_teacher'] = $excel_array[$kk][5]; //主考老师
                $excel_array[$kk]['walk_teacher_one'] = $excel_array[$kk][6];  //巡考老师
                $excel_array[$kk]['door_number'] = $excel_array[$kk][7];     //课室
                $excel_array[$kk]['watch_teacher_one'] = $excel_array[$kk][8];   //监考老师
                unset($excel_array[$kk][0]);
                unset($excel_array[$kk][1]);
                unset($excel_array[$kk][2]);
                unset($excel_array[$kk][3]);
                unset($excel_array[$kk][4]);
                unset($excel_array[$kk][5]);
                unset($excel_array[$kk][6]);
                unset($excel_array[$kk][7]);
                unset($excel_array[$kk][8]);
                unset($excel_array[$kk][9]);

                if ($excel_array[$kk]['name']) {
                    $begincourse = $excel_array[$kk]['name'];
                    $walk_teacher_two = $excel_array[$kk]['walk_teacher_one'];
                    $walk_teacher_one = '';
                } else {
                    $excel_array[$kk]['name'] = $begincourse;
                    $excel_array[$kk]['courseId'] = $this->replacetext($begincourse);
                    if ($excel_array[$kk]['walk_teacher_one']) {
                        $walk_teacher_one = $excel_array[$kk]['walk_teacher_one'];
                    } else {
                        $excel_array[$kk]['walk_teacher_one'] = $walk_teacher_one;
                    }

                    $excel_array[$kk]['walk_teacher_two'] = $walk_teacher_two;
                }

                if ($excel_array[$kk]['week']) {
                    $beginweek = $excel_array[$kk]['week'];
                } else {
                    $excel_array[$kk]['week'] = $beginweek;
                }

                if ($excel_array[$kk]['day']) {
                    $beginday = $excel_array[$kk]['day'];
                } else {
                    $excel_array[$kk]['day'] = $beginday;
                }

                if ($excel_array[$kk]['data']) {
                    $begindata = $excel_array[$kk]['data'];
                } else {
                    $excel_array[$kk]['data'] = $begindata;
                }

                if ($excel_array[$kk]['time']) {
                    $begintime = $excel_array[$kk]['time'];
                } else {
                    $excel_array[$kk]['time'] = $begintime;
                    $time = explode('-', $excel_array[$kk]['time']);
                    $i = 0;
                    foreach ($time as $val) {
                        if ($i == 0) {
                            $excel_array[$kk]['begindate'] = strtotime($excel_array[$kk]['data'] . ' ' . $val);
                            $i++;
                        } else {
                            $excel_array[$kk]['enddate'] = strtotime($excel_array[$kk]['data'] . ' ' . $val);
                            $i = 0;
                        }
                    }
                    unset($excel_array[$kk]['data']);
                    unset($excel_array[$kk]['time']);
                }

                if ($excel_array[$kk]['main_teacher']) {
                    $main_teacher = $excel_array[$kk]['main_teacher'];
                } else {
                    $excel_array[$kk]['main_teacher'] = $main_teacher;
                    $main_name = explode(':', $excel_array[$kk]['main_teacher']);
                    foreach ($main_name as $val) {
                        $excel_array[$kk]['main_teacher'] = $val;
                    }
                }

                if ($excel_array[$kk]['door_number']) {
                    $beginclassroom = $excel_array[$kk]['door_number'];
                    $watch_teacher_one = $excel_array[$kk]['watch_teacher_one'];
                    unset($excel_array[$kk]);
                    $count = 1;
                } else {
                    $excel_array[$kk]['door_number'] = $beginclassroom;
                    $excel_array[$kk]['door_number'] = $this->replacetext($beginclassroom);
                    $count++;
                    if ($count == 2) {
                        $watch_teacher_second = $excel_array[$kk]['watch_teacher_one'];
                        $excel_array[$kk]["watch_teacher_one"] = $watch_teacher_one;
                        $excel_array[$kk]["watch_teacher_second"] = $watch_teacher_second;
                        $excel_array[$kk]["watch_teacher_third"] = "";
                        $excel_array[$kk]["watch_teacher_four"] = "";
                    } elseif ($count == 3) {
                        unset($excel_array[$kk - 1]);
                        $watch_teacher_third = $excel_array[$kk]['watch_teacher_one'];
                        $excel_array[$kk]["watch_teacher_one"] = $watch_teacher_one;
                        $excel_array[$kk]["watch_teacher_second"] = $watch_teacher_second;
                        $excel_array[$kk]["watch_teacher_third"] = $watch_teacher_third;
                        $excel_array[$kk]["watch_teacher_four"] = "";
                    } elseif ($count == 4) {
                        unset($excel_array[$kk - 1]);
                        unset($excel_array[$kk - 2]);
                        $watch_teacher_four = $excel_array[$kk]['watch_teacher_one'];
                        $excel_array[$kk]["watch_teacher_one"] = $watch_teacher_one;
                        $excel_array[$kk]["watch_teacher_second"] = $watch_teacher_second;
                        $excel_array[$kk]["watch_teacher_third"] = $watch_teacher_third;
                        $excel_array[$kk]["watch_teacher_four"] = $watch_teacher_four;
                    }
                }
            }

            //开始将数据进行入库操作
            foreach ($excel_array as $kk => $value) {
                $course = Db::table('course')->where('courseId', $excel_array[$kk]['courseId'])->find();
                //查询该门课程是否存在，存在
                if ($course) {
                    //查询这个教室是否存在，
                    $classroom = Db::table('classroom')->where('door_number', $excel_array[$kk]['door_number'])->find();
                    if ($classroom) { //教室存在
                        unset($excel_array[$kk]['name']);
                        unset($excel_array[$kk]['door_number']);
                        $excel_array[$kk]['courseId'] = $course['id'];
                        $excel_array[$kk]['classroomId'] = $classroom['id'];
                        $assignment = Db::table('assignment')
                            ->where('courseId', $excel_array[$kk]['courseId'])
                            ->where('classroomId', $excel_array[$kk]['classroomId'])
                            ->where('main_teacher', $excel_array[$kk]['main_teacher'])
                            ->where('walk_teacher_one', $excel_array[$kk]['walk_teacher_one'])
                            ->where('walk_teacher_two', $excel_array[$kk]['walk_teacher_two'])
                            ->where('watch_teacher_one', $excel_array[$kk]['watch_teacher_one'])
                            ->where('watch_teacher_second', $excel_array[$kk]['watch_teacher_second'])
                            ->where('watch_teacher_third', $excel_array[$kk]['watch_teacher_third'])
                            ->where('watch_teacher_four', $excel_array[$kk]['watch_teacher_four'])
                            ->where('begindate', $excel_array[$kk]['begindate'])
                            ->where('enddate', $excel_array[$kk]['enddate'])
                            ->where('week', $excel_array[$kk]['week'])
                            ->where('day', $excel_array[$kk]['day'])
                            ->find();
                        if ($assignment) {

                        } else {
                            $result = Db::table('assignment')->insert($excel_array[$kk]);
                            if ($result) {
                            } else {
                                $this->error('录入失败', '');
                            }
                        }
                    } else {  //教室不存在
                        $classroomId = Db::table('classroom')->insertGetId(['door_number' => $excel_array[$kk]['door_number']]);
                        if ($classroomId) {
                            unset($excel_array[$kk]['name']);
                            unset($excel_array[$kk]['door_number']);
                            $excel_array[$kk]['courseId'] = $course['id'];
                            $excel_array[$kk]['classroomId'] = $classroomId;
                            $assignment = Db::table('assignment')
                                ->where('courseId', $excel_array[$kk]['courseId'])
                                ->where('classroomId', $excel_array[$kk]['classroomId'])
                                ->where('main_teacher', $excel_array[$kk]['main_teacher'])
                                ->where('walk_teacher_one', $excel_array[$kk]['walk_teacher_one'])
                                ->where('walk_teacher_two', $excel_array[$kk]['walk_teacher_two'])
                                ->where('watch_teacher_one', $excel_array[$kk]['watch_teacher_one'])
                                ->where('watch_teacher_second', $excel_array[$kk]['watch_teacher_second'])
                                ->where('watch_teacher_third', $excel_array[$kk]['watch_teacher_third'])
                                ->where('watch_teacher_four', $excel_array[$kk]['watch_teacher_four'])
                                ->where('begindate', $excel_array[$kk]['begindate'])
                                ->where('enddate', $excel_array[$kk]['enddate'])
                                ->where('week', $excel_array[$kk]['week'])
                                ->where('day', $excel_array[$kk]['day'])
                                ->find();
                            if ($assignment) {

                            } else {
                                $result = Db::table('assignment')->insert($excel_array[$kk]);
                                if ($result) {
                                } else {
                                    $this->error('录入失败', '');
                                }
                            }
                        } else {
                            $this->error('新建课室编号失败', '');
                        }
                    }
                } else {
                    //课程不存在
                    $courseId = Db::table('course')->insertGetId(['courseId' => $excel_array[$kk]['courseId'], 'name' => $excel_array[$kk]['name']]);
                    if ($courseId) {  //课室存在
                        $classroom = Db::table('classroom')->where('door_number', $excel_array[$kk]['door_number'])->find();
                        if ($classroom) {
                            unset($excel_array[$kk]['name']);
                            unset($excel_array[$kk]['door_number']);
                            $excel_array[$kk]['courseId'] = $courseId;
                            $excel_array[$kk]['classroomId'] = $classroom['id'];
                            $assignment = Db::table('assignment')
                                ->where('courseId', $excel_array[$kk]['courseId'])
                                ->where('classroomId', $excel_array[$kk]['classroomId'])
                                ->where('main_teacher', $excel_array[$kk]['main_teacher'])
                                ->where('walk_teacher_one', $excel_array[$kk]['walk_teacher_one'])
                                ->where('walk_teacher_two', $excel_array[$kk]['walk_teacher_two'])
                                ->where('watch_teacher_one', $excel_array[$kk]['watch_teacher_one'])
                                ->where('watch_teacher_second', $excel_array[$kk]['watch_teacher_second'])
                                ->where('watch_teacher_third', $excel_array[$kk]['watch_teacher_third'])
                                ->where('watch_teacher_four', $excel_array[$kk]['watch_teacher_four'])
                                ->where('begindate', $excel_array[$kk]['begindate'])
                                ->where('enddate', $excel_array[$kk]['enddate'])
                                ->where('week', $excel_array[$kk]['week'])
                                ->where('day', $excel_array[$kk]['day'])
                                ->find();

                            if ($assignment) {

                            } else {
                                $result = Db::table('assignment')->insert($excel_array[$kk]);
                                if ($result) {
                                } else {
                                    $this->error('录入失败', '');
                                }
                            }
                        } else {
                            $classroomId = Db::table('classroom')->insertGetId(['door_number' => $excel_array[$kk]['door_number']]);
                            if ($classroomId) { //课室不存在
                                unset($excel_array[$kk]['name']);
                                unset($excel_array[$kk]['door_number']);
                                $excel_array[$kk]['courseId'] = $courseId;
                                $excel_array[$kk]['classroomId'] = $classroomId;
                                $assignment = Db::table('assignment')
                                    ->where('courseId', $excel_array[$kk]['courseId'])
                                    ->where('classroomId', $excel_array[$kk]['classroomId'])
                                    ->where('main_teacher', $excel_array[$kk]['main_teacher'])
                                    ->where('walk_teacher_one', $excel_array[$kk]['walk_teacher_one'])
                                    ->where('walk_teacher_two', $excel_array[$kk]['walk_teacher_two'])
                                    ->where('watch_teacher_one', $excel_array[$kk]['watch_teacher_one'])
                                    ->where('watch_teacher_second', $excel_array[$kk]['watch_teacher_second'])
                                    ->where('watch_teacher_third', $excel_array[$kk]['watch_teacher_third'])
                                    ->where('watch_teacher_four', $excel_array[$kk]['watch_teacher_four'])
                                    ->where('begindate', $excel_array[$kk]['begindate'])
                                    ->where('enddate', $excel_array[$kk]['enddate'])
                                    ->where('week', $excel_array[$kk]['week'])
                                    ->where('day', $excel_array[$kk]['day'])
                                    ->find();
                                if ($assignment) {

                                } else {
                                    $result = Db::table('assignment')->insert($excel_array[$kk]);
                                    if ($result) {
                                    } else {
                                        $this->error('录入失败', '');
                                    }
                                }
                            } else {
                                $this->error('新建课室编号失败', '');
                            }
                        }
                    } else {
                        $this->error('新建课程编号失败', '');
                    }
                }

            }
            $this->success('批量导入成功', '');
        }
    }

//正则匹配 提取括号中的
    public function replacetext($str)
    {
        $result = array();
        preg_match_all("/\((\w+)\)/", $str, $result);
        return $result[1][0];
    }

    //批量软刪除數據
    public function delruanquestionall(){
        $id=input('id');
        $result=Db::table('assignment')->where('id','in',$id)->update(['delete'=>1]);
        if($result){
            return ['error_code'=>1,'msg'=>'删除成功'];
        }else{
            return ['error_code'=>0,'msg'=>'删除失败'];
        }
    }

    //批量删除数据
    public function delall(){
        $id=input('id');
        $result=Db::table('assignment')->where('id','in',$id)->delete();
        if($result){
            return ['error_code'=>1,'msg'=>'删除成功'];
        }else{
            return ['error_code'=>0,'msg'=>'删除失败'];
        }
    }

    //批量还原数据
    public function delhuanall(){
        $id=input('id');
        $result=Db::table('assignment')->where('id','in',$id)->update(['delete'=>0]);
        if($result){
            return ['error_code'=>1,'msg'=>'删除成功'];
        }else{
            return ['error_code'=>0,'msg'=>'删除失败'];
        }
    }
}