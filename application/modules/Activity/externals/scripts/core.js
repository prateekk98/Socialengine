
/* $Id: core.js 10182 2014-04-29 23:52:40Z andres $ */



(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;



en4.activity = {
  postLength: 1000,
  editComposers : new Hash(),
  load : function(next_id, subject_guid){
    if( en4.core.request.isRequestActive() ) return;

    $('feed_viewmore').style.display = 'none';
    $('feed_loading').style.display = '';

    en4.core.request.send(new Request.HTML({
      url : en4.core.baseUrl + 'activity/widget/feed',
      data : {
        //format : 'json',
        'maxid' : next_id,
        'feedOnly' : true,
        'nolayout' : true,
        'subject' : subject_guid
      }
      /*
      onSuccess : function(){
        $('feed_viewmore').style.display = '';
        $('feed_loading').style.display = 'none';
      }*/
    }), {
      'element' : $('activity-feed'),
      'updateHtmlMode' : 'append'
    });
  },

  like : function(action_id, comment_id) {
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/index/like',
      data : {
        format : 'json',
        action_id : action_id,
        comment_id : comment_id,
        subject : en4.core.subject.guid
      }
    }), {
      //'element' : $('activity-item-'+action_id),
      //'updateHtmlMode': 'comments'
      'element' : $('comment-likes-activity-item-'+action_id),
      'updateHtmlMode': 'comments2'
    });
  },
  
  unlike : function(action_id, comment_id) {
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/index/unlike',
      data : {
        format : 'json',
        action_id : action_id,
        comment_id : comment_id,
        subject : en4.core.subject.guid
      }
    }), {
      //'element' : $('activity-item-'+action_id),
      //'updateHtmlMode': 'comments'
      'element' : $('comment-likes-activity-item-'+action_id),
      'updateHtmlMode': 'comments2'
    });
  },

  comment : function(formData) {
    if( formData.body.trim() == '' ) {
      return;
    }

    en4.core.request.send(new Request.JSON({
      format: 'json',
      url : en4.core.baseUrl + 'activity/index/comment',
      data : formData,
    }), {
      'element' : $('comment-likes-activity-item-' + formData.action_id),
      'updateHtmlMode': 'comments2'
    });
  },

  viewComments : function(action_id){
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/index/viewComment',
      data : {
        format : 'json',
        action_id : action_id,
        nolist : true
      }
    }), {
      'element' : $('activity-item-'+action_id),
      'updateHtmlMode': 'comments'
      //'element' : $('comment-likes-activity-item-'+action_id),
    });
  },

  viewLikes : function(action_id){
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/index/viewLike',
      data : {
        format : 'json',
        action_id : action_id,
        nolist : true
      }
    }), {
      'element' : $('activity-item-'+action_id),
      'updateHtmlMode': 'comments'
    });
  },

  hideNotifications : function(reset_text) {
    en4.core.request.send(new Request.JSON({
      'url' : en4.core.baseUrl + 'activity/notifications/hide'
    }));
    $('updates_toggle').removeClass('new_updates');
    if($('update_count'))
    $('update_count').removeClass('minimenu_update_count_bubble_active');
    /*
    var notify_link = $('core_menu_mini_menu_updates_count').clone();
    $('new_notification').destroy();
    notify_link.setAttribute('id', 'core_menu_mini_menu_updates_count');
    notify_link.innerHTML = "0 updates";
    notify_link.inject($('core_menu_mini_menu_updates'));
    $('core_menu_mini_menu_updates').setAttribute('id', '');
    */
    if($('notifications_main')){
      var notification_children = $('notifications_main').getChildren('li');
      notification_children.each(function(el){
          el.setAttribute('class', '');
      });
    }

    if($('notifications_menu')){
      var notification_children = $('notifications_menu').getChildren('li');
      notification_children.each(function(el){
          el.setAttribute('class', '');
      });
    }
    //$('core_menu_mini_menu_updates').setStyle('display', 'none');
  },

  updateNotifications : function() {
    if( en4.core.request.isRequestActive() ) return;
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/notifications/update',
      data : {
        format : 'json'
      },
      onSuccess : this.showNotifications.bind(this)
    }));
  },

  showNotifications : function(responseJSON){
    if (responseJSON.notificationCount>0){
      $('updates_toggle').addClass('new_updates');
    }
  },

  markRead : function (action_id){
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/notifications/test',
      data : {
        format     : 'json',
        'actionid' : action_id
      }
    }));
  },

  cometNotify : function(responseObject){
    $('core_menu_mini_menu_updates').style.display = '';
    $('core_menu_mini_menu_updates_count').innerHTML = responseObject.text;
  },
  alignHtml : function(){
      scriptJquery(".feed_item_body_content > .feed_item_bodytext").each(function(){
        var element = scriptJquery(this);
        element.clone().insertAfter(scriptJquery(this).closest(".feed_item_body_content"));
        element.remove();
      });
  },
  post : function (composeInstance, event) {
    event.stop();
    var formData = composeInstance.getForm().toQueryString().parseQueryString();
    en4.core.request.send(new Request.JSON({
      url: composeInstance.getForm().get('action'),
      data: formData,
      format: 'json',
      onComplete: function (resp) {
        if(!resp.status) {
          $('fail_msg').style.display = "block";
          return;
        }
        if(resp.flood){
          $('flood_msg').style.display = "block";
          $('flood_msg_cnt').innerHTML = resp.floodMessage;
          return;
        }else{
            $('flood_msg').style.display = "none";
            $('flood_msg_cnt').innerHTML = "";
        }

        if (!$('activity-feed')) {
          new Element('ul', {
            'id': 'activity-feed',
            'class': 'feed'
          }).inject(document.getElement('.layout_activity_feed').getElementById('fail_msg'), 'after');
          $('no-feed-tip').destroy();
        }

        if (window._activityUpdateHandler !== undefined) {
          window._activityUpdateHandler.options.next_id = resp.action_id;
          window._activityUpdateHandler.getFeedUpdate(window._activityUpdateHandler.options.next_id, true);
        } else {
          new ActivityUpdateHandler({
            'baseUrl' : en4.core.baseUrl,
            'basePath' : en4.core.basePath,
            'identity' : 4,
            'subject_guid' : en4.core.subject.guid
          }).getFeedUpdate(resp.action_id, true);
        }

        $('token').value = resp.formToken;
        // Remove the params values after submit
        composeInstance.plugins.each(function(plugin) {
          plugin.reset();
        });
        composeInstance.reset();
      }
    }), {
      force: true,
    });
  },
  bindEditFeed: function(action_id, composerOptions) {
    if( $('activity-item-' + action_id).getElement('.compose-container') ) {
      this.bindEditLink(action_id);
      return;
    }
    var editComposeInstance = new Composer('feed-edit-body-' + action_id, {
      lang: composerOptions.lang,
      hashtagEnabled : composerOptions.hashtagEnabled,
      hideSubmitOnBlur: false,
      allowEmptyWithoutAttachment: composerOptions.allowEmptyWithoutAttachment,
      submitCallBack: function(composeInstance, event) {
        event.stop();
        var params = composeInstance.getForm().toQueryString().parseQueryString();
        en4.core.request.send(new Request.JSON({
          url: editComposeInstance.getForm().get('action'),
          data: $merge({
            format: 'json',
            subject: en4.core.subject.guid
          },params),
          method: 'POST',
          onRequest: function() {
            editComposeInstance.getForm().getElementById('fieldset-buttons').setStyle('display', 'none');
            en4.core.loader.inject(editComposeInstance.getForm().getElementById('buttons-wrapper'));
          },
          onSuccess: function() {
            (function(){en4.activity.alignHtml()}).delay(100);
            $('activity-item-' + action_id).getElement('.compose-container').destroy();
          }
        }), {
          'force': true,
          'element': $('activity-item-' + action_id),
          'updateHtmlMode': 'comments'
        });
      }
    });
    this.editComposers[action_id] = editComposeInstance;
    if( editComposeInstance._supportsContentEditable() ) {
      editComposeInstance.setContent(editComposeInstance.elements.body.get('html').replace(/(\r\n?|\n)/ig, "<br>"));
    }
    document.store('editComposeInstanceActivity' + action_id, editComposeInstance);

    this.bindEditLink(action_id);
    $('activity-item-' + action_id).getElement('.feed-edit-content-cancel').addEvent('click', function(event) {
      var el = $(event.target);
      var parent = el.getParent('.activity-item');
      parent.getElement('.feed_item_body_edit_content').setStyle('display', 'none');
      parent.getElement('.feed_item_body_content').setStyle('display', 'block');
    });
  },
  bindEditLink : function (action_id) {
    if (!$('activity-item-' + action_id).getElement('.feed_item_option_edit')) {
      return;
    }
    var self = this;
    $('activity-item-' + action_id).getElement('.feed_item_option_edit').addEvent('click', function(event) {
      var el = $(event.target);
      var parent = el.getParent('.activity-item');
      parent.getElement('.feed_item_body_content').setStyle('display', 'none');
      parent.getElement('.feed_item_body_edit_content').setStyle('display', 'block');
      self.editComposers[action_id].focus();
      self.editComposers[action_id].placeCaretAtEnd();
    });
    if(self.postLength > 0){
      self.editComposers[action_id].elements.textCounter = new Element('div', {
        'class' : 'compose-content-counter',
        'styles' : {
          'display' : 'none'
        },
      }).inject(self.editComposers[action_id].elements.textarea, 'after');
      
      $('activity-item-' + action_id).getElement('.compose-content').addEvent('input', function(e) {
          self.checkPostLength(e,self,action_id);
      });
    }
  },
  checkPostLength: function(e,self,action_id) {
    var content = self.editComposers[action_id].getContent();
    content = content.replace(/&nbsp;/g, ' ');
    content = content.replace(/&amp;/g, '&'); 
    content = content.replace(/&lt;/g, '<'); 
    content = content.replace(/&gt;/g, '>');
    if(self.postLength < content.length){
      content = content.substr(0,self.postLength);
      self.editComposers[action_id].setContent(content);
      self.editComposers[action_id].setCaretPos(content.length);
    }
    self.editComposers[action_id].elements.textCounter.setStyle("display","block");
    self.editComposers[action_id].elements.textCounter.innerHTML = (self.postLength-content.length);
    return;
  },
};

