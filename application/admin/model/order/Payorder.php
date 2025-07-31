<?php

namespace app\admin\model\order;

use app\admin\common\library\OrderHelper;
use think\Model;


class Payorder extends Model
{

    

    

    // 表名
    protected $name = 'pay_order';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'notify_status_text',
        'request_time_text',
        'create_time_text',
        'create_success_time_text',
        'success_time_text',
        'refund_time_text',
        'create_report_status_text',
        'create_success_report_status_text',
        'success_report_status_text',
        'refund_report_status_text',
        'status_text'
    ];

    public function getStatusList()
    {
        return [
            OrderHelper::ORDER_TYPE_DEFAULT => __('Status 0'),
            OrderHelper::ORDER_TYPE_WAIT_PAY => __('Status 1'),
            OrderHelper::ORDER_TYPE_PAY_SUCCESS => __('Status 2'),
            OrderHelper::ORDER_TYPE_REFUND_ING => __('Status 3'),
            OrderHelper::ORDER_TYPE_REFUND_SUCCESS => __('Status 4'),
            OrderHelper::ORDER_TYPE_TIME_OUT => __('Status 5'),
        ];
    }

    public function getNotifyStatusList()
    {
        return ['0' => __('Notify_status 0'), '1' => __('Notify_status 1')];
    }

    public function getCreateReportStatusList()
    {
        return ['0' => __('Create_report_status 0'), '1' => __('Create_report_status 1')];
    }

    public function getCreateSuccessReportStatusList()
    {
        return ['0' => __('Create_success_report_status 0'), '1' => __('Create_success_report_status 1')];
    }

    public function getSuccessReportStatusList()
    {
        return ['0' => __('Success_report_status 0'), '1' => __('Success_report_status 1')];
    }

    public function getRefundReportStatusList()
    {
        return ['0' => __('Refund_report_status 0'), '1' => __('Refund_report_status 1')];
    }


    public function getNotifyStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['notify_status'] ?? '');
        $list = $this->getNotifyStatusList();
        return $list[$value] ?? '';
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }


    public function getRequestTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['request_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCreateSuccessTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_success_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getSuccessTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['success_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getRefundTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['refund_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCreateReportStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_report_status'] ?? '');
        $list = $this->getCreateReportStatusList();
        return $list[$value] ?? '';
    }


    public function getCreateSuccessReportStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_success_report_status'] ?? '');
        $list = $this->getCreateSuccessReportStatusList();
        return $list[$value] ?? '';
    }


    public function getSuccessReportStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['success_report_status'] ?? '');
        $list = $this->getSuccessReportStatusList();
        return $list[$value] ?? '';
    }


    public function getRefundReportStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['refund_report_status'] ?? '');
        $list = $this->getRefundReportStatusList();
        return $list[$value] ?? '';
    }

    protected function setRequestTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setCreateSuccessTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setSuccessTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setRefundTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
