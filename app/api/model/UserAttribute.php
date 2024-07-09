<?php

namespace app\api\model;

use app\api\model\config\Level as LevelModel;
use app\common\model\BaseModel;
use app\api\model\config\Platform as PlatformModel;
use think\db\exception\DbException;
use think\facade\Db;
use think\model\relation\HasOne;


class UserAttribute extends BaseModel
{

    protected $name = 'user_attribute';


    /**
     * 初始化一下用户属性
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
    public function initUserAttribute(int $userId): bool
    {
        //获取平台列表
        $platformList = PlatformModel::getAllPlatformId();
        if (empty($platformList)) {
            return true;
        }

        //遍历数据
        $insertData = [];
        foreach ($platformList as $v) {
            if ($this->isExistRecord(['platform_id' => $v, 'user_id' => $userId])) {
                continue;
            }
            $insertData[] = [
                'platform_id' => $v,
                'user_id' => $userId,
                'level_id' => LevelModel::getMinLevelId(['platform_id' => $v]),
                'app_id' => $v . '000' . $userId,
            ];
        }
        if(!empty($insertData)){
            $this->saveAll($insertData);
        }
        return true;
    }


    /**
     * 更新用户等级
     * @param array $option
     * @param int $levelId
     * @return false|void
     */
    public function updateLevelId(array $option , int $levelId)
    {
        if(empty($levelId)){
            return false;
        }
        $this->where($option)->save([
            'level_id' => $levelId
        ]);
    }


    /**
     * 冻结分销商资金
     * @param array $option
     * @param int|float $money
     * @throws DbException
     */
    public static function freezeMoney(array $option , $money): void
    {
        Db::name('user_attribute')->where($option)->inc('freeze_money' , $money)->dec('money' , $money)->update();
    }


    /**
     * 获取用户的佣金余额
     * @param array $option
     * @return mixed
     */
    public static function getUserMoney(array $option)
    {
        return self::where($option)->value('money' , 0);
    }

    /**
     * 关联等级
     * @return HasOne
     */
    public function level(): HasOne
    {
        return $this->hasOne(LevelModel::class , 'id' , 'level_id');
    }


}