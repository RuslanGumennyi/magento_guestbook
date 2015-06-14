<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'guestbook/guestbook'
 */
$table = $installer->getConnection()
            ->newTable($installer->getTable('guestbook/guestbook'))
            ->addColumn('message_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                    'identity'  => true,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    ), 'Message Id')
            ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                    'unsigned'  => true,
                    'default'   => '0',
                    ), 'Store Id')
            ->addColumn('author_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                    ), 'Author Name')
            ->addColumn('added_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                    ), 'Added at')
            ->addColumn('message_text', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
                    ),'Message text')
            ->addColumn('message_status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                    'nullable'  => false,
                    'default'   => '0',
                    ), 'Message Status')
            ->addIndex($installer->getIdxName('guestbook/guestbook', array('message_status')),
                    array('message_status'))
            ->addIndex($installer->getIdxName('guestbook/guestbook', array('store_id')),
                    array('store_id'))
            ->addForeignKey($installer->getFkName('guestbook/guestbook', 'store_id', 'core/store', 'store_id'),
                    'store_id', $installer->getTable('core/store'), 'store_id',
                    Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
            ->setComment('Guestbook');
$installer->getConnection()->createTable($table);
$installer->endSetup();
