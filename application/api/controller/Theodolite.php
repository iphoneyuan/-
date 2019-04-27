<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2019/1/5
 * Time: 11:19
 */

namespace app\api\controller;


use think\Controller;
use think\Db;

class Theodolite extends  Controller
{
    //获取经纬度
    public function index(){
        $result=Db::table("theodolite")->find();
        return json_encode($result);
    }
}