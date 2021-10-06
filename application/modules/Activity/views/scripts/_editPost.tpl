<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _editPost.tpl 10194 2014-05-01 17:41:40Z mfeineman $
 * @author     Jung
 */
?>
<?php
$composerOptions = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options');
$hashtagEnabled = in_array("hashtags", $composerOptions);
?>
<script type="text/javascript">
  en4.core.runonce.add(function () {
    en4.activity.postLength = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_postLength',1000); ?>;
    en4.activity.bindEditFeed(<?php echo $this->action->getIdentity() ?>, {
      lang: {
        'Post Something...': '<?php echo $this->string()->escapeJavascript($this->translate('Post Something...')) ?>'
      },
      allowEmptyWithoutAttachment: <?php echo !empty($this->action->attachment_count) ? 1 : 0 ?>,
      hashtagEnabled : '<?php echo $hashtagEnabled ?>',
    });
  });
</script>
<?php foreach ($this->composePartials as $partial): ?>
  <?php if (false !== strpos($partial[0], '_composeTag') && !in_array('userTags', $composerOptions)) {
    continue;
  } ?>
  <?php
  echo $this->partial($partial[0], $partial[1], array(
    "edit" => 1,
    'forEdit' => $this->action->getIdentity(),
    'action' => $this->action
  ))
  ?>
<?php endforeach; ?>

<span class="feed_item_body_edit_content <?php echo ( empty($this->action->getTypeInfo()->is_generated) ? 'feed_item_posted' : 'feed_item_generated' ) ?>" style="display:none;">
  <?php echo $this->content; ?>
  <?php echo $this->form->render($this) ?>
</span>
