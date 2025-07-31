<?php

namespace app\admin\controller\report;

use app\admin\common\library\CommonHelper;
use app\common\controller\Backend;
use think\exception\DbException;
use think\response\Json;

/**
 * 每日报管理
 *
 * @icon fa fa-circle-o
 */
class Merchantreport extends Backend
{

    /**
     * Merchantreport模型对象
     * @var \app\admin\model\report\Merchantreport
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\report\Merchantreport;
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

        $count_info = $this->model
            ->field('
                sum(create_order_number) create_order_number,
                sum(create_order_amount) create_order_amount,
                sum(success_create_order_number) success_create_order_number,
                sum(success_create_order_amount) success_create_order_amount,
                sum(success_order_number) success_order_number,
                sum(success_order_amount) success_order_amount,
                sum(refund_order_number) refund_order_number,
                sum(refund_order_amount) refund_order_amount,
                sum(merchant_rate_amount) merchant_rate_amount,
                sum(cost_rate_amount) cost_rate_amount,
                sum(merchant_rate_amount) merchant_rate_amount,
                sum(profit_amount) profit_amount
            ')
            ->where($where)
            ->find();
        $count_info['create_order_number'] = $count_info['create_order_number'] ?? 0;
        $count_info['success_create_order_number'] = $count_info['success_create_order_number'] ?? 0;
        $count_info['success_order_number'] = $count_info['success_order_number'] ?? 0;
        $count_info['refund_order_number'] = $count_info['refund_order_number'] ?? 0;
        $count_info['create_order_amount'] = CommonHelper::buildFloatValue($count_info['create_order_amount'] ?? 0);
        $count_info['success_create_order_amount'] = CommonHelper::buildFloatValue($count_info['success_create_order_amount'] ?? 0);
        $count_info['success_order_amount'] = CommonHelper::buildFloatValue($count_info['success_order_amount'] ?? 0);
        $count_info['refund_order_amount'] = CommonHelper::buildFloatValue($count_info['refund_order_amount'] ?? 0);
        $count_info['cost_rate_amount'] = CommonHelper::buildFloatValue($count_info['cost_rate_amount'] ?? 0);
        $count_info['merchant_rate_amount'] = CommonHelper::buildFloatValue($count_info['merchant_rate_amount'] ?? 0);
        $count_info['profit_amount'] = CommonHelper::buildFloatValue($count_info['profit_amount'] ?? 0);
        $count_info['real_back_amount'] = CommonHelper::buildFloatValue(($count_info['success_order_amount'] ?? 0) - $count_info['merchant_rate_amount'] ?? 0);


        $list = $this->model
            ->field('
                date_key,product_code, merchant_id, 
                sum(create_order_number) create_order_number,
                sum(create_order_amount) create_order_amount,
                sum(success_create_order_number) success_create_order_number,
                sum(success_create_order_amount) success_create_order_amount,
                sum(success_order_number) success_order_number,
                sum(success_order_amount) success_order_amount,
                sum(merchant_rate_amount) merchant_rate_amount,
                sum(cost_rate_amount) cost_rate_amount,
                sum(merchant_rate_amount) merchant_rate_amount,
                sum(profit_amount) profit_amount
            ')
            ->where($where)
            ->group('date_key,product_code, merchant_id')
            ->order($sort, $order)
            ->paginate($limit)
            ->each(function ($value) {
                $value['product_code'] = db()->name('pay_product')
                    ->where('product_code', $value['product_code'])
                    ->value('product_name');
                $value['merchant_id'] = db()->name('admin')
                    ->where('id', $value['merchant_id'])
                    ->value('username') . '('.$value['merchant_id'].')';
                $value['success_rate'] = number_format($value['success_order_number'] / $value['create_order_number'] * 100, 2, '.', '');
                $value['success_create_rate'] = number_format(100-$value['success_create_order_number'] / $value['create_order_number'] * 100, 2, '.', '');
                $value['real_back_amount'] = number_format($value['success_order_amount'] - $value['merchant_rate_amount'], 2, '.', '');
            });
        $result = ['total' => $list->total(), 'rows' => $list->items(), 'counts' => $count_info];
        return json($result);
    }

}
