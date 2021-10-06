<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Standard.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
abstract class Core_Controller_Action_Standard extends Engine_Controller_Action
{
    public $autoContext = true;

    public function __construct(
        Zend_Controller_Request_Abstract $request,
        Zend_Controller_Response_Abstract $response,
        array $invokeArgs = []
    ) {

        // Pre-init setSubject
        try {
            if ('' !== ($subject = trim((string) $request->getParam('subject')))) {
                $subject = Engine_Api::_()->getItemByGuid($subject);
                if (($subject instanceof Core_Model_Item_Abstract) && $subject->getIdentity() && !Engine_Api::_()->core()->hasSubject()) {
                    Engine_Api::_()->core()->setSubject($subject);
                }
            }
        } catch (Exception $e) {
            // Silence
            //throw $e;
        }

        // Parent
        parent::__construct($request, $response, $invokeArgs);
    }

    public function postDispatch()
    {
        if ($this->getRequest()->get('ajax-upload')) {
            $this->sendJson($this->view);
        }

        $layoutHelper = $this->_helper->layout;
        if(!empty($_SESSION['requirepassword'])){
            $layoutHelper->setLayout('default-simple');
        }
        if ($layoutHelper->isEnabled() && !$layoutHelper->getLayout()) {
            $layoutHelper->setLayout('default');
        }
        if ('default' == $layoutHelper->getLayout() && $this->_getParam('module', false)) {
            // Increment page views and referrer
            Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.views');
            Engine_Api::_()->getDbtable('referrers', 'core')->increment();
        }
    }

    protected function sendJson($response)
    {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    protected function isAjax()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    protected function exceptionWrapper(
        Exception $exception,
        Engine_Form $form = null,
        Zend_Db_Adapter_Abstract $db = null
    ) {
        if ($db !== null) {
            $db->rollBack();
        }

        if (!empty($_FILES['Filedata']) &&
            (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/adobe/i', $_SERVER['HTTP_USER_AGENT']))) {
            header('Content-Type: application/json');
            echo Zend_Json::encode(['error' => $exception->getMessage()]);
            exit;
        }

        if ($form !== null) {
            if ($exception instanceof Engine_Image_Exception) {
                $form->addError(Zend_Registry::get('Zend_Translate')->_('The image you selected was too large.'));
            } elseif ($exception->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
                $form->addError($exception->getMessage());
            }

            return $form;
        } else {
            throw $exception;
        }
    }

    protected function _redirectCustom($to, $options = [])
    {
        $options = array_merge([
            'prependBase' => false
        ], $options);

        // Route
        if (is_array($to) && empty($to['uri'])) {
            $route = (!empty($to['route']) ? $to['route'] : 'default');
            $reset = (isset($to['reset']) ? $to['reset'] : true);
            unset($to['route']);
            unset($to['reset']);
            $to = $this->_helper->url->url($to, $route, $reset);
            // Uri with options
        } elseif (is_array($to) && !empty($to['uri'])) {
            $to = $to['uri'];
            unset($params['uri']);
            $params = array_merge($params, $to);
        } elseif (is_object($to) && method_exists($to, 'getHref')) {
            $to = $to->getHref();
        }

        if (!is_scalar($to)) {
            $to = (string) $to;
        }

        $message = (!empty($options['message']) ? $options['message'] : 'Changes saved!');

        switch ($this->_helper->contextSwitch->getCurrentContext()) {
            case 'smoothbox':
                return $this->_forward('success', 'utility', 'core', [
                    'messages' => [$message],
                    'smoothboxClose' => true,
                    'redirect' => $to
                ]);
                break;
            case 'json':
            case 'xml':
            case 'async':
                // What should be do here?
                //break;
            default:
                return $this->_helper->redirector->gotoUrl($to, $options);
                break;
        }
    }
}
