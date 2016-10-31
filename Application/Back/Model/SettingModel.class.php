<?php

namespace Back\Model;

use Think\Model\RelationModel;

class SettingModel extends RelationModel
{

    protected $_link = [
        'option' => [
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'SettingOption',// 关联模型名
            'foreign_key'   => 'setting_id',// 外键字段
        ]
    ];
}