<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Config;
use app\api\model\log\UserBalanceLog as UserBalanceLogModel;
use think\model\Relation;

class UserBalanceLog extends Api
{
    protected $noNeedLogin = [];

    protected $noNeedRight = '*';

    /**
     * @var UserBalanceLogModel
     */
    protected $model;

    public function _initialize()
    {
        parent::_initialize();
        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }
        $this->model = new UserBalanceLogModel();
    }


    /**
     * 获取用户佣金记录
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getUserBalanceList()
    {
        $platformId = $this->request->param('platformId' , 1);
        $userId = $this->auth->id;
        $list = $this->model->with([
            'origin' => function(Relation $relation){
                $relation->field(['id' , 'nickname' , 'mobile']);
            }
        ])->where([
            'user_id' => $userId,
            'platform_id' => $platformId
        ])->order('create_time' , 'desc')->select();
        $this->success('success', [
            'list' => $list
        ]);
    }
}