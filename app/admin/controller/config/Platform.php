<?php

namespace app\admin\controller\config;

use app\common\controller\Backend;
use app\admin\model\config\Platform as PlatformModel;
use think\response\Json;

/**
 * 平台管理
 *
 * @icon fa fa-circle-o
 */
class Platform extends Backend
{

    protected $banMethod = ['del' , 'import' , 'destroy' , 'multi' , 'add'];

    /**
     * Platform模型对象
     * @var PlatformModel
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new PlatformModel;

    }


    /**
     * 获取平台列表
     * @return Json
     */
    public function getList(): Json
    {
        $lists = $this->model->order('id' , 'asc')->column('name', 'id');
        return json($lists);
    }

}
