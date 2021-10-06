<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _composeLink.tpl 10245 2017-01-02 18:08:24Z lucas $
 * @author     John
 */
?>
<?php $backUrl = Zend_Controller_Front::getInstance()->getRouter()
  ->assemble(array('module' => 'core', 'controller' => 'themes', 'action' => 'index'), 'admin_default', true); ?>
<?php if( $this->colorVariants ): ?>
  <div class="admin_theme_variants_wrapper">
    <ul class="admin_themes">
        <?php
        $alternateRow = true;
        foreach( $this->colorVariants as $colorVariant ):
          $upgradeRequired = false;
          $alreadyCreated = false;
          $thumb = $this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/anonymous.png';
          if( !empty($colorVariant['thumb']) ) {
            $thumb = $colorVariant['path'] .'/'. $colorVariant['thumb'];
          }
          ?>
          <li <?php echo ($alternateRow) ? ' class="alt_row"' : "";?>>
            <div class="theme_wrapper">
              <img src="<?php echo $thumb ?>" >
            </div>
            <div class="theme_chooser_info">
              <h3>
                <?php echo $colorVariant['title']?>
              </h3>

              <?php if( $colorVariant['name'] !== $this->activeTheme->name):?>
                <?php if( array_key_exists($colorVariant['name'], $this->themeList )):?>
                  <?php $manifest = include(APPLICATION_PATH . '/application/themes/' . $colorVariant['name'] . '/manifest.php'); ?>
                  <?php if( version_compare($colorVariant['version'], $manifest['package']['version']) > 0 ): ?>
                    <?php $upgradeRequired = true; ?>
                  <?php else:?>
                    <?php $alreadyCreated = true;?>
                  <?php endif;?>
                <?php endif;?>

                <?php if( !$upgradeRequired ): ?>
                  <h4 class="version">v<?php echo $colorVariant['version'] ?></h4>
                  <input type="radio" name="colorVariantName" id="colorVariantName" value="<?php echo $colorVariant['name'] ?>" onclick="showSubmit()">
                  <?php if( $alreadyCreated ):?>
                    <p>
                      (<?php  echo $this->translate("Already created") ?>)
                    </p>
                  <?php endif;?>
                <?php else:?>
                  <h4 class="version">v<?php echo $manifest['package']['version'] ?></h4>
                  <p>
                    <?php echo $this->htmlLink(
                        array('reset' => false, 'action' => 'upgrade-color-variant', 'name' => $colorVariant['parentTheme'], 'colorVariantName' => $colorVariant['name']),
                        '<button class="upgrade_theme_button">' . $this->translate('Upgrade Theme') . ' (v' . $colorVariant['version'] . ')</button>',
                        array('class' => 'smoothbox')
                        ) ?>
                  </p>
                <?php endif;?>

              <?php else:?>
                <div class="current_theme">
                  (<?php  echo $this->translate("this is your current theme") ?>)
                </div>
              <?php endif;?>

            </div>
          </li>
          <?php $alternateRow = !$alternateRow; ?>
        <?php endforeach; ?>
    </ul>
    <div class="button_wrapper" id="submitWrapper">
      <div class="activate_checkbox">
        <input type="checkbox" name="activate" value="activate"><?php echo $this->translate('Activate immediately') ?>
      </div>
      <div class="create_theme_button_wrapper">
        <button type="submit" ><?php echo $this->translate('Create Theme') ?></button>
        <span> or </span>
        <?php echo $this->htmlLink($backUrl, 'Cancel') ?>
      </div>
    </div>

  </div>
<?php else: ?>
  <div class="theme_selector_message">
    <?php  echo $this->translate("Color variants does not exist for selected theme.") ?>
    <div class="back_link_wrapper">
      <?php echo $this->htmlLink($backUrl, 'Back to Theme Editor') ?>
    </div>
  </div>
<?php endif;?>
