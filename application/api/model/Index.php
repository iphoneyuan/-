<?php
namespace app\api\model;

use think\Controller;
use think\Db;

class Index extends Controller
{
    public function index()
    {

        $banner=Db::table("banner")->select();
        return json_encode($banner[0]);

    }


}
