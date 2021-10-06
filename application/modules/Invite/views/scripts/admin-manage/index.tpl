<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9915 2013-02-15 01:30:19Z alex $
 * @author     John
 */
?>

<h2>
  <?php echo $this->translate("Manage Invites") ?>
</h2>
<p>
  <?php echo $this->translate("The email address of users invited to your social network are listed below. if you need to search for a specific member, enter email address in the field below.") ?>
</p>
<br />

<script type="text/javascript">


function multiModify()
{
  var multimodify_form = $('multimodify_form');
  if (multimodify_form.submit_button.value == 'delete')
  {
    return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete the selected user accounts?")) ?>');
  }
}

function selectAll()
{
  var i;
  var multimodify_form = $('multimodify_form');
  var inputs = multimodify_form.elements;
  for (i = 1; i < inputs.length - 1; i++) {
    if (!inputs[i].disabled) {
      inputs[i].checked = inputs[0].checked;
    }
  }
}


<?php if( $this->openUser ): ?>
window.addEvent('load', function() {
  $$('#multimodify_form .admin_table_options a').each(function(el) {
    if( -1 < el.get('href').indexOf('/edit/') ) {
      el.click();
      //el.fireEvent('click');
    }
  });
});
<?php endif ?>
</script>

<div class='admin_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>

<br />
<?php $count = $this->paginator->getTotalItemCount() ?>

<?php if($count > 0) { ?>
  <div class='admin_results'>
    <div>
      
      <?php echo $this->translate(array("%s invite found", "%s invites found", $count),
          $this->locale()->toNumber($count)) ?>
    </div>
    <div>
      <?php echo $this->paginationControl($this->paginator, null, null, array(
        'pageAsQuery' => true,
        'query' => $this->formValues,
        //'params' => $this->formValues,
      )); ?>
    </div>
  </div>
  <br />
  <div class="admin_table_form">
    <form id='multimodify_form' method="post" action="<?php echo $this->url(array('action'=>'multi-modify'));?>" onSubmit="multiModify()">
      <table class='admin_table'>
        <thead>
          <tr>
            <th style='width: 1%;'><input onclick="selectAll()" type='checkbox' class='checkbox'></th>
            <th style='width: 1%;'><?php echo $this->translate("ID") ?></th>
            <th style='width: 1%;'><?php echo $this->translate("Email") ?></th>
            <th style='width: 1%;'><?php echo $this->translate("Invite Sent Date") ?></th>
            <th style='width: 1%;' class='admin_table_options'><?php echo $this->translate("Options") ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if( count($this->paginator) ): ?>
            <?php foreach( $this->paginator as $item ): ?>
              <tr>
                <td><input name='modify_<?php echo $item->id;?>' value=<?php echo $item->id;?> type='checkbox' class='checkbox'></td>
                <td><?php echo $item->id ?></td>
                <td class='admin_table_email'>
                  <?php if( !$this->hideEmails ): ?>
                    <a href='mailto:<?php echo $item->recipient ?>'><?php echo $item->recipient ?></a>
                  <?php else: ?>
                    (hidden)
                  <?php endif; ?>
                </td>
                <td class="nowrap">
                  <?php echo $this->locale()->toDateTime($item->timestamp) ?>
                </td>
                <td class='admin_table_options'>
                  <a class='smoothbox' href='<?php echo $this->url(array('action' => 'delete', 'id' => $item->id));?>'>
                    <?php echo $this->translate("delete") ?>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      <br />
      <div class='buttons'>
        <button type='submit' name="submit_button" value="delete" style="float: right;"><?php echo $this->translate("Delete Selected") ?></button>
      </div>
    </form>
  </div>
<?php } else { ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no invites yet."); ?>
    </span>
  </div>
<?php } ?>
