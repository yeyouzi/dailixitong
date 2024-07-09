<?php

namespace app\admin\model\config;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;


class Platform extends BaseModel
{

    use SoftDelete;

    // 表名
    protected $name = 'platform';
    
    protected $deleteTime = 'delete_time';

    // 追加属性
    protected $append = [
        'create_time_text',
        'update_time_text',
        'delete_time_text'
    ];
    

    public function getCreateTimeTextAttr($value, $data): string
    {
        $value = $value ? $value : ($data['create_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getUpdateTimeTextAttr($value, $data): string
    {
        $value = $value ? $value : ($data['update_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getDeleteTimeTextAttr($value, $data): string
    {
        $value = $value ? $value : ($data['delete_time'] ?? '');
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

    protected function setDeleteTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    /**
     * 获取所有的平台Id
     * @return array
     */
    public static function getAllPlatformId(): array
    {
        return self::column('id');
    }

    /**
     * 获取第一个平台
     * @return mixed
     */
    public static function getFirstPlatformId()
    {
        return self::min('id');
    }


}
