<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: default.tpl 10227 2014-05-16 22:43:27Z andres $
 * @author     John
 */
?>
<?php echo $this->doctype()->__toString() ?>
<?php $locale = $this->locale()->getLocale()->__toString(); $orientation = ($this->layout()->orientation == 'right-to-left' ? 'rtl' : 'ltr'); ?>
<?php $headerContent = $this->content('header'); ?>
<?php $footerContent = $this->content('footer'); ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $locale ?>" lang="<?php echo $locale ?>" dir="<?php echo $orientation ?>">
<head>
    <base href="<?php echo rtrim($this->serverUrl($this->baseUrl()), '/'). '/' ?>" />


    <?php // ALLOW HOOKS INTO META?>
    <?php echo $this->hooks('onRenderLayoutDefault', $this) ?>


    <?php // TITLE/META?>
    <?php
    $counter = (int) $this->layout()->counter;
    $staticBaseUrl = $this->layout()->staticBaseUrl;
    $headIncludes = $this->layout()->headIncludes;

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->headTitle()
        ->setSeparator(' - ');
    $pageTitleKey = 'pagetitle-' . $request->getModuleName() . '-' . $request->getActionName()
        . '-' . $request->getControllerName();
    $pageTitle = $this->translate($pageTitleKey);
    if ($pageTitle && $pageTitle != $pageTitleKey) {
        $this
            ->headTitle($pageTitle, Zend_View_Helper_Placeholder_Container_Abstract::PREPEND);
    }
    $this
        ->headTitle($this->translate($this->layout()->siteinfo['title']))
    ;
    $this->headMeta()
        ->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8')
        ->appendHttpEquiv('Content-Language', $this->locale()->getLocale()->__toString());

    // Make description and keywords
    $description = $this->layout()->siteinfo['description'];
    $keywords = $this->layout()->siteinfo['keywords'];

    if ($this->subject() && $this->subject()->getIdentity()) {
        $this->headTitle($this->subject()->getTitle(), Zend_View_Helper_Placeholder_Container_Abstract::PREPEND);

        $description = $this->subject()->getDescription() . ' ' . $description;
        // Remove the white space from left and right side
        $keywords = trim($keywords);
        if (!empty($keywords) && (strrpos($keywords, ',') !== (strlen($keywords) - 1))) {
            $keywords .= ',';
        }
        $keywords .= $this->subject()->getKeywords(',');
    }

    $keywords = trim($keywords, ',');

    $this->headMeta()->appendName('description', trim($description));
    $this->headMeta()->appendName('keywords', trim($keywords));
    $this->headMeta()->appendName('viewport', 'width=device-width, initial-scale=1.0');

    //Adding open graph meta tag for video thumbnail
    if ($this->subject() && $this->subject()->getPhotoUrl()) {
        $this->headMeta()->setProperty('og:image', $this->absoluteUrl($this->subject()->getPhotoUrl()));
    }

    // Get body identity
    if (isset($this->layout()->siteinfo['identity'])) {
        $identity = $this->layout()->siteinfo['identity'];
    } else {
        $identity = $request->getModuleName() . '-' .
            $request->getControllerName() . '-' .
            $request->getActionName();
    }
    ?>

    <?php $controllerName = $request->getControllerName();?>
    <?php $actionName = $request->getActionName(); ?>
    
    <?php echo $this->headTitle()->toString()."\n" ?>
    <?php echo $this->headMeta()->toString()."\n" ?>


    <?php // LINK/STYLES?>
    <?php $favicon = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.site.favicon',false); ?>
    <?php
    $this->headLink(array(
        'rel' => 'shortcut icon',
        'href' => ($favicon ? Engine_Api::_()->core()->getFileUrl($favicon) : $staticBaseUrl . ( isset($this->layout()->favicon) ? $this->layout()->favicon : 'favicon.ico')),
        'type' => 'image/x-icon'),
        'PREPEND');
    $themes = array();
    if (!empty($this->layout()->themes)) {
        $themes = $this->layout()->themes;
    } else {
        $themes = array('default');
    }

    foreach ($themes as $theme) {
        if (APPLICATION_ENV != 'development') {
            $this->headLink()
                ->prependStylesheet('application/css.php?request=application/themes/' . $theme . '/theme.css');
        } else {
            $this->headLink()
                ->prependStylesheet(rtrim($this->baseUrl(), '/') . '/application/css.php?request=application/themes/' . $theme . '/theme.css');
        }
    }
    // Process
    foreach ($this->headLink()->getContainer() as $dat) {
        if (!empty($dat->href)) {
            if (false === strpos($dat->href, '?')) {
                $dat->href .= '?c=' . $counter;
            } else {
                $dat->href .= '&c=' . $counter;
            }
        }
    }

    $currentTheme = APPLICATION_PATH . '/application/themes/' . $themes[0] . '/default.tpl';
    $currentThemeHeader = APPLICATION_PATH . '/application/themes/' . $themes[0] . '/head.tpl';
    ?>
    <?php echo $this->headLink()->toString()."\n" ?>
    <?php echo $this->headStyle()->toString()."\n" ?>

    <?php // TRANSLATE?>
    <?php $this->headScript()->prependScript($this->headTranslate()->toString()) ?>
    
    <?php 
      $loginSignupPage = true;
      $flagLoginSignup = false;
      if(empty($this->viewer()->getIdentity())) {
        $spamSettings = Engine_Api::_()->getApi('settings', 'core')->core_spam;
        if(($spamSettings['recaptchaprivate'] && $spamSettings['recaptchapublic']) || ($spamSettings['recaptchaprivatev3'] && $spamSettings['recaptchapublicv3'])) {
          if(empty($_SESSION['User_Plugin_Signup_Account']['data']) && empty($_SESSION['Payment_Subscription']['user_id']) && $controllerName == 'signup') {
            $loginSignupPage = false;
            $flagLoginSignup = true;
          } else if($actionName == 'login') { 
            $loginSignupPage = false;
            $flagLoginSignup = true;
          }
        }
      }
    ?>
    
    <?php if($loginSignupPage) { ?>
    <?php // SCRIPTS?>
    <script type="text/javascript">if (window.location.hash == '#_=_')window.location.hash = '';</script>
    <script type="text/javascript">
        <?php echo $this->headScript()->captureStart(Zend_View_Helper_Placeholder_Container_Abstract::PREPEND) ?>

        Date.setServerOffset('<?php echo date('D, j M Y G:i:s O', time()) ?>');

        en4.orientation = '<?php echo $orientation ?>';
        en4.core.environment = '<?php echo APPLICATION_ENV ?>';
        en4.core.language.setLocale('<?php echo $this->locale()->getLocale()->__toString() ?>');
        en4.core.setBaseUrl('<?php echo $this->url(array(), 'default', true) ?>');
        en4.core.staticBaseUrl = '<?php echo $this->escape($staticBaseUrl) ?>';
        en4.core.loader = new Element('img', {src: en4.core.staticBaseUrl + 'application/modules/Core/externals/images/loading.gif'});

        <?php if ($this->subject()): ?>
        en4.core.subject = {
            type : '<?php echo $this->subject()->getType(); ?>',
            id : <?php echo $this->subject()->getIdentity(); ?>,
            guid : '<?php echo $this->subject()->getGuid(); ?>'
        };
        <?php endif; ?>
        <?php if ($this->viewer()->getIdentity()): ?>
        en4.user.viewer = {
            type : '<?php echo $this->viewer()->getType(); ?>',
            id : <?php echo $this->viewer()->getIdentity(); ?>,
            guid : '<?php echo $this->viewer()->getGuid(); ?>'
        };
        <?php endif; ?>
        if( <?php echo(Engine_Api::_()->getDbtable('settings', 'core')->core_dloader_enabled ? 'true' : 'false') ?> ) {
            en4.core.runonce.add(function() {
                en4.core.dloader.attach();
            });
        }

        <?php echo $this->headScript()->captureEnd(Zend_View_Helper_Placeholder_Container_Abstract::PREPEND) ?>
    </script>
    <?php
    $this->headScript()
        
        ->prependFile($staticBaseUrl . 'externals/scrollbars/scrollbars.min.js')
        ->prependFile($staticBaseUrl . 'externals/smoothbox/smoothbox4.js')
        ->prependFile($staticBaseUrl . 'application/modules/User/externals/scripts/core.js')
        ->prependFile($staticBaseUrl . 'application/modules/Core/externals/scripts/core.js')
        ->prependFile($staticBaseUrl . 'externals/chootools/chootools.js')
        ->prependFile($staticBaseUrl . 'externals/mootools/mootools-more-1.4.0.1-full-compat-' . (APPLICATION_ENV == 'development' ? 'nc' : 'yc') . '.js')
        ->prependFile($staticBaseUrl . 'externals/mootools/mootools-core-1.4.5-full-compat-' . (APPLICATION_ENV == 'development' ? 'nc' : 'yc') . '.js')
        ->prependFile($staticBaseUrl . 'externals/jQuery/jquery.min.js');
    // Process
    foreach ($this->headScript()->getContainer() as $dat) {
        if (!empty($dat->attributes['src'])) {
            if (false === strpos($dat->attributes['src'], '?')) {
                $dat->attributes['src'] .= '?c=' . $counter;
            } else {
                $dat->attributes['src'] .= '&c=' . $counter;
            }
        }
    }
    ?>
    <?php echo $this->headScript()->toString()."\n" ?>
    <?php } else if(empty($this->viewer()->getIdentity()) && !empty($flagLoginSignup)) { ?>
      
      <script src='<?php echo $staticBaseUrl . 'externals/jQuery/jquery.min.js'; ?>'></script>
      <script src='https://www.google.com/recaptcha/api.js' async defer></script>
      <?php 
      $spamSettings = Engine_Api::_()->getApi('settings', 'core')->core_spam;
      $recaptchaVersionSettings = Engine_Api::_()->getApi('settings', 'core')->core_spam_recaptcha_version;
      if($recaptchaVersionSettings == 0  && $spamSettings['recaptchaprivatev3'] && $spamSettings['recaptchapublicv3']) { ?>
        <script type="text/javascript">
          scriptJquery(document).ready(function() {
            scriptJquery('#captcha-wrapper').hide();
            scriptJquery('<input>').attr({ 
              name: 'recaptcha_response', 
              id: 'recaptchaResponse', 
              type: 'hidden', 
            }).appendTo('.global_form'); 
          });
        </script>
      <?php } ?>
    <?php } ?>


    <?php echo $headIncludes ?>

    <?php
    if (file_exists($currentThemeHeader)) {
        require($currentThemeHeader);
    }
    ?>

