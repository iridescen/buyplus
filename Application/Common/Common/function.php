<?php

/**
 * 前后台通用的函数库
 */

/**
 * [getConfig description]
 * @param  [type] $key     配置项的key字段
 * @param  string $default 如果没有匹配配置项, 则使用默认值
 * @return [type]          配置项的值
 */
function getConfig($key, $default=NULL)
{
    // 初始缓存配置
    S(['type'=>'File']);
    if ( ! $value = S('setting_' . $key)) {
        $value = M('Setting')->where(['key'=>$key])->getField('value');
        S('setting_'.$key,  $value);
    }
    return is_null($value) ? $default : $value;
}