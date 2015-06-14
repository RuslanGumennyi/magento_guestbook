<?php
/**
 * @author Ruslan
 *
 *
 */
class RG_Guestbook_Model_Resource_Guestbook extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * DB read connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;

    /**
     * DB write connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_write;


    /**
     *
     * @see Mage_Core_Model_Resource_Abstract::_construct()
     */
    protected function _construct ()
    {
        $this->_init('guestbook/guestbook', 'message_id');
        $this->_read  = $this->_getReadAdapter();
        $this->_write = $this->_getWriteAdapter();
    }

    /**
     *
     * @param int $store
     * @return array
     */
    public function loadRecords($store)
    {
        $select = $this->_read->select();
        $select->from($this->getMainTable())
               ->where('store_id=:store')
               //->where('message_status=1')
               ->order('added_at DESC');
        if (false !== ($messages = $this->_read->fetchAssoc($select, array(':store' => $store ) ))) {
            return $messages;
        }
    }

    public function addRecord(array $data)
    {
        $this->_write->insert(
                    $this->getMainTable(),
                    $data
                );
        return $this;
    }
}