</head>

<?php
    $themeFontSize = !empty($_SESSION['font_theme']) && $_SESSION['font_theme'] ? $_SESSION['font_theme'] : "";
    $themeModeColor = !empty($_SESSION['mode_theme']) && $_SESSION['mode_theme'] ? $_SESSION['mode_theme'] : "";
    $bodyClass = "";
    if (!$this->viewer()->getIdentity()){
        $bodyClass .= "guest-user";
    }
    if($themeModeColor){
        $bodyClass .= " ".$themeModeColor;
    }

?>

<body id="global_page_<?php echo $identity ?>"<?php if ($bodyClass): ?> class="<?php echo $bodyClass; ?>"<?php endif; ?><?php if ($themeFontSize): ?> style="font-size: <?php echo $themeFontSize; ?>"<?php endif; ?>>
<script type="javascript/text">
    if(DetectIpad()){
      $$('a.album_main_upload').setStyle('display', 'none');
      $$('a.album_quick_upload').setStyle('display', 'none');
      $$('a.icon_photos_new').setStyle('display', 'none');
    }
  </script>
<?php if (file_exists($currentTheme)): ?>
    <?php $this->content()->renderThemeLayout($this, $currentTheme); ?>
<?php else: ?>
    <div id="global_header">
        <?php echo $headerContent ?>
    </div>
    <div id='global_wrapper'>
        <div id='global_content'>
            <?php echo $this->layout()->content ?>
        </div>
    </div>
    <div id="global_footer">
        <?php echo $footerContent ?>
    </div>
<?php endif; ?>
</body>
</html>
