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
<?php $viewer = Engine_Api::_()->user()->getViewer(); ?>
<h2><?php echo $this->translate('Search') ?></h2>
<div id="searchform" class="global_form_box">
  <?php echo $this->form->setAttrib('class', '')->render($this) ?>
</div>
<br />
<br />
<?php if( empty($this->paginator) ): ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('Please enter a search query.') ?>
    </span>
  </div>
<?php elseif( $this->paginator->getTotalItemCount() <= 0 ): ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('No results were found.') ?>
    </span>
  </div>
<?php else: ?>
  <?php foreach( $this->paginator as $item ):
    $item = $this->item($item->type, $item->id);
    if( !$item ) continue; 
    $canView = $item->authorization()->isAllowed($viewer, 'view');
    if(!$canView) break;
    ?>
    <div class="search_result">
      <div class="search_photo">
        <?php echo $this->htmlLink($item->getHref(), $this->itemPhoto($item, 'thumb.icon')) ?>
      </div>
      <div class="search_info">
        <?php if( '' != $this->query ): ?>
          <?php echo $this->htmlLink($item->getHref(), $this->highlightText($item->getTitle(), $this->query), array('class' => 'search_title')) ?>
        <?php else: ?>
          <?php echo $this->htmlLink($item->getHref(), $item->getTitle(), array('class' => 'search_title')) ?>
        <?php endif; ?>
        <p class="search_description">
          <?php if( '' != $this->query ): ?>
            <?php echo $this->highlightText($this->viewMore($item->getDescription()), $this->query); ?>
          <?php else: ?>
            <?php echo $this->viewMore($item->getDescription()); ?>
          <?php endif; ?>
        </p>
      </div>
    </div>
  <?php endforeach; ?>
  <br />
  <div>
    <?php echo $this->paginationControl($this->paginator, null, null, array('query' => array('query' => $this->query, 'type' => $this->type))); ?>
  </div>
<?php endif; ?>
