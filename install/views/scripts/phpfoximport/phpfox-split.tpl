<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: phpfox-split.tpl 2015-05-15 00:00:00Z john $
 * @author     John
 */
?>
<style>
    .divWarning
    {
        background: #f5f5f5; 
        background: -moz-linear-gradient(top,  #f5f5f5 0%, #efefef 100%); /* FF3.6+ */
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f5f5f5), color-stop(100%,#efefef)); /* Chrome,Safari4+ */
        background: -webkit-linear-gradient(top,  #f5f5f5 0%,#efefef 100%); /* Chrome10+,Safari5.1+ */
        background: -o-linear-gradient(top,  #f5f5f5 0%,#efefef 100%); /* Opera 11.10+ */
        background: -ms-linear-gradient(top,  #f5f5f5 0%,#efefef 100%); /* IE10+ */
        background: linear-gradient(top,  #f5f5f5 0%,#efefef 100%); /* W3C */
        -pie-background: linear-gradient(top,  #f5f5f5 0%,#efefef 100%); 
        border-radius: 7px;
        border:1px solid #d8d8d8;
        box-shadow: 0px 0px 1px 1px white inset, 0px 23px 21px -33px #000;
    }
    .button {
  font: bold 11px Arial;
  text-decoration: none;
  background-color: #EEEEEE;
  color: #333333;
  padding: 2px 6px 2px 6px;
  border-top: 1px solid #CCCCCC;
  border-right: 1px solid #333333;
  border-bottom: 1px solid #333333;
  border-left: 1px solid #CCCCCC;
  
}
    
</style>
<script type="text/javascript">
    var token = '<?php echo $this->token ?>';
    var url = '<?php echo $this->url(array('action' => 'phpfox-remote')) ?>';
    var runOnce = false;
    var state = true;
    var warningArr=<?php echo count($this->warningArr)>0 ? (json_encode($this->warningArr)) : '{}';?>;
    var pauseImport = function () {
        state = false;
        $('import_resume').setStyle('display', '');
        $('import_pause').setStyle('display', 'none');
    }
    var resumeImport = function () {
        state = true;
        runOnce = false;
        $('import_resume').setStyle('display', 'none');
        $('import_pause').setStyle('display', '');
        sendImportRequest();
    }
    var toggleLoading = function (state) {
        if (state) {

        } else {

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
                    $('import_fatal_error').set('html', 'ERROR: ' + responseText);
                    pauseImport();
                    return;
                }
                if (!$type(responseJSON.status) || !responseJSON.status) {
                   if ($type(responseJSON.error)) {
                        $('import_fatal_error').set('html', 'ERROR: ' + responseJSON.error);
                    } else {
                        $('import_fatal_error').set('html', 'ERROR: ' + responseText);
                    }
                    pauseImport();
                    return;
                }

                // Normal

                // Special case for done
                if ($type(responseJSON.complete)) {
                    responseJSON.migratorCurrent = responseJSON.migratorTotal;
                    responseJSON.totalProcessed = responseJSON.totalRecords;
                    responseJSON.timeRemainingStr = '0 hours, 0 minutes, 0 seconds (0 seconds total)';
                    responseJSON.ratioComplete = 1;
                }

                // Progress
                var progressString = '';

                // Show step progress
                if ($type(responseJSON.migratorCurrent)) {
                    progressString += responseJSON.migratorCurrent + ' of ' + responseJSON.migratorTotal + ' steps have been completed. ';
                }

                // Show record progress
                if ($type(responseJSON.totalRecords) && $type(responseJSON.totalProcessed)) {
                    if (progressString != '') {
                        progressString += '<br />' + "\n";
                    }
                    progressString += responseJSON.totalProcessed + ' of ' + responseJSON.totalRecords + ' records have been completed. ';
                }

                // Show time spent
                if ($type(responseJSON.deltaTimeStr) && responseJSON.deltaTimeStr != '') {
                    if (progressString != '') {
                        progressString += '<br />' + "\n";
                    }
                    progressString += ' ' + responseJSON.deltaTimeStr + ' have passed.';
                }

                // Show time remaining
                if ($type(responseJSON.timeRemainingStr) && responseJSON.timeRemainingStr != '') {
                    if (progressString != '') {
                        progressString += '<br />' + "\n";
                    }
                    progressString += ' ' + responseJSON.timeRemainingStr + ' remaining.';
                }

                // Show percent progress
                if ($type(responseJSON.ratioComplete)) {
                    if (progressString != '') {
                        progressString += '<br />' + "\n";
                    }
                    progressString += ' ' + (Math.round(parseFloat(responseJSON.ratioComplete) * 1000) / 10) + ' percent complete.';
                }

                if ('' != progressString) {
                    $('import_progress').set('html', progressString);
                }

                // Done!
                if ($type(responseJSON.complete)) {
                    
                    (new Element('li', {
                        'html': '<h3>' + 'Complete!' + '</h3>' + '<ul class="import_log"><li class="notice">' + 'The migration is complete!' + '</li></ul>'
                    })).inject($('import_log_container').getElement('.import_log_section'), 'top');
                   var container= $('import_log_container').getElement('.import_log_section');
                   var isWarning=false;
                   for (var name in warningArr) {
                       if(name=='escape')
                            continue;
                      isWarning=true;
                      break;
                   }
                   if(isWarning)
                   {
                       divElement = new Element('div', {
                                    'class': 'divWarning'
                                });
                        divElement.inject(container, 'bottom');
                        divElement.empty();
                        (new Element('br', {
                                         'html': ''
                                         
                                     })).inject(divElement);
                        (new Element('h3', {
                                         'html': 'Warnings : ',
                                         'style':'color:red;margin-left:7px;'
                                         
                                     })).inject(divElement);
                    }
                    for (var name in warningArr) {
                        
                        if(name=='escape')
                            continue;
                        messageObj= warningArr[name];
                        var elementIdentity = 'import_log_' + messageObj.class;

                        element = new Element('ul', {
                                    'id': elementIdentity,
                                    'class': 'import_log',
                                    'style':'margin-left:11px;'
                                    
                                });
                   
                        element.inject(divElement);
                        element.empty();
                        (new Element('li', {
                            'class': 'warning',
                            'html': messageObj.message,
                            'style':'margin-left:8px;'
                        })).inject(element);
                    }
                }
                else {
                    // Check for progress report
                    var className = responseJSON.className;
                    var elementIdentity = 'import_log_' + className;
                    var element = $(elementIdentity);
                    if(element)
                        element.empty();
                    $A(responseJSON.messages).each(function (message) {
                      
                        if(message.toLowerCase().indexOf('@@@@@') != -1)
                        {
                           msgArr= message.split ("$$$key$$$");
                           msgContent=msgArr[0].replace("@@@@@", "");
                           ind=msgContent.indexOf('<span style="font-weight:bolder;">Warning:</span>'); 
                           if(ind>-1)
                           {
                                clsName=msgContent.substring(0, ind);
                                msgContent=msgContent.replace(clsName, "");
                           }
                           warningArr[msgArr[1]]={"message":msgContent,"class":className};
                        }
                        else
                        {
                           if (!element) {
                                var tmpEl = new Element('li');
                                tmpEl.inject($('import_log_container').getElement('ul'), 'top');
                                (new Element('h3', {
                                    'html': className
                                })).inject(tmpEl);
                                element = new Element('ul', {
                                    'id': elementIdentity,
                                    'class': 'import_log'
                                });
                                element.inject(tmpEl);
                            }
                            (new Element('li', {
                            'class': (message.toLowerCase().indexOf('error') != -1 ? 'error' : (message.toLowerCase().indexOf('warning') != -1 ? 'warning' : 'notice')),
                                'html': message
                            })).inject(element);
                        }
                        
                    });
                    if (state) {
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

<div>
    <a href="javascript:void(0);" onclick="resumeImport();" id="import_resume" style="display:none;text-decoration: none;" class="button">
        Resume import
    </a>
    <a href="javascript:void(0);" onclick="pauseImport();" id="import_pause" style="display:none;text-decoration: none;" class="button">
        Pause Import
    </a>
    <?php
    $url = 'http://' . $_SERVER['HTTP_HOST'] . str_replace('\\', '/', dirname(dirname($_SERVER['PHP_SELF'])));
    $url= $url."/admin/log?file=import-phpfox&length=50&execute=";
    ?>
    <a href="<?php echo $url; ?>" target="_blank" style="margin-left:100px;text-decoration: none;" class="button">
       Log Browser
    </a>
    <br />
    <br />
    <div>
        Token: <?php echo $this->token ?>
        <br />
        URI: http://<?php echo $_SERVER['HTTP_HOST'] ?><?php echo $this->url(array(), 'default', true) ?>
    </div>
</div>
<br />

<div id="import_fatal_error">

</div>
<br />

<div id="import_progress">
<img src="externals/images/loading.gif" /> Loading...
</div>
<br />

<div id="import_log_container">
    <ul class="import_log_section">

    </ul>
</div>

<script type="text/javascript">
    $$('.navigation').getElement('li:nth-child(3)').addClass('active');
</script>
