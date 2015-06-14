<?php
/**
 * @author Ruslan Gumennyi
 *
 *
 */
class RG_Guestbook_IndexController extends Mage_Core_Controller_Front_Action
{
    const XML_PATH_RECAPTCHA_PUB_KEY = 'guestbook/recaptcha/public_key';
    const XML_PATH_RECAPTCHA_PRV_KEY = 'guestbook/recaptcha/private_key';
    const XML_PATH_RECAPTCHA_ENABLED = 'guestbook/recaptcha/enabled';
    const XML_PATH_ENABLED           = 'guestbook/guestbook/enabled';

    protected $_isReCaptchaEnabled = false;
    protected $_isCustomer = false;

    public function preDispatch ()
    {
        parent::preDispatch();
        if (! Mage::getStoreConfigFlag(self::XML_PATH_ENABLED)) {
            $this->norouteAction();
        }
        if (Mage::getStoreConfigFlag(self::XML_PATH_RECAPTCHA_ENABLED)) {
            if (Mage::getStoreConfig(self::XML_PATH_RECAPTCHA_PRV_KEY) &&
             Mage::getStoreConfig(self::XML_PATH_RECAPTCHA_PUB_KEY)) {
                $this->_isReCaptchaEnabled = true;
            }
        }
        if (Mage::getModel('customer/session')->isLoggedIn()) {
            $this->_isReCaptchaEnabled = false;
        }
        if (Mage::getModel('core/session')->getShowList()) {
            $this->_isReCaptchaEnabled = false;
        }

    }

    public function indexAction ()
    {
        $this->loadLayout();
        $this->getLayout()
            ->getBlock('guestbookForm')
            ->setFormAction(Mage::getUrl('*/*/post'));

        if(Mage::getModel('core/session')->getShowList()) {
            $this->getLayout()
                 ->getBlock('records')
                 ->setRecords(Mage::getModel('guestbook/guestbook')->getRecords());
        }

        if ($this->_isReCaptchaEnabled) {
                    $this->getLayout()
                         ->getBlock('recaptcha')
                         ->setEnabled(true)
                         ->setPrvKey(Mage::getStoreConfig(self::XML_PATH_RECAPTCHA_PRV_KEY))
                         ->setPubKey(Mage::getStoreConfig(self::XML_PATH_RECAPTCHA_PUB_KEY));
        }

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        $this->renderLayout();
    }

    public function postAction ()
    {
        $post = $this->getRequest()->getPost();
        if ($post) {
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {
                $error = false;

                if ($this->_isReCaptchaEnabled) {
                    $recaptcha_pub_key = Mage::getStoreConfig(self::XML_PATH_RECAPTCHA_PUB_KEY);
                    $recaptcha_prv_key = Mage::getStoreConfig(self::XML_PATH_RECAPTCHA_PRV_KEY);

                    $recaptcha = new Zend_Service_ReCaptcha($recaptcha_pub_key, $recaptcha_prv_key);

                    if (! empty($post['recaptcha_response_field'])) {
                        $result = $recaptcha->verify($post['recaptcha_challenge_field'], $post['recaptcha_response_field']);
                        if (! $result->isValid()) {
                            $error = true;
                        }
                    } else {
                        $error = true;
                    }
                }
                if (! Zend_Validate::is(trim($post['name']), 'NotEmpty')) {
                    $error = true;
                }
                if (! Zend_Validate::is(trim($post['comment']), 'NotEmpty')) {
                    $error = true;
                }
                if (Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
                    $error = true;
                }
                if ($error) {
                    throw new Exception();
                }
                $data = array(
                        'author_name'  => htmlentities($post['name']),
                        'message_text' => htmlentities($post['comment']),
                        );
                Mage::getModel('guestbook/guestbook')->addRecord($data);
                Mage::getModel('core/session')->setShowList(true);

                $translate->setTranslateInline(true);

                if($post['ajax']) {
                    $this->_forward('defaultList');
                } else {
                    $this->_redirect('*/*/');
                }
                return;
            } catch (Exception $e) {
                if($post['ajax']) {
                    $this->_forward('defaultError');
                } else {
                    $translate->setTranslateInline(true);
                    Mage::getSingleton('customer/session')->addError(
                    Mage::helper('guestbook')->__(
                    'Unable to submit your request. Please, try again later'));
                    $this->_redirect('*/*/');
                }
                return;
            }
        } else {
            $this->_redirect('*/*/');
        }
    }

    public function rssAction()
    {
            $records = Mage::getModel('guestbook/guestbook')->getRecords();

            $feedArray = array (
                'title'    => 'Guestbook News',
                'link'     => Mage::getUrl('guestbook/index/rss'),
                'language' => 'en-us',
                'charset'  => 'utf-8',
                'pubDate'  => time(),
                'entries'  => array()
            );

            foreach ( $records as $_item ) {
                //$link = Mage::helper('guestbook')->getUrl($_item['news_id']);
                $link = Mage::getUrl('guestbook/index/rss');
                $date = new Zend_Date($_item['date']);

                $feedArray['entries'][] = array (
                    'title'       =>    $_item['author_name'],
                    'link'        =>    $link,
                    'guid'        =>    $link,
                    'description' =>    $_item['message_text'],
                    'lastUpdate'  =>    $date->get(Zend_Date::TIMESTAMP)
                );

            }

            $feed = Zend_Feed::importArray($feedArray, 'rss');
            $feed->send();
            exit;

        }

    protected function defaultListAction()
    {
        $layout = $this->getLayout();
        $layout->getUpdate()
            ->addHandle('guestbook_index_list')
            ->load();

        $layout->generateXml()
               ->generateBlocks();

        $records = $layout->getBlock('records')
                    ->setRecords(Mage::getModel('guestbook/guestbook')->getRecords())
                    ->toHtml();

        echo $records;
    }
    protected function defaultErrorAction()
    {
        $error  = '<div class="error-msg">';
        $error .= Mage::helper('guestbook')->__(
                    'Unable to submit your request. Please, make sure you typed all correctly, or try again later');
        $error .= '</div>';
        echo $error;
    }
}
?>
