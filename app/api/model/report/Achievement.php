<?php

namespace app\api\model\report;

use app\api\model\config\Platform as PlatformModel;
use app\api\model\User as UserModel;
use app\common\model\BaseModel;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;


class Achievement extends BaseModel
{

    use SoftDelete;

    // 表名
    protected $name = 'achievement';
    
    protected $deleteTime = 'delete_time';


    /**
     * 获取今天的分红
     * @param int $userId
     * @param int $platformId
     * @return float
     */
    public function getTodayMoney(int $userId , int $platformId)
    {
        $time = strtotime(date('Y-m-d 00:00:00'));
        $todayMoney = $this->where([
            ['user_id' , '=' , $userId],
            ['platform_id' , '=' , $platformId],
            ['create_time' , '>' , $time - 1],
        ])->sum('total_profit');
        return $todayMoney;
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
