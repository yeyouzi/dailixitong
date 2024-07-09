<?php

namespace app\api\model\log;

use app\api\model\config\Platform as PlatformModel;
use app\api\model\UserAttribute as UserAttributeModel;
use app\api\model\User as UserModel;
use app\common\model\BaseModel;
use think\Exception;
use think\facade\Log;
use think\model\relation\BelongsTo;


class Withdrawal extends BaseModel
{

    // 表名
    protected $name = 'withdrawal';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'state_text',
        'audit_time_text',
        'pay_state_text',
        'create_time_text',
        'update_time_text',
        'delete_time_text'
    ];

    const STATS_0 = 0;
    const STATS_1 = 1;
    const STATS_2 = 2;


    public function getStateList()
    {
        return ['0' => __('State 0'), '1' => __('State 1'), '2' => __('State 2')];
    }

    public function getPayStateList()
    {
        return ['0' => __('Pay_state 0'), '1' => __('Pay_state 1'), '2' => __('Pay_state 2')];
    }


    public function getStateTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['state'] ?? '');
        $list = $this->getStateList();
        return $list[$value] ?? '';
    }


    public function getAuditTimeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['audit_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getPayStateTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['pay_state'] ?? '');
        $list = $this->getPayStateList();
        return $list[$value] ?? '';
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['create_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['update_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    protected function setAuditTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setDeleteTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    /**
     * @return BelongsTo
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(PlatformModel::class, 'platform_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'id');
    }



    /**
     * 申请提交的校验
     * @param int $userId
     * @param int $platformId
     * @param $auditMoney
     * @return $this
     * @throws Exception
     */
    public function checkAudit(int $userId, int $platformId, $auditMoney): Withdrawal
    {
        $todayStartTime = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $todayEndTime = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
        $option = [
            ['user_id', '=', $userId],
            ['create_time', 'BETWEEN', [$todayStartTime, $todayEndTime]],
        ];
        if ($this->isExistRecord($option)) {
            throw new Exception('每天仅能提现1次');
        }

        //获取用户的余额并判断
        $userMoney = UserAttributeModel::getUserMoney(['user_id' => $userId, 'platform_id' => $platformId]);
        if (empty($userMoney) || $auditMoney > $userMoney) {
            throw new Exception('提现超出账户余额');
        }
        return $this;
    }

    /**
     * 提现申请提交
     * @param array $data
     */
    public function addWithdrawal(array $data): void
    {
        $allowField = ['user_id', 'platform_id', 'money', 'fee', 'name', 'account', 'remark' , 'create_time'];
        $data['create_time'] = time();
        $result = $this->allowField($allowField)->save($data);
        //冻结金额
        UserAttributeModel::freezeMoney([ 'platform_id' => $data['platform_id'] ,'user_id' => $data['user_id'] ] , $data['money'] );
    }


}
