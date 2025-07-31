<?php

namespace app\admin\controller\mashangchannel;

use app\admin\common\library\AdminHelper;
use app\admin\common\library\CommonHelper;
use app\admin\common\library\ReceivingAccountHelper;
use app\common\controller\Backend;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

/**
 * 收款账号
 *
 * @icon fa fa-circle-o
 */
class Receivingaccount extends Backend
{

    /**
     * Receivingaccount模型对象
     * @var \app\admin\model\mashangchannel\Receivingaccount
     */
    protected $model = null;

    protected $multiFields = 'is_open';

    protected $noNeedRight = [
        'getSearchList'
    ];

    protected $html_path = APP_PATH . 'admin' . DS . 'view' . DS . 'mashangchannel' . DS . 'receivingaccount' . DS;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\mashangchannel\Receivingaccount;
        $this->view->assign("notifyStatusList", $this->model->getNotifyStatusList());
        $this->view->assign("systemOepnList", $this->model->getSystemOepnList());
        $this->view->assign("isOpenList", $this->model->getIsOpenList());
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
        $receiving_account_code = input('receiving_account_code');
        if (!AdminHelper::checkAdminReceivingAccountAuth($receiving_account_code))
            $this->error('未授权该产品');

        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            $this->view->assign('receiving_account_code', $receiving_account_code);
            $this->assignconfig('receiving_account_code', $receiving_account_code);
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
//        if ($this->request->request('keyField')) {
//            return $this->selectpage();
//        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();

        $where_extra = [];
        if (session('admin.type') != AdminHelper::ADMIN) {
            $where_extra = [
                'admin_id' => session('admin.id')
            ];
        }

        $receiving_account_code = input('receiving_account_code');
        if (!$receiving_account_code)
            $this->error('参数错误');

        $list = $this->model
            ->where($where)
            ->where('receiving_account_code', $receiving_account_code)
            ->where($where_extra)
            ->where('is_del', '0')
            ->order($sort, $order)
            ->paginate($limit)
            ->each(function ($value) {
                $value['yesterday_amount'] = db()->name('receiving_account_report')
                    ->where('receiving_account_id', $value['id'])
                    ->where('date_key', date('Y-m-d', time() - 86400))
                    ->sum('success_order_amount');
                $value['today_amount'] = db()->name('receiving_account_report')
                    ->where('receiving_account_id', $value['id'])
                    ->where('date_key', date('Y-m-d', time()))
                    ->sum('success_order_amount');

                $value['yesterday_amount'] = CommonHelper::buildFloatValue($value['yesterday_amount']);
                $value['today_amount'] = CommonHelper::buildFloatValue($value['today_amount']);
            });
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
        $receiving_account_code = input('receiving_account_code');
        if (!AdminHelper::checkAdminReceivingAccountAuth($receiving_account_code))
            $this->error('未授权该产品');

        if (false === $this->request->isPost()) {
            $feth_path = '';
            if (file_exists($this->html_path . $receiving_account_code . DS . 'add.html'))
                $feth_path = 'mashangchannel' . DS . 'receivingaccount' . DS . $receiving_account_code . DS . 'add';

            if ($receiving_account_code == 'jintiao') {
                $result_json = file_get_contents('http://103.251.112.18:19357/api/dmf/apiAccountAuth/C1725170016');
                $result_array = json_decode($result_json, true);
                if (!isset($result_array['code']) || $result_array['code'] != 0)
                    $this->error($result_array['msg'] ?? '获取授权链接失败');

                $this->view->assign('qr_code_url', $result_array['data']);
            }
            $this->assignconfig('receiving_account_code', $receiving_account_code);
            return $this->view->fetch($feth_path);
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
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }

            $check_class = new ReceivingAccountHelper();
            $method_name = $receiving_account_code . 'Check';

            $params['receiving_account_code'] = $receiving_account_code;

            if (method_exists($check_class, $method_name))
                $params = $check_class::$method_name($params);

