<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2016-10-21 02:08:08Z john $
 * @author     John
 */
?>
<div id="column_width_<?php echo $this->identity ?>"> </div>
<script type="text/javascript">
  en4.core.layout.setColumnWidth('column_width_<?php echo $this->identity ?>','<?php echo $this->columnWidth ?>');
</script>
