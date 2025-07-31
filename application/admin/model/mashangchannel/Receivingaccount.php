<?php

namespace app\admin\model\mashangchannel;

use think\Model;


class Receivingaccount extends Model
{

    

    

    // 表名
    protected $name = 'receiving_account';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = 'delete_time';

    // 追加属性
    protected $append = [
        'proxy_valid_time_text',
        'notify_status_text',
        'system_oepn_text',
        'is_open_text',
        'create_time_text',
        'expired_time_text',
        'update_time_text',
        'delete_time_text',
        'status_text'
    ];
    

    
    public function getNotifyStatusList()
    {
        return ['0' => __('Notify_status 0'), '1' => __('Notify_status 1')];
    }

    public function getSystemOepnList()
    {
        return ['0' => __('System_oepn 0'), '1' => __('System_oepn 1')];
    }

    public function getIsOpenList()
    {
        return ['0' => __('Is_open 0'), '1' => __('Is_open 1')];
    }
    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3')];
    }


    public function getProxyValidTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['proxy_valid_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getNotifyStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['notify_status'] ?? '');
        $list = $this->getNotifyStatusList();
        return $list[$value] ?? '';
    }


    public function getSystemOepnTextAttr($value, $data)
    {
        $value = $value ?: ($data['system_oepn'] ?? '');
        $list = $this->getSystemOepnList();
        return $list[$value] ?? '';
    }


    public function getIsOpenTextAttr($value, $data)
    {
        $value = $value ?: ($data['is_open'] ?? '');
        $list = $this->getIsOpenList();
        return $list[$value] ?? '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getIsOpenList();
        return $list[$value] ?? '';
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getExpiredTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['expired_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['update_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getDeleteTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['delete_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setProxyValidTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setExpiredTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setDeleteTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
