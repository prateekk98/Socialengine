<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<h3>Import Tools</h3>
<p>Below is a list of supported import tools that can be utilized for importing your data into SocialEngine.</p>
<p>More info: <a href="https://socialengine.atlassian.net/wiki/spaces/SU/pages/5308681/se-php-list-of-import-tools-for-socialengine" target="_blank">See KB article</a>.</p>
<br />

<ul>
  <?php foreach( $this->importers as $importer ): ?>
  <li>
    <a class="buttonlink import_version3" href="<?php echo $this->url($importer['url']) ?>">
      <?php echo $importer['title']; ?>
    </a>
    <p class="buttontext">
      <?php echo $importer['description']; ?>
    </p>
    <br />
  </li>
  <?php endforeach; ?>
</ul>
