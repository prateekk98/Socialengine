<?php

class Serenity_Bootstrap extends Engine_Application_Bootstrap_Abstract {

	public function __construct($application) {

    parent::__construct($application);
    $front = Zend_Controller_Front::getInstance();
    $front->registerPlugin(new Serenity_Plugin_Core);
	}
}
