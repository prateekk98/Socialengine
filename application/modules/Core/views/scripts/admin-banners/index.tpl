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

<h2>
    <?php echo $this->translate("Manage Banners") ?>
</h2>
<p>
    <?php echo $this->translate("CORE_VIEWS_SCRIPTS_ADMINBANNERS_INDEX_DESCRIPTION") ?>
    <?php
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if( $settings->getSetting('user.support.links', 0) == 1 ) : ?>
      <br> More Info: <a href="https://socialengine.atlassian.net/wiki/spaces/SU/pages/5210659/se-php-banner-manager" target="_blank">KB Article</a>
    <?php endif; ?>
</p>
<br />
<div>
    <?php echo $this->htmlLink(array('action' => 'create', 'reset' => false),
    $this->translate("Create New Banner"), array(
    'class' => 'buttonlink',
    'style' => 'background-image: url(' . $this->layout()->staticBaseUrl . 'application/modules/Announcement/externals/images/admin/add.png);')) ?>
</div>

<br/>
<div class='admin_results'>
   <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s banner found", "%s banners found", $count), $count) ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>
</div>

<br />
<?php if( count($this->paginator) ): ?>
<table class='admin_table'>
    <thead>
        <tr>
            <th style="width: 1%;">
                <?php echo $this->translate("ID") ?>
            </th>
            <th>
                <?php echo $this->translate("Title") ?>
            </th>
            <th>
                <?php echo $this->translate("CTA Label") ?>
            </th>
            <th style="width: 1%;">
                <?php echo $this->translate("Options") ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php foreach( $this->paginator as $item ): ?>
        <tr>
            <td><?php echo $item->banner_id ?></td>
            <td style="white-space: normal;"><?php echo $item->getTitle() ?></td>
            <td style="white-space: normal;"><?php echo $item->getCTALabel() ? $item->getCTALabel() : '-' ?></td>
            <td class="admin_table_options">
                <a href='<?php echo $this->url(array('action' => 'edit', 'id' => $item->banner_id)) ?>'>
                   <?php echo $this->translate("edit") ?>
                </a> |
                <a class='smoothbox' href='<?php echo $this->url(array('action' => 'preview', 'id' => $item->banner_id)) ?>'>
                   <?php echo $this->translate("preview") ?>
                </a>
                <?php if($item->custom): ?>
                |
                <a class='smoothbox' href='<?php echo $this->url(array('action' => 'delete', 'id' => $item->banner_id)) ?>'>
                   <?php echo $this->translate("delete") ?>
                </a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php else:?>

<div class="tip">
    <span><?php echo $this->translate("There are no banners created.") ?></span>
</div>
<?php endif; ?>
