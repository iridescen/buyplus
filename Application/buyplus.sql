create database buyplus3 charset=utf8;

use buyplus3;
-- 会员表
create table kang_member
(
	member_id int unsigned auto_increment,
	email varchar(255),
	telephone varchar(16),
	password varchar(64) not null default '',
	name varchar(64) not null default '',
	is_newsletter tinyint not null default 0, -- 不订阅
	primary key (member_id),
	unique key (email),
	unique key (telephone),
	unique key (name)
) charset=utf8;
-- 促销活动表
create table kang_event
(
	event_id int unsigned auto_increment,
	title varchar(64) not null default '',
	primary key (event_id)
) charset=utf8;
-- 活动会员关联表
create table kang_event_member
(
	event_member_id int unsigned auto_increment,
	event_id int unsigned not null default 0,
	member_id int unsigned not null default 0,
	primary key (event_member_id)
) charset=utf8;
insert into kang_event values (101, '2016双11');
insert into kang_event values (100, '2016国庆大促');
insert into kang_event_member values (null, 101, 14);
insert into kang_event_member values (null, 100, 14);
insert into kang_event_member values (null, 101, 17);

-- 会员登陆行为日志
drop table if exists kang_member_login_log;
create table kang_member_login_log(
	member_login_log_id int unsigned auto_increment,
	member_id int unsigned not null default 0,
	login_time int not null default 0, -- 登陆时间
	login_ip int unsigned not null default 0, -- 登陆IP
	error_number int unsigned not null default 0, -- 错误次数
	primary key (member_login_log_id),
	index (member_id)
) charset=utf8;
insert into kang_member_login_log values (null, 11, unix_timestamp()-50000, inet_aton('22.45.163.11'), 0);
insert into kang_member_login_log values (null, 11, unix_timestamp()-10000, inet_aton('22.45.165.11'), 0);
insert into kang_member_login_log values (null, 11, unix_timestamp(), inet_aton('22.45.163.12'), 0);


create table member_regisger_log();-- 注册行为日志


CREATE TABLE kang_session 
(
	session_id varchar(255) NOT NULL,
	session_expire int(11) NOT NULL,
	session_data blob,
	UNIQUE KEY `session_id` (`session_id`)
) charset=utf8;



-- 商品分类表
drop table if exists kang_category;
create table kang_category (
	category_id int unsigned auto_increment,
	title varchar(32) not null default '',
	parent_id int unsigned not null default 0,
	sort_number int not null default 0,


	image varchar(255) not null default '', -- 分类图片
	image_thumb varchar(255) not null default '', -- 分类图片缩略图
	-- 前台展示
	is_used boolean not null default 1, -- tinyint(1)
	is_nav tinyint not null default 1, -- 针对顶级分类

	-- SEO优化
	meta_title varchar(255) not null default '',
	meta_keywords varchar(255) not null default '',
	meta_description varchar(1024) not null default '',
	primary key (category_id),
	index (parent_id),
	index (sort_number)
) charset=utf8;

insert into kang_category values (1, '未分类', 0, -1, '', '', 0, 0, '', '', '');
insert into kang_category values (5, '眼镜', 0, 0, '', '', 1, 1, '', '', '');
insert into kang_category values (6, '男士眼镜', 5, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (7, '女士眼镜', 5, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (8, '飞行员眼镜', 5, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (9, '驾驶镜', 5, 0,'', '',  1, 0, '', '', '');
insert into kang_category values (10, '太阳镜', 5, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (11, '图书', 0, 0, '', '', 1, 1, '', '', '');
insert into kang_category values (12, '历史', 11, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (14, '科技', 11, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (15, '计算机', 11, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (16, '电子书', 11, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (17, '科普', 14, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (18, '建筑', 14, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (19, '工业技术', 14, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (20, '电子通信', 14, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (21, '自然科学', 14, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (22, '互联网', 15, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (23, '计算机编程', 15, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (24, '硬件，攒机', 15, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (25, '大数据', 15, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (26, '移动开发', 15, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (27, 'PHP', 15, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (28, '近代史', 12, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (29, '当代史', 12, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (30, '古代史', 12, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (31, '先秦百家', 12, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (32, '三皇五帝', 12, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (33, '励志', 16, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (34, '小说', 16, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (35, '成功学', 16, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (36, '经济金融', 16, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (37, '免费', 16, 0, '', '', 1, 0, '', '', '');



create table kang_brand
(
	brand_id int unsigned auto_increment,
	title varchar(32) not null default '', -- 品牌名
	logo varchar(255) not null default '', -- 品牌logo
	logo_ori varchar(255) not null default '', -- 品牌logo原始图像文件
	sort_number int not null default 0, -- 排序

	created_at int not null default 0, -- 创建时间
	updated_at int not null default 0, -- 修改时间
	primary key (brand_id),
	index (sort_number),
	index (title)
) charset=utf8;