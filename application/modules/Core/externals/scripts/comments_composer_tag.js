
/* $Id: comments_composer_tag.js 9572 2011-12-27 23:41:06Z john $ */



(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;

CommentsComposer.Plugin.Tag = new Class({

  Extends : CommentsComposer.Plugin.Interface,

  name : 'tag',

  options : {
    'enabled' : false,
    requestOptions : {},
    'suggestOptions' : {
      'minLength': 0,
      'maxChoices' : 9,
      'delay' : 250,
      'selectMode': 'pick',
      'multiple': false,
      'filterSubset' : true,
      'tokenFormat' : 'object',
      'tokenValueKey' : 'label',
      'injectChoice': $empty,
      'onPush' : $empty,
      'prefetchOnInit' : true,
      'alwaysOpen' : false,
      'ignoreKeys' : true
    }
  },

  initialize : function(options) {
    this.params = new Hash(this.params);
    this.parent(options);
  },

  suggest : false,

  attach : function() {
    if( !this.options.enabled ) return;
    this.parent();

    var self=this;
    this.getComposer().addEvent((Browser.Engine.trident || Browser.Engine.webkit) ? 'editorKeyDown':'editorKeyPress',
      function (event) {
        if (self.suggest && self.suggest.visible && event) {
          self.suggest.onCommand(event);
          if (self.suggest.stopKeysEvent){
            event.stop();
            return;
          }
        }
        self.monitor.bind(self)(event);
      }
    );

    this.getComposer().addEvent('editorKeyPress', this.monitor.bind(this));
    this.getComposer().addEvent('editorClick', this.monitor.bind(this));
    this.getComposer().addEvent('editorSubmit', this.submit.bind(this));
    this.getComposer().addEvent('editorExtractTag', this.extractTag.bind(this));
    return this;
  },

  detach : function() {
    if( !this.options.enabled ) return;
    this.parent();
    this.getComposer().removeEvent('editorKeyPress', this.monitor.bind(this));
    this.getComposer().removeEvent('editorClick', this.monitor.bind(this));
    this.getComposer().removeEvent('editorSubmit', this.submit.bind(this));
    this.getComposer().removeEvent('editorExtractTag', this.extractTag.bind(this));
    if( this.interval ) $clear(this.interval);
    return this;
  },

  activate: $empty,

  deactivate : $empty,

  poll : function() {
  },

  monitor : function(e) {
    // seems like we have to do this stupid delay or otherwise the last key
    // doesn't get in the content
    (function () {

      var content = this.getComposer().getContent();
      content = content.replace(/&nbsp;/g, ' ');
      if (!content) {
        return;
      }
      this.caretPosition = this.getComposer().getCaretPos();
      content = content.substring(0, this.caretPosition);
      var currentIndex = content.lastIndexOf('@');
      if (currentIndex === -1) {
        return this.endSuggest();
      }

      var value = content.replace(/\n/gi, ' ');
      if (currentIndex > 0 && value.substr((currentIndex - 1), 1) !== ' ') {
        return this.endSuggest();
      }

      var segment = content.substring(currentIndex + 1, this.caretPosition).trim();
      // Check next space
      var spaceIndex = segment.indexOf(' ');
      if (spaceIndex > -1) {
        segment = segment.substring(0, spaceIndex);
      }

      if( segment == '' ) {
        this.endSuggest();
        return;
      }
      this.doSuggest(segment);

    }).delay(5, this);
  },

  doSuggest : function(text) {
    this.currentText = text;
    var suggest = this.getSuggest();
    var input = this.getHiddenInput();
    input.set('value', text);
    input.value = text;
    suggest.prefetch();
  },

  endSuggest : function() {
    this.currentText = '';
    this.positions = {};
    if( this.suggest ) {
      this.getSuggest().destroy();
      delete this.suggest;
    }
  },

  getHiddenInput : function() {
    if( !this.hiddenInput ) {
      this.hiddenInput = new Element('input', {
        'type' : 'text',
        'styles' : {
          'display' : 'none'
        }
      }).inject(document.body);
    }
    return this.hiddenInput;
  },

  getSuggest : function() {
    if( !this.suggest ) {
      var width = this.getComposer().elements.body.getSize().x;
      this.choices = new Element('ul', {
				'class':'tag-autosuggest',
        'styles' : {
          'width' : (width-2 )+ 'px'
        }
      }).inject(this.getComposer().elements.body, 'after');

      var self = this;
      var options = $merge(this.options.suggestOptions, {
        'customChoices' : this.choices,
        'injectChoice' : function(token) {
          var choice = new Element('li', {
            'class': 'autocompleter-choices',
            //'value': token.id,
            'html': token.photo || '',
            'id': token.guid
          });
          new Element('div', {
            'html' : this.markQueryValue(token.label),
            'class' : 'autocompleter-choice'
          }).inject(choice);
          new Element('input', {
            'type' : 'hidden',
            'value' : JSON.encode(token)
          }).inject(choice);
          this.addChoiceEvents(choice).inject(this.choices);
          choice.store('autocompleteChoice', token);
        },
        'onChoiceSelect' : function(choice) {
          var data = JSON.decode(choice.getElement('input').value);
          var replaceString = '@' + self.currentText;
          var newString = '&nbsp;<span class="feed_composer_tag" rel="'+data.guid+'" rev="'+data.label+'" >'+data.label+'</span>&nbsp;';
          var content = self.getComposer().getContent();
          content = content.replace(replaceString, newString);

          var text = self.getComposer().hiddenTagContent.get('value') + '@userTags:' + data.guid + ':'
            + data.label + '@';

          self.getComposer().hiddenTagContent.set('value', text);
          self.getComposer().setContent(content);
          self.getComposer().setCaretPos(self.caretPosition + 2 - replaceString.length + data.label.length);
        },
        'emptyChoices' : function() {
          this.fireEvent('onHide', [this.element, this.choices]);
        },
        'onCommand' : function(e) {
          if (e && e.key && !e.shift) {
            switch (e.key) {
              case 'enter':
                e.stop();
                if( !this.selected ) {
                  if( !this.options.customChoices ) {
                    // @todo support multiple
                    this.element.value = '';
                  }
                  return true;
                }
                if (this.selected && this.visible) {
                  this.stopKeysEvent = true;
                  this.choiceSelect(this.selected);
                  return !!(this.options.autoSubmit);
                }
                break;
              case 'up': case 'down':
                var value = this.element.value;
                if (!this.prefetch() && this.queryValue !== null) {
                  this.stopKeysEvent = true;
                  var up = (e.key == 'up');
                  if (this.selected) this.selected.removeClass('autocompleter-selected');
                  if(!(this.selected)[
                    ((up) ? 'getPrevious' : 'getNext')
                    ](this.options.choicesMatch)){
                    this.selected=null;
                  }

                  this.choiceOver(
                    (this.selected || this.choices)[
                    (this.selected) ? ((up) ? 'getPrevious' : 'getNext') : ((up) ? 'getLast' : 'getFirst')
                    ](this.options.choicesMatch), true);
                  this.element.value = value;
                }
                return false;
              case 'esc':
                this.stopKeysEvent = true;
                this.hideChoices(true);
                if( !this.options.customChoices ) this.element.value = '';
                break;
              case 'tab':
                this.stopKeysEvent = true;
                if (this.selected && this.visible) {
                  this.choiceSelect(this.selected);
                  return !!(this.options.autoSubmit);
                } else {
                  this.hideChoices(true);
                  if( !this.options.customChoices ) this.element.value = '';
                  break;
                }
              default :
                this.stopKeysEvent = false;
            }
        }}
      });

      if( this.options.suggestProto == 'local' ) {
        this.suggest = new Autocompleter.Local(this.getHiddenInput(), this.options.suggestParam, options);
      } else if( this.options.suggestProto == 'request.json' ) {
        this.suggest = new Autocompleter.Request.JSON(this.getHiddenInput(), this.options.suggestOptions.url, options);
      }
    }

    return this.suggest;
  },

  extractTag: function () {
    var tagText = this.getComposer().tagText;
    var tagRegex = /@userTags:\w+:[^\@]+@/gim;
    var tagContent = this.getComposer().hiddenTagContent.get('value');
    var tagMatch = tagContent.match(tagRegex);
    if (tagMatch === null) {
      return;
    }

    var tempText = '';
    var hiddenTags = '';
    var tagRel = new Array();
    var tagLabel = new Array();
    var tagsArray = tagContent.split(tagRegex);
    var matchCount = tagMatch.length;
    for (var i = 0; i < matchCount; ++i) {
      tagRel[i] = tagMatch[i].replace(/@userTags:/, '').replace(/:(.*)@/, '').trim();
      tagLabel[i] = tagMatch[i].replace(/^@userTags+:\w+:/, '').replace(/\@$/, '').trim();
      var tagIndex = tagText.indexOf(tagLabel[i]);
      if (tagIndex > -1) {
        tagsArray[i] = tagText.substr(0, tagIndex);
        tagText = tagText.substr(tagIndex + tagLabel[i].length);
      } else {
        tagMatch[i] = tagLabel[i] = '';
      }

      var subText = tagsArray[i] || '';
      hiddenTags += tagMatch[i];
      if (tagLabel[i] && tagRel[i]) {
        // extract user tags
        tempText += subText + '<span class="feed_composer_tag" rel="' + tagRel[i] + '" rev="' + tagLabel[i] + '" >' +
          tagLabel[i] + '</span>';
      }
    }

    var tempString = '';
    if (i > 0) {
      tempString = tagText.replace(tagLabel[i - 1], '');
      tempText += tempString;
    }

    this.getComposer().tagText = tempText;
    this.getComposer().hiddenTags = hiddenTags;
  },

  submit:function () {
    this.makeFormInputs({
      tag: this.getTagsFromComposer().toQueryString()
    });
  },
  getTagsFromComposer: function () {
    this.filterTagsFromComposer();
    var composerTags = new Hash();
    var body = this.getComposer().elements.body;
    body.getElements('.feed_composer_tag').each(function (tag) {
      composerTags[tag.get('rel')] = tag.get('text');
    });
    return composerTags;
  },
  filterTagsFromComposer: function () {
    var body = this.getComposer().elements.body;
    body.getElements('.feed_composer_tag').each(function (tag) {
      if (tag.get('text') != tag.get('rev')){
        tag.removeClass('feed_composer_tag');
      }
    });
  },
  makeFormInputs : function(data) {
    $H(data).each(function(value, key) {
      this.setFormInputValue(key, value);
    }.bind(this));
  },
  // make tag hidden input and set value into composer form
  setFormInputValue : function(key, value) {
    var elName = 'aafComposerForm' + key.capitalize();
    var composerObj=this.getComposer();
    if(composerObj.elements.has(elName))
      composerObj.elements.get(elName).destroy();
      composerObj.elements.set(elName, new Element('input', {
        'type' : 'hidden',
        'name' : 'composer[' + key + ']',
        'value' : value || ''
      }).inject(composerObj.getInputArea()));
    composerObj.elements.get(elName).value = value;
  }

});

})(); // END NAMESPACE
