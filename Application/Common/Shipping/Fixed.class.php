<?php
namespace Common\Shipping;
use Common\Interfaces\I_Shipping;
/**
 * 固定运费 货运方式的实现类
 */
class Fixed implements I_Shipping
{

    private $title = '固定运费';

    /**
     * 获取或者设置配送方式的标题
     * @return [type] [description]
     */
    public function title()
    {
        return $this->title;
    }

    public function key()
    {
        return basename(__CLASS__);
    }

    /**
     * 返回计算好的运费
     * @return [type] [description]
     */
    public function price()
    {
        return '5.0';

    }

}