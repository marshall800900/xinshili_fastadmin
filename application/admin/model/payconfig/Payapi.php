<?php

namespace app\admin\model\payconfig;

use think\Model;


class Payapi extends Model
{

    

    

    // 表名
    protected $name = 'pay_api';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'is_open_text',
        'create_time_text',
        'update_time_text'
    ];
    

    
    public function getIsOpenList()
    {
        return ['0' => __('Is_open 0'), '1' => __('Is_open 1')];
    }


    public function getIsOpenTextAttr($value, $data)
    {
        $value = $value ?: ($data['is_open'] ?? '');
        $list = $this->getIsOpenList();
        return $list[$value] ?? '';
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['update_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
