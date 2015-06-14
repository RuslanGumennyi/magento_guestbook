<?php
/**
 * @author Ruslan Gumennyi
 *
 *
 */
class RG_Guestbook_Model_Guestbook extends Mage_Core_Model_Abstract
{
    const STATUS_VERIFIED     = 1;
    const STATUS_NOT_VERIFIED = 2;

    protected function _construct()
    {
        $this->_init('guestbook/guestbook');
    }

    /**
     *
     * @return array
     */
    public function getRecords()
    {
        $store    = Mage::app()->getStore()->getWebsiteId();
        $messages = Mage::getResourceModel('guestbook/guestbook')->loadRecords($store);

        return $messages;
    }

    /**
     *
     * Enter description here ...
     * @param array $data
     * @return Mage_Core_Model_Abstract
     */
    public function addRecord(array $data)
    {
        if (!empty($data)) {
            $data['store_id'] = Mage::app()->getStore()->getWebsiteId();
            $data['added_at'] = date('Y-m-d H:i:s');
            Mage::getResourceModel('guestbook/guestbook')->addRecord($data);
        }
        return $this;
    }

}
?>
