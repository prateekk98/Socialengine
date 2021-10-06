<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 * @version    $Id: update-member-level.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div style="margin:20px 10px 0 10px;" >
    <form method="post" class="global_form" enctype="application/x-www-form-urlencoded">
        <p class="form-description">
            <?php echo $this->translate("Do you also want to update the Member Level of existing users of your website based on this mapping ?");?>
        </p>
        <?php if (!empty($this->user_infos['total_superadmin_users'])) { ?>
            <br />
            <div class="tip" id="no-feed-tip">
              <span>
                <?php echo $this->translate('The "%s" profile type currently contains %s members under member level \'Super Admin\'. The Member Level of members under "Super Admin" level will not be updated.', $this->profile_type_label, $this->user_infos['total_superadmin_users']);?>
              </span>
            </div>
        <?php } ?>
        <br />
        <div class="form-elements">
            <div id="buttons-wrapper" class="form-wrapper">
                <fieldset id="fieldset-buttons">
                    <button type="submit" id="submit" name="submit"><?php echo $this->translate("Yes");?></button>
                    <?php echo $this->translate("or");?>
                    <a onclick="window.parent.location.reload();" href="javascript:void(0);" type="button" id="cancel" name="cancel">
                        <?php echo $this->translate("No");?>
                    </a>
                </fieldset>
            </div>
            <input type="hidden" id="userids" value="<?php echo !empty($this->user_infos['userids']) ? $this->user_infos['userids'] : '' ;?>" name="userids">
            <input type="hidden" id="level_id" value="<?php echo $this->member_level_id;?>" name="level_id">
        </div>
    </form>
</div>
