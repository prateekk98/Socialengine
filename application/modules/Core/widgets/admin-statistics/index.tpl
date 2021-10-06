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

<h3 class="sep">
  <span>
    <?php echo $this->translate('Quick Stats') ?>
  </span>
</h3>

<table class='admin_home_stats'>
  <thead>
    <tr>
      <th colspan='3' align="left"><?php echo $this->translate('Network Information') ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><?php echo $this->translate('Created') ?></td>
      <td colspan='2'><?php echo $this->timestamp($this->site['creation']) ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Version') ?></td>
      <td colspan='2'><?php echo $this->coreVersion ?></td>
    </tr>
  </tbody>
</table>

<table class='admin_home_stats'>
  <thead>
    <tr>
      <th align="left"><?php echo $this->translate('Statistics') ?></th>
      <th style="text-align:center;"><?php echo $this->translate('Today') ?></th>
      <th style="text-align:center;"><?php echo $this->translate('Total') ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach( $this->statistics as $statistic ): ?>
      <tr>
        <td>
          <?php echo $this->translate($statistic['label']) ?>
        </td>
        <td style="text-align:center;">
          <?php echo $this->locale()->toNumber((int)$statistic['today']) ?>
        </td>
        <td style="text-align:center;">
          <?php echo $this->locale()->toNumber((int)$statistic['total']) ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