            $params['admin_id'] = session('admin.id');
            if (isset($params['adds'])) {
                $result = 0;
                foreach ($params['list'] as $val) {
                    $id = db()->name('receiving_account')
                        ->where('charge_account', $val['charge_account'])
                        ->where('charge_account_name', $val['charge_account_name'])
                        ->value('id');
                    if (!$id) {
                        $val['create_time'] = time();
                        $val['update_time'] = time();
                        db()->name('receiving_account')
                            ->insert($val);
                        $result++;
//                        $this->model->allowField(true)->save($params);
                    }
                }
            } else {

                $result = $this->model->allowField(true)->save($params);
            }
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
     * 编辑
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {


        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if (!AdminHelper::checkAdminReceivingAccountAuth($row['receiving_account_code']))
            $this->error('未授权该产品');

        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {

            $feth_path = '';
            if (file_exists($this->html_path . $row['receiving_account_code'] . DS . 'edit.html'))
                $feth_path = 'mashangchannel' . DS . 'receivingaccount' . DS . $row['receiving_account_code'] . DS . 'edit';

            if ($row['receiving_account_code'] == 'fxsh'){
                $row['cookie'] = json_decode($row['cookie'], true);
            }

            $row['area'] = json_decode($row['area'], true);
            $this->assignconfig('receiving_account_code', $row['receiving_account_code']);
            $this->view->assign('row', $row);
            return $this->view->fetch($feth_path);
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $where_extra = [];
            if (AdminHelper::ADMIN != session('admin.type'))
                $where_extra['admin_id'] = session('admin.id');

            $params['id'] = $row['id'];
            $params['proxy_ip'] = $row['proxy_ip'];
            $params['proxy_ip_invalid_time'] = $row['proxy_ip_invalid_time'];

            $check_class = new ReceivingAccountHelper();
            $method_name = $row['receiving_account_code'] . 'Check';
            if (method_exists($check_class, $method_name))
                $params = $check_class::$method_name($params);

            $result = $this->model->where('id', $row['id'])->where($where_extra)->update($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    /**
     * 删除
     *
     * @param $ids
     * @return void
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function del($ids = null)
    {

        $where_extra = [];
        if (AdminHelper::ADMIN != session('admin.type'))
            $where_extra['admin_id'] = session('admin.id');

        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post("ids");
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = $this->model->where($pk, 'in', $ids)->select();

        $count = 0;
        Db::startTrans();
        try {
            foreach ($list as $item) {
                $count += $item
                    ->where($where_extra)
                    ->where('id', $item['id'])
                    ->update([
                        'is_del' => 1,
                        'delete_time' => time()
                    ]);
//                $count += $item->delete();
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }

    public function checkOnline($ids = null)
    {
        try {
            $info = db()->name('receiving_account')
                ->field('receiving_account_code, cookie, proxy_ip, proxy_ip_invalid_time, id,area, extra_params')
                ->where('id', $ids)
                ->find();
            if (!$info)
                throw new \Exception('参数错误');

            $check_class = new ReceivingAccountHelper();
            $method_name = $info['receiving_account_code'] . 'Check';

            if (method_exists($check_class, $method_name))
                $params = $check_class::$method_name($info);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('success');
    }

    public function queryBalance($ids = null)
    {
        try {
            $info = db()->name('receiving_account')
                ->field('receiving_account_code, cookie, proxy_ip, proxy_ip_invalid_time, id,area, extra_params')
                ->where('id', $ids)
                ->find();
            if (!$info)
                throw new \Exception('参数错误');

            $check_class = new ReceivingAccountHelper();
            $method_name = $info['receiving_account_code'] . 'QueryBalance';

            if (method_exists($check_class, $method_name))
                $result = $check_class::$method_name($info);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success($result);
    }

    public function getSearchList()
    {
        $model_name = input('model_name');
        return $this->model->$model_name();
    }

    public function sendSmsCode(){
        try{
            $params = input();
            if (!isset($params['receiving_account_code']) || !$params['receiving_account_code'])
                throw new \Exception('参数错误');

            $check_class = new ReceivingAccountHelper();
            $method_name = $params['receiving_account_code'] . 'SendSmsCode';

            if (method_exists($check_class, $method_name))
                $params = $check_class::$method_name($params);
        }catch (\Exception $e){
            $this->error($e->getMessage());
        }
        $this->success('success', null, $params);
    }

    public function login()
    {
        try {
            $params['username'] = input('username', '');
            $params['password'] = input('password', '');
            $params['receiving_account_code'] = input('receiving_account_code', '');
            $params['proxy_ip'] = input('proxy_ip', '');

            $check_class = new ReceivingAccountHelper();
            $method_name = $params['receiving_account_code'] . 'Login';

            if (method_exists($check_class, $method_name))
                $params = $check_class::$method_name($params);

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('success', null, $params);
    }

}
