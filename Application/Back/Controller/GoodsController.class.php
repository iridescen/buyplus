<?php
namespace Back\Controller;

use Think\Controller;
use Think\Page;

class GoodsController extends Controller
{
    /**
     * 将对应的goodsID的的商品信息加入到索引中
     * @param [type] $goods_id [description]
     */
    protected function addIndex($goods_id)
    {
        // 利用商品ID, 获取需要加入到索引中的文档信息
        $row = M('Goods')->field('goods_id, name, UPC, description, b.title brand_title, c.title category_title, price, quantity, date_available, g.sort_number')
                        ->alias('g')
                        ->join('left join __BRAND__ b using(brand_id)')
                        ->join('left join __CATEGORY__ c using(category_id)')
                        ->find($goods_id);
        // 获取索引管理对象
        require VENDOR_PATH . 'XunSearch/lib/XS.php';
        $xs = new XS('goods');
        $index = $xs->index;
        // 索引文档对象处理
        $doc = new XSDocument;
        $doc->setFields($row);
        // 添加索引
        $index->add($doc);
    }
    /**
     * 添加动作
     */
    public function addAction()
    {
        // 判断是否为POST数据提交
        if (IS_POST) {
            // 数据处理
            // $model = M('Goods');
            $model = D('Goods');
            $result = $model->create();

            if (!$result) {
                $this->error('数据添加失败: ' . $model->getError(), U('add'));
            }

            $result = $model->add();
            if (!$result) {
                $this->error('数据添加失败:' . $modle->getError(), U('add'));
            }

            // 自动更新当前商品对应的索引
            $this->addIndex($goods_id);
            
            // 日志层面管理 
            
            // 成功重定向到list页
            $this->redirect('list', [], 0);
        } else {
            // 一: 获取关联数据
            // 品牌
            $this->assign('brand_list', M('Brand')->order('sort_number')->select());
            // 分类
            $this->assign('category_list', D('Category')->getTreeList());
            // 长度单位
            $this->assign('length_unit_list', M('LengthUnit')->select());
            // 重量单位
            $this->assign('weight_unit_list', M('WeightUnit')->select());
            // 税类型
            $this->assign('tax_list', M('Tax')->select());
            // 库存状态
            $this->assign('stock_status_list', M('StockStatus')->select());
            
            // 二: 表单展示
            $this->display();
        }
    }


    /**
     * 列表相关动作
     */
    public function listAction()
    {

        $model = M('Goods');  

        // 分页, 搜索, 排序等
        // 搜索, 筛选, 过滤
        // 判断用户传输的搜索条件, 进行处理
        // $filter 表示用户输入的内容
        // $cond 表示用在模型中查询条件
        $cond = $filter = [];// 初始条件
        // 在生成代码的基础上, 自定义完成搜索条件
        // 
        // 分配筛选数据, 到模板, 为了展示搜索条件
        $this->assign('filter', $filter);

        // 排序
        $sort = $order = [];
        // 考虑用户所传递的排序方式和字段
        // 在生成代码的基础上,自定义默认的排序字段(假设,表中存在sort_number字段, 不存在需要修改)
        // $order['field'] = I('get.field', 'sort_number', 'trim');// 初始排序, 字段
        // $order['type'] = I('get.type', 'asc', 'trim');// 初始排序, 方式

        if (!empty($order)) {
            $sort = $order['field'] . ' ' . $order['type'];
        }
        $this->assign('order', $order);

        // 分页
        $page = I('get.p', '1');// 当前页码
        $pagesize = 10;// 每页记录数\\

        // 获取总记录数
        $count = $model->where($cond)->count();// 合计
        $t_page = new Page($count, $pagesize);// use Think\Page;
        // 配置格式
        $t_page->setConfig('next', '&gt;');
        $t_page->setConfig('last', '&gt;|');
        $t_page->setConfig('prev', '&lt;');
        $t_page->setConfig('first', '|&lt;');
        $t_page->setConfig('theme', '<div class="col-sm-6 text-left"><ul class="pagination">%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% </ul></div><div class="col-sm-6 text-right">%HEADER%</div>');
        $t_page->setConfig('header', '显示开始 %FIRST_ROW% 到 %LAST_ROW% 之 %TOTAL_ROW% （总 %TOTAL_PAGE% 页）');
        // 生成HTML代码
        $page_html = $t_page->show();
        $this->assign('page_html', $page_html);
          
        $rows = $model->where($cond)->order($sort)->page("$page, $pagesize")->select();
        $this->assign('rows', $rows);


        $this->display();
    }

    /**
     * 编辑
     */
    public function editAction()
    {

        if (IS_POST) {

            $model = D('Goods');
            $result = $model->create();

            if (!$result) {
                $this->error('数据修改失败: ' . $model->getError(), U('edit'));
            }

            $result = $model->save();
            if (!$result) {
                $this->error('数据修改失败:' . $modle->getError(), U('edit'));
            }
            // 成功重定向到list页
            $this->redirect('list', [], 0);

        } else {

            // 获取当前编辑的内容
            $goods_id = I('get.goods_id', '', 'trim');
            $this->assign('row', M('Goods')->find($goods_id));

            // 展示模板
            $this->display();
        }
    }


    /**
     * 批处理
     */
    public function multiAction()
    {
        // 确定动作
        $operate = I('post.operate', 'delete', 'trim');
        // 确定ID列表
        $selected = I('post.selected', []);
        
        // 如果为空数组, 表示没有选择, 则立即跳转回列表页.
        if (empty($selected)) {
            $this->redirect('list', [], 0);
            return ;
        }

        switch ($operate) {
            case 'delete':
                // 使用in条件, 删除全部的品牌
                $cond = ['goods_id' => ['in', $selected]];
                M('Goods')->where($cond)->delete();
                $this->redirect('list', [], 0);
                break;
            default:
                # code...
                break;
        }
    }


    /**
     * ajax的相关请求
     */
    public function ajaxAction()
    {
        $operate = I('request.operate', null, 'trim');

        if (is_null($operate)) {
            return ;
        }

        switch ($operate) {
            // 验证品牌名称唯一的操作
            case 'checkBrandUnique':
                // 获取填写的品牌名称
                $title = I('request.title', '');
                $cond['title'] = $title;
                // 判断是否传递了brand_id
                $brand_id = I('request.brand_id', null);
                if (!is_null($brand_id)) {
                    // 存在, 则匹配与当前ID不相同的记录
                    $cond['brand_id'] = ['neq', $brand_id];
                }
                // 获取模型后, 利用条件获取匹配的记录数
                $count = M('Goods')->where($cond)->count();
                // 如果记录数>0, 条件为真, 说明存在记录, 重复, 验证未通过, 响应false
                echo $count ? 'false' : 'true';
            break;
        }
    }
}