<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2021 Webligo Developments
 * @license    https://www.socialengine.com/eula
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2021 Webligo Developments
 * @license    https://www.socialengine.com/eula
 */
class Core_AdminNewsController extends Core_Controller_Action_Admin
{
    public function indexAction()
    {
        // Get params
        $this->view->url = $url = 'https://blog.socialengine.com/feed/';
        $this->view->max = $max = $this->_getParam('max', 4);
        $this->view->strip = $strip = $this->_getParam('strip', false);
        $cacheTimeout = 1800;

        // Caching
        $cache = Zend_Registry::get('Zend_Cache');
        if ($cache instanceof Zend_Cache_Core &&
            $cacheTimeout > 0) {
            $cacheId = get_class($this) . md5($url . $max . $strip);
            $channel = $cache->load($cacheId);
            if (!is_array($channel) || empty($channel)) {
                $channel = null;
            } elseif (time() > $channel['fetched'] + $cacheTimeout) {
                $channel = null;
            }
        } else {
            $cacheId = null;
            $channel = null;
        }

        if (!$channel) {
            try {
                $rss = Zend_Feed::import($url);
            } catch (Exception $e) {
                exit;
            }

            $channel = array(
                'title'       => $rss->title(),
                'link'        => $rss->link(),
                'description' => $rss->description(),
                'items'       => array(),
                'fetched'     => time(),
            );

            // Loop over each channel item and store relevant data
            $count = 0;
            foreach ($rss as $item) {
                if ($count++ >= $max) {
                    break;
                }
                $channel['items'][] = array(
                    'title'       => $item->title(),
                    'link'        => $item->link(),
                    'description' => $item->description(),
                    'pubDate'     => $item->pubDate(),
                    'guid'        => $item->guid(),
                );
            }

            $this->view->isCached = false;

            // Caching
            if ($cacheId && !empty($channel)) {
                $cache->save($channel, $cacheId);
            }
        } else {
            $this->view->isCached = true;
        }

        $this->view->channel = $channel;
        $this->_helper->layout->disableLayout();
    }
}