NotificationUpdateHandler = new Class({

  Implements : [Events, Options],
  options : {
      debug : false,
      baseUrl : '/',
      identity : false,
      delay : 5000,
      minDelay : 5000,
      maxDelay : 600000,
      delayFactor : 1.5,
      admin : false,
      idleTimeout : 600000,
      last_id : 0,
      subject_guid : null
    },

  state : true,

  activestate : 1,

  fresh : true,

  lastEventTime : false,

  title: document.title,

  initialize : function(options) {
    this.setOptions(options);
    this.options.minDelay = this.options.delay;
  },

  start : function() {
    this.state = true;

    // Do idle checking
    this.idleWatcher = new IdleWatcher(this, {timeout : this.options.idleTimeout});
    this.idleWatcher.register();
    this.addEvents({
      'onStateActive' : function() {
        this.activestate = 1;
        this.state= true;
      }.bind(this),
      'onStateIdle' : function() {
        this.activestate = 0;
        this.state = false;
      }.bind(this)
    });

    this.loop();
  },

  stop : function() {
    this.state = false;
  },

  updateNotifications : function() {
    if( en4.core.request.isRequestActive() ) return;
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/notifications/update',
      data : {
        format : 'json'
      },
      onSuccess : this.showNotifications.bind(this)
    }));
  },

  showNotifications : function(responseJSON){
    if (responseJSON.notificationCount>0){
      this.options.delay = this.options.minDelay;
        if (!$('updates_toggle')) {
            return;
        }
      //$('updates_toggle').set('html', responseJSON.text).addClass('new_updates');
      if($('update_count'))
      $('update_count').set('html', responseJSON.notificationCount).addClass('minimenu_update_count_bubble_active');
    } else {
      this.options.delay = Math.min(this.options.maxDelay, this.options.delayFactor * this.options.delay);
    }
  },

  loop : function() {
    if( !this.state) {
      this.loop.delay(this.options.delay, this);
      return;
    }

    try {
      this.updateNotifications().addEvent('complete', function() {
        this.loop.delay(this.options.delay, this);
      }.bind(this));
    } catch( e ) {
      this.loop.delay(this.options.delay, this);
      this._log(e);
    }
  },

  // Utility

  _log : function(object) {
    if( !this.options.debug ) {
      return;
    }

    // Firefox is dumb and causes problems sometimes with console
    try {
      if( typeof(console) && $type(console) ) {
        console.log(object);
      }
    } catch( e ) {
      // Silence
    }
  }
});

