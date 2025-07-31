<?php

namespace app\admin\model\mashangchannel;

use think\Model;


class Admin extends Model
{

    

    

    // 表名
    protected $name = 'admin';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'logintime_text',
        'status_text'
    ];
    

    
    public function getTypeList()
    {
        return ['admin' => __('Admin'), 'merchant' => __('Merchant'), 'mashang' => __('Mashang')];
    }

    public function getStatusList()
    {
        return ['30' => __('Status 30')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }


    public function getLogintimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['logintime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }

    protected function setLogintimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
