<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\facade\Config;
use app\api\model\log\Withdrawal as WithdrawalModel;
use think\facade\Db;

class Withdrawal extends Api
{

    protected $noNeedLogin = [];

    protected $noNeedRight = '*';

    /**
     * @var WithdrawalModel
     */
    protected $model;

    public function _initialize()
    {
        parent::_initialize();
        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }
        $this->model = new WithdrawalModel();
    }

    /**
     * 获取用户佣金记录
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getWithdrawalList()
    {
        $platformId = $this->request->param('platformId' , 1);
        $userId = $this->auth->id;
        $list = $this->model->where([
            'user_id' => $userId,
            'platform_id' => $platformId
        ])->order('create_time' , 'desc')->select();
        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 申请提现
     */
    public function auditWithdrawal()
    {
        $platformId = $this->request->param('platformId' , 1);
        $userId = $this->auth->id;
        $data = $this->request->param();
        $data['platform_id'] = $platformId;
        $data['user_id'] = $userId;
        Db::startTrans();
        try {
            //基本判断
            $this->model->checkAudit($userId, $platformId , $data['money'])->addWithdrawal($data);
            Db::commit();
            $this->success('提交成功');
        }catch (Exception $e){
            Db::rollback();
            $this->error($e->getMessage() ?? '申请失败' , $data);
        }
    }

}