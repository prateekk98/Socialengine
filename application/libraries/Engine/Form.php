<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Form.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Form extends Zend_Form
{
    /**
     * Secret Salt for CSRF token
     * @var string
     */
    protected static $_secretSalt = 'secretSalt';

    /**
     * The title of the form
     *
     * @var string
     */
    protected $_title;

    /**
     * An array of messages (non-warning)
     *
     * @var array
     */
    protected $_notices;

    /**
     * Default display group class
     *
     * @var string
     */
    protected $_defaultDisplayGroupClass = 'Engine_Form_DisplayGroup';

    /**
     * Type of CSRF Element
     *
     * @var string
     */
    protected $_csrfElementType = 'Hash';

    // Static

    /**
     * Adds paths to an existing form
     *
     * @param Zend_Form $form
     */
    public static function enableForm(Zend_Form $form)
    {
        $form
            ->addPrefixPath('Engine_Form_Decorator', 'Engine/Form/Decorator', 'decorator')
            ->addPrefixPath('Engine_Form_Element', 'Engine/Form/Element', 'element')
            ->addElementPrefixPath('Engine_Form_Decorator', 'Engine/Form/Decorator', 'decorator')
            ->addDisplayGroupPrefixPath('Engine_Form_Decorator', 'Engine/Form/Decorator')
            ->setDefaultDisplayGroupClass('Engine_Form_DisplayGroup');
    }

    /**
     * Adds default decorators to an existing element
     *
     * @param Zend_Form_Element $element
     */
    public static function addDefaultDecorators(Zend_Form_Element $element)
    {
        $fqName = $element->getName();
        if (null !== ($belongsTo = $element->getBelongsTo())) {
            $fqName = $belongsTo . '-' . $fqName;
        }

        Engine_Hooks_Dispatcher::getInstance()->callEvent('onBeforeAddFormElementDefaultDecorators', array(
            'element' => $element,
            'elementId' => $fqName
        ));

        $element
            ->addDecorator('Description', array('tag' => 'p', 'class' => 'description', 'placement' => 'PREPEND'))
            ->addDecorator('HtmlTag', array('tag' => 'div', 'id' => $fqName . '-element', 'class' => 'form-element'))
            ->addDecorator('Label', array('tag' => 'div', 'tagOptions' => array('id' => $fqName . '-label', 'class' => 'form-label')))
            ->addDecorator('HtmlTag2', array('tag' => 'div', 'id' => $fqName . '-wrapper', 'class' => 'form-wrapper'));

        Engine_Hooks_Dispatcher::getInstance()->callEvent('onAfterAddFormElementDefaultDecorators', array(
            'element' => $element,
            'elementId' => $fqName
        ));
    }

    /**
     * Sets each element with a class based on its type
     *
     * @param Zend_Form $form
     */
    public static function setFormElementTypeClasses(Zend_Form $form)
    {
        foreach ($form->getElements() as $element) {
            $type = strtolower(array_pop(explode('_', $element->getType())));
            $class = (isset($element->class) ? $element->class . ' ' : '');
            $class .= 'element-type-'.$type;
            $element->class = $class;
        }
    }

    /**
     * Set Secret Salt for CSRF token
     *
     * @param  string $secretSalt
     */
    public static function setSecretSalt($secretSalt)
    {
        self::$_secretSalt = (string) $secretSalt;
    }

    /**
     * Object version of {@link Engine_Form::setFormElementTypeClasses()}
     */
    public function setElementTypeClasses()
    {
        // Semi-deprecated
        self::setFormElementTypeClasses($this);
    }

    // General

    /**
     * Constructor
     *
     * @param array|null $options
     */
    public function __construct($options = null)
    {
        $this->setAttrib('class', 'global_form');
        $this->setAction($_SERVER['REQUEST_URI']);
        self::enableForm($this);
        parent::__construct($options);
        $eventName = 'on' . str_replace('_', '', get_class($this)) . 'InitAfter';
        Engine_Hooks_Dispatcher::getInstance()->callEvent($eventName, $this);
    }

    /**
     * Loads default decorators
     *
     * @return void
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this
                ->addDecorator('FormElements')
                ->addDecorator('HtmlTag', ['tag' => 'div', 'class' => 'form-elements'])
                ->addDecorator('FormMessages', ['placement' => 'PREPEND'])
                ->addDecorator('FormErrors', ['placement' => 'PREPEND'])
                ->addDecorator('Description', ['placement' => 'PREPEND', 'class' => 'form-description'])
                ->addDecorator('FormTitle', ['placement' => 'PREPEND', 'tag' => 'h3'])
                ->addDecorator('FormWrapper', ['tag' => 'div'])
                ->addDecorator('FormContainer', ['tag' => 'div'])
                ->addDecorator('Form')
            ; //->addDecorator($decorator);
        }
    }



    // Options

    /**
     * Set form title
     *
     * @param string $title
     * @return Engine_Form
     */
    public function setTitle($title)
    {
        $this->_title = $title;
        return $this;
    }

    /**
     * Get current form title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }


    // Messaging

    /**
     * Add a notice
     *
     * @param string $message
     * @return Engine_Form
     */
    public function addNotice($message)
    {
        $this->_notices[] = $message;
        return $this;
    }

    /**
     * Clear all notices
     *
     * @return Engine_Form
     */
    public function clearNotices()
    {
        $this->_notices = [];
        return $this;
    }

    /**
     * Get all notices
     *
     * @return array
     */
    public function getNotices()
    {
        return (array) $this->_notices;
    }



    // These are required for Engine_Form_Element_Composite
    // @todo deprecated or not?

    public function addElement($element, $name = null, $options = null)
    {
        $ret = parent::addElement($element, $name, $options);

        // If adding and composite, set plugin loader
        if ($element instanceof Engine_Form_Element_Composite) {
            $element->setPluginLoader($this->getPluginLoader(self::DECORATOR));
        }

        return $ret;
    }

    public function createElement($type, $name, $options = null)
    {
        if ($type === $this->_csrfElementType && empty($options['salt'])) {
            $options['salt'] = self::$_secretSalt;
        }

        $element = parent::createElement($type, $name, $options);

        if ($element instanceof Engine_Form_Element_Composite) {
            $element->setPluginLoader($this->getPluginLoader(self::DECORATOR));
        }

        return $element;
    }
}
