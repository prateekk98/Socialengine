<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 * @version    $Id: mange-profile-type-mapping.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<script type="text/javascript">
    var checkAll = function(pel) {
      var state = pel.checked;
      $$('input[type=checkbox]').each(function(el){
        el.checked = state;
      });
    }
    var check_selected = function () {
      try {
        $$('input[type=checkbox]').each(function(el) {
          if (el.id != 'checkall' && el.checked == true) {
            throw true;
          }
        });
        alert("No entry selected to delete.");
        return false;
      } catch (e) {
        return true;
      }
    }
</script>

<h2>
  <?php echo $this->translate("Member Levels") ?>
</h2>

<div class='tabs'>
  <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
</div>

<h2>
  <?php echo $this->translate("Manage Profile Types and Member Levels Mapping") ?>
</h2>

<p>
  <?php echo $this->translate("Below you can map a Profile Type created on your website to any existing Member Level. This will assign a member level to new users based on the profile type chosen by them during signup. While creating these mappings, you can also decide if you want existing users of a profile type to fall under the mapped member level or not.") ?>
</p>

<br />

<div>
    <?php echo $this->htmlLink(array('action' => 'map-profile-type', 'reset' => false, 'format' => 'smoothbox'), $this->translate('Create a Mapping'), array('class' => 'buttonlink smoothbox',  'style' => 'background-image: url(application/modules/Network/externals/images/admin/add.png);')) ?>
</div>

<br/>

<?php if(count($this->paginator)): ?>
<form id='delete_selected' method='post' action='<?php echo $this->url(array('action' => 'delete-selected-mapping')) ?>' onsubmit="return check_selected();">

  <table class='admin_table'>
    <thead>
      <tr>
        <th style="width: 1%;">
          <input type='checkbox' class='checkbox' id="checkall" onchange="checkAll(this);" />
        </th>
        <th style="width: 1%;">
            <?php echo $this->translate("ID") ?>
        </th>
        <th style="width: 3%;">
            <?php echo $this->translate("Profile Type") ?>
        </th>
        <th style="width: 3%;">
            <?php echo $this->translate("Member Level") ?>
        </th>
        <th style="width: 3%;" class="admin_table_centered">
          <?php echo $this->translate("Members") ?>
        </th>
        <th style="width: 2%;">
          <?php echo $this->translate("Options") ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach( $this->paginator as $mapprofile ): ?>
      <tr>
        <td>
          <?php echo $this->formCheckbox('actions[]', $mapprofile->mapprofiletypelevel_id) ?>
        </td>
        <td>
          <?php echo $this->locale()->toNumber($mapprofile->mapprofiletypelevel_id) ?>
        </td>
        <td class="admin_table_bold">
            <?php echo $this->string()->truncate(Engine_Api::_()->getItem('option',$mapprofile->profile_type_id)->label, 15); ?>
        </td>
        <td class="admin_table_bold">
          <?php echo $this->translate(Engine_Api::_()->getItem('authorization_level',$mapprofile->member_level_id)->getTitle()) ?>
        </td>
        <td class="admin_table_centered">
          <?php
            $memberCount = $mapprofile->getMembershipCount($mapprofile->member_level_id);
            echo $this->translate(
                array("%s member", "%s members", $memberCount),
                $this->locale()->toNumber($memberCount)
            );
          ?>
        </td>
        <td class="admin_table_options">
          <?php echo $this->htmlLink(array('action' => 'map-profile-type', 'id' => $mapprofile->mapprofiletypelevel_id, 'reset' => false), $this->translate('edit'),  array('class' => 'smoothbox')) ?> |
          <?php echo $this->htmlLink(array('action' => 'delete-mapping', 'id' => $mapprofile->mapprofiletypelevel_id, 'reset' => false, 'format' => 'smoothbox'), $this->translate('delete'), array('class' => 'smoothbox')) ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <br/>

  <div class='buttons'>
    <button type='submit'>
      <?php echo $this->translate("Delete Selected") ?>
    </button>
  </div>
</form>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("No mappings have been created yet.") ?>
    </span>
  </div>
<?php endif ?>
