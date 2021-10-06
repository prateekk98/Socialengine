
/* $Id: composer.js 10019 2013-03-27 01:52:21Z john $ */

(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;

CommentsComposer = new Class({
  Implements : [Events, Options],
  elements : {},
  plugins : {},
  options : {
    lang : {},
    overText : true,
    hashtagEnabled : false,
    allowEmptyWithoutAttachment : false,
    allowEmptyWithAttachment : true,
    hideSubmitOnBlur : true,
    submitElement : false,
    useContentEditable : true,
    submitCallBack: null,
  },

  isMobile : {
    Android: navigator.userAgent.match(/Android/i),
    BlackBerry: navigator.userAgent.match(/BlackBerry/i),
    iOS: navigator.userAgent.match(/iPhone|iPad|iPod/i),
    Opera: navigator.userAgent.match(/Opera Mini/i),
    Windows: navigator.userAgent.match(/IEMobile/i),
  },

  hashRegex: /\B(#[^\s[!\"\#$%&'()*+,\-.\/\\:;<=>?@\[\]\^`{|}~]+)/g,

  initialize : function(element, options) {
    this.setOptions(options);
    this.elements = new Hash(this.elements);
    this.plugins = new Hash(this.plugins);

    this.elements.textarea = $(element);
    this.elements.textarea.store('Composer');

    this.attach();

    this.pluginReady = false;

    this.getForm().addEvent('submit', function(e) {
      this.fireEvent('editorSubmit');
      if( this.pluginReady ) {
        if( !this.options.allowEmptyWithAttachment && this.getContent() == '' ) {
          e.stop();
          return;
        }
      } else {
        if( !this.options.allowEmptyWithoutAttachment && this.getContent() == '' ) {
          e.stop();
          return;
        }
      }
      this.saveContent();
      if (this.options.submitCallBack) {
        this.options.submitCallBack(this.getForm().toQueryString().parseQueryString());
        e.stop();
      }
    }.bind(this));
  },

  getInputArea : function() {
    if( !$type(this.elements.inputarea) ) {
      var form = this.elements.textarea.getParent('form');
      this.elements.inputarea = new Element('div', {
        'styles' : {
          'display' : 'none'
        }
      }).inject(form);
    }
    return this.elements.inputarea;
  },

  getForm : function() {
    return this.elements.textarea.getParent('form');
  },

  // Editor

  attach : function() {
    var size = this.elements.textarea.getSize();

    // Modify textarea
    this.elements.textarea.addClass('compose-textarea').setStyle('display', 'none');

    // Create container
    this.elements.container = new Element('div', {
      'id' : 'comment-compose-container',
      'class' : 'comment-compose-container',
      'styles' : {
      }
    });
    this.elements.container.wraps(this.elements.textarea);
    // Create body
    var supportsContentEditable = this._supportsContentEditable();
    if( supportsContentEditable ) {
      this.elements.body = new Element('div', {
        'class' : 'compose-content',
        'styles' : {
          'display' : 'block'
        },
        'events' : {
          'keypress' : function(event) {
            if( event.key == 'a' && event.control ) {
              // FF only
              if( Browser.Engine.gecko ) {
                fix_gecko_select_all_contenteditable_bug(this, event);
              }
            }
          }
        }
      }).inject(this.elements.textarea, 'before');
    } else {
      this.elements.body = this.elements.textarea;
    }

    this.prepareTag();
    if( supportsContentEditable ) {
      $(this.elements.body);
      this.elements.body.contentEditable = true;
      this.elements.body.designMode = 'On';

      ['MouseUp', 'MouseDown', 'ContextMenu', 'Click', 'Dblclick', 'KeyPress', 'KeyUp', 'KeyDown'].each(function(eventName) {
        var method = (this['editor' + eventName] || function(){}).bind(this);
        this.elements.body.addEvent(eventName.toLowerCase(), method);
      }.bind(this));

      this.setContent(this.elements.textarea.value);

      this.selection = new CommentsComposer.Selection(this.elements.body);
    } else {
      this.elements.textarea.setStyle('display', '');
    }

    if( this.options.overText && supportsContentEditable ) {
      new CommentsComposer.OverText(this.elements.body, $merge({
        textOverride : this._lang('Write a comment...'),
        poll : true,
        isPlainText : !supportsContentEditable,
        positionOptions: {
          position: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
          edge: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
          offset: {
            x: ( en4.orientation == 'rtl' ? -4 : 4 ),
            y: 2
          }
        }
      }, this.options.overTextOptions));
    }

    this.fireEvent('attach', this);
  },

  detach : function() {
    this.saveContent();
    this.textarea.setStyle('display', '').removeClass('compose-textarea').inject(this.container, 'before');
    this.container.dispose();
    this.fireEvent('detach', this);
    return this;
  },

  prepareTag: function () {
    this.hiddenTagContent = new Element('input', {
      'type': 'hidden'
    }).inject(this.elements.body, 'after');

    if (!this.isMobileorIE()) {
      this.addEvent('editorKeyDown', this.extractTag);
    }
    this.elements.body.addEvent('input', this.extractTag.bind(this));
    this.elements.container.style.position = 'relative';
  },

  extractTag: function () {
    var content = this.getContent();
    this.tagText = content;
    this.extractHashTag();
    this.fireEvent('editorExtractTag');
    try {
      var pos = this.getCaretPos();
    } catch (e) {
      // Listen empty caret error.
    }

    if (!this._supportsContentEditable()) {
      return;
    }

    this.elements.body.innerHTML = this.tagText;
    this.setCaretPos(pos);
    this.hiddenTagContent.set('value', this.hiddenTags);
  },

  extractHashTag: function() {
    if (!this.options.hashtagEnabled) {
      return;
    }

    var content = this.tagText;
    content = content.replace(/\<span\>\<\/span\>/ig, '');
    var hashTags = content.split(this.hashRegex);
    if (hashTags.length === 0) {
      return;
    }

    var tagString = '';
    var updateString = true;
    for (var i = 0; i < hashTags.length; i++) {
      var subString = hashTags[i] || '';
      if (subString.indexOf('#') === 0 && updateString) {
        // extract hashTags
        tagString += this.getTagString(subString);
        updateString = false;
        continue;
      }
      updateString = true;
      tagString += subString;
    }

    this.tagText = tagString;
  },

  getTagString: function (str) {
    return '<span class="feed_composer_hashtag">' + str + '</span>';
  },

  getCaretPos: function() {
    var element = this.elements.body;
    var caretPos = 0;
    if (typeof window.getSelection != "undefined") {
      var range = window.getSelection().getRangeAt(0);
      var caretRange = range.cloneRange();
      caretRange.selectNodeContents(element);
      caretRange.setEnd(range.endContainer, range.endOffset);
      caretPos = caretRange.toString().length;
    } else if (typeof document.selection != "undefined" && document.selection.type != "Control") {
      var range = document.selection.createRange();
      var caretRange = document.body.createTextRange();
      caretRange.expand(element);
      caretRange.setEndPoint("EndToEnd", range);
      caretPos = caretRange.text.length;
    }
    return caretPos;
  },

  setCaretPos: function(pos) {
    var index = 0, range = document.createRange(), body = this.elements.body;
    range.setStart(body, 0);
    range.collapse(true);
    var nodeArray = [body], node, isStart = false, stop = false;

    while (!stop && (node = nodeArray.pop())) {
      if (node.nodeType === 3) {
        var nextIndex = index + node.length;
        if (!isStart && pos >= index && pos <= nextIndex) {
          range.setStart(node, pos - index);
          isStart = true;
        } else if (isStart && pos >= index && pos <= nextIndex) {
          range.setEnd(node, pos - index);
          stop = true;
        }
        index = nextIndex;
      } else {
        var i = node.childNodes.length;
        while (i--) {
          nodeArray.push(node.childNodes[i]);
        }
      }
    }
    var selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(range);
  },

  focus: function(){
    // needs the delay to get focus working
    (function(){
      this.elements.body.focus();
      this.fireEvent('focus', this);
    }).bind(this).delay(10);
    return this;
  },

  reset : function(){
    this.setContent('');
    this.deactivate();
  },

  // Content
  getContent: function(){
    if( this._supportsContentEditable() ) {
      return this.cleanup(this.elements.body.get('html'));
    } else {
      return this.cleanup(this.elements.body.get('value'));
    }
  },

  setContent: function(newContent) {
    if( this._supportsContentEditable() ) {
      if( !newContent.trim() && !Browser.Engine.trident ) newContent = '<br />';
      this.elements.body.set('html', newContent.replace(/&nbsp;/g, ' '));
    } else {
      this.elements.body.set('value', newContent);
    }
    try {
      this.extractTag();
    } catch (e) {
      //Handle caret error.
    }
    return this;
  },

  saveContent: function(){
    if( this._supportsContentEditable() ) {
      this.elements.textarea.set('value', this.getContent().trim().replace(/&nbsp;/g, ' '));
    }
    return this;
  },

  cleanup : function(html) {
    // @todo
    return html
      .replace(/<(br|p|div)[^<>]*?>/ig, "\r\n")
      .replace(/<\/?span[^>]*>/g, '')
      .replace(/<[^<>]+?>/ig, '')
      .replace(/(\r\n){2,}/ig, "\n");
  },

  // Check IE
  isMobileorIE: function() {
    var isIE = window.navigator.userAgent.indexOf("MSIE ") > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./);
    return (this.isMobile.Android || this.isMobile.BlackBerry || this.isMobile.iOS || this.isMobile.Opera || this.isMobile.Windows || isIE);
  },

  // Plugins
  addPlugin : function(plugin) {
    var key = plugin.getName();
    this.plugins.set(key, plugin);
    plugin.setComposer(this);
    return this;
  },

  addPlugins : function(plugins) {
    plugins.each(function(plugin) {
      this.addPlugin(plugin);
    }.bind(this));
  },

  getPlugin : function(name) {
    return this.plugins.get(name);
  },

  activate : function(name) {
    this.deactivate();
    this.getMenu().setStyle();
    this.plugins.get(name).activate();
  },

  deactivate : function() {
    this.plugins.each(function(plugin) {
      plugin.deactivate();
    });
    this.getTray().empty();
  },

  signalPluginReady : function(state) {
    this.pluginReady = state;
  },

  hasActivePlugin : function() {
    var active = false;
    this.plugins.each(function(plugin) {
      active = active || plugin.active;
    });
    return active;
  },

  // Key events
  editorMouseUp: function(e){
    this.fireEvent('editorMouseUp', e);
  },

  editorMouseDown: function(e){
    this.fireEvent('editorMouseDown', e);
  },

  editorContextMenu: function(e){
    this.fireEvent('editorContextMenu', e);
  },

  editorClick: function(e){
    // make images selectable and draggable in Safari
    if (Browser.Engine.webkit){
      var el = e.target;
      if (el.get('tag') == 'img'){
        this.selection.selectNode(el);
      }
    }

    this.fireEvent('editorClick', e);
  },

  editorDoubleClick: function(e){
    this.fireEvent('editorDoubleClick', e);
  },

  editorKeyPress: function(e){
    this.keyListener(e);
    this.fireEvent('editorKeyPress', e);
  },

  editorKeyUp: function(e){
    this.fireEvent('editorKeyUp', e);
  },

  editorKeyDown: function(e) {
    if (this.isMobileorIE()) {
      this.fireEvent('editorKeyDown', e);
      return;
    }

    // Work for line break
    if (e.key == 'enter') {
      var content = this.getContent();
      content = content.replace(/&nbsp;/g, ' ');
      var textBeforeCaret = content.substr(0, this.getCaretPos());
      var textAfterCaret = content.substr(this.getCaretPos());
      var text = textBeforeCaret.endsWith('\n') || content.trim() == '' || textAfterCaret.startsWith('\n') ? '<br>' : '<br>\n';
      this.setContent(textBeforeCaret + text + textAfterCaret);
      this.setCaretPos(textBeforeCaret.length + 1);
      this.extractTag();
      e.preventDefault();
    }
    // Handle selection with ctrl+a
    if (e.event.ctrlKey) {
      if (e.event.keyCode == 65 || e.event.keyCode == 97) { // 'A' or 'a'
        return;
      }
    }
    // Handle deletion with ctrl+a
    if (e.event.keyCode == 8 || e.event.keyCode == 46) {
      return;
    }

    this.fireEvent('editorKeyDown', e);
  },

  keyListener: function(e){
  },

  _lang : function() {
    try {
      if( arguments.length < 1 ) {
        return '';
      }

      var string = arguments[0];
      if( $type(this.options.lang) && $type(this.options.lang[string]) ) {
        string = this.options.lang[string];
      }

      if( arguments.length <= 1 ) {
        return string;
      }

      var args = new Array();
      for( var i = 1, l = arguments.length; i < l; i++ ) {
        args.push(arguments[i]);
      }

      return string.vsprintf(args);
    } catch( e ) {
      alert(e);
    }
  },

  _supportsContentEditable : function() {
    if( 'useContentEditable' in this.options &&
        this.options.useContentEditable) {
      return true;
    } else {
      return false;
    }
  }
});

CommentsComposer.Selection = new Class({

  initialize: function(win){
    this.win = win;
  },

  getSelection: function(){
    return window.getSelection();
  },

  getRange: function(){
    var s = this.getSelection();

    if (!s) return null;

    try {
      return s.rangeCount > 0 ? s.getRangeAt(0) : (s.createRange ? s.createRange() : null);
    } catch(e) {
      // IE bug when used in frameset
      return document.body.createTextRange();
    }
  },

  setRange: function(range){
    if (range.select){
      $try(function(){
        range.select();
      });
    } else {
      var s = this.getSelection();
      if (s.addRange){
        s.removeAllRanges();
        s.addRange(range);
      }
    }
  },

  selectNode: function(node, collapse){
    var r = this.getRange();
    var s = this.getSelection();

    if (r.moveToElementText){
      $try(function(){
        r.moveToElementText(node);
        r.select();
      });
    } else if (s.addRange){
      collapse ? r.selectNodeContents(node) : r.selectNode(node);
      s.removeAllRanges();
      s.addRange(r);
    } else {
      s.setBaseAndExtent(node, 0, node, 1);
    }

    return node;
  },

  isCollapsed: function(){
    var r = this.getRange();
    if (r.item) return false;
    return r.boundingWidth == 0 || this.getSelection().isCollapsed;
  },

  collapse: function(toStart){
    var r = this.getRange();
    var s = this.getSelection();

    if (r.select){
      r.collapse(toStart);
      r.select();
    } else {
      toStart ? s.collapseToStart() : s.collapseToEnd();
    }
  },

  getContent: function(){
    var r = this.getRange();
    var body = new Element('body');

    if (this.isCollapsed()) return '';

    if (r.cloneContents){
      body.appendChild(r.cloneContents());
    } else if ($defined(r.item) || $defined(r.htmlText)){
      body.set('html', r.item ? r.item(0).outerHTML : r.htmlText);
    } else {
      body.set('html', r.toString());
    }

    var content = body.get('html');
    return content;
  },

  getText : function(){
    var r = this.getRange();
    var s = this.getSelection();

    return this.isCollapsed() ? '' : r.text || s.toString();
  },

  getNode: function(){
    var r = this.getRange();

    if (!Browser.Engine.trident){
      var el = null;

      if (r){
        el = r.commonAncestorContainer;

        // Handle selection a image or other control like element such as anchors
        if (!r.collapsed)
          if (r.startContainer == r.endContainer)
            if (r.startOffset - r.endOffset < 2)
              if (r.startContainer.hasChildNodes())
                el = r.startContainer.childNodes[r.startOffset];

        while ($type(el) != 'element') el = el.parentNode;
      }

      return $(el);
    }

    return $(r.item ? r.item(0) : r.parentElement());
  },

  insertContent: function(content){
    var r = this.getRange();

    if (r.insertNode){
      r.deleteContents();
      r.insertNode(r.createContextualFragment(content));
    } else {
      // Handle text and control range
      (r.pasteHTML) ? r.pasteHTML(content) : r.item(0).outerHTML = content;
    }
  }

});


CommentsComposer.OverText = new Class({

  Extends : OverText,

  test : function() {
    if( !$type(this.options.isPlainText) || !this.options.isPlainText ) {
      return !this.element.get('html').replace(/\s+/, '').replace(/<br.*?>/, '');
    } else {
      return this.parent();
    }
  },

  hide: function(suppressFocus, force){
    if (this.text && (this.text.isDisplayed() && (!this.element.get('disabled') || force))){
      this.text.hide();
      this.fireEvent('textHide', [this.text, this.element]);
      this.pollingPaused = true;
      try {
        this.element.fireEvent('focus');
        this.element.focus();
      } catch(e){} //IE barfs if you call focus on hidden elements
    }
    return this;
  }

})


CommentsComposer.Plugin = {};

CommentsComposer.Plugin.Interface = new Class({

  Implements : [Options, Events],

  name : 'interface',

  active : false,

  composer : false,

  options : {
    loadingImage : en4.core.staticBaseUrl + 'application/modules/Core/externals/images/loading.gif'
  },

  elements : {},

  persistentElements : ['activator', 'loadingImage'],

  params : {},

  initialize : function(options) {
    this.params = new Hash();
    this.elements = new Hash();
    this.reset();
    this.setOptions(options);
  },

  getName : function() {
    return this.name;
  },

  setComposer : function(composer) {
    this.composer = composer;
    this.attach();
    return this;
  },

  getComposer : function() {
    if( !this.composer ) throw "No composer defined";
    return this.composer;
  },

  attach : function() {
    this.reset();
  },

  detach : function() {
    this.reset();
    if( this.elements.activator ) {
      this.elements.activator.destroy();
      this.elements.erase('menu');
    }
  },

  reset : function() {
    this.elements.each(function(element, key) {
      if( $type(element) == 'element' && !this.persistentElements.contains(key) ) {
        element.destroy();
        this.elements.erase(key);
      }
    }.bind(this));
    this.params = new Hash();
    this.elements = new Hash();
  },

  activate : function() {
    if( this.active ) return;
    this.active = true;

    this.reset();
    var submitButtonEl = $(this.getComposer().options.submitElement);
    if( submitButtonEl ) {
      submitButtonEl.setStyle('display', 'none');
    }
  },

  deactivate : function() {
    if( !this.active ) return;
    this.active = false;

    this.reset();

    this.getComposer().signalPluginReady(false);
  },

  ready : function() {
    this.getComposer().signalPluginReady(true);

    var submitEl = $(this.getComposer().options.submitElement);
    if( submitEl ) {
      submitEl.setStyle('display', '');
    }
  },


  // Utility

  makeBody : function() {
    if( !this.elements.body ) {
      var tray = this.getComposer().getTray();
      this.elements.body = new Element('div', {
        'id' : 'compose-' + this.getName() + '-body',
        'class' : 'compose-body'
      }).inject(tray);
    }
  },

});

})(); // END NAMESPACE
