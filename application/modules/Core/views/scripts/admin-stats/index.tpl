<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9861 2013-02-12 02:25:28Z john $
 * @author     John
 */
?>

<h2><?php echo $this->translate("Site-wide Statistics") ?></h2>
<p>
  <?php echo $this->translate("CORE_VIEWS_SCRIPTS_ADMINSTATS_INDEX_DESCRIPTION") ?>
</p>

<?php
  $settings = Engine_Api::_()->getApi('settings', 'core');
  if( $settings->getSetting('user.support.links', 0) == 1 ) {
    echo 'More info: <a href="https://socialengine.atlassian.net/wiki/spaces/SU/pages/5243324/se-php-site-wide-statistics" target="_blank">See KB article</a>.';
  } 
?>	

<br />
<br />

<div class="admin_search">
  <div class="search">
    <?php echo $this->filterForm->render($this) ?>
  </div>
</div>

<br />



<div id="admin_statistics" class="admin_statistics">
  <div class="admin_statistics_nav">
    <a id="admin_stats_offset_previous" onclick="processStatisticsPage(-1);"><?php echo $this->translate("Previous") ?></a>
    <a id="admin_stats_offset_next" onclick="processStatisticsPage(1);" style="display: none;"><?php echo $this->translate("Next") ?></a>
  </div>

  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script type="text/javascript">
    var currentArgs = {};
    var processStatisticsFilter = function(formElement) {
      var vals = formElement.toQueryString().parseQueryString();
      vals.offset = 0;
      buildChart(vals);
      return false;
    }
    var processStatisticsPage = function(count) {
      var args = $merge(currentArgs);
      args.offset += count;
      buildChart(args);
    }
    var updateFormOptions = function() {
      var periodEl = $$('form').getElement('#period');
      var chunkEl = $$('form').getElement('#chunk');
      switch( periodEl.get('value')[0] ) {
        case 'ww':
          var children = chunkEl.getChildren()[0];
          for( var i = 0, l = children.length; i < l; i++ ) {
            if( ['dd'].indexOf(children[i].get('value')) == -1 ) {
              children[i].setStyle('display', 'none');
              if( children[i].get('selected') ) {
                children[i].set('selected', false);
              }
            } else {
              children[i].setStyle('display', '');
            }
          }
          break;
        case 'MM':
          var children = chunkEl.getChildren()[0];
          for( var i = 0, l = children.length; i < l; i++ ) {
            if( ['dd', 'ww'].indexOf(children[i].get('value')) == -1 ) {
              children[i].setStyle('display', 'none');
              if( children[i].get('selected') ) {
                children[i].set('selected', false);
              }
            } else {
              children[i].setStyle('display', '');
            }
          }
          break;
        case 'y':
          var children = chunkEl.getChildren()[0];
          for( var i = 0, l = children.length; i < l; i++ ) {
            if( ['dd', 'ww', 'MM'].indexOf(children[i].get('value')) == -1 ) {
              children[i].setStyle('display', 'none');
              if( children[i].get('selected') ) {
                children[i].set('selected', false);
              }
            } else {
              children[i].setStyle('display', '');
            }
          }
          break;
        default:
          break;
      }
    }
    var buildChart = function(args) {
      currentArgs = args;
      $('admin_stats_offset_next').setStyle('display', (args.offset < 0 ? '' : 'none'));

      var url = new URI('<?php echo (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']
        . $this->url(array('action' => 'chart-data')) ?>');
      url.setData(args);
      while ($('my_chart').firstChild) {
        $('my_chart').removeChild($('my_chart').firstChild);
      }
      $('loading').setStyle('display', '').inject($('my_chart'));
      var req = new Request.JSON({
        url : url.toString(),
        data : {
          format : 'json',
        },
        onComplete : function(responseJSON) {
          $('loading').setStyle('display', 'none').inject($('admin_statistics'));
          google.charts.setOnLoadCallback(drawChart(responseJSON));
        }
      });
      (function() {
        req.send();
      }).delay(250);
    }

    window.addEvent('load', function() {
      updateFormOptions();
      $('period').addEvent('change', function(event) {
        updateFormOptions();
      });
      buildChart({
        'type' : 'core.views',
        'mode' : 'normal',
        'chunk' : 'dd',
        'period' : 'ww',
        'start' : 0,
        'offset' : 0
      });
    });

    google.charts.load('current', {'packages':['corechart']});
    function drawChart(response) {
      var data = [];
      for (var key in response.data) {
        if (response.data.hasOwnProperty(key)) {
          data.push([key, response.data[key]]);
        }
      }
      var data = google.visualization.arrayToDataTable(data);

      var options = {
        title: response.title,
        legend: { position: 'bottom' }
      };
      var chart = new google.visualization.PieChart(document.getElementById('my_chart'));
      chart.draw(data, options);
    }
  </script>
    <div id="my_chart" class="my_chart"></div>
      <div id="loading" style="display: none"></div>
  </div>
</div>
<style>
  .my_chart {
    height: 450px;
    width: 1000px
  }

  #loading {
    width: inherit;
    height: inherit;
    background-position: center 10%;
    background-repeat: no-repeat;
    background-image: url(application/modules/Core/externals/images/large-loading.gif);
  }
</style>
