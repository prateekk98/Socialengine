<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<?php $viewer = Engine_Api::_()->user()->getViewer();
      $subject = Engine_Api::_()->core()->getSubject('user');
      if (empty($this->fieldValueLoop($this->subject(), $this->fieldStructure)) &&
        $subject->getOwner()->isSelf($viewer)) {
        $href = Zend_Registry::get('Zend_View')->url(array(
          'controller' => 'edit',
          'action' => 'profile'
        ),'user_extended',true);
        echo '
           <div class="tip">
             <span>
               '. $this->translate(sprintf("Fill your profile details %1shere%2s.",
                  "<a href='$href'>",
                  "</a>"
                )) .'
             </span>
           </div>';
        return;
      }
      echo $this->fieldValueLoop($this->subject(), $this->fieldStructure)
?>
