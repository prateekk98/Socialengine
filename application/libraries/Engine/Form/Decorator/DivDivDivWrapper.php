<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: DivDivDivWrapper.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Form_Decorator_DivDivDivWrapper extends Zend_Form_Decorator_Abstract
{
    /**
     * Default placement: surround content
     * @var string
     */
    protected $_placement = null;

    /**
     * Render
     *
     * Renders as the following:
     * <dt></dt>
     * <dd>$content</dd>
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $elementName = $element->getName();

        $wrapperClassName = '';
        $options = $this->getOptions();
        if (!isset($options['label'])) {
          $options['label'] = '&nbsp;';
        } else {
          $translate = $this->getElement()->getTranslator();
          if ($translate) {
            $options['label'] = $translate->translate($options['label']);
          }
        }
        if (!empty($element->{'data-wrapper-name'})) {
          $wrapperClassName = ' ' . $element->{'data-wrapper-name'};
        }

        return
          '<div id="' . $elementName . '-wrapper" class="form-wrapper' . $wrapperClassName . '">'.
          '<div id="' . $elementName . '-label" class="form-label">' . $options['label'] . '</div>' .
          '<div id="' . $elementName . '-element" class="form-element">' . $content . '</div>'.
          '</div>';
    }
}
