<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Core.php 9917 2013-02-15 05:51:36Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_Api_Core extends Core_Api_Abstract
{
    /**
     * Loader for parsers
     *
     * @var Zend_Loader_PluginLoader
     */
    protected $_pluginLoader;


    // Parsing

    /**
     * Activity template parsing
     *
     * @param string $body
     * @param array $params
     * @return string
     */
    public function assemble($body, array $params = array(), $translate = true)
    {
        // Translate body
        if( $translate ) {
            $body = $this->getHelper('translate')->direct($body);
        }
        $body = nl2br($body);
        // Do other stuff
        preg_match_all('~\{([^{}]+)\}~', $body, $matches, PREG_SET_ORDER);
        foreach( $matches as $match ) {
            $tag = $match[0];
            $args = explode(':', $match[1]);
            $helper = array_shift($args);

            $helperArgs = array();
            foreach( $args as $arg ) {
                if( substr($arg, 0, 1) === '$' ) {
                    $arg = substr($arg, 1);
                    $helperArgs[] = ( isset($params[$arg]) ? $params[$arg] : null );
                } else {
                    $helperArgs[] = $arg;
                }
            }

            $helper = $this->getHelper($helper);
            $r = new ReflectionMethod($helper, 'direct');
            $content = $r->invokeArgs($helper, $helperArgs);
            $content = preg_replace('/\$(\d)/', '\\\\$\1', $content);
            $body = preg_replace("/" . preg_quote($tag) . "/", $content, $body, 1);
        }

        return $body;
    }

    /**
     * Gets the plugin loader
     *
     * @return Zend_Loader_PluginLoader
     */
    public function getPluginLoader()
    {
        if( null === $this->_pluginLoader ) {
            $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR
                . 'modules' . DIRECTORY_SEPARATOR
                . 'Activity';
            $this->_pluginLoader = new Zend_Loader_PluginLoader(array(
                'Activity_Model_Helper_' => $path . '/Model/Helper/'
            ));
        }

        return $this->_pluginLoader;
    }

    /**
     * Get a helper
     *
     * @param string $name
     * @return Activity_Model_Helper_Abstract
     */
    public function getHelper($name)
    {
        $name = $this->_normalizeHelperName($name);
        if( !isset($this->_helpers[$name]) ) {
            $helper = $this->getPluginLoader()->load($name);
            $this->_helpers[$name] = new $helper;
        }

        return $this->_helpers[$name];
    }

    /**
     * Normalize helper name
     *
     * @param string $name
     * @return string
     */
    protected function _normalizeHelperName($name)
    {
        $name = preg_replace('/[^A-Za-z0-9]/', '', $name);
        //$name = strtolower($name);
        $name = ucfirst($name);
        return $name;
    }

    public function getNetworks($type, $viewer) {
        $ids = array();
        $viewer_id = $viewer->getIdentity();
        if (empty($type) || empty($viewer_id)) {
            return;
        }

        if( $type == 1 ) {
            $networkTable = Engine_Api::_()->getDbtable('membership', 'network');
            $ids = $networkTable->getMembershipsOfIds($viewer);
            $count = count($ids);
            if( empty($count) ) {
                return;
            }

            $ids = array_unique($ids);
        }

        $table = Engine_Api::_()->getItemTable('network');
        $select = $table->select()
            ->order('title ASC');
        if ($type == 1 && !empty($ids)) {
            $select->where('network_id IN(?)', $ids);
        }
        return $table->fetchAll($select);
    }

    public function isNetworkBasePrivacy($string) {
        if (empty($string)) {
            return;
        }

        $arr = explode(',', $string);
        return preg_match("/network_/", $arr[0]);
    }

    public function getNetworkBasePrivacyIds($string) {
        if (empty($string)) {
            return;
        }

        $ids = array();
        $arr = explode(',', $string);
        foreach ($arr as $val) {
            $ids[] = str_replace('network_', '', $val);
        }
        return $ids;
    }

    public function editContentPrivacy($item, $user, $auth_view = null) {
        $type = null;
        switch ($auth_view) {
            case 'everyone':
                $auth_view = "everyone";
                break;
            case 'networks':
                $auth_view = "owner_network";
                $type = '_network';
                break;
            case 'friends':
                $auth_view = 'owner_member';
                $type = '_friend';
                break;
            case 'onlyme':
                $auth_view = 'owner';
                $type = '_onlyme';
                break;
        }
        if (empty($auth_view)) {
            $auth_view = "everyone";
        }

        // Work For Album
        if ($item->getType() == 'album_photo') {
            $parent = $item->getParent();
            if ($auth_view != "everyone") {
                $type = 'wall' . $type;
                $album = $this->getSpecialAlbum($user, $type, $auth_view);
                if (isset($item->album_id)) {
                    $item->album_id = $album->album_id;
                } else {
                    $item->collection_id = $album->album_id;
                }

                $item->save();
            }
        }


        // Work For Music
        if ($item->getType() == 'music_playlist_song') {
            $parent = $item->getParent();
            if ($auth_view != "everyone") {
                $type = 'wall' . $type;
                $playlist = $this->getSpecialPlaylist($user, $type, $auth_view);
                $item->playlist_id = $playlist->playlist_id;
                $item->save();
            }
        }

        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        $viewMax = array_search($auth_view, $roles);
        foreach ($roles as $i => $role) {
            $auth->setAllowed($item, $role, 'view', ($i <= $viewMax));
        }
    }

    public function getSpecialAlbum(User_Model_User $user, $type, $auth_view) {
        if (!in_array($type, array('wall_friend', 'wall_network', 'wall_onlyme'))) {
            throw new Album_Model_Exception('Unknown special album type');
        }
        $table = Engine_Api::_()->getDbtable('albums', 'album');
        $select = $table->select()
            ->where('owner_type = ?', $user->getType())
            ->where('owner_id = ?', $user->getIdentity())
            ->where('type = ?', $type)
            ->order('album_id ASC')
            ->limit(1);

        $album = $table->fetchRow($select);

        // Create wall photos album if it doesn't exist yet
        if (null === $album) {
            $translate = Zend_Registry::get('Zend_Translate');
            $album = $table->createRow();
            $album->owner_type = 'user';
            $album->owner_id = $user->getIdentity();
            $album->title = $translate->_(ucfirst(str_replace("_", " ", $type)) . ' Photos');
            $album->type = $type;
            $album->save();

            // Authorizations
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            $viewMax = array_search($auth_view, $roles);
            foreach ($roles as $i => $role) {
                $auth->setAllowed($album, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($album, $role, 'comment', ($i <= $viewMax));
            }
        }

        return $album;
    }

    public function getHashTags($string) {
        preg_match_all("/\s(#[^\s[!\"\#$%&'()*+,\-.\/\\:;<=>?@\[\]\^`{|}~]+)/", ' ' . $string, $hashtags);
        if (!empty($hashtags[0])) {
            foreach ($hashtags[0] as $key => $hashtag) {
                $hashtag = str_replace('#', '', $hashtag);
                $hashtags[0][$key] = trim($hashtag);
            }
        }

        return $hashtags;
    }


    public function getEmoticons($withIcons = false, $tinyEditor = false, $chatEmotions = false)
    {
        $filePath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR
            . 'modules' . DIRECTORY_SEPARATOR
            . "Activity/externals/emoticons/emoticons.php";
        $emoticons = file_exists($filePath) ? include $filePath : NULL;

        if (!$withIcons && !$tinyEditor && !$chatEmotions) {
            return $emoticons;
        }

        if($tinyEditor) {
            $emoticonString = '[';
            foreach($emoticons as $emoticon) {
                $emoticonString .= '"'.$emoticon.'",';
            }
            $emoticonString .= ']';
            return $emoticonString;
        }

        //Chat emoticon
        if($chatEmotions) {
            $emoticonArray = array();
            foreach($emoticons as $symbol => $emoticon) {
                $emoticonArray[$symbol] = $emoticon;
            }
            return json_encode($emoticonArray, JSON_HEX_QUOT | JSON_HEX_TAG);
        }

        $emoticonIcons = array();
        foreach ($emoticons as $symbol => $icon) {
            $emoticonIcons[$symbol] = "<img class = \"emoticon_img\" src=\"" . Zend_Registry::get('Zend_View')->layout()->staticBaseUrl . "application/modules/Activity/externals/emoticons/images/$icon\" border=\"0\" />";
        }
        return $emoticonIcons;
    }
}
