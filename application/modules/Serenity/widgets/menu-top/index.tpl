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
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Serenity/externals/styles/styles.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'externals/jQuery/jquery-1.12.4.min.js'); ?>
<div class="serenity_menu_top">
  <div class="serenity_top_inner">
     <div class="serenity_menu_top_settings">
         <ul>
            <li>
                <ul class="color_modes">
                   <li class="color-label"><?php echo $this->translate("Contrast") ?></li>
                   <li class="<?php echo empty($_SESSION['mode_theme']) || !$_SESSION['mode_theme']  ? 'active' : '' ; ?>"><a href="javascript:void(0)" title="<?php echo $this->translate('Default Mode') ?>" onclick="defmode(this)"><i class="fa fa-eye"></i></a></li>
                   <?php if($this->contrast_mode == "dark_mode"): ?>
                      <li class="<?php echo !empty($_SESSION['mode_theme']) && $_SESSION['mode_theme'] == 'dark_mode' ? 'active' : '' ; ?>"><a href="javascript:void(0)" title="<?php echo $this->translate('Dark Mode') ?>" onclick="darkmode(this)"><i class="fa fa-eye"></i></a></li>
                   <?php else: ?>
                      <li class="<?php echo !empty($_SESSION['mode_theme']) && $_SESSION['mode_theme'] == 'light_mode' ? 'active' : '' ; ?>"><a href="javascript:void(0)" title="<?php echo $this->translate('Light Mode') ?>" onclick="lightmode(this)"><i class="fa fa-eye"></i></a></li>
                   <?php endif; ?>
                </ul>
            </li>
            <li>
               <ul class="resizer">
                  <li class="resizer-label"><?php echo $this->translate("Font") ?></li>
                  <li class="<?php echo !empty($_SESSION['font_theme']) && $_SESSION['font_theme'] == '85%' ? 'active' : '' ; ?>"><a href="javascript:void(0)" title="<?php echo $this->translate('Small Font') ?>" onclick="smallfont(this)"><i class="fa fa-minus-circle"></i></a></li>
                  <li class="<?php echo empty($_SESSION['font_theme']) || !$_SESSION['font_theme'] ? 'active' : '' ; ?>"><a href="javascript:void(0)" title="<?php echo $this->translate('Default Font') ?>" onclick="defaultfont(this)">A</a></li>
                  <li class="<?php echo !empty($_SESSION['font_theme']) && $_SESSION['font_theme'] == '115%' ? 'active' : '' ; ?>"><a href="javascript:void(0)" title="<?php echo $this->translate('Large Font') ?>" onclick="largefont(this)"><i class="fa fa-plus-circle"></i></a></li>
               </ul>
            </li>
         </ul>
     </div>
   </div>
</div>
<script>
   function smallfont(obj){
       scriptJquery(obj).parent().parent().find('.active').removeClass('active');
       scriptJquery(obj).parent().addClass('active');
       scriptJquery('body').css({
        'font-size': '85%'
        });
       scriptJquery.post("serenity/index/font",{size:"85%"},function (response) {

       })
	};
	function defaultfont(obj){
        scriptJquery(obj).parent().parent().find('.active').removeClass('active');
        scriptJquery(obj).parent().addClass('active');
        scriptJquery('body').css({
        'font-size': '15px'
        });
        scriptJquery.post("serenity/index/font",{size:""},function (response) {

        })
	};
	function largefont(obj){
        scriptJquery(obj).parent().parent().find('.active').removeClass('active');
        scriptJquery(obj).parent().addClass('active');
        scriptJquery('body').css({
        'font-size': '115%'
        });
        scriptJquery.post("serenity/index/font",{size:"115%"},function (response) {

        })
	};
	function darkmode(obj){
        scriptJquery(obj).parent().parent().find('.active').removeClass('active');
        scriptJquery(obj).parent().addClass('active');
        scriptJquery('body').addClass("dark_mode");
        scriptJquery.post("serenity/index/mode",{mode:"dark_mode"},function (response) {

        })
	};
  function lightmode(obj){
        scriptJquery(obj).parent().parent().find('.active').removeClass('active');
        scriptJquery(obj).parent().addClass('active');
        scriptJquery('body').addClass("light_mode");
        scriptJquery.post("serenity/index/mode",{mode:"light_mode"},function (response) {

        })
  };
	function defmode(obj){
        scriptJquery(obj).parent().parent().find('.active').removeClass('active');
        scriptJquery(obj).parent().addClass('active');
        scriptJquery('body').removeClass("dark_mode");
				scriptJquery('body').removeClass("light_mode");
        scriptJquery.post("serenity/index/mode",{mode:""},function (response) {

        })
	};
</script>
