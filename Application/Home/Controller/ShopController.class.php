<?php

namespace Home\Controller;


class ShopController extends CommonController
{

    public function indexAction()
    {
              
        // 推荐商品数据
        $m_goods = D('Goods');
        $this->assign('promote_goods_list', $m_goods->getPromote());

        // 展示首页模板
        $this->display();
        
    }

    /**
     * 搜索相关功能
     * @return [type] [description]
     */
    public function searchAction()
    {
        // 用户所填写的关键词
        $query = I('q', '', 'trim');

        // 搜索(不满足自动加载)
        require VENDOR_PATH . 'XunSearch/lib/XS.php';
        $project = 'goods';
        $xs = new XS($project);
        $search = $xs->search;
        // 是否模糊搜索
        // $search->setFuzzy(true);
        $search->setQuery($query);
        // 排序
        // $search->setSort('sort_number', 'ASC');
        // limit
        // $pagesize = 12;
        // $page = I('p', '1', 'intval');// 考虑过界问题
        // $offset = ($page-1) * $pagesize;
        // $search->setLimit($pagesize, $offset);
        $docs = $search->search();

        // 总记录数, 当前匹配的记录数
        $count = $search->getLastCount();
        $total = $search->getDbTotal();
        // 如果搜索匹配数量较少, 给出用户建议:
        if ($count <= 3) {
            // 需要给出建议
           $words1 = $search->getExpandedQuery($query, 3);
           $words2 = $search->getCorrectedQuery($query);
           // 合并两组词, 取出重复词即可
           $words = array_unique(array_merge($words1, $words2));
        }

    }

    /**
     * 商品详细信息
     */
    public function goodsAction()
    {

        $goods_id = I('get.goods_id', 0, 'trim');
        if ($goods_id == 0) {
            $this->redirect('/index', [], 0);
        }

        $m_goods = D('goods');

        // 获取商品信息
        $goods = $m_goods->find($goods_id);
        $this->assign('goods', $goods);

        // 面包屑信息
        $breadcrumb = $m_goods->getBreadcrumb($goods_id);
        $this->assign('breadcrumb', $breadcrumb);
        // dump($breadcrumb);die;
         
        // 图像展示
        $this->assign('image_list', M('GoodsImage')->where(['goods_id'=>$goods_id])->select());

        // 属性信息
        // join查询, 获取属性的信息, 及属性的输入类型(输入还是选择)
        $attribute_list = D('GoodsAttributeValue')->field('gav.*, ga.title attribute_title, at.title type_title')->where(['goods_id'=>$goods_id])->alias('gav')->join('left join __GOODS_ATTRIBUTE__ ga using(goods_attribute_id)')->join('left join __ATTRIBUTE_TYPE__ at using(attribute_type_id)')-> select();

        // 遍历属性列表
        // 获取属性对应的预设值列表
        // 将可选的选项属性, 独立存储.
        $option_list = [];
        foreach($attribute_list as $key=>$attribute) {
            // 获取属性对应的预设值列表
            // 判断是否为多选属性
            if ($attribute['type_title'] == 'select') {
                $attribute_list[$key]['option'] = M('AttributeOption')->where(['attribute_option_id'=>['in', $attribute['value']]])->select();
            }

            // 将可选的选项属性, 独立存储.
            // 判断是否为选项
            if ($attribute['is_option'] == '1') {
                $option_list[] = $attribute_list[$key];
            }
        }
        $this->assign('attribute_list', $attribute_list);
        $this->assign('option_list', $option_list);

        // dump($attribute_list);
        
        // 选项信息


        $this->display();
    }


    public function ajaxAction() 
    {

        $operate = I('request.operate', '', 'trim');
        if ($operate == '') {
            $this->ajaxReturn(['error'=>1, 'errorInfo'=>'没有操作']);
        }

        switch ($operate) {
            // 获取当前商品的货品
            case 'getProduct': 
                $product_list = D('GoodsProduct')->where(['goods_id'=>I('request.goods_id', '0')])->relation(true)->select();
                if ($product_list) {
                    $this->ajaxReturn(['error'=>0, 'rows'=>$product_list]);
                } else {
                    $this->ajaxReturn(['error'=>1, 'errorInfo'=>'当前商品不存在选项货品']);
                }
                break;
        }
    }
}