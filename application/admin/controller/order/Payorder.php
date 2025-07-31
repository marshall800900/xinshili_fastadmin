<?php

namespace app\admin\controller\order;

use app\admin\common\library\AdminHelper;
use app\admin\common\library\CommonHelper;
use app\common\controller\Backend;
use think\exception\DbException;
use think\response\Json;

/**
 * 代收订单
 *
 * @icon fa fa-circle-o
 */
class Payorder extends Backend
{

    /**
     * Payorder模型对象
     * @var \app\admin\model\order\Payorder
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\order\Payorder;
        $this->view->assign("notifyStatusList", $this->model->getNotifyStatusList());
        $this->view->assign("createReportStatusList", $this->model->getCreateReportStatusList());
        $this->view->assign("createSuccessReportStatusList", $this->model->getCreateSuccessReportStatusList());
        $this->view->assign("successReportStatusList", $this->model->getSuccessReportStatusList());
        $this->view->assign("refundReportStatusList", $this->model->getRefundReportStatusList());
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
            $this->assignconfig('defaultDateValue', date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d 23:59:59'));
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit)
            ->each(function ($value){
                $value['user_ip'] = empty($value['user_ip']) ? '-' : $value['user_ip'];
                $value['user_ip_area'] = empty($value['user_ip_area']) ? '-' : $value['user_ip_area'];
                $value['user_device'] = empty($value['user_device']) ? '-' : $value['user_device'];
                $value['pay_channel_number'] = empty($value['pay_channel_number']) ? '-' : $value['pay_channel_number'];
                $value['merchant_id'] = db()->name('admin')
                    ->where('id', $value['merchant_id'])
                    ->value('username') . '('.$value['merchant_id'].')';

                $value['pay_channel_id'] = db()->name('pay_channel')
                    ->where('id', $value['pay_channel_id'])
                    ->value('name');

                $value['product_code'] = db()->name('pay_product')
                    ->where('product_code', $value['product_code'])
                    ->value('product_name');

                $value['receiving_account_id'] = db()->name('receiving_account')
                    ->where('id', $value['receiving_account_id'])
                    ->value('charge_account_name');

                $value['admin_id'] = db()->name('admin')
                    ->where('id', $value['admin_id'])
                    ->value('username');

                $value['admin_id'] = empty($value['admin_id']) ? '-' : $value['admin_id'];
                $value['receiving_account_id'] = empty($value['receiving_account_id']) ? '-' : $value['receiving_account_id'];

//                $value['extra_params'] = '';
//                if ($value['api_code'] == 'dytb'){
//                    $balance = db()->name('receiving_account_pay_url')
//                        ->where('pay_channel_number', $value['pay_channel_number'])
//                        ->value('extra_params');
//                    $value['extra_params'] = empty($balance) ? 0 : number_format($balance/100, 2, '.', '');
//                }

            });
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    /**
     * 补单
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function budan($ids = null){
        $order_info = (new \app\admin\model\order\Payorder())->alias('po')
            ->where('po.id', $ids)
            ->find();

        if ($this->request->isPost()) {
            try {
                if (!$order_info['pay_channel_number'])
                    throw new \Exception('订单状态异常');

                $google_auth_code = input('google_auth_code');
                AdminHelper::verifyGooleCode($google_auth_code);

                CommonHelper::requestBackend([
                    'pay_channel_number' => $order_info['pay_channel_number'],
                    'remark' => input('row.remark')
                ], 'budan');

            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success('补单成功');
        }

        $order_info['charge_account_name'] = db()->name('receiving_account')
            ->where('id', $order_info['receiving_account_id'])
            ->value('charge_account_name');

        $this->view->assign('order_info', $order_info);
        return $this->view->fetch();
    }

    /**
     * 查询订单
     * @param $ids
     * @return null
     */
    public function query($ids = null){
        try{
            $pay_channel_number = db()->name('pay_order')
                ->where('id', $ids)
                ->value('pay_channel_number');

            if (!$pay_channel_number)
                throw new \Exception('参数错误');

            CommonHelper::requestBackend(['pay_channel_number' => $pay_channel_number], 'queryOrder');

        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }

        return $this->success('已支付');
    }

    /**
     * 异步通知
     * @param $ids
     * @return null
     */
    public function notify($ids = null){
        try{
            CommonHelper::requestBackend(['id' => $ids], 'notify');

        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }

        return $this->success('已推送至通知队列');
    }

    /**
     * 测试对接
     * @param $ids
     * @return null
     */
    public function testNotify($ids = null){
        try{
            db()->name('pay_order')
                ->where('id', $ids)
                ->update([
                    'status' => 2
                ]);
            CommonHelper::requestBackend(['id' => $ids], 'testNotify');

        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }

        return $this->success('已推送至通知队列');
    }
}
