<?php
namespace Back\Controller;

use Think\Controller;
use Think\Page;

class SettingController extends Controller
{
    /**
     * 添加动作
     */
    public function addAction()
    {
        // 判断是否为POST数据提交
        if (IS_POST) {
            // 数据处理
            // $model = M('Setting');
            $model = D('Setting');
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

        // 获取分组
        $m_group = M('SettingGroup');
        $group_rows = $m_group->select();
        $this->assign('group_rows', $group_rows);

        // 获取配置项
        $m_setting = D('Setting');
        $setting_rows = $m_setting
                    ->alias('s')
                    ->join('left join __SETTING_TYPE__ st Using(setting_type_id)')
                    ->relation(true)
                    ->select();

        // 遍历所有的配置项, 分组管理
        $group_setting = [];
        foreach($setting_rows as $setting) {
            // 判断是否为多选类型, 如果是, 拆分value为数组
            if ($setting['type_title'] == 'select-multi') {
                $setting['value_list'] = explode(',', $setting['value']);
            }
            // 当前分组ID
            $group_id = $setting['setting_group_id'];
            // 将配置项, 存储在以组ID为下标的数组.
            $group_setting[$group_id][] = $setting;
        }
        // [1=>[配置项1, 配置项2]
        // [2=>[配置项3, 配置项4]
        $this->assign('group_setting', $group_setting);


        $this->display();
    }

    /**
     * 更新
     */
    public function updateAction()
    {
        // 获取所有的配置项
        $setting = I('post.setting');
        // dump($setting);die;
        $m_setting = M('Setting');
        // 保证多选配置项, 存在合理的数据
        // 获得所有的多选配置项ID.
        $cond['type_title'] = 'select-multi';
        $multi_setting = $m_setting->alias('s')->join('left join __SETTING_TYPE__ st Using(setting_type_id)')->where($cond)->getField('setting_id', true);
        // var_dump($multi_setting);die;
        // 判断多选类型的配置项是否出现在用户提交的post数据中
        foreach($multi_setting as $m_setting_id) {
            if (! isset($setting[$m_setting_id])) {
                // 用户没有选择任何多选选项
                $setting[$m_setting_id] = '';
            }
        }
        // 遍历配置项, 更新配置项
        foreach($setting as $setting_id=>$value) {
            // 如果是数组, 多选类型, 则将多选值逗号连接起来
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $m_setting->save(['setting_id'=>$setting_id, 'value'=>$value]);
        }

        // 清空所有的配置项缓存
        // 获取所有的配置项的key, key与缓存项的key是对应
        S(['type'=>'File']);
        foreach($m_setting->getField('key', true) as $key) {
            S('setting_' . $key, null);
        }

        $this->redirect('list', [], 0);
    }

    /**
    * 批处理
    */
    public function multiAction()
    {
        var_dump(getConfig('shop_title'));
        var_dump(getConfig('non_key'));
        var_dump(getConfig('non_key', 'default-value'));
    }


}