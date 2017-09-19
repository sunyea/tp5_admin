/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     2017-04-02 00:19:38                          */
/*==============================================================*/


drop table if exists sy_auth_group;

drop table if exists sy_auth_group_access;

drop table if exists sy_auth_rule;

drop table if exists sy_manager;

drop table if exists sy_menu;

/*==============================================================*/
/* Table: sy_auth_group                                         */
/*==============================================================*/
create table sy_auth_group
(
   id                   int not null auto_increment,
   title                varchar(50) not null,
   status               tinyint not null default 1,
   rules                varchar(500),
   primary key (id)
);

/*==============================================================*/
/* Table: sy_auth_group_access                                  */
/*==============================================================*/
create table sy_auth_group_access
(
   uid                  int not null,
   group_id             int not null,
   unique key uid_group_id (uid, group_id),
   key uid (uid),
   key group_id (group_id)
);

/*==============================================================*/
/* Table: sy_auth_rule                                          */
/*==============================================================*/
create table sy_auth_rule
(
   id                   int not null auto_increment,
   title                varchar(50) not null,
   name                 varchar(200) not null,
   type                 tinyint not null default 1,
   status               tinyint not null default 1,
   `condition`          varchar(200),
   `order`              int not null default 10,
   primary key (id),
   unique key name (name)
);

/*==============================================================*/
/* Table: sy_manager                                            */
/*==============================================================*/
create table sy_manager
(
   id                   int not null auto_increment,
   user_guid            varchar(32) not null,
   user_id              varchar(50) not null,
   user_pwd             varchar(255) not null,
   heard                varchar(200),
   name                 varchar(50) not null,
   email                varchar(50),
   status               tinyint not null default 1,
   login_ip             varchar(15),
   login_time           datetime,
   login_count          int not null default 0,
   primary key (id),
   unique key user_guid (user_guid),
   unique key user_id (user_id)
);

/*==============================================================*/
/* Table: sy_menu                                               */
/*==============================================================*/
create table sy_menu
(
   id                   int not null auto_increment,
   title                varchar(20) not null,
   icon                 varchar(20),
   status               tinyint not null default 1,
   url                  varchar(200) not null,
   parent               int not null default 0,
   `order`              int not null default 10,
   primary key (id)
);

/*==============================================================*/
/* 插入默认数据                                               */
/*==============================================================*/
INSERT INTO `sy_auth_rule` (`title`,`name`,`type`,`status`,`condition`,`order`) VALUES 
 ('后台主页','/admin/index/index',1,1,NULL,1),
 ('欢迎主页','/admin/index/welcome',1,1,NULL,2),
 ('系统设置','/admin/system/setting',1,1,NULL,10),
 ('人员管理','/admin/manager/index',1,1,NULL,11),
 ('添加人员','/admin/manager/add',1,1,NULL,12),
 ('修改人员','/admin/manager/update',1,1,NULL,13),
 ('删除人员','/admin/manager/delete',1,1,NULL,14),
 ('权限管理','/admin/rule/index',1,1,NULL,20),
 ('添加权限','/admin/rule/add',1,1,NULL,21),
 ('修改权限','/admin/rule/update',1,1,NULL,22),
 ('删除权限','/admin/rule/delete',1,1,NULL,23),
 ('角色管理','/admin/group/index',1,1,NULL,30),
 ('添加角色','/admin/group/add',1,1,NULL,31),
 ('修改角色','/admin/group/update',1,1,NULL,32),
 ('删除角色','/admin/group/delete',1,1,NULL,33),
 ('菜单管理','/admin/menu/index',1,1,NULL,40),
 ('添加菜单','/admin/menu/add',1,1,NULL,41),
 ('修改菜单','/admin/menu/update',1,1,NULL,42),
 ('删除菜单','/admin/menu/delete',1,1,NULL,43);

 INSERT INTO `sy_auth_group` (`title`,`status`,`rules`) VALUES 
 ('超级管理员',1,'1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19');

 INSERT INTO `sy_menu` (`title`,`icon`,`status`,`url`,`parent`,`order`) VALUES 
 ('系统设置','iconadmin-settings',1,'/admin/system/setting',0,1),
 ('人员管理','iconadmin-people',1,'/admin/manager/index',1,2),
 ('权限管理','iconadmin-safe',1,'/admin/rule/index',1,3),
 ('角色管理','iconadmin-group',1,'/admin/group/index',1,4),
 ('菜单管理','iconadmin-list',1,'/admin/menu/index',1,5);