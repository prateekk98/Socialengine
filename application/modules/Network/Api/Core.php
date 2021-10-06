<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Core.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Network_Api_Core extends Core_Api_Abstract
{
    public function recalculate(User_Model_User $member, $values = null)
    {
        return Engine_Api::_()->getDbtable('networks', 'network')->recalculate($member, $values);
    }

    public function getViewerNetworkPrivacy($item, $owner_id = 'owner_id') {

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();

        $privacy = true;
        if($viewer->getIdentity() != $item->$owner_id && !$viewer->isAdmin()) {
            $networks = explode(',', $item->networks);
            if(!empty($item->networks) && count($networks) > 0) {
                $viewerNetworks = Engine_Api::_()->getDbtable('membership', 'network')->getMembershipsOfIds($viewer);
                if(!array_intersect($viewerNetworks, $networks)) {
                    $privacy = false;
                }
            }
        }
        return $privacy;
    }

    public function getNetworkSelect($rName, $select, $owner_id = 'owner_id') {

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();

        $networkSqlExecute = false;
        if (!empty($viewerId)) {
            if($viewer->isAdmin())
                return $select;
            $network_table = Engine_Api::_()->getDbTable('membership', 'network');
            $network_select = $network_table->select('resource_id')->where('user_id = ?', $viewerId);
            $network_id_query = $network_table->fetchAll($network_select);
            $network_id_query_count = count($network_id_query);
            $networkSql = '(';
            for ($i = 0; $i < $network_id_query_count; $i++) {
                $networkSql = $networkSql . "CONCAT(',',CONCAT(networks,',')) LIKE '%,". $network_id_query[$i]['resource_id'] .",%' || ";
            }
            $networkSql = trim($networkSql, '|| ') . ')';
            if ($networkSql != '()') {
                $networkSqlExecute = true;
                $networkSql = $networkSql . ' || networks IS NULL || networks = "" || ' . $rName . '.'. $owner_id .' =' . $viewerId;
                $select->where($networkSql);
            }
        }

        if (!$networkSqlExecute) {
            $networkUser = '';
            if ($viewerId)
                $networkUser = ' || ' . $rName . '.'. $owner_id . ' =' . $viewerId . ' ';
            $select->where('networks IS NULL || networks = ""  ' . $networkUser);
        }
        return $select;
    }
}
