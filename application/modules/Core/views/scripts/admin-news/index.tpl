<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <john@socialengine.com>
 */
?>
<?php if (!empty($this->channel)) { ?>
<div class="admin_home_news">
    <h3 class="sep">
        <span><?php echo $this->translate("News & Updates") ?></span>
    </h3>
    <ul>
        <?php foreach ($this->channel['items'] as $item) { ?>
            <li>
                <div class="admin_home_news_date">
                    <?php echo $this->locale()->toDate(strtotime($item['pubDate']), array('size' => 'long')) ?>
                </div>
                <div class="admin_home_news_info">
                    <a href="<?php echo @$item['link'] ? $item['link'] : $item['guid'] ?>" target="_blank">
                        <?php echo $item['title'] ?>
                    </a>
                    <span class="admin_home_news_blurb">
                    <?php echo $this->string()->truncate($this->string()->stripTags($item['description']), 350) ?>
                </span>
                </div>
            </li>
        <?php } ?>
        <li>
            <div class="admin_home_news_date">
                &nbsp;
            </div>
            <div class="admin_home_news_info">
                 <a href="http://www.socialengine.com/blog" target="_blank"><?php echo $this->translate("More SE News") ?> &#187;</a>
            </div>
        </li>
    </ul>
</div>
<?php } else { ?>
<?php } ?>
