<?php

namespace app\admin\controller\log;

use app\common\controller\Backend;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\model\Relation;
use think\response\Json;
use app\admin\model\log\UserBalanceLog as UserBalanceLogModel;

/**
 * 用户余额变动明细管理
 *
 * @icon fa fa-circle-o
 */
class UserBalanceLog extends Backend
{
    
    /**
     * UserBalanceLog模型对象
     * @var UserBalanceLogModel
     */
    protected $model = null;

    protected $banMethod = ['del' , 'import' , 'edit' , 'add' , 'multi'];


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new UserBalanceLogModel;
        $this->view->assign("sceneList", $this->model->getSceneList());
    }

    /**
     * 列表
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            [$where, $sort, $order, $offset, $limit] = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with([
                    'platform' => function(Relation $relation){
                        $relation->field(['id' , 'name']);
                    },
                    'user' => function(Relation $relation){
                        $relation->field(['id' , 'app_id' , 'username' , 'nickname' , 'mobile']);
                    }
                ])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list   = $list->toArray();
            $result = ['total' => $total, 'rows' => $list];
            return json($result);
        }
        $platformId = $this->getFirstPlatformId();
        $this->assignconfig('defaultPlatformId' , $platformId);
        $this->assign('defaultPlatformId' , $platformId);
        return $this->view->fetch();
    }


}
