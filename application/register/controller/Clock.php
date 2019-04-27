<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2019/1/16
 * Time: 18:36
 */

namespace app\register\controller;


use controller\BasicAdmin;
use think\Controller;
use think\Db;
use think\Request;
class Clock extends BasicAdmin
{
    public function index(){
        $get=$this->request->get();
        $db = Db::table('assignment')->alias('a')
            ->join('course b', 'a.courseId=b.id')
            ->join('classroom c', 'a.classroomId=c.id')
            ->where('delete', '0')
            ->field('a.id,status_one,status_two,status_three,status_four,b.courseId,a.week,a.day,b.name,c.door_number,a.main_teacher,a.second_teacher,walk_teacher_one,walk_teacher_two,watch_teacher_one,watch_teacher_second,watch_teacher_four,watch_teacher_third,a.begindate,a.enddate');

        if(!empty($get['coursename'])&&!empty($get['teachername'])){
            $db=$db->where('b.name','like','%'.$get['coursename'].'%')
                    ->where('watch_teacher_one|watch_teacher_second|watch_teacher_third|watch_teacher_four','like','%'.$get['teachername'].'%')
                   ->paginate(100);

        }elseif(!empty($get['coursename'])){
            $db=$db->where('b.name','like','%'.$get['coursename'].'%')->paginate(20);;
        }elseif(!empty($get['teachername'])){
            $db=$db ->where('watch_teacher_one|watch_teacher_second|watch_teacher_third|watch_teacher_four','like','%'.$get['teachername'].'%')->paginate(100);
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
        return $this->fetch('clock/index');
    }

    public function other(){
        $db = Db::table('assignment')->alias('a')
            ->join('course b', 'a.courseId=b.id')
            ->join('classroom c', 'a.classroomId=c.id')
            ->where('delete', 0)
            ->where('status_one|status_two|status_three|status_four',0)
            ->field('a.id,status_one,status_two,status_three,status_four,b.courseId,a.week,a.day,b.name,c.door_number,a.main_teacher,a.second_teacher,walk_teacher_one,walk_teacher_two,watch_teacher_one,watch_teacher_second,watch_teacher_four,watch_teacher_third,a.begindate,a.enddate')
            ->paginate(20);

        $page = preg_replace(['|href="(.*?)"|', '|pagination|'], ['data-open="$1" href="javascript:void(0);"', 'pagination pull-right'], $db->render());
        $list = $db->items();
        foreach ($list as $kk => $value) {
            $list[$kk]["begindate"] = date('Y-m-d H:i:s', $list[$kk]["begindate"]);
            $list[$kk]["enddate"] = date('Y-m-d H:i:s', $list[$kk]["enddate"]);
            if($list[$kk]["watch_teacher_four"]==''&&$list[$kk]["status_four"]=='0'&&$list[$kk]["watch_teacher_third"]!=''&&$list[$kk]["status_three"]=='1'&&$list[$kk]["watch_teacher_second"]!=''&&$list[$kk]["status_two"]=='1'&&$list[$kk]["watch_teacher_one"]!=''&&$list[$kk]["status_one"]=='1'){
                   unset($list[$kk]);
            }elseif($list[$kk]["watch_teacher_four"]==''&&$list[$kk]["status_four"]=='0'&&$list[$kk]["watch_teacher_third"]==''&&$list[$kk]["status_three"]=='0'&&$list[$kk]["watch_teacher_second"]!=''&&$list[$kk]["status_two"]=='1'&&$list[$kk]["watch_teacher_one"]!=''&&$list[$kk]["status_one"]=='1'){
                   unset($list[$kk]);
            }elseif($list[$kk]["watch_teacher_four"]==''&&$list[$kk]["status_four"]=='0'&&$list[$kk]["watch_teacher_third"]==''&&$list[$kk]["status_three"]=='0'&&$list[$kk]["watch_teacher_second"]==''&&$list[$kk]["status_two"]=='0'&&$list[$kk]["watch_teacher_one"]!=''&&$list[$kk]["status_one"]=='1'){
                   unset($list[$kk]);
            }
        }

        $this->assign('page', $page);
        $this->assign('list', $list);
        return $this->fetch('clock/other');
    }

    //修改状态页面
    public function edit(){
        $id=input('id');
        $list=Db::table('assignment')->where('id',$id)->find();
        $this->assign('list',$list);
        return $this->fetch('clock/edit');
    }
    //修改页面
    public function replace(){
       $result=Request::instance()->post();
       $db=Db::table('assignment')->where('id',$result['id'])->update($result);

       if($db){
           $this->success('修改成功','');
       }else{
           $this->error('修改失败','');
       }
    }
    //导出未签到表
    public function output(){
        //引入PHPExcel库文件
        vendor('PHPExcel/Classes/PHPExcel');
        //创建对象
        $phpexcel = new \PHPExcel();
        $db = Db::table('assignment')->alias('a')
            ->join('course b', 'a.courseId=b.id')
            ->join('classroom c', 'a.classroomId=c.id')
            ->where('delete', 0)
            ->where('status_one|status_two|status_three|status_four',0)
            ->field('a.id,status_one,status_two,status_three,status_four,b.courseId,a.week,a.day,b.name,c.door_number,a.main_teacher,a.second_teacher,walk_teacher_one,walk_teacher_two,watch_teacher_one,watch_teacher_second,watch_teacher_four,watch_teacher_third,a.begindate,a.enddate')
            ->select();
        foreach ($db as $kk =>$val){
            //主考老师
            if($db[$kk]['main_teacher']){
                $db[$kk]['main_teacher'] = '主:'.$db[$kk]['main_teacher'];
            }else{}
            //考试日期
            $db[$kk]['begindate'] = date('Y-m-d', $val['begindate']);
            $db[$kk]['enddate'] = date('Y-m-d', $val['enddate']);
            if ($db[$kk]['begindate'] == $db[$kk]['enddate']){
                $db[$kk]['date'] = $db[$kk]['begindate'];
                $db[$kk]['date'] = str_replace( '-0', '/', $db[$kk]['date']);
                $db[$kk]['date'] = str_replace('-', '/', $db[$kk]['date']);
            }else{}
            //考试时间
            $db[$kk]['begindate'] = date('H:i', $val['begindate']);
            $db[$kk]['enddate'] = date('H:i', $val['enddate']);
            $db[$kk]['time'] = $db[$kk]['begindate'].'-'.$db[$kk]['enddate'];
            //考场名称(编号)
            $db[$kk]['door_number'] = $db[$kk]['door_number'].'('.$db[$kk]['door_number'].')';
        }


        $date = date("Y-m-d", time());
        $filename = "未签到任务表" . $date . '.xls';
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
        foreach ($db as $k => $val) {
            $kk = $k;
            $kk1 = $kk-1;
            $kkA=0;

            //2019-4-3
            $j = $i + 1;
            $n = $i + 2;
            $m = $i + 3;
//            if($db[$k]['watch_teacher_one']!="" && $db[$k]['watch_teacher_second']!=""&&$db[$k]['watch_teacher_third']==""&&$db[$k]['watch_teacher_four']==""){
//                $phpexcel->getActiveSheet()
//                    ->setCellValue('A' . $i, $val['name'])
//                    ->setCellValue('A' . $j, $val['name'])
//                    ->setCellValue('B' . $i, $val['week'])
//                    ->setCellValue('B' . $j, $val['week'])
//                    ->setCellValue('C' . $i, $val['day'])
//                    ->setCellValue('C' . $j, $val['day'])
//                    ->setCellValue('D' . $i, $val['date'])
//                    ->setCellValue('D' . $j, $val['date'])
//                    ->setCellValue('E' . $i, $val['time'])
//                    ->setCellValue('E' . $j, $val['time'])
//                    ->setCellValue('F' . $i, $val['main_teacher'])
//                    ->setCellValue('F' . $j, $val['main_teacher'])
//                    ->setCellValue('G' . $i, $val['walk_teacher_two'])
//                    ->setCellValue('G' . $j, $val['walk_teacher_one'])
//                    ->setCellValue('H' . $i, $val['door_number'])
//                    ->setCellValue('H' . $j, $val['door_number'])
//                    ->setCellValue('I' . $i, $val['watch_teacher_one'])
//                    ->setCellValue('I' . $j, $val['watch_teacher_second'])
//                    ->setCellValue('J' . $i, $val['status_one'])
//                    ->setCellValue('J' . $j, $val['status_two']);
//
//                $phpexcel->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $phpexcel->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
//                $phpexcel->getActiveSheet()->getStyle('I'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $phpexcel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $phpexcel->getActiveSheet()->getStyle('I'.$i)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
//                $phpexcel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
//                $i = $i+2;
//            }elseif($db[$k]['watch_teacher_one']!="" && $db[$k]['watch_teacher_second']!="" &&$db[$k]['watch_teacher_third']!="" &&$db[$k]['watch_teacher_four']==""){
//                $phpexcel->getActiveSheet()
//                    ->setCellValue('A' . $i, $val['name'])
//                    ->setCellValue('A' . $j, $val['name'])
//                    ->setCellValue('A' . $n, $val['name'])
//                    ->setCellValue('B' . $i, $val['week'])
//                    ->setCellValue('B' . $j, $val['week'])
//                    ->setCellValue('B' . $n, $val['week'])
//                    ->setCellValue('C' . $i, $val['day'])
//                    ->setCellValue('C' . $j, $val['day'])
//                    ->setCellValue('C' . $n, $val['day'])
//                    ->setCellValue('D' . $i, $val['date'])
//                    ->setCellValue('D' . $j, $val['date'])
//                    ->setCellValue('D' . $n, $val['date'])
//                    ->setCellValue('E' . $i, $val['time'])
//                    ->setCellValue('E' . $j, $val['time'])
//                    ->setCellValue('E' . $n, $val['time'])
//                    ->setCellValue('F' . $i, $val['main_teacher'])
//                    ->setCellValue('F' . $j, $val['main_teacher'])
//                    ->setCellValue('F' . $n, $val['main_teacher'])
//                    ->setCellValue('G' . $i, $val['walk_teacher_two'])
//                    ->setCellValue('G' . $j, $val['walk_teacher_one'])
//                    ->setCellValue('H' . $i, $val['door_number'])
//                    ->setCellValue('H' . $j, $val['door_number'])
//                    ->setCellValue('H' . $n, $val['door_number'])
//                    ->setCellValue('I' . $i, $val['watch_teacher_one'])
//                    ->setCellValue('I' . $j, $val['watch_teacher_second'])
//                    ->setCellValue('I' . $n, $val['watch_teacher_third'])
//                    ->setCellValue('J' . $i, $val['status_one'])
//                    ->setCellValue('J' . $j, $val['status_two'])
//                    ->setCellValue('J' . $n, $val['status_three']);
//                $phpexcel->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $phpexcel->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
//                $phpexcel->getActiveSheet()->getStyle('I'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $phpexcel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $phpexcel->getActiveSheet()->getStyle('I'.$n)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $phpexcel->getActiveSheet()->getStyle('I'.$i)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
//                $phpexcel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
//                $phpexcel->getActiveSheet()->getStyle('I'.$n)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
//                $i = $i+3;
//            }elseif($db[$k]['watch_teacher_one'] !="" && $db[$k]['watch_teacher_second']!="" &&$db[$k]['watch_teacher_third']!="" &&$db[$k]['watch_teacher_four'] !=""){
//                $phpexcel->getActiveSheet()
//                    ->setCellValue('A' . $i, $val['name'])
//                    ->setCellValue('A' . $j, $val['name'])
//                    ->setCellValue('A' . $n, $val['name'])
//                    ->setCellValue('A' . $m, $val['name'])
//                    ->setCellValue('B' . $i, $val['week'])
//                    ->setCellValue('B' . $j, $val['week'])
//                    ->setCellValue('B' . $n, $val['week'])
//                    ->setCellValue('B' . $m, $val['week'])
//                    ->setCellValue('C' . $i, $val['day'])
//                    ->setCellValue('C' . $j, $val['day'])
//                    ->setCellValue('C' . $n, $val['day'])
//                    ->setCellValue('C' . $m, $val['day'])
//                    ->setCellValue('D' . $i, $val['date'])
//                    ->setCellValue('D' . $j, $val['date'])
//                    ->setCellValue('D' . $n, $val['date'])
//                    ->setCellValue('D' . $m, $val['date'])
//                    ->setCellValue('E' . $i, $val['time'])
//                    ->setCellValue('E' . $j, $val['time'])
//                    ->setCellValue('E' . $n, $val['time'])
//                    ->setCellValue('E' . $m, $val['time'])
//                    ->setCellValue('F' . $i, $val['main_teacher'])
//                    ->setCellValue('F' . $j, $val['main_teacher'])
//                    ->setCellValue('F' . $n, $val['main_teacher'])
//                    ->setCellValue('F' . $m, $val['main_teacher'])
//                    ->setCellValue('G' . $i, $val['walk_teacher_two'])
//                    ->setCellValue('G' . $j, $val['walk_teacher_one'])
//                    ->setCellValue('H' . $i, $val['door_number'])
//                    ->setCellValue('H' . $j, $val['door_number'])
//                    ->setCellValue('H' . $n, $val['door_number'])
//                    ->setCellValue('H' . $m, $val['door_number'])
//                    ->setCellValue('I' . $i, $val['watch_teacher_one'])
//                    ->setCellValue('I' . $j, $val['watch_teacher_second'])
//                    ->setCellValue('I' . $n, $val['watch_teacher_third'])
//                    ->setCellValue('I' . $m, $val['watch_teacher_four'])
//                    ->setCellValue('J' . $i, $val['status_one'])
//                    ->setCellValue('J' . $j, $val['status_two'])
//                    ->setCellValue('J' . $n, $val['status_three'])
//                    ->setCellValue('J' . $m, $val['status_four']);
//                $phpexcel->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $phpexcel->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
//                $phpexcel->getActiveSheet()->getStyle('I'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $phpexcel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $phpexcel->getActiveSheet()->getStyle('I'.$n)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $phpexcel->getActiveSheet()->getStyle('I'.$m)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $phpexcel->getActiveSheet()->getStyle('I'.$i)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
//                $phpexcel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
//                $phpexcel->getActiveSheet()->getStyle('I'.$n)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
//                $phpexcel->getActiveSheet()->getStyle('I'.$m)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
//                $i = $i+4;
//            }
            if($db[$k]['watch_teacher_one']!="" && $db[$k]['watch_teacher_second']!=""&&$db[$k]['watch_teacher_third']==""&&$db[$k]['watch_teacher_four']==""){
                if($db[$k]['status_one']== 1 &&$db[$k]['status_two'] == 1){
                }elseif($db[$k]['status_one']== 0 &&$db[$k]['status_two'] == 1){
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
                        ->setCellValue('I' . $j, '');
                    $i = $i + 2 ;
                }elseif($db[$k]['status_one']== 1 &&$db[$k]['status_two'] == 0){
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
                        ->setCellValue('I' . $i, $val['watch_teacher_second'])
                        ->setCellValue('I' . $j, '');
                    $i = $i + 2 ;
                }else{
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
                    $i = $i + 2 ;
                }
            }elseif($db[$k]['watch_teacher_one']!="" && $db[$k]['watch_teacher_second']!="" &&$db[$k]['watch_teacher_third']!="" &&$db[$k]['watch_teacher_four']==""){
                if($db[$k]['status_one']== 1 &&$db[$k]['status_two'] == 1 && $db[$k]['status_three']== 1){
                }elseif($db[$k]['status_one']== 1 &&$db[$k]['status_two'] == 0 && $db[$k]['status_three']== 1){
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
                        ->setCellValue('I' . $i, $val['watch_teacher_second'])
                        ->setCellValue('I' . $j, '');
                    $i = $i + 2 ;
                }elseif($db[$k]['status_one']== 1 &&$db[$k]['status_two'] == 1 && $db[$k]['status_three']== 0){
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
                        ->setCellValue('I' . $i, $val['watch_teacher_third'])
                        ->setCellValue('I' . $j, '');
                    $i = $i + 2 ;
                }elseif($db[$k]['status_one']== 1 &&$db[$k]['status_two'] == 0 && $db[$k]['status_three']== 0){
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
                        ->setCellValue('I' . $i, $val['watch_teacher_second'])
                        ->setCellValue('I' . $j, $val['watch_teacher_third']);
                    $i = $i + 2 ;
                }elseif($db[$k]['status_one']== 0 &&$db[$k]['status_two'] == 1 && $db[$k]['status_three']== 1){
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
                        ->setCellValue('I' . $j, '')
                        ->setCellValue('I' . $n, '');
                    $i = $i + 2 ;
                }elseif($db[$k]['status_one']== 0 &&$db[$k]['status_two'] == 0 && $db[$k]['status_three']== 1){
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
                    $i = $i + 2 ;
                }elseif($db[$k]['status_one']== 0 &&$db[$k]['status_two'] == 1 && $db[$k]['status_three']== 0){
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
                        ->setCellValue('I' . $j, $val['watch_teacher_third']);
                    $i = $i + 2 ;
                }else{ // 0 0 0
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
                    $i = $i + 3 ;
                }
            }elseif($db[$k]['watch_teacher_one'] !="" && $db[$k]['watch_teacher_second']!="" &&$db[$k]['watch_teacher_third']!="" &&$db[$k]['watch_teacher_four'] !=""){
                if($db[$k]['status_one']== 1 &&$db[$k]['status_two'] == 1 && $db[$k]['status_three']== 1 && $db[$k]['status_four']== 1){
                }elseif($db[$k]['status_one']== 1 &&$db[$k]['status_two'] == 0 && $db[$k]['status_three']== 1 && $db[$k]['status_four']== 1){
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
                    ->setCellValue('I' . $i, $val['watch_teacher_second'])
                    ->setCellValue('I' . $j, '');
                    $i = $i + 2;
                }elseif($db[$k]['status_one']== 1 &&$db[$k]['status_two'] == 0 && $db[$k]['status_three']== 0 && $db[$k]['status_four']== 1){
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
                    ->setCellValue('I' . $i, $val['watch_teacher_second'])
                    ->setCellValue('I' . $j, $val['watch_teacher_third']);
                    $i = $i + 2;
                }elseif($db[$k]['status_one']== 1 &&$db[$k]['status_two'] == 0 && $db[$k]['status_three']== 1 && $db[$k]['status_four']== 0){
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
                        ->setCellValue('I' . $i, $val['watch_teacher_second'])
                        ->setCellValue('I' . $j, $val['watch_teacher_four']);
                    $i = $i + 2;
                }elseif($db[$k]['status_one']== 1 &&$db[$k]['status_two'] == 0 && $db[$k]['status_three']== 0 && $db[$k]['status_four']== 0){
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
                        ->setCellValue('I' . $i, $val['watch_teacher_second'])
                        ->setCellValue('I' . $j, $val['watch_teacher_third'])
                        ->setCellValue('I' . $n, $val['watch_teacher_four']);
                    $i = $i + 3;
                }elseif($db[$k]['status_one']== 1 &&$db[$k]['status_two'] == 1 && $db[$k]['status_three']== 0 && $db[$k]['status_four']== 1){
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
                        ->setCellValue('I' . $i, $val['watch_teacher_third'])
                        ->setCellValue('I' . $j, '');
                    $i = $i + 2;
                }elseif($db[$k]['status_one']== 1 &&$db[$k]['status_two'] == 1 && $db[$k]['status_three']== 0 && $db[$k]['status_four']== 0){
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
                        ->setCellValue('I' . $i, $val['watch_teacher_third'])
                        ->setCellValue('I' . $j, $val['watch_teacher_four']);
                    $i = $i + 2;
                }elseif($db[$k]['status_one']== 1 &&$db[$k]['status_two'] == 1 && $db[$k]['status_three']== 1 && $db[$k]['status_four']== 0){
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
                        ->setCellValue('I' . $i, $val['watch_teacher_four'])
                        ->setCellValue('I' . $j, '');
                    $i = $i + 2;
                }elseif($db[$k]['status_one']== 0 &&$db[$k]['status_two'] == 1 && $db[$k]['status_three']== 1 && $db[$k]['status_four']== 1){
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
                        ->setCellValue('I' . $j, '');
                    $i = $i + 2;
                }elseif($db[$k]['status_one']== 0 &&$db[$k]['status_two'] == 1 && $db[$k]['status_three']== 0 && $db[$k]['status_four']== 1){
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
                        ->setCellValue('I' . $j, $val['watch_teacher_third']);
                    $i = $i + 2;
                }elseif($db[$k]['status_one']== 0 &&$db[$k]['status_two'] == 1 && $db[$k]['status_three']== 1 && $db[$k]['status_four']== 0){
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
                        ->setCellValue('I' . $j, $val['watch_teacher_four']);
                    $i = $i + 2;
                }elseif($db[$k]['status_one']== 0 &&$db[$k]['status_two'] == 1 && $db[$k]['status_three']== 0 && $db[$k]['status_four']== 0){
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
                        ->setCellValue('I' . $j, $val['watch_teacher_third'])
                        ->setCellValue('I' . $n, $val['watch_teacher_four']);
                    $i = $i + 3;
                }elseif($db[$k]['status_one']== 0 &&$db[$k]['status_two'] == 0 && $db[$k]['status_three']== 1 && $db[$k]['status_four']== 1){
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
                    $i = $i + 2;
                }elseif($db[$k]['status_one']== 0 &&$db[$k]['status_two'] == 0 && $db[$k]['status_three']== 0 && $db[$k]['status_four']== 1){
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
                    $i = $i + 3;
                }elseif($db[$k]['status_one']== 0 &&$db[$k]['status_two'] == 0 && $db[$k]['status_three']== 1 && $db[$k]['status_four']== 0){
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
                        ->setCellValue('I' . $n, $val['watch_teacher_four']);
                    $i = $i + 3;
                }else{ //0 0 0 0
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
                    $i = $i + 4;
                }
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
        $listTeacher = 1;
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
        //单独抽取单人监考老师
        for($listTeacher;$listTeacher<$rowCount;$listTeacher++){
            $next = $listTeacher + 1;
            if($listTeacher == 1){
            }else{
                if($phpexcel->getActiveSheet()->getCell('I'.$listTeacher)->getValue()){
                    if($phpexcel->getActiveSheet()->getCell('I'.$next)->getValue()){

                    }else{
                        $A = $listTeacher;
                        $B = $next;
                        $phpexcel->getActiveSheet()->mergeCells('I'.$A.':'.'I'.$B); // 5 -6
                    }
                }else{
                }
            }
        }
        //创建Excel输入对象
        $write = new \PHPExcel_Writer_Excel5($phpexcel);
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header('Content-Type: application/vnd.ms-excel');
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="未签到任务表.xlsx"');
        //header('Content-Disposition: attachment; filename*="utf8"' . $filename . '"');
        header("Content-Transfer-Encoding:binary");
        //ob_clean();
        $write->save('php://output');

    }


}