<?php

namespace app\api\model\config;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;
use app\api\model\config\Platform as PlatformModel;


class Level extends BaseModel
{
    use SoftDelete;

    // 表名
    protected $name = 'level';

    protected $deleteTime = 'delete_time';

    // 追加属性
    protected $append = [
        'create_time_text',
        'update_time_text'
    ];


    /**
     * 获取等级数值
     * @return int[]
     */
    public function getLevelValueList()
    {
        return [1,2,3,4,5,6,7,8,9,10];
    }



    /**
     * @return BelongsTo
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(PlatformModel::class , 'platform_id', 'id');
    }


    /**
     * 返回所有等级的数据
     * @return array
     */
    public function getAllLevelInfo(): array
    {
        return $this->column('percent,second_percent,platform_id' , 'id');
    }

    /**
     * 获取最小等级的ID
     * @param array $option
     * @return mixed
     */
    public static function getMinLevelId(array $option)
    {
        return self::where($option)->order('value' , 'asc')->value('id');
    }


}
