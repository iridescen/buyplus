<?php

namespace Home\Controller;

use Think\Controller;


class ShopController extends Controller
{

    public function indexAction()
    {
        // 展示首页模板
        // 如果需要获取配置的商店标题
        $shop_title = getConfig('shop_title', '败家');
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
}