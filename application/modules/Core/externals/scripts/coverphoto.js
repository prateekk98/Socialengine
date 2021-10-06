var Coverphoto = new Class({
  Implements:[Options],
  options : {
    element : null,
    buttons : 'cover_photo_options',
    photoUrl : '',
    positionUrl : '',
    position : {
      top : 0,
      left : 0
    }
  },
  block : null,
  buttons : null,
  element : null,
  changeButton : null,
  saveButton : null,
  initialize : function (options) {
    if (options.block == null) {
      return;
    }
    this.setOptions(options);
    this.block = options.block;
    this.getCoverPhoto(0);
  },
  attach : function () {
    var self = this;
    if (!$(this.options.buttons)) {
      return;
    }
    this.element = self.block.getElement('.cover_photo');
    this.buttons = $(this.options.buttons);
    this.editButton = this.buttons.getElement('.edit-button');
    this.saveButton = this.buttons.getElement('.save-button');
    if (this.saveButton) {
      this.saveButton.getElement('.save-positions').addEvent('click', function () {
        self.reposition.save();
      });
      this.saveButton.getElement('.cancel').addEvent('click', function () {
        self.reposition.stop(1);
      });
    }
  },

  getCoverPhoto : function (canRepostion) {
    var self = this;
    new Request.HTML({
      'method' : 'get',
      'url' : self.options.photoUrl,
      'format' : 'html',
      'data' : {
        'subject' : en4.core.subject.guid,
      },
      'onComplete' : function (responseTree, responseElements, responseHTML, responseJavaScript) {
        if ( responseHTML.length <= 0) {
          return;
        }

        self.block.set('html', responseHTML);
        Smoothbox.bind(self.block);
        self.attach();
        if (canRepostion === 0) {
          return;
        }

        self.options.position = {
          top: 0,
          left : 0
        };
        Smoothbox.close();
        setTimeout(function () {
          self.reposition.start()
        }, '1000');
      }
    }).send();
  },

  reposition : {
    drag : null,
    active : false,
    start : function () {
      if (this.active) {
        return;
      }

      var self = document.coverPhoto;
      var cover = self.getCover();
      this.active = true;
      self.getButton().addClass('is_hidden');
      self.buttons.addClass('cover_photo_options');
      self.getButton('save').removeClass('is_hidden');
      self.block.getElement('.cover_tip_wrap').removeClass('is_hidden');
      if($$('.cover_photo_profile_options')) {
        $$('.cover_photo_profile_options').addClass('is_hidden');
				$$('.profile_cover_photo').addClass('remove_overlay');
      }
      cover.addClass('draggable');
      var content = cover.getParent();
      var verticalLimit = cover.offsetHeight.toInt() - content.offsetHeight.toInt();
      var horizontalLimit = cover.offsetWidth.toInt() - content.offsetWidth.toInt();
      var limit = {
        x:[0, 0],
        y:[0, 0]
      };
      limit.y = verticalLimit > 0 ? [-verticalLimit, 0] : limit.y;
      limit.x = horizontalLimit > 0 ? [-horizontalLimit, 0] : limit.x;

      this.drag = new Drag(cover, {
        limit : limit,
        onComplete:function (el) {
          self.options.position.top = el.getStyle('top').toInt();
          self.options.position.left = el.getStyle('left').toInt();
        }
      }).detach();
      this.drag.attach();
    },
    stop : function() {
      var self = document.coverPhoto;
      self.reposition.drag.detach();
      self.getButton('save').addClass('is_hidden');
      self.block.getElement('.cover_tip_wrap').addClass('is_hidden');
      self.buttons.removeClass('cover_photo_options');
      self.getButton().removeClass('is_hidden');
      if($$('.cover_photo_profile_options')) {
        $$('.cover_photo_profile_options').removeClass('is_hidden');
				$$('.profile_cover_photo').removeClass('remove_overlay');
      }
      self.getCover().removeClass('draggable');
      self.reposition.drag = null;
      self.reposition.active = false;
    },
    save : function () {
      if (!this.active) {
        return;
      }

      var self = document.coverPhoto;
      var current = this;
      new Request.JSON({
        method : 'get',
        url : self.options.positionUrl,
        format : 'json',
        data : {
          'position' : self.options.position
        },
        onSuccess:function () {
          if($$('.cover_photo_profile_options')) {
            $$('.cover_photo_profile_options').removeClass('is_hidden');
          }
          current.stop();
        }
      }).send();
    }
  },

  getCover : function (type) {
    if (type == 'block') {
      return this.block;
    }

    return this.element;
  },

  getButton : function (type) {
    if (type == 'save') {
      return this.saveButton;
    }

    return this.editButton;
  }
});

var Mainphoto = new Class({
  Implements : [Options],
  options : {
    element : null,
    buttons : 'mainphoto_options',
    photoUrl : '',
    showContent : {},
    position : {
      top : 0,
      left : 0
    }
  },
  block : null,
  buttons : null,
  element : null,
  changeButton : null,
  saveButton : null,
  initialize : function (options) {
    if (options.block == null) {
      return;
    }
    this.block = options.block;
    this.setOptions(options);
    var self = this;
    self.getMainPhoto();
  },
  attach : function () {
    var self = this;
    if(!$(this.options.buttons)){
      return;
    }
    this.element = self.block.getElement('.cover_photo');
  },

  getMainPhoto : function () {
    var self = this;
    new Request.HTML({
      method : 'get',
      url : self.options.photoUrl,
      data : {
        'format' : 'html',
        'subject' : en4.core.subject.guid,
      },
      onComplete:function (responseTree, responseElements, responseHTML, responseJavaScript) {
        if (responseHTML.length <= 0) {
          return;
        }
        self.block.set('html', responseHTML);
        Smoothbox.bind(self.block);
        self.attach();
        en4.core.runonce.trigger();
        Smoothbox.close();
      }
    }).send();
  }
});
