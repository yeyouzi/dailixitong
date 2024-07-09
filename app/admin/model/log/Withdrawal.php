<?php

namespace app\admin\model\log;

use app\admin\model\Admin as AdminModel;
use app\admin\model\config\Platform as PlatformModel;
use app\admin\model\User as UserModel;
use app\common\model\BaseModel;
use think\model\relation\BelongsTo;


class Withdrawal extends BaseModel
{

    // 表名
    protected $name = 'withdrawal';

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


    public function getDeleteTimeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['delete_time'] ?? '');
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
        return $this->belongsTo(PlatformModel::class , 'platform_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class , 'user_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminModel::class , 'admin_id', 'id');
    }


}
