<?php

namespace app\api\model\log;

use app\api\model\config\Platform as PlatformModel;
use app\api\model\User as UserModel;
use app\common\model\BaseModel;
use think\model\relation\BelongsTo;


class UserBalanceLog extends BaseModel
{

    // 表名
    protected $name = 'user_balance_log';

    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text'
    ];


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['create_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }



    protected function setCreateTimeAttr($value)
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
    public function origin(): BelongsTo
    {
        return $this->belongsTo(UserModel::class , 'origin_uid', 'id');
    }

}
