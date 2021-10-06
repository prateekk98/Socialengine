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

<?php if( !$this->noForm ): ?>
<div class="user_login_page">
  <?php $image = $this->image ? Engine_Api::_()->core()->getFileUrl($this->image) : 'application/modules/User/externals/images/login-bg.jpg'; ?>
  <div class="user_login_bg" style="background-image:url(<?php echo $image; ?>);"></div>
  <div class="user_login_form">
  <h3>
    <?php echo $this->translate('Enter Details to Login', '<a href="'.$this->url(array(), "user_signup").'" class="user_signup_link">', '</a>'); ?>
  </h3>

  <?php echo $this->form->setAttrib('class', 'global_form_box')->render($this) ?>
  <?php echo $this->htmlLink(array('route' => 'user_signup'), $this->translate('Not a Member? <b>Join</b>')) ?>
  <?php if( !empty($this->fbUrl) ): ?>

    <script type="text/javascript">
      var openFbLogin = function() {
        Smoothbox.open('<?php echo $this->fbUrl ?>');
      }
      var redirectPostFbLogin = function() {
        window.location.href = window.location;
        Smoothbox.close();
      }
    </script>

    <?php // <button class="user_facebook_connect" onclick="openFbLogin();"></button> ?>

  <?php endif; ?>

<?php else: ?>
    
  <h3 style="margin-bottom: 0px;">
    <?php echo $this->htmlLink(array('route' => 'user_login'), $this->translate('Sign In')) ?>
    <?php echo $this->translate('or') ?>
    <?php echo $this->htmlLink(array('route' => 'user_signup'), $this->translate('Join')) ?>
  </h3>

  <?php echo $this->form->setAttrib('class', 'global_form_box no_form')->render($this) ?>
    
  <?php endif; ?>
  </div>
</div>
