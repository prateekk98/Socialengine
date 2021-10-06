<?php
/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Phpfoximporter
* @copyright  Copyright 2006-2020 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: phpfox-instructions.tpl 2015-05-15 00:00:00Z john $
* @author     John
*/
?>
<div>
  <h2>PHPfox Import Instructions</h2>

  <p>
    This PHPfox Import tool is designed to migrate content directly from a PHPfox installation into SocialEngine. It is intended to be used on a fresh install of SocialEngine PHP. It will remove any existing content on the network.
  </p>

  <br />


  <?php if (!empty($this->dbHasContent)): ?>
  <div class="warning">
    Your site already has content. The content will be removed if you use this
    import tool.
  </div>
  <br />
  <br />
  <?php endif; ?>


  <ul>
    <li>
      The following types of data
      <b style="font-weight: bold;">will be removed or overwritten from the existing <em>SocialEngine PHP</em> installation</b>:
      <ul style="margin-left: 20px;margin-bottom: 10px;padding-top: 4px;list-style: circle;">
        <li>
          All admin and user accounts.
        </li>
        <li>
          User levels.
        </li>
        <li>
          All user content.
        </li>
        <li>
          All announcements.
        </li>
        <li>
          Files uploaded in the admin panel media manager.
        </li>
        <li>
          All admin created categoried (i.e. blog categories, video categories, etc)
        </li>


      </ul>
    </li>
    <li>
      The following types of data
      <b style="font-weight: bold;">will not be removed or overwritten from the existing <em>SocialEngine PHP</em> installation</b>:
      <ul style="margin-left: 20px;margin-bottom: 10px;padding-top: 4px;list-style: circle;">
        <li>
          Any installed plugins, themes, widgets, or language packs.
        </li>
        <li>
          Custom pages or changes to existing pages made in the layout editor.
        </li>
      </ul>
    </li>
    <li>
      The following types or data
      <b style="font-weight: bold;">may be removed or overwritten from the existing <em>SocialEngine PHP</em> installation</b>:
      <ul style="margin-left: 20px;margin-bottom: 10px;padding-top: 4px;list-style: circle;">
        <li>
          Global settings
        </li>
        <li>
          Level settings
        </li>
      </ul>
    </li>
  </ul>
  <div class="warning">
    Note: In Order to proceed with the import process from phpfox, you need to enter the Youtube API Key in the Global Settings of the Video Plugin in your SocialEngine 5 installation.
  </div>
  <br />

  <button type="button" style="margin:10px 0px" id="continue" name="continue"
          onclick="window.location.href = '<?php echo $this->url(array('action' => 'phpfox')) ?>';
                      return false;">
    Start Import
  </button>

</div>
