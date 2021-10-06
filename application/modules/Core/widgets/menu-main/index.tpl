<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

?>
<?php if( $this->menuFromTheme ): ?>
  <ul class="navigation">
    <?php foreach( $this->navigation as $link ): ?>
      <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
        <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
          <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
          <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
          <span><?php echo $this->translate($link->getlabel()) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <?php $countMenu = 0; ?>
  <div class="main_menu_navigation scrollbars">
    <ul class="navigation">
      <?php foreach( $this->navigation as $link ): ?>
        <?php if( $countMenu < $this->menuCount ): ?>
          <?php 
            $explodedString = explode(' ', $link->class);
            $menuName = end($explodedString); 
            $moduleName = str_replace('core_main_', '', $menuName);
            if(strpos($moduleName, 'custom_') !== 0){
              $moduleName = $moduleName.'_main';
            }
          ?>
         <?php $subMenus = Engine_Api::_()->getApi('menus', 'core')->getNavigation($moduleName); 
            $menuSubArray = $subMenus->toArray();
         ?>
          <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
            <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
              <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
              <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
              <span><?php echo $this->translate($link->getlabel()) ?></span>
               <?php if(count($menuSubArray) > 0 && $this->submenu): ?>
                  <i class="fa fa-angle-down open_submenu"></i>
               <?php endif; ?>
            </a>
          <?php if(count($menuSubArray) > 0 && $this->submenu): ?>
            <ul class="main_menu_submenu">
              <?php 
              $counter = 0; 
              foreach( $subMenus as $subMenu): 
             	$active = isset($menuSubArray[$counter]['active']) ? $menuSubArray[$counter]['active'] : 0;
              ?>
                <li class="sesbasic_clearfix <?php echo ($active) ? 'selected_sub_main_menu' : '' ?>">
                  <a href="<?php echo $subMenu->getHref(); ?>" <?php if( $subMenu->get('target') ): ?> target='<?php echo $subMenu->get('target') ?>' <?php endif; ?> class="<?php echo $subMenu->getClass(); ?>">
                    <i class="<?php echo $subMenu->get('icon') ? $subMenu->get('icon') : 'fa fa-star' ?>"></i><span><?php echo $this->translate($subMenu->getLabel()); ?></span>
                  </a>
                </li>
              <?php 
              $counter++;
              endforeach; ?>
            </ul>
          <?php endif; ?>
          </li>
        <?php else:?>
          <?php break;?>
        <?php endif;?>
        <?php $countMenu++;?>
      <?php endforeach; ?>
      <?php if (count($this->navigation) > $this->menuCount):?>
        <?php $countMenu = 0; ?>
        <li class="more_tab">
          <a href="javascript:void(0);">
            <span><?php echo $this->translate("More") ?></span>
            <i class="fa fa-angle-down open_submenu"></i>
          </a>
          <ul class="navigation_submenu">
            <?php foreach( $this->navigation as  $link ): ?>
              <?php if ($countMenu >= $this->menuCount): ?>

                <?php 
                  $explodedString = explode(' ', $link->class);
                  $menuName = end($explodedString); 
                  $moduleName = str_replace('core_main_', '', $menuName);
                  if(strpos($moduleName, 'custom_') !== 0){
                    $moduleName = $moduleName.'_main';
                  }
                ?>
                <?php 
                  $subMenus = Engine_Api::_()->getApi('menus', 'core')->getNavigation($moduleName);
                  $menuSubArray = $subMenus->toArray();
                ?>

                <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
                  <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
                    <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
                    <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
                    <span><?php echo $this->translate($link->getlabel()) ?>
                      <?php if(count($menuSubArray) > 0 && $this->submenu): ?>
                          <i class="fa fa-angle-down open_submenu"></i>
                       <?php endif; ?>
                    </span>
                  </a>

                  <?php if(count($menuSubArray) > 0 && $this->submenu): ?>
                    <ul class="main_menu_submenu">
                      <?php 
                      $counter = 0; 
                      foreach( $subMenus as $subMenu): 
                      $active = isset($menuSubArray[$counter]['active']) ? $menuSubArray[$counter]['active'] : 0;
                      ?>
                        <li class="sesbasic_clearfix <?php echo ($active) ? 'selected_sub_main_menu' : '' ?>">
                            <a href="<?php echo $subMenu->getHref(); ?>" <?php if( $subMenu->get('target') ): ?> target='<?php echo $subMenu->get('target') ?>' <?php endif; ?>  class="<?php echo $subMenu->getClass(); ?>">
                            <i class="<?php echo $subMenu->get('icon') ? $subMenu->get('icon') : 'fa fa-star' ?>"></i><span><?php echo $this->translate($subMenu->getLabel()); ?></span>
                          </a>
                        </li>
                      <?php 
                      $counter++;
                      endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </li>
              <?php endif;?>
              <?php $countMenu++;?>
            <?php endforeach; ?>
          </ul>
        </li>
      <?php endif;?>
    </ul>
  </div>
  <div class="core_main_menu_toggle panel-toggle"></div>
  <script type="text/javascript">
    scriptJquery(document).on("click",".open_submenu",function(e){
      e.preventDefault();
      var submenu = scriptJquery(this).closest("li").find("ul");
      submenu.toggle();
      if(submenu.is(":visible") == true){
        scriptJquery(this).closest("li").addClass("has_submenu");
        scriptJquery(this).removeClass("fa-angle-down"); 
        scriptJquery(this).addClass("fa-angle-up");
      } else {
        scriptJquery(this).closest("li").removeClass("has_submenu");
        scriptJquery(this).removeClass("fa-angle-up"); 
        scriptJquery(this).addClass("fa-angle-down");
      }
    });
    scriptJquery(document).ready(function(){
      var selectedMenu = scriptJquery('.main_menu_navigation').find(".selected_sub_main_menu");
      if(selectedMenu.length){
        var parentMenu = selectedMenu.closest(".main_menu_submenu").closest("li");
        if(parentMenu.length && !parentMenu.hasClass("active")){
          parentMenu.addClass("active");
        }
      }
      selectedMenu = scriptJquery('.main_menu_navigation').find(".more_tab > ul > li.active");
      if(selectedMenu.length){
        selectedMenu.closest(".more_tab").addClass("active");
      }
    });
    if(typeof en4 != "undefined"){
      en4.core.layout.setLeftPannelMenu('<?php echo $this->menuType; ?>');
    }
  </script>
<?php endif; ?>
