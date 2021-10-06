<?php

/**
 * @package     Engine_Core
 * @version     $Id: index.php 9764 2012-08-17 00:04:31Z matthew $
 * @copyright   Copyright (c) 2006-2020 Webligo Developments
 * @license     http://www.socialengine.com/license/
 */
abstract class Engine_Boot_Abstract
{
    protected $_boot;
    
    /**
     * Constructor
     *
     * @param Engine_Application $application
     */
    public function __construct(Engine_Boot $boot)
    {
        $this->_boot = $boot;
    }

    abstract public function beforeBoot();
}
