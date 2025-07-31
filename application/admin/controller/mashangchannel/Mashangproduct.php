<?php

namespace app\admin\controller\mashangchannel;

use app\admin\common\library\AdminHelper;
use app\common\controller\Backend;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

/**
 * 码商产品授权
 *
 * @icon fa fa-circle-o
 */
class Mashangproduct extends Backend
{

    /**
     * Mashangproduct模型对象
     * @var \app\admin\model\mashangchannel\Mashangproduct
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\mashangchannel\Mashangproduct;
        $this->view->assign("isOpenList", $this->model->getIsOpenList());
        $this->view->assign("systemOpenList", $this->model->getSystemOpenList());
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
        $admin_id = input('admin_id');
        if (empty($admin_id))
            $this->error('参数错误');

        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            $this->assignconfig('admin_id', $admin_id);
            return $this->view->fetch();
        }

        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->where('admin_id', $admin_id)
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
            $admin_id = input('admin_id');
            if (empty($admin_id))
                $this->error('参数错误');

            $from_pid = db()->name('admin')
                ->where('id', $admin_id)
                ->value('pid');

            if (session('admin.type') != AdminHelper::ADMIN) {
                if ($from_pid != session('admin.id'))
                    $this->error('不可以跨级授权');
            }else{
                if ($from_pid)
                    $this->error('不可以跨级授权');
            }

            if ($from_pid){
                $product_info = db()->name('mashang_product')
                    ->where('receiving_account_code', $params['receiving_account_code'])
                    ->where('admin_id', $from_pid)
                    ->find();

                if (!$product_info)
                    $this->error('暂无权限');

                if ($product_info['is_open'] != 1)
                    $this->error('暂无权限');

                if ($product_info['rate'] < $params['rate'])
                    $this->error('费率最高可设置【' . $product_info['rate'] . '】');
            }


            if ($params['width'] > 9 || $params['width'] < 1)
                $this->error('权重最高设置');


            $params['admin_id'] = $admin_id;
            $params['system_open'] = $product_info['system_open'] ?? 1;
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
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }

        $pid = db()->name('admin')
            ->where('id', $row['admin_id'])
            ->value('pid');
        if ($pid != session('admin.id') && session('admin.type') != AdminHelper::ADMIN)
            $this->error('不可以越级编辑');

        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
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

            $from_id = db()->name('admin')
                ->where('pid', $row['admin_id'])
                ->column('id');
            if ($from_id){
                $from_rate = db()->name('mashang_product')
                    ->whereIn('admin_id', $from_id)
                    ->where('receiving_account_code', $params['receiving_account_code'])
                    ->order('rate desc')
                    ->value('rate');


                if ($params['rate'] < $from_rate)
                    throw new ValidateException('费率最低可设置【'.$from_rate.'】');
            }


            $pid = db()->name('admin')
                ->where('id', $row['admin_id'])
                ->value('pid');

            if (session('admin.type') != AdminHelper::ADMIN) {
                if ($pid != session('admin.id'))
                    $this->error('不可以跨级授权');
            }else{
                if ($pid)
                    $this->error('不可以跨级授权');
            }

            if ($pid){
                $product_info = db()->name('mashang_product')
                    ->where('receiving_account_code', $params['receiving_account_code'])
                    ->where('admin_id', $pid)
                    ->find();

                if (!$product_info)
                    $this->error('暂无权限');

                if ($product_info['is_open'] != 1)
                    $this->error('暂无权限');

                if ($product_info['rate'] < $params['rate'])
                    $this->error('费率最高可设置【' . $product_info['rate'] . '】');
            }


            if ($params['width'] > 9 || $params['width'] < 1)
                $this->error('权重最高设置');

            $result = $row->allowField(true)->save($params);
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
                $count += $item->delete();
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


    /**
     * 批量更新
     *
     * @param $ids
     * @return void
     */
    public function multi($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }

        if (false === $this->request->has('params')) {
            $this->error(__('No rows were updated'));
        }
        parse_str($this->request->post('params'), $values);
        $values = $this->auth->isSuperAdmin() ? $values : array_intersect_key($values, array_flip(is_array($this->multiFields) ? $this->multiFields : explode(',', $this->multiFields)));
        if (empty($values)) {
            $this->error(__('You have no permission'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $count = 0;
        Db::startTrans();
        try {
            $info = db()->name('mashang_product')
                ->where('id', $ids)
                ->find();

            $pid = db()->name('admin')
                ->where('id', $info['admin_id'])
                ->value('pid');

            if ($pid != session('admin.id') && session('admin.type') != AdminHelper::ADMIN)
                throw new Exception('不可以越级编辑');

            $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
            foreach ($list as $item) {
                $count += $item->allowField(true)->isUpdate(true)->save($values);
                if ($values['is_open'] == 0){
                    db()->name('mashang_product')
                        ->where('receiving_account_code', $info['receiving_account_code'])
                        ->whereIn('admin_id', db()->name('admin')->where('line', 'like', '%,'.$info['admin_id'].',%')->column('id'))
                        ->update($values);
                }
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were updated'));
    }

}
