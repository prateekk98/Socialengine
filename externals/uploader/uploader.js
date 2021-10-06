
Uploader = new Class ({
  uploadedFileArray : [],
  Implements : [Options],

  options : {
    uploadLinkClass : '',
    uploadLinkTitle : '',
    uploadLinkDesc : '',
  },

  initialize : function (uploadElement, options) {
    this.uploadElement = $(uploadElement);
    this.setOptions(options);
    this.attachUploadEvent();
  },

  attachUploadEvent : function () {
    var self = this;
    var FileSize;
    var valid = true;
    var fileStatusElement = new Element('div', {
      'html' : self.options.uploadLinkDesc
    }).inject($('file-status'), 'top');

    new Element('a', {
       id: "upload_file_link",
       class: self.options.uploadLinkClass,
       html: self.options.uploadLinkTitle,
      'events' : {
        'click' : function() {
          self.uploadElement.click();
        }
      }
    }).inject(fileStatusElement, 'after');
    this.uploadElement.addEvent('change', function () {
      var files = this.files;
      var total = files.length;
      var iteration = 0;
      for(var i = 0; i < files.length; i++) {
        FileSize = this.files[i].size / 1024 / 1024; // in MB
        if(FileSize > post_max_size) {
          valid = false;
          continue;
        }
        iteration++;
        self.uploadFile(self.uploadElement, this.files[i], iteration, total);
      }
      //if(!valid) alert("The size of the file exceeds the limits set on the server.");
    });
    this.uploadElement.addEvent('click', function () {
      this.value = '';
    });
    $('remove_all_files').addEvent('click', function () {
      $$('.file-remove').each(function (el) {
        self.removeFile(el);
      });
      $$('.file-error').each(function (el) {
        el.destroy();
      });
      $('remove_all_files').setStyle('display', 'none');
      $('uploaded-file-list').setStyle('display', 'none');
    });
  },

  uploadFile: function (obj, file, iteration, total) {
    var self = this;

    //Upload file size check
    var FileSize = file.size / 1024 / 1024; // in MB
    if(FileSize > post_max_size) {
      return;
    }

    if (this.alreadyUploaded(file)) {
      return self.processUploadError(file['name'] + ' already added.');
    }

    var url = obj.get('data-url');
    if (url === '') {
      return;
    }

    var xhr = new XMLHttpRequest();
    var fd = new FormData();
    xhr.open("POST", url, true);
    $('files-status-overall').setStyle('display', 'block');
    xhr.upload.addEventListener('progress', function (e) {
      var per = (total <= 1 ? e.loaded/e.total : iteration/total).toFixed(2) * 100;
      var overAllFileProgress = -400 + ((per) * 2.5);
      $$('div#files-status-overall img')[0].setStyle('background-position', overAllFileProgress + 'px 0px');
      $$('div#files-status-overall span')[0].set('html', per + '%');
    }, false);

    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        try {
          // Show progress
          var res = JSON.parse(xhr.responseText);
        } catch (err) {
          self.processUploadError('An error occurred.');
          return false;
        }

        if (res['error'] !== undefined) {
          self.processUploadError('FAILED (' + res['name'] + ') : ' + res['error']);
          return false;
        }
        self.processResponseData(res);
        if (typeof self.processCustomResponse !== "undefined") {
          self.processCustomResponse(res);
        }

        if (iteration === total) {
          self.showButton();
        }
      }
    };
    fd.append('ajax-upload', 'true');
    fd.append(obj.get('name'), file);
    xhr.send(fd);
  },

  alreadyUploaded: function (file) {
    if (this.uploadedFileArray.length === 0) {
      return false;
    }
    return false;
    return this.uploadedFileArray.every(function (uploadedFile) {
      return uploadedFile === file.name;
    });
  },

  processResponseData: function(response) {
    var self = this;
    var fancyUploadFileds = $('fancyuploadfileids');
    var currentValue = fancyUploadFileds.get('value');
    currentValue += response['id'] + ' ';

    fancyUploadFileds.set('value', currentValue);
    this.uploadedFileArray[response['id']] = response['fileName'];
    var uploadedFileList = document.getElementById("uploaded-file-list");
    var uploadedFile = new Element('li', {
      'class': 'file file-success',
    }).inject(uploadedFileList);

    var fileLink = new Element('a', {
       class: "file-remove",
       href: 'javascript:void(0);',
       title: 'Click to remove this entry.',
       'data-file_id': response['id'],
       html : '<b>Remove</b>',
      'events' : {
        'click' : function() {
          self.removeFile(this);
        }
      }
    }).inject(uploadedFile);

    new Element('span', {
      class: 'file-name',
      html: response['fileName']
    }).inject(fileLink, 'after');

    // If hidden show upload list
    if (uploadedFile.offsetParent === null) {
      uploadedFileList.style.display = "block";
    }
    if ($('remove_all_files').offsetParent === null) {
      $('remove_all_files').setStyle('display', 'inline');
    }
  },

  processUploadError: function(errorMessage) {
    var uploadedFileList = document.getElementById("uploaded-file-list");
    var uploadedFile = new Element('li', {
      'class': 'file file-error',
      'html' : '<span class="validation-error" title="Click to remove this entry.">' + errorMessage + '</span>',
      events: {
        click: function() {
          this.destroy();
          if ($$('ul#uploaded-file-list li').length === 0) {
            $('submit-wrapper').setStyle('display', 'none');
            $('remove_all_files').setStyle('display', 'none');
            $('uploaded-file-list').setStyle('display', 'none');
          }
        }
      }
    }).inject(uploadedFileList, 'top');
    // If hidden show upload list
    if (uploadedFile.offsetParent === null) {
      uploadedFileList.style.display = "block";
    }
    $('files-status-overall').setStyle('display', 'none');
    return false;
  },

  showButton: function () {
    document.getElementById("submit-wrapper").style.display = "block";
    $('files-status-overall').setStyle('display', 'none');
  },

  removeFile: function (el) {
    var file_id = el.get('data-file_id');
    delete this.uploadedFileArray[file_id];
    var fancyUploadFileds = $('fancyuploadfileids');
    var currentValue = fancyUploadFileds.get('value');
    currentValue = currentValue.replace(file_id + ' ', '');

    if (typeof deleteFile !== "undefined") {
      deleteFile(el);
    }
    fancyUploadFileds.set('value', currentValue);
    el.parentElement.destroy();
    if ($$('ul#uploaded-file-list li').length === 0) {
      $('remove_all_files').setStyle('display', 'none');
      $('uploaded-file-list').setStyle('display', 'none');
      document.getElementById("submit-wrapper").style.display = "none";
    }
  },
});
