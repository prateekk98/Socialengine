<?php

/**
 * Class Install_Import_Phpfox_VideoFeedsV4
 */
class Install_Import_Phpfox_VideoFeedsV4 extends Install_Import_Phpfox_AbstractFeeds
{
  protected $_fromResourceType = 'PHPfox_Videos';
  protected $_toResourceType = 'post_self';
  protected $_fromWhere = array('type_id=?' => 'PHPfox_Videos');
  protected $_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  protected $_priority = 200;
}
