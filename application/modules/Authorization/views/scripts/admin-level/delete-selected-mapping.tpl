<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 * @version    $Id: delete-selected-mapping.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div class="settings">
  <div class='global_form'>
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
      <div>
        <h3><?php echo $this->translate("Delete Profile Type to Member Level mapping"); ?></h3>
        <p>
          <?php echo $this->translate(array(
              "Are you sure that you want to delete this member level mapping? It will not be recoverable after being deleted.",
              "Are you sure that you want to delete these %d member level mappings? They will not be recoverable after being deleted.",
              $this->count),
            $this->count) ?>
        </p>
        <p><?php echo $this->translate("Please select in which member level you want to move users.");?></p>
        <table class='admin_table'>
            <thead>
                <tr>
                    <th style="width: 1%;">
                        <?php echo $this->translate("Profile Types");?>
                    </th>
                    <th style="width: 1%;">
                        <?php echo $this->translate("Member Level");?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($this->profile_types)) { ?>
                    <?php foreach ($this->profile_types as $value) { ?>
                        <tr>
                            <td><?php echo Engine_Api::_()->getItem('option',$value['profile_type_id'])->label;?></td>
                            <td>
                                <select name="level_id_<?php echo $value['profile_type_id'];?>" id="level_id_<?php echo $value['profile_type_id'];?>">
                                    <?php foreach ($this->member_levels as $key => $level) {
                                            if ($key == $value['member_level_id']) { ?>
                                                <option value="<?php echo $key;?>" label="<?php echo $level;?>" selected>
                                                    <?php echo $level;?>
                                                </option>
                                            <?php } else { ?>
                                                <option value="<?php echo $key;?>" label="<?php echo $level;?>">
                                                    <?php echo $level;?>
                                                </option>
                                            <?php }
                                    } ?>
                                </select>
                            </td>
                        </tr>
                    <?php }
                } else { ?>
                    <div class="tip"><span>There are no profile type to be deleted.</span></div>
                <?php } ?>
            </tbody>
        </table>
        <br />
        <div>
            <input type="hidden" name="confirm" value='true'/>
            <input type="hidden" name="ids" value="<?php echo $this->idsString ?>"/>
            <button type='submit'><?php echo $this->translate("Delete") ?></button>
            <?php echo $this->translate(" or ") ?>
            <a href='<?php echo $this->url(array('action' => 'index')) ?>'>
                <?php echo $this->translate("cancel") ?>
            </a>
        </div>
      </div>
    </form>
  </div>
</div>
