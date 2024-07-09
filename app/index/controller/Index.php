<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\facade\Route;

class Index extends Frontend
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    //重定向到fast模块
    public function index()
    {
        return redirect('/h5/index.html');
    }

}
