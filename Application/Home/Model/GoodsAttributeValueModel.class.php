<?php

namespace Home\Model;

use Think\Model\RelationModel;

class GoodsAttributeValueModel extends RelationModel
{

    protected $_link = [

        'option'    => [
            'mapping_type' => self::HAS_MANY,
            'class_name'    => 'AttributeOption',
            'foreign_key'  => 'goods_attribute_id',
        ],
    ];
}