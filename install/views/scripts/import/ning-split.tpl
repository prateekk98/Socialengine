
<script type="text/javascript">
  var token = '<?php echo $this->token ?>';
  var url = '<?php echo $this->url(array('action' => 'ning-remote')) ?>';
  var runOnce = false;
  var state = true;

  var toggleLog = function (el) {
    el.getParent('li').getElement('ul').toggle();
    var html = el.get('data-action-title');
    el.set('data-action-title', el.get('html'));
    el.set('html', html);
  }

  var stopNextImport = function () {
    state = false;
    $('import_resume').setStyle('display', '');
    $('import_stop').setStyle('display', 'none');
  }
  var resumeImport = function () {
    state = true;
    runOnce = false;
    $('import_resume').setStyle('display', 'none');
    $('import_stop').setStyle('display', '');
    if (!$('import_log_container').getElement('.in_process')) {
        $('import_log_container').getElement('.not_start').addClass('in_process').removeClass('not_start');
    }
    sendImportRequest();
  }

  var toggleLoading = function (state) {
    if (state) {
      $('import_progress').setStyle('display', 'block');
      $('import_fatal_error').setStyle('display', 'none').set('html', '');
      $('import_log_container').getElement('.in_process').removeClass('error');
    } else {
      $('import_progress').setStyle('display', 'none');

    }
  }
  var sendImportRequest = function () {
    if (runOnce || !state) {
      return;
    }

    runOnce = true;
    toggleLoading(true);

    (new Request.JSON({
      url: url,
      data: {
        token: token
      },
      onComplete: function (responseJSON, responseText) {
        runOnce = false;
        toggleLoading(false);
        // An error occurred
        if ($type(responseJSON) != 'object') {
          $('import_fatal_error').setStyle('display', 'block').set('html', 'ERROR: ' + responseText);
          stopNextImport();
          return;
        }
        if (!$type(responseJSON.status) || !responseJSON.status) {
          if ($type(responseJSON.error)) {
            var error = responseJSON.error;
            if (responseJSON.errorOutput) {
              error = error + responseJSON.errorOutput;
            }
            $('import_fatal_error').set('html', 'ERROR: ' + error);
          } else {
            $('import_fatal_error').set('html', 'ERROR: ' + responseText);
          }
          $('import_fatal_error').setStyle('display', 'block');
          $('import_log_container').getElement('.in_process').addClass('error');
          stopNextImport();
          return;
        }

        // Normal
        // Check for progress report
        var responseMigrator = responseJSON.migratorResponse;
        var className = responseMigrator.className;
        var elementIdentity = 'import_log_' + className;
        var element = $(elementIdentity);
        element.empty();
        element.getParent('li').getElement('.status').removeClass('in_process').addClass('completed');
        element.getParent('li').getElement('.toggle_link').setStyle('display', '');
        var messages = $A(responseMigrator.messages);
        messages.push(responseMigrator.deltaTimeStr);
        messages.each(function (message) {
          (new Element('li', {
            'class': (message.toLowerCase().indexOf('error') != -1 ? 'error' : (message.toLowerCase().indexOf('warning') != -1 ? 'warning' : 'notice')),
            'html': message
          })).inject(element);
        });

        // Done!
        if (responseJSON.complete === true) {
          (new Element('div', {
            'html': '<h3>' + 'Congratulations!' + '</h3>' + '<span class="notice">' + 'The migration has been completed successfully!' + '</span>'
          })).inject($('import_container'));
          $('import_actions').setStyle('display', 'none');
        }

        else {

          if (state) {
            $('import_log_container').getElement('.not_start').addClass('in_process').removeClass('not_start');
            sendImportRequest();
          }
        }
      }
    })).send();
  }
  window.addEvent('load', function () {
    resumeImport();
  });

</script>
<div class="ning_import">
  <div id="import_container">
    <p> This page enables you to view the progress of your Ning Import process. As soon as each content type gets imported,
      you'll see a "View Details" link, clicking on which will display the main import log messages for that content.</p>
    <br />
    <div id="import_actions">
      <a href="javascript:void(0);" onclick="resumeImport();" id="import_resume" style="display:none;">
        Resume import
      </a>
      <div id="import_stop" style="display:none;">
        <a href="javascript:void(0);" onclick="stopNextImport();" >
          Click here to stop the import process
        </a> after the currently running step. You'll be able to resume the import after that.
      </div>
      <br />
      <br />
    </div>
    <div id="import_fatal_error" class="error">
    </div>
    <div id="import_progress">
      <h3 id="process_step"></h3>
      Please wait while the process is running...
    </div>
  </div>
  <br />
  <br />
  <div id="import_log_container">
    <ul class="import_log_section">
      <?php $inProcess = true; ?>
      <?php foreach($this->steps as $step => $title) : ?>
      <li>
        <?php $response = isset($this->responseMigrators[$step]) ? $this->responseMigrators[$step] : null; ?>
        <span class="status <?php echo $response ? 'completed' : ($inProcess ? 'in_process' : 'not_start' )?>"></span>
        <b><?php echo $title ?></b>
        <a onclick="toggleLog(this);" class="toggle_link" <?php if(!$response):?>style="display: none;"<?php endif; ?> data-action-title="Hide Details">
          View Details
        </a>
        <ul class="import_log" id="import_log_<?php echo $step ?>" style="display: none;">
        <?php if($response): ?>
          <?php foreach ($response['messages'] as $message): ?>
          <?php $className = strpos($message, 'error') !== false ? 'error' : (
              strpos($message, 'warning') !== false ? 'warning' : 'notice'
            );
          ?>
          <li class="<?php echo $className ?>">
            <!--  break a large string -->
            <?php echo substr(htmlentities($message), 0, 500); ?>
          </li>
          <?php endforeach; ?>
          <li>
            <?php echo $response['deltaTimeStr']; ?>
          </li>
        <?php else: ?>
          <?php $inProcess = false; ?>
        <?php endif;?>
        </ul>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
