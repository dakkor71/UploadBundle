var uploadErrors = false;

if (typeof Backbone == "undefined") {
    self.alertCallback('Please add Backbone.js');
    uploadErrors = true;
}

if (typeof _ == "undefined") {
    self.alertCallback('Please add Underscore.js');
    uploadErrors = true;
}

var cordinates;
var uploadedImages = new Array();


if (uploadErrors == false) {}
var uploadView = Backbone.View.extend({

    requiredFormData : [],

    paths : {},

    events: {
        'sortElements': 'sortItems',
        'click .remove': 'removeItem'
    },

    initialize : function(options) {
        var self = this;

        self.paths = juiceUploadBundlePaths;

        this.$el.each(function() {
            self.initUploader(this);
        });

        self.initRemoteUpload();
        self.initSorting();
    },
    
    alertCallback: function (message) {
        alert(message);
    },

    initUploader: function(el) {
        var self = this;
        var $el = $(el).find(".file_upload");

        var $container = $(el);
        var containerData = $container.data();
        var options = {};

        self.prepareOptionsAndData(options , containerData , $container);

        if(!self.validData($container , options)) {
            return;
        }

        $.extend(options , {
            'url' :  self.paths.upload,
            'autoUpload' : true,
            'dataType': 'json',
            progressall: function (e, data) {
                self.progressHandler(e, data, $container);
            },
            'done' : function(e, data) {
                self.uploadSuccessHandler(data.result, $container);
            },
            'fail' : function(e, data) {
                //self.alertCallback('The error was: ' + errorType);
            }
        });

        $el.fileupload(options);
    },

    initRemoteUpload: function() {
        self = this;
        $('[data-remote="true"]').each(function() {
            $element = $(this);
            $(this).focusout(function() {
                $.ajax({
                    url: self.paths.remote,
                    data: {
                        fileUrl: $(this).val(),
                        kind: 'image'
                    },
                    type: 'post',
                    dataType: 'json',
                    success: function (data) {
                        self.uploadSuccessHandler(data, $('div[data-input-name*="\[' + $element.data('parent') + '\]\[file\]"]'));
                    }
                });
            });
        });
    },

    validData : function($container , options) {
        for(var i in this.requiredFormData) {
            if(!options.formData[this.requiredFormData[i]]) {
                self.alertCallback('Please set file ' + this.requiredFormData[i]);
                return false;
            }
        }

        if(!$container.data('callback') || !typeof(this[$container.data('callback')])) {
            self.alertCallback('defind callback');
            return false;
        }

        return true;
    },

    prepareOptionsAndData : function(options , elementData, $container) {

        var formData = {};

        for (var i in elementData) {

            var optionsMatch = i.match(/(options)(.+)/);
            var formMatch = i.match(/(form)(.+)/);

            if (optionsMatch) {
                this.setObjectValueFromMatch(options , optionsMatch , elementData[i]);
            } else if(formMatch) {
                this.setObjectValueFromMatch(formData , formMatch , elementData[i]);
            } else {
                $container.data(i , elementData[i]);
            }
        }

        options.formData = formData;

        return options;
    },

    setObjectValueFromMatch: function(object , match , value) {
        var name = match[2].toLowerCase();
        object[name] = value;
    },

    uploadSuccessHandler : function(data, $container) {
        var data = eval(data);

        if (data.status == 'error') {
            self.alertCallback(data.error);
            return;
        }

        if($container.data('crop')) {
            uploadedImages.push(data.params);
            this.cropHandler($container);
            return;
        }

        this[$container.data('callback')]($container, data.params);
    },

    cropHandler: function($container) {
        var self = this;
        //check if crop action is in progress
        if($('#cropPopup').size() || uploadedImages.length == 0) {
            return;
        }

        var currentPhoto = uploadedImages[0];
        var minSize = eval($container.data('minsize'));
        var size = currentPhoto.size;

        if(minSize['width'] == size['width'] && minSize['height'] == size['height']) {
            //dont crop if uploaded image is same size as minimal
            this[$container.data('callback')]($container,  currentPhoto);
            uploadedImages.shift();
            if(uploadedImages.length > 0) {
                self.cropHandler($container);
            }
            return;
        }

        if(minSize['width'] > size['width'] || minSize['height'] > size['height']) {
            self.alertCallback('Please upload image with min size ' + minSize['width'] + ' / ' + minSize['height']);
            uploadedImages.shift();
            this.cropHandler($container);
            return;
        }

        //add popup
        var popup = cropPopupTemplate;
        $container.append(popup({file : currentPhoto.path}));
        $('#cropPopup').show();

        //and modal on close
        $('.mask').click(function ($container) {
            self.removePopup($container);
            self.resetCordinates();
        });

        //init crop after image is loaded
        cropImage = new Image();
        cropImage.onload = function() {
            $('.popup .popup-body img').removeClass('hidden');
            self.cropInit($container.data('ratio') , minSize ,  size, this);
        };

        cropImage.src = currentPhoto.path;
    },

    removePopup : function($container) {
        $('#cropTarget').Jcrop("destoy");
        $('.mask').remove();
        $('#cropPopup').remove();

        uploadedImages.shift();
        if(uploadedImages.length > 0) {
            self.cropHandler($container);
        }

    },

    cropInit: function(ratio , minSize , size, image) {
        var self = this;

        $Jcrop = $('#cropTarget').Jcrop({
            onSelect : self.updateCordinates,
            onChange : self.updateCordinates,
            onRelease : self.resetCordinates,
            aspectRatio: ratio,
            trueSize : [size['width'] , size['height']],
            minSize : [minSize['width'] , minSize['height']],
            setSelect:   [ 0, 0, image.width, image.height ],
            boxWidth: 568,
            boxHeight: 568
        });

        $('.crop-button').click(function() {
            self.onCropButtonClickHandler();
        });
    },

    resetCordinates : function(c) {
        cordinates = {};
    },

    updateCordinates : function(c) {
        cordinates = c;
    },

    onCropButtonClickHandler: function() {
        if ($.isEmptyObject(cordinates)) {
            self.alertCallback('Please select crop area');
            return;
        }
        this.cropImage();
    },

    cropImage: function() {
        var self = this;
        $.ajax({
            url: self.paths.crop,
            type: 'POST',
            dataType: 'json',
            data: {
                cordinates: cordinates,
                file: uploadedImages[0].fileName
            },
            success: function(data) {
                var data = eval(data);
                if (data.status == 'success') {
                    $container = $('#cropPopup').parent();
                    self[$container.data('callback')]($container, uploadedImages[0]);
                    self.removePopup($container);
                } else {
                    self.alertCallback(data.error);
                }
            }
        });
    },

    /*
        Default functions
     */

    uploadInitHandler : function(instance, $container) {},

    progressHandler: function(e, data, $container) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $container.find('.juice_upload_progress_container .percent').html( progress + '%');

        if (progress == 100) {
            $container.find('.juice_upload_progress_container .percent').empty();
            $container.find('.juice_upload_progress_container span').hide();
        }
    },

    initSorting: function() {
        var self = this;
        $(".juice_upload_collection").sortable({
            update : function() {
                self.sortItems();
            }
        });
    },

    sortItems : function() {
        this.$el.find('.juice_upload_collection > div').each(function() {
            $(this).find('.position').attr('value' , $(this).index() + 1);
        });
    },

    removeItem : function(e) {
        if ($(e.currentTarget).closest('.juice_upload_collection').size()) {
            //remove from gallery
            $(e.currentTarget).closest('.juice_upload_gallery_item').remove();
        } else {
            //remove single file and image
            $(e.currentTarget).closest('.juice_upload_item').find('.file_container').empty();
        }
    },

    handleImage: function($container, params) {
        var html = imageTpl({
            'inputName' : $container.data('input-name'),
            'fileName' : params.fileName,
            'fileNameWithPath' : params.path + '?' + Math.random()
        });

        $container.find('.file_container').html(html);

        this.delegateEvents();
    },

    handleFile: function($container, params) {
        var html = fileTpl({
            'inputName' : $container.data('input-name'),
            'fileName' : params.fileName
        });

        console.log(123);

        $container.find('.file_container').html(html);

        this.delegateEvents();
    },

    handleGalleryImage: function($container, params) {
        var prototype = $container.data('prototype');
        var newForm = prototype.replace(/__name__/g, 1000 + $container.find('.juice_upload_item').length);
        var $newForm = $(newForm);

        fileNameWithPath = params.path + '?' + Math.random();


        $newForm.addClass('juice_upload_gallery_item');
        $newForm.find('.file_container input').attr('value', params.fileName);
        $newForm.find('.file_container').append('<img src="' + fileNameWithPath  + '"/>');
        $newForm.find('input.position').attr('value', $('.juice_upload_item').length+1);

        $container.find('.juice_upload_collection').append($newForm);

        this.delegateEvents();
    }

});
