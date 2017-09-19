-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.7.12-log


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema zzccdb168
--

CREATE DATABASE IF NOT EXISTS zzccdb168;
USE zzccdb168;

--
-- Definition of table `zc_auth_group`
--

DROP TABLE IF EXISTS `zc_auth_group`;
CREATE TABLE `zc_auth_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `rules` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `zc_auth_group`
--

/*!40000 ALTER TABLE `zc_auth_group` DISABLE KEYS */;
INSERT INTO `zc_auth_group` (`id`,`title`,`status`,`rules`) VALUES 
 (1,'超级管理员',1,'1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22'),
 (3,'测试角色',1,NULL),
 (5,'wwwww',1,NULL),
 (6,'eeeeee',1,NULL);
/*!40000 ALTER TABLE `zc_auth_group` ENABLE KEYS */;


--
-- Definition of table `zc_auth_group_access`
--

DROP TABLE IF EXISTS `zc_auth_group_access`;
CREATE TABLE `zc_auth_group_access` (
  `uid` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `zc_auth_group_access`
--

/*!40000 ALTER TABLE `zc_auth_group_access` DISABLE KEYS */;
INSERT INTO `zc_auth_group_access` (`uid`,`group_id`) VALUES 
 (1,1),
 (1,4);
/*!40000 ALTER TABLE `zc_auth_group_access` ENABLE KEYS */;


--
-- Definition of table `zc_auth_rule`
--

DROP TABLE IF EXISTS `zc_auth_rule`;
CREATE TABLE `zc_auth_rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `condition` varchar(200) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT '10',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `zc_auth_rule`
--

/*!40000 ALTER TABLE `zc_auth_rule` DISABLE KEYS */;
INSERT INTO `zc_auth_rule` (`id`,`title`,`name`,`type`,`status`,`condition`,`order`) VALUES 
 (1,'后台主页','/admin/index/index',1,1,NULL,1),
 (2,'欢迎主页','/admin/index/welcome',1,1,NULL,2),
 (3,'系统设置','/admin/system/setting',1,1,NULL,10),
 (4,'人员管理','/admin/manager/index',1,1,NULL,11),
 (5,'添加人员','/admin/manager/add',1,1,NULL,12),
 (6,'修改人员','/admin/manager/update',1,1,NULL,13),
 (7,'删除人员','/admin/manager/delete',1,1,NULL,14),
 (8,'权限管理','/admin/rule/index',1,1,NULL,20),
 (9,'添加权限','/admin/rule/add',1,1,NULL,21),
 (10,'修改权限','/admin/rule/update',1,1,'',22),
 (11,'删除权限','/admin/rule/delete',1,1,'',23),
 (12,'角色管理','/admin/group/index',1,1,'',30),
 (13,'添加角色','/admin/group/add',1,1,'',31),
 (14,'修改角色','/admin/group/update',1,1,NULL,32),
 (15,'删除角色','/admin/group/delete',1,1,NULL,33),
 (16,'菜单管理','/admin/menu/index',1,1,NULL,40),
 (17,'添加菜单','/admin/menu/add',1,1,NULL,41),
 (18,'修改菜单','/admin/menu/update',1,1,NULL,42),
 (19,'删除菜单','/admin/menu/delete',1,1,NULL,43),
 (20,'修改密码','/admin/index/changepassword',1,1,NULL,3),
 (21,'修改个人资料','/admin/index/changeinfo',1,1,NULL,4),
 (22,'上传头像','/admin/index/upheard',1,1,NULL,5);
/*!40000 ALTER TABLE `zc_auth_rule` ENABLE KEYS */;


--
-- Definition of table `zc_manager`
--

DROP TABLE IF EXISTS `zc_manager`;
CREATE TABLE `zc_manager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_guid` varchar(32) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `user_pwd` varchar(255) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `login_ip` varchar(15) DEFAULT NULL,
  `login_time` datetime DEFAULT NULL,
  `login_count` int(11) NOT NULL DEFAULT '0',
  `heard` varchar(100) DEFAULT NULL,
  `login_local` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_guid` (`user_guid`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `zc_manager`
--

/*!40000 ALTER TABLE `zc_manager` DISABLE KEYS */;
INSERT INTO `zc_manager` (`id`,`user_guid`,`user_id`,`user_pwd`,`name`,`email`,`status`,`login_ip`,`login_time`,`login_count`,`heard`,`login_local`) VALUES 
 (1,'72cafd80bc18d6ae8e9651a720606f1f','admin','$2y$10$yrbm87MgKuI.57r3p.tRuOoKCwTPi6EiQJLM44UDcWhAYqbWKJJFW','超级管理员','7192506@qq.com',1,'192.168.1.7','2017-04-22 22:22:25',98,'/upload/user/admin//6512bd43d9caa6e02c990b0a82652dca.jpg','未分配或者内网IP');
/*!40000 ALTER TABLE `zc_manager` ENABLE KEYS */;


--
-- Definition of table `zc_menu`
--

DROP TABLE IF EXISTS `zc_menu`;
CREATE TABLE `zc_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL,
  `icon` varchar(20) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `url` varchar(200) NOT NULL,
  `parent` int(11) NOT NULL DEFAULT '0',
  `order` int(11) NOT NULL DEFAULT '10',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `zc_menu`
--

/*!40000 ALTER TABLE `zc_menu` DISABLE KEYS */;
INSERT INTO `zc_menu` (`id`,`title`,`icon`,`status`,`url`,`parent`,`order`) VALUES 
 (1,'系统设置','iconadmin-settings',1,'/admin/system/setting',0,1),
 (2,'人员管理','iconadmin-people',1,'/admin/manager/index',1,2),
 (3,'权限管理','iconadmin-safe',1,'/admin/rule/index',1,3),
 (4,'角色管理','iconadmin-group',1,'/admin/group/index',1,4),
 (5,'菜单管理','iconadmin-list',1,'/admin/menu/index',1,5);
/*!40000 ALTER TABLE `zc_menu` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
