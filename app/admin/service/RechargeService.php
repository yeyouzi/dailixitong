<?php

namespace app\admin\service;

use app\admin\model\log\UserBalanceLog as UserBalanceLogModel;
use app\admin\model\report\Achievement as AchievementModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use app\admin\model\User as UserModel;

class RechargeService
{
    private $adminId;
    private $createTime;
    private $updateTime;


    public function __construct(?int $adminId = null )
    {
        $this->createTime = $this->updateTime = time();
        $this->adminId = $adminId;
    }

    public function handle(array $data)
    {
        $userModel = new UserModel();
        $userInfo = $userModel->where('id' , $data['user_id'])->find();
        if(empty($userInfo)){
            throw new Exception("找不到用户信息");
        }
        $data['app_id'] = $userInfo->app_id;
        if($data['recharge_type'] == 0){
            $this->rechargeBalance($data);
        }elseif($data['recharge_type'] == 1){
            $this->rechargeToMoney($data);
        }
    }


    /**
     * 用户充值业绩
     * @param array $data
     * @return bool
     * @throws Exception
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    private function rechargeBalance( array $data )
    {
        if (!isset($data['amount'])) {
            throw new Exception('请输入正确的业绩金额');
        }

        // 更新账户余额
        UserModel::setTotalBalance($data['user_id'] , $data['platform_id'] , $data['amount']);

        //添加日志
        $logData = [
            'user_id' => $data['user_id'],
            'app_id' => $data['app_id'],
            'platform_id' => $data['platform_id'],
            'balance' => $data['amount'],
            'total_profit' => $data['amount'],
            'date' => date("Y-m-d"),
            'create_time' => $this->createTime,
            'update_time' => $this->updateTime,
            'settle_time' => $this->createTime,
            'admin_id' => $this->adminId,
            'remark' => !empty($data['remark']) ? $data['remark'] : '充值业绩',
        ];
        (new AchievementModel)->save($logData);
        return true;
    }


    /**
     * 用户充值佣金
     * @param array $data
     * @return bool
     * @throws Exception
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    private function rechargeToMoney(array $data)
    {
        if (!isset($data['amount'])) {
            throw new Exception('请输入正确的佣金金额');
        }
        UserModel::updateUserMoney([
            'platform_id' => $data['platform_id'],
            'user_id' => $data['user_id'],
        ] , $data['amount']);
        $logData = [
            'user_id' => $data['user_id'],
            'platform_id' => $data['platform_id'],
            'money' => $data['amount'],
            'scene' => 1,
            'remark' =>!empty($data['remark']) ? $data['remark'] : '充值佣金',
            'create_time' => $this->createTime,
            'update_time' => $this->updateTime
        ];
        (new UserBalanceLogModel)->save($logData);
        return true;
    }


}