<?php

namespace app\admin\controller\mashangchannel;

use app\admin\common\library\AdminHelper;
use app\admin\common\library\ReceivingAccountHelper;
use app\common\controller\Backend;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

/**
 * 账号账单
 *
 * @icon fa fa-circle-o
 */
class Billlist extends Backend
{

    protected $relationSearch = true;
    /**
     * Billlist模型对象
     * @var \app\admin\model\mashangchannel\Billlist
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\mashangchannel\Billlist;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     *
     * @return string|Json
     * @throws \think\Exception
     * @throws DbException
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();


        $where_extra = [];
        if (session('admin.type') != AdminHelper::ADMIN) {
            $where_extra = [
                'billlist.admin_id' => session('admin.id')
            ];
        }
        $list = $this->model->alias('billlist')
            ->field('billlist.*, r.charge_account')
            ->join('fa_receiving_account r', 'billlist.receiving_account_id = r.id', 'left')
            ->where($where)
            ->where('billlist.receiving_account_code', input('receiving_account_code'))
            ->where($where_extra)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            $this->view->assign('receiving_account_code', input('receiving_account_code'));
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            $receiving_account = db()->name('receiving_account')
                ->where('id', $params['receiving_account_id'])
                ->find();

            if (session('admin.type') != AdminHelper::ADMIN && $receiving_account['admin_id'] != session('admin.id'))
                throw new ValidateException('参数错误');

            $check_class = new ReceivingAccountHelper();
            $method_name = $receiving_account['receiving_account_code'] . 'Transfer';

            if (method_exists($check_class, $method_name))
                $result_params = $check_class::$method_name($receiving_account, $params);

            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }

    /**
     * 查询余额
     * @return void
     */
    public function getBalance()
    {
        try {
            $receiving_account_id = input('receiving_account_id');
            if (!$receiving_account_id)
                throw new \Exception('参数错误');

            $charge_account_info = db()->name('receiving_account')
                ->where('id', $receiving_account_id)
                ->find();

            $check_class = new ReceivingAccountHelper();
            $method_name = $charge_account_info['receiving_account_code'] . 'GetBalance';

            if (method_exists($check_class, $method_name))
                $params = $check_class::$method_name($charge_account_info);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('success', null, $params);
    }
}
