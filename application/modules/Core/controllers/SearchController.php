<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: SearchController.php 9906 2013-02-14 02:54:51Z shaun $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_SearchController extends Core_Controller_Action_Standard
{
    public function indexAction()
    {
        $searchApi = Engine_Api::_()->getApi('search', 'core');

        // check public settings
        $require_check = Engine_Api::_()->getApi('settings', 'core')->core_general_search;
        if (!$require_check) {
            if (!$this->_helper->requireUser()->isValid()) {
                return;
            }
        }

        // Prepare form
        $this->view->form = $form = new Core_Form_Search();

        // Get available types
        $availableTypes = $searchApi->getAvailableTypes();
        if (is_array($availableTypes) && count($availableTypes) > 0) {
            $options = array();
            foreach ($availableTypes as $index => $type) {
                $options[$type] = strtoupper('ITEM_TYPE_' . $type);
            }
            $form->type->addMultiOptions($options);
        } else {
            $form->removeElement('type');
        }

        // Check form validity?
        $values = array();
        if ($form->isValid($this->_getAllParams())) {
            $values = $form->getValues();
        }

        $this->view->query = $query = (string) @$values['query'];
        $this->view->type = $type = (string) @$values['type'];
        $this->view->page = $page = (int) $this->_getParam('page');
        if ($query) {
            $this->view->paginator = $searchApi->getPaginator($query, $type);
            $this->view->paginator->setCurrentPageNumber($page);
        }

        if ($this->isAjax()) {
            $results = array();
            if (is_array($this->view->paginator) || is_object($this->view->paginator)) {
                foreach ($this->view->paginator as $item) {
                    $item = $this->view->item($item->type, $item->id);
                    $results[] = array(
                        'icon' => $this->view->htmlLink($item->getHref(), $this->view->itemPhoto($item, 'thumb.icon')),
                        'title' => $this->view->htmlLink(
                            $item->getHref(),
                            $this->view->highlightText($item->getTitle(), $this->view->query),
                            array('class' => 'search_title')
                        )
                    );
                }
            }
            header('Content-Type: application/json');
            echo json_encode($results);
            exit;
        }

        // Render the page
        $this->_helper->content
            // ->setNoRender()
            ->setEnabled();
    }
}
