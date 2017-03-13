<?php
namespace Back\Controller;

use Think\Controller;
use Think\Page;

class PaymentController extends Controller
{
    /**
     * 添加动作
     */
    public function addAction()
    {
        // 判断是否为POST数据提交
        if (IS_POST) {
            // 数据处理
            // $model = M('Payment');
            $model = D('Payment');
            $result = $model->create();

            if (!$result) {
                $this->error('数据添加失败: ' . $model->getError(), U('add'));
            }

            $result = $model->add();
            if (!$result) {
                $this->error('数据添加失败:' . $modle->getError(), U('add'));
            }
            // 成功重定向到list页
            $this->redirect('list', [], 0);
        } else {
            // 表单展示
            $this->display();
        }
    }


    /**
     * 列表相关动作
     */
    public function listAction()
    {

        $model = M('Payment');  

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
         
        $rows = $model->where($cond)->order($sort)->page("$page, $pagesize")->select();
        $this->assign('rows', $rows);

        // 插件目录中的配送方式
        $payment_path = COMMON_PATH . 'Payment/';
        // 遍历所有的文件, 依次require即可
        $handle = opendir($payment_path);
        $payment_list = [];
        while($filename = readdir($handle)) {
            if (in_array($filename, ['.', '..'])) continue;
            // 全部的配送方式文件
            require $payment_path . $filename;
            // 反射机制检测是实现了I_payment接口, 再记录
           
            $class_name = strchr($filename, '.', true);
            $full_class_name = 'Common\Payment\\' . $class_name;
            $rf_c = new \ReflectionClass($full_class_name);
            // 1获取结构
            if ($rf_c->implementsInterface('Common\Interfaces\I_Payment')) {
                // $payment_list[$class_name] = new $full_class_name;
                // 2代理执行 实例化对象
                $payment_list[$class_name] = $rf_c->newInstance();
            }
            // class_implements(class)
        }
        closedir($handle);
        // 全部的配送方式类
        $this->assign('payment_list', $payment_list);



        $this->display();
    }

    /**
     * 编辑
     */
    public function editAction()
    {

        if (IS_POST) {

            $model = D('Payment');
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
            $payment_id = I('get.payment_id', '', 'trim');
            $this->assign('row', M('Payment')->find($payment_id));

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
                $cond = ['payment_id' => ['in', $selected]];
                M('Payment')->where($cond)->delete();
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
                $count = M('Payment')->where($cond)->count();
                // 如果记录数>0, 条件为真, 说明存在记录, 重复, 验证未通过, 响应false
                echo $count ? 'false' : 'true';
            break;
        }
    }
}