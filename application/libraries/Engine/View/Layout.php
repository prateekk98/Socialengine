<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

/**
 * Class Engine_View_Layout
 *
 * @property $content
 */
class Engine_View_Layout
{
    private $viewer;

    private $zendView;

    /**
     * @var Engine_View_Helper_Content
     */
    private $helperContent;

    public function __construct(Zend_View $viewer, $file)
    {
        $this->zendView = $viewer;
        $this->viewer = $this->zendView->viewer();
        $this->helperContent = $this->zendView->content();
        // $request = Zend_Controller_Front::getInstance();
        // print_r($request->getRouter()->getCurrentRoute());
        // exit;

        require($file);
    }

    public function __get($name)
    {
        switch ($name) {
            case 'search':
                echo $this->helperContent->renderWidget('core.search-mini');
                break;
            case 'logo':
                echo $this->menu('logo');
                break;
            case 'menu':
                echo $this->menu('main');
                break;
            case 'footer':
                echo $this->zendView->content('footer');
                break;
            case 'notify':
                echo $this->menu('mini');
                break;
            case 'content':
                echo $this->zendView->layout()->content;
                break;
        }
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->zendView, $name), $arguments);
    }

    private function menu($type)
    {
        echo $this->helperContent->renderWidget('core.menu-' . $type, array(
            'menuFromTheme' => true
        ));
    }
}
