<?php
/**
 * Created by PhpStorm.
 * User: sinao
 * Date: 2018/12/10
 * Time: 19:01
 */

namespace app\api\model;


use think\Model;

class banner extends Model
{
    public function banner(){
        $result=self::where('bannerId',1)->find();
        return $result;
    }

}