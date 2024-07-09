<?php
namespace app\api\controller;


use app\common\controller\Api;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Config;
use app\api\model\Platform as PlatformModel;

class Platform extends Api
{
    protected $noNeedLogin = ['getPlatformList'];

    protected $noNeedRight = '*';

    /**
     * @var PlatformModel
     */
    protected $model;

    public function _initialize()
    {
        parent::_initialize();
        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }
        $this->model = new PlatformModel();
    }


    /**
     * 获取平台列表
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getPlatformList()
    {
        $lists = $this->model->field(['id' => 'value' , 'name' => 'label'])->order('id' , 'asc')->select();
        $this->success("获取成功" , $lists);
    }

}