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

$url = $this->url(array(), 'core_rss_news');
?>


    <div id="admin-rss-news">
        <span><i class="fa fa-circle-o-notch fa-spin"></i></span>
    </div>
    <script>
        en4.core.runonce.add(function () {
            var r = new Request({
                url: '<?php echo $url; ?>',
                onSuccess: function (res) {
                    $('admin-rss-news').set('html', res);
                }
            });

            r.send();
        });
    </script>

