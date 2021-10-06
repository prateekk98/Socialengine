<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FieldValueLoop.php 10103 2013-10-25 14:33:33Z ivan $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */
class Fields_View_Helper_FieldValueLoop extends Fields_View_Helper_FieldAbstract
{
  public function fieldValueLoop($subject, $partialStructure)
  {
    if( empty($partialStructure) ) {
      return '';
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    if( !($subject instanceof Core_Model_Item_Abstract) || !$subject->getIdentity() ) {
      return '';
    }

    // Calculate viewer-subject relationship
    $usePrivacy = ($subject instanceof User_Model_User);
    if( $usePrivacy ) {
      $relationship = 'everyone';
      if( $viewer && $viewer->getIdentity() ) {
        if( $viewer->getIdentity() == $subject->getIdentity() ) {
          $relationship = 'self';
        } elseif( $viewer->membership()->isMember($subject, true) ) {
          $relationship = 'friends';
        } else {
          $relationship = 'registered';
        }
      }
    }

    // Generate
    $content = '';
    $lastContents = '';
    $lastHeadingTitle = null; //Zend_Registry::get('Zend_Translate')->_("Missing heading");
    $showHidden = $viewer->getIdentity()
                 ? ($subject->getOwner()->isSelf($viewer) || 'admin' === Engine_Api::_()->getItem('authorization_level', $viewer->level_id)->type)
                 : false;
    $alreadyId = array();
    $alreadyHeading = array();
    foreach( $partialStructure as $map ) {

      // Get field meta object
      $field = $map->getChild();
      $value = $field->getValue($subject);
      if( !$field || $field->type == 'profile_type' ) continue;
      if( !$field->display && !$showHidden ) continue;
      $isHidden = !$field->display;

      // Get first value object for reference
      $firstValue = $value;
      if( is_array($value) && !empty($value) ) {
        $firstValue = $value[0];
      }

      // Evaluate privacy
      if( $usePrivacy && !empty($firstValue->privacy) && $relationship != 'self' ) {
        if( $firstValue->privacy == 'self' && $relationship != 'self' ) {
          $isHidden = true; //continue;
        } elseif( $firstValue->privacy == 'friends' && ($relationship != 'friends' && $relationship != 'self') ) {
          $isHidden = true; //continue;
        } elseif( $firstValue->privacy == 'registered' && $relationship == 'everyone' ) {
          $isHidden = true; //continue;
        }
      }

      // Render
      if( $field->type == 'heading' ) {
        // Heading
        if( $isHidden || in_array( $field->label, $alreadyHeading)) {
          continue;
        }
        if( !empty($lastContents) ) {
          $content .= $this->_buildLastContents($lastContents, $lastHeadingTitle);
          $lastContents = '';
        }
        $lastHeadingTitle = $this->view->translate($field->label);
        $alreadyHeading[] = $field->label;
      } else {
        // Normal fields
        $tmp = $this->getFieldValueString($field, $value, $subject, $map, $partialStructure);
        $hasValidValue = !empty($firstValue->value) || $field->type === 'checkbox';

        if( $hasValidValue && !empty($tmp) ) {
          if(in_array($field->field_id, $alreadyId)) {
            continue;
          }
          if($field->type == "textarea"){
            $tmp = html_entity_decode($tmp);
          }
          $notice = $isHidden && $showHidden
                  ? sprintf('<div class="tip"><span>%s</span></div>',
                      $this->view->translate('This field is hidden and only visible to you and admins:'))
                  : '';
          $alreadyId[] = $field->field_id;
          if( !$isHidden || $showHidden ) {
            $icon = "";
            if($field->icon)
              $icon = '<i class="'. $field->icon .'"></i>';
            $label = $icon.$this->view->translate($field->label);
            $lastContents .= <<<EOF
  <li data-field-id={$field->field_id} class=field_{$field->type}>
    {$notice}
    <span>
      {$label}
    </span>
    <span>
      {$tmp}
    </span>
  </li>
EOF;

          }
        }
      }

    }

    if( !empty($lastContents) ) {
      $content .= $this->_buildLastContents($lastContents, $lastHeadingTitle);
    }

    return $content;
  }

  public function getFieldValueString($field, $value, $subject, $map = null,
      $partialStructure = null)
  {
    if( (!is_object($value) || !isset($value->value)) && !is_array($value) ) {
      return null;
    }

    // @todo This is not good practice:
    // if($field->type =='textarea'||$field->type=='about_me') $value->value = nl2br($value->value);

    $helperName = Engine_Api::_()->fields()->getFieldInfo($field->type, 'helper');
    if( !$helperName ) {
      return null;
    }

    $helper = $this->view->getHelper($helperName);
    if( !$helper ) {
      return null;
    }

    $helper->structure = $partialStructure;
    $helper->map = $map;
    $helper->field = $field;
    $helper->subject = $subject;
    $tmp = $helper->$helperName($subject, $field, $value);
    unset($helper->structure);
    unset($helper->map);
    unset($helper->field);
    unset($helper->subject);

    return $tmp;
  }

  protected function _buildLastContents($content, $title)
  {
    if( !$title ) {
      return '<div class="profile_fields"><ul>' . $content . '</ul></div>';
    }
    return <<<EOF
        <div class="profile_fields">
          <h4>
            <span>{$title}</span>
          </h4>
          <ul>
            {$content}
          </ul>
        </div>
EOF;
  }
}
