<?php

namespace app\index\controller;

use think\Controller;

/**
 * 网站入口控制器
 * Class Index
 * @package app\index\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/04/05 10:38
 */
class Index extends Controller
{

    /**
     * 网站入口
     */
    public function index()
    {
        $this->redirect('@admin/login');
    }

    public function qrc()
    {
        $wechat = load_wechat('Extends');
        for ($i = 10; $i < 90; $i++) {
            $qrc = $wechat->getQRCode($i, 1);
            print_r($qrc);
        }

    }

}
