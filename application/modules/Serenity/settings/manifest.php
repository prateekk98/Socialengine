<?php

return array (
  'package' =>
  array(
    'type' => 'module',
    'name' => 'serenity',
    'sku' => 'serenity',
    'version' => '5.7.0',
    'dependencies' => array(
      array(
        'type' => 'module',
        'name' => 'core',
        'minVersion' => '5.0.0',
      ),
    ),
    'path' => 'application/modules/Serenity',
    'title' => 'Serenity Theme',
    'description' => 'Serenity Theme',
    'author' => 'Webligo Developments',
    'callback' => array(
        'path' => 'application/modules/Serenity/settings/install.php',
        'class' => 'Serenity_Installer',
    ),
    'actions' =>
    array(
        0 => 'install',
        1 => 'upgrade',
        2 => 'refresh',
        3 => 'enable',
        4 => 'disable',
    ),
    'directories' =>
    array(
      'application/modules/Serenity',
      'application/themes/serenity',
    ),
    'files' =>
    array(
      'application/languages/en/serenity.csv',
    ),
  ),
  'items' => array(
    'serenity_customthemes',
  ),
	// Hooks ---------------------------------------------------------------------
	'hooks' => array(
		array(
			'event' => 'onRenderLayoutDefault',
			'resource' => 'Serenity_Plugin_Core'
		),
	),
);
