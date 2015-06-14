<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS `{$installer->getTable('guestbook')}`;
CREATE TABLE `{$installer->getTable('guestbook')}` (
  `message_id` int(11) unsigned NOT NULL auto_increment,
  `store_id` smallint(5) unsigned default '0',
  `author_name` VARCHAR(255) default NULL,
  `added_at` datetime default NULL,
  `message_text` text,
  `message_status` int(1) NOT NULL default '0',
  PRIMARY KEY  (`message_id`),
  KEY `GUESTBOOK_MESSAGE_STATUS` (`message_status`),
  KEY `FK_GUESTBOOK_STORE` (`store_id`),
  CONSTRAINT `FK_GUESTBOOK_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Guestbook';
");
$installer->endSetup();
