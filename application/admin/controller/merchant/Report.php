<?php

namespace app\admin\controller\merchant;

use app\admin\common\library\AdminHelper;
use app\common\controller\Backend;
use think\exception\DbException;
use think\response\Json;

/**
 * 每日报管理
 *
 * @icon fa fa-circle-o
 */
class Report extends Backend
{

    /**
     * Report模型对象
     * @var \app\admin\model\merchant\Report
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\merchant\Report;
        $this->view->assign("deviceList", $this->model->getDeviceList());
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
        if (session('admin.type') != AdminHelper::ADMIN)
            $where_extra['merchant_id'] = session('admin.id');

        $list = $this->model
            ->field('
                date_key, product_code, merchant_id, 
                sum(create_order_number) create_order_number,
                sum(create_order_amount) create_order_amount,
                sum(success_order_number) success_order_number,
                sum(success_order_amount) success_order_amount,
                sum(merchant_rate_amount) merchant_rate_amount
            ')
            ->where($where_extra)
            ->where($where)
            ->group('date_key,product_code,merchant_id')
            ->order($sort, $order)
            ->paginate($limit)
            ->each(function ($value) {
                $value['real_back_amount'] = number_format($value['success_order_amount'] - $value['merchant_rate_amount'], 2, '.', '');
//                $value['merchant_id'] = db()->name('admin')
//                        ->where('id', $value['merchant_id'])
//                        ->value('username') . '(' . $value['merchant_id'] . ')';
//               $value['product_code'] = db()->name('')
            });
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }


}
