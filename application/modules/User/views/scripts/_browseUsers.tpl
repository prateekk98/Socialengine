<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _browseUsers.tpl 9979 2013-03-19 22:07:33Z john $
 * @author     John
 */
?>
<?php if ($this->isAjaxSearch): ?>
<h3>
  <?php echo $this->translate(array('%s member found.', '%s members found.', $this->totalUsers),$this->locale()->toNumber($this->totalUsers)) ?>
</h3>
<?php endif; ?>
<?php $viewer = Engine_Api::_()->user()->getViewer();?>

<?php if( count($this->users) ): ?>
  <?php
    $ulClass = '';
    $excludedLevels = array(1, 2, 3);
    $isAdmin = false;

    if( !$this->viewer()->getIdentity() ) {
      $ulClass = 'public_user';
    } else {
      $viewerId = $viewer->getIdentity();
      if( in_array($viewer->level_id, $excludedLevels) ) {
        $isAdmin = true;
      } else {
        $registeredPrivacy = array('everyone', 'registered');
        $friendsIds = $viewer->membership()->getMembersIds();
      }
    }

?>

  <ul id="browsemembers_ul" class="grid_wrapper <?php echo $ulClass;?>">
    <?php foreach( $this->users as $user ): ?>
      <li>
        <?php
          $showPhoto = false;
          $viewPrivacy = $user->view_privacy;

          if( !isset($viewerId) ) {
            if( $viewPrivacy == 'everyone' ) {
              $showPhoto = true;
            }
          } elseif( $isAdmin
            || $viewerId == $user->getIdentity()
            || in_array($viewPrivacy, $registeredPrivacy)
            || ($viewPrivacy == 'member' && in_array($user->getIdentity(), $friendsIds))) {
            $showPhoto = true;
          } elseif($viewPrivacy == 'network' ) {
            $netMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
            $viewerNetwork = $netMembershipTable->getMembershipsOfIds($viewer);
            $userNetwork = $netMembershipTable->getMembershipsOfIds($user);

            if( in_array($user->getIdentity(), $friendsIds)
              || !empty(array_intersect($userNetwork, $viewerNetwork)) ) {
              $showPhoto = true;
            }
          }

          if( $showPhoto ){
            $profileImg = $this->itemBackgroundPhoto($user, 'thumb.profile');
          } else {
            $profileImg = '<span class="bg_item_photo bg_thumb_profile bg_item_photo_user bg_item_nophoto" '.
            'style=background-image:url("' . $this->baseUrl() . '/application/modules/User/externals/images/privatephoto_user_thumb_profile.png");></span>';
          }

        ?>
        <?php echo $this->htmlLink($user->getHref(), $profileImg) ?>
          <div class='browsemembers_results_info'>
            <?php echo $this->htmlLink($user->getHref(), $user->getTitle()) ?>
            <span>
              <?php echo $user->status; ?>
              <?php if( $user->status != "" ): ?>
            </span>
            <div>
              <?php echo $this->timestamp($user->status_date) ?>
            </div>
            <?php endif; ?>
          </div>
          <?php if( isset($viewerId) && $viewerId != $user->getIdentity() ): ?>
            <div class='browsemembers_results_links'>
              <?php echo $this->userFriendship($user) ?>
              <?php if($viewer->isAllowed('user', 'block') && !in_array($user->getIdentity(), $this->blockedUserIds) ) :?>
                <?php echo '<a href ="'. $this->url(array(
                'controller' => 'block',
                'action' => 'add',
                'user_id' => $user->getIdentity()
                ),'user_extended',true)
                . '" class = "buttonlink icon_user_block smoothbox">'. $this->translate("Block Member") . '</a>'; ?>
             <?php endif; ?>
            </div>
          <?php endif; ?>
        <p class="half_border_bottom"></p>

      </li>
    <?php endforeach; ?>
  </ul>
<?php endif ?>

<?php if( $this->users ):
    $pagination = $this->paginationControl($this->users, null, null, array(
      'pageAsQuery' => true,
      'query' => $this->formValues,
    ));
  ?>
  <?php if( trim($pagination) ): ?>
    <div class='browsemembers_viewmore' id="browsemembers_viewmore">
      <?php echo $pagination ?>
    </div>
  <?php endif ?>
<?php endif; ?>

<script type="text/javascript">
  page = '<?php echo sprintf('%d', $this->page) ?>';
  totalUsers = '<?php echo sprintf('%d', $this->totalUsers) ?>';
  userCount = '<?php echo sprintf('%d', $this->userCount) ?>';
</script>
