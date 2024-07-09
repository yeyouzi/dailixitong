<?php

namespace app\admin\controller\log;

use app\common\controller\Backend;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\ValidateException;
use think\facade\Db;
use think\model\Relation;
use app\admin\model\log\Withdrawal as WithdrawalModel;
use app\admin\model\UserAttribute as UserAttributeModel;
use think\response\Json;

/**
 * 提现管理记录
 *
 * @icon fa fa-circle-o
 */
class Withdrawal extends Backend
{
    
    /**
     * Withdrawal模型对象
     * @var WithdrawalModel
     */
    protected $model = null;

    protected $banMethod = ['del' , 'import', 'add' , 'edit' , 'multi'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new WithdrawalModel;
        $this->view->assign("stateList", $this->model->getStateList());
        $this->view->assign("payStateList", $this->model->getPayStateList());
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

    /**
     * 审核记录
     * @param $ids
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function audit($ids)
    {
        $row = $this->model->where('id' , $ids)->with([
            'platform' => function(Relation $relation){
                $relation->field(['id' , 'name']);
            },
            'user' => function(Relation $relation){
                $relation->field(['id' , 'app_id' , 'username' , 'nickname' , 'mobile']);
            }
        ])->find();
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();

                try {
                    $userId = $row->user_id;
                    $platformId = $row->platform_id;
                    $money = $row->money;
                    if($params['state'] == WithdrawalModel::STATS_0 ){
                        throw new Exception(__('Audit stats is not allow'));
                    }

                    if($row->state != WithdrawalModel::STATS_0){
                        throw new Exception(__('Audit stats is not 0'));
                    }

                    if($params['state'] == WithdrawalModel::STATS_2 && empty($params['admin_remark'])){
                        throw new Exception(__('No admin remark'));
                    }
                    $params['admin_id'] = $this->auth->id;
                    $params['audit_time'] = time();
                    $result = $row->allowField(['admin_id' , 'audit_time' , 'admin_remark' , 'state'])->save($params);
                    if($params['state'] == WithdrawalModel::STATS_2){
                        UserAttributeModel::backFreezeMoney( ['platform_id' => $platformId , 'user_id' => $userId ] , $money);
                    }elseif($params['state'] == WithdrawalModel::STATS_1){
                        UserAttributeModel::totalMoney( ['platform_id' => $platformId , 'user_id' => $userId ] , $money);
                    }
                    Db::commit();
                    $this->success();
                }  catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

    /**
     * 查看原因
     * @param $ids
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function reason($ids)
    {
        $row = $this->model->where('id' , $ids)->with([
            'platform' => function(Relation $relation){
                $relation->field(['id' , 'name']);
            },
            'admin' => function(Relation $relation){
                $relation->field(['id' , 'username' , 'nickname']);
            }
        ])->find();
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }


}
