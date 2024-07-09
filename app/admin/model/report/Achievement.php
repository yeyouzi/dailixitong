<?php

namespace app\admin\model\report;

use app\admin\model\config\Platform as PlatformModel;
use app\admin\model\User as UserModel;
use app\common\model\BaseModel;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;


class Achievement extends BaseModel
{

    use SoftDelete;

    // 表名
    protected $name = 'achievement';
    
    protected $deleteTime = 'delete_time';

    // 追加属性
    protected $append = [
        'create_time_text',
        'update_time_text',
        'settle_time_text'
    ];
    

    



    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['update_time']) ? $data['update_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getSettleTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['settle_time']) ? $data['settle_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setSettleTimeAttr($value)
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
}
