<?php

namespace Home\Controller;
use Think\Controller;


class CommonController extends Controller
{


    public function _initialize()
    {
        // 数据的获取
        // 分类数据
        $m_category = D('Category');
        $category_list = $m_category->getNested();// nested嵌套
        $this->assign('category_list', $category_list);

        // 当前会员信息
        $this->assign('member', session('member'));
    }

    /**
     * 校验当前是否登录
     * @param $target 登录后的跳转目标URL
     * @return [type] [description]
     */
    protected function checkLogin($target='', $is_redirect=true)
    {
        if (session('member')) {
            return true;
        } else {
            if ($is_redirect) {   
                // 立即跳转到登录页面
                if ($target !== '') {
                    // 存储在session中保存
                    session('login_target', $target);
                }
                $this->redirect('/login', [], 0);
            }
            return false; 
        }
    }
}