//(function(){

  en4.activity.compose = {

    composers : {},

    register : function(object){
      name = object.getName();
      this.composers[name] = object;
    },

    deactivate : function(){
      for( var x in this.composers ){
        this.composers[x].deactivate();
      }
      return this;
    }

  };


  en4.activity.compose.icompose = new Class({

    Implements: [Events, Options],

    name : false,

    element : false,

    options : {},

    initialize : function(element, options){
      this.element = $(element);
      this.setOptions(options);
    },

    getName : function(){
      return this.name;
    },

    activate : function(){
      en4.activity.compose.deactivate();
    },

    deactivate : function(){

    }
  });

//})();

ActivityUpdateHandler = new Class({

  Implements : [Events, Options],
  options : {
      debug : true,
      baseUrl : '/',
      identity : false,
      delay : 5000,
      admin : false,
      idleTimeout : 600000,
      last_id : 0,
      next_id : null,
      subject_guid : null,
      showImmediately : false
    },

  state : true,

  activestate : 1,

  fresh : true,

  lastEventTime : false,

  title: document.title,

  //loopId : false,

  initialize : function(options) {
    this.setOptions(options);
  },

  start : function() {
    this.state = true;

    // Do idle checking
    this.idleWatcher = new IdleWatcher(this, {timeout : this.options.idleTimeout});
    this.idleWatcher.register();
    this.addEvents({
      'onStateActive' : function() {
        this._log('activity loop onStateActive');
        this.activestate = 1;
        this.state = true;
      }.bind(this),
      'onStateIdle' : function() {
        this._log('activity loop onStateIdle');
        this.activestate = 0;
        this.state = false;
      }.bind(this)
    });
    this.loop();
    //this.loopId = this.loop.periodical(this.options.delay, this);
  },

  stop : function() {
    this.state = false;
  },

  checkFeedUpdate : function(action_id, subject_guid){
    if( en4.core.request.isRequestActive() ) return;

    function getAllElementsWithAttribute(attribute) {
      var matchingElements = [];
      var values = [];
      var allElements = document.getElementsByTagName('*');
      for (var i = 0; i < allElements.length; i++) {
        if (allElements[i].getAttribute(attribute)) {
          // Element exists with attribute. Add to array.
          matchingElements.push(allElements[i]);
          values.push(allElements[i].getAttribute(attribute));
          }
        }
      return values;
    }
    var list = getAllElementsWithAttribute('data-activity-feed-item');
    this.options.last_id = Math.max.apply( Math, list );
    min_id = parseInt(this.options.last_id) + 1;

    var req = new Request.HTML({
      url : en4.core.baseUrl + 'widget/index/name/activity.feed',
      data : {
        'format' : 'html',
        'minid' : min_id,
        'feedOnly' : true,
        'nolayout' : true,
        'subject' : this.options.subject_guid,
        'getUpdate' : true
      },
    });
    en4.core.request.send(req, {
      'element' : $('activity-feed'),
      'updateHtmlMode' : 'prepend'
      }
    );

    req.addEvent('complete', function() {
      (function() {
        if( this.options.showImmediately && $('feed-update').getChildren().length > 0 ) {
          $('feed-update').setStyle('display', 'none');
          $('feed-update').empty();
            this.getFeedUpdate(this.options.next_id);
          }
          en4.activity.alignHtml();
        }).delay(50, this);
    }.bind(this));



   // Start LOCAL STORAGE STUFF
   if(localStorage) {
     var pageTitle = document.title;
     //@TODO Refill Locally Stored Activity Feed

     // For each activity-item, get the item ID number Data attribute and add it to an array
     var feed  = document.getElementById('activity-feed');
     // For every <li> in Feed, get the Feed Item Attribute and add it to an array
     var items = feed.getElementsByTagName("li");
     var itemObject = { };
     // Loop through each item in array to get the InnerHTML of each Activity Feed Item
     var c = 0;
     for (var i = 0; i < items.length; ++i) {
       if(items[i].getAttribute('data-activity-feed-item') != null){
         var itemId = items[i].getAttribute('data-activity-feed-item');
         itemObject[c] = {id: itemId, content : document.getElementById('activity-item-'+itemId).innerHTML };
         c++;
         }
       }
     // Serialize itemObject as JSON string
     var activityFeedJSON = JSON.stringify(itemObject);
     localStorage.setItem(pageTitle+'-activity-feed-widget', activityFeedJSON);
   }


   // Reconstruct JSON Object, Find Highest ID
   if(localStorage.getItem(pageTitle+'-activity-feed-widget')) {
     var storedFeedJSON = localStorage.getItem(pageTitle+'-activity-feed-widget');
     var storedObj = eval ("(" + storedFeedJSON + ")");

     //alert(storedObj[0].id); // Highest Feed ID
    // @TODO use this at min_id when fetching new Activity Feed Items
   }
   // END LOCAL STORAGE STUFF


   return req;
  },

  getFeedUpdate : function(last_id, force){
    if( !force && en4.core.request.isRequestActive() ) return;
    var min_id = parseInt(this.options.last_id) + 1;
    this.options.last_id = last_id;
    document.title = this.title;
    var req = new Request.HTML({
      url : en4.core.baseUrl + 'widget/index/name/activity.feed',
      data : {
        'format' : 'html',
        'minid' : min_id,
        'feedOnly' : true,
        'nolayout' : true,
        'getUpdate' : true,
        'subject' : this.options.subject_guid
      }
    });
    en4.core.request.send(req, {
      'element' : $('activity-feed'),
      'updateHtmlMode' : 'prepend',
      force: !!force
    });
    req.addEvent('complete', function() {
      (function() {
          en4.activity.alignHtml();
        }).delay(100, this);
    }.bind(this));
    return req;
  },

  loop : function() {
    this._log('activity update loop start');

    if( !this.state ) {
      this.loop.delay(this.options.delay, this);
      return;
    }

    try {
      this.checkFeedUpdate().addEvent('complete', function() {
        try {
          this._log('activity loop req complete');
          this.loop.delay(this.options.delay, this);
        } catch( e ) {
          this.loop.delay(this.options.delay, this);
          this._log(e);
        }
      }.bind(this));
    } catch( e ) {
      this.loop.delay(this.options.delay, this);
      this._log(e);
    }

    this._log('activity update loop stop');
  },

  // Utility
  _log : function(object) {
    if( !this.options.debug ) {
      return;
    }

    try {
      if( 'console' in window && typeof(console) && 'log' in console ) {
        console.log(object);
      }
    } catch( e ) {
      // Silence
    }
  }
});



})(); // END NAMESPACE
