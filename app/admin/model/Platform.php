<?php

namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;

class Platform extends Model
{

    use SoftDelete;

    // 表名
    protected $name = 'platform';

    protected $deleteTime = 'delete_time';


}
