window.project.templates['cropModalTemplate'] = _.template(
    '<div class="modal fade" id="cropModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">' +
        '<div class="modal-dialog">' +
            '<div class="modal-content">' +
                '<div class="modal-header">' +
                    '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                    ' <h4 class="modal-title" id="myModalLabel">Crop image</h4>' +
                '</div>' +
                '<div class="modal-body">' +
                    '<img class="hidden" src="<%= file %>" id="cropTarget" alt="[Jcrop Example]" />'+
                '</div>' +
                '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>' +
                    '<button type="button" class="btn btn-primary crop-button">Crop</button>' +
                '</div>' +
            '</div><!-- /.modal-content -->' +
        '</div><!-- /.modal-dialog -->' +
    '</div>'
);

var cordinates;
var uploadedImages = new Array();

var uploadView = Backbone.View.extend({
    
    requiredPaths : ["crop", "upload"],
    requiredFormData : ["kind"],
    
    paths : {},
    
    initialize : function(options) {
        var self = this;
        
        //set paths
        for(var i in self.requiredPaths) {
            if(!options.paths[self.requiredPaths[i]]) {
                alert('Please set all paths!');
                return;    
            }
            self.paths[self.requiredPaths[i]] = options.paths[self.requiredPaths[i]];
        }
        
        this.$el.each(function() {
            self.initUploader(this);
        });
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
                //alert('The error was: ' + errorType);
            }
        });

        $el.fileupload(options);
    },
    
    validData : function($container , options) {
        for(var i in this.requiredFormData) {
            if(!options.formData[this.requiredFormData[i]]) {
                alert('Please set file ' + this.requiredFormData[i]);
                return false;
            }
        }
        
        if(!$container.data('callback') || !typeof(this[$container.data('callback')])) {
            alert('defind callback');
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
        
        if($container.data('crop')) {
            uploadedImages.push(data.params);
            this.cropHandler($container);
            return;
        }
        
        this[$container.data('callback')]($container, data.params.fileName);
    },

    cropHandler: function($container) {
        var self = this;
        //check if crop action is in progress
        if($('#cropModal').size() || uploadedImages.length == 0) {
            return;
        }

        var currentPhoto = uploadedImages[0];
        var minSize = eval($container.data('minsize'));
        var size = currentPhoto.size;

        if(minSize['width'] == size['width'] && minSize['height'] == size['height']) {
            //dont crop if uploaded image is same size as minimal
            this[$container.data('callback')]($container, currentPhoto.fileName);
            uploadedImages.shift();
            if(uploadedImages.length > 0) {
                self.cropHandler($container);
            }
            return;
        }
        
        if(minSize['width'] > size['width'] || minSize['height'] > size['height']) {
            alert('Please upload image with min size ' + minSize['width'] + ' / ' + minSize['height']);
            uploadedImages.shift();
            this.cropHandler($container);
            return;
        }
        
        //add modal
        var modal = window.project.templates['cropModalTemplate'];
        $container.append(modal({file : '/' + config.tmp_upload_dir + currentPhoto.fileName}));
        $('#cropModal').modal('show');
        
        //and modal on close
        $('#cropModal').on('hidden.bs.modal', function () {
            $('#cropTarget').Jcrop("destoy");
            $('#cropModal').remove();
            uploadedImages.shift();
            if(uploadedImages.length > 0) {
                self.cropHandler($container);
            }
        });
        
        //init crop after image is loaded
        cropImage = new Image();
        cropImage.onload = function() {
            $('.modal .modal-body img').removeClass('hidden');
            self.cropInit($container.data('ratio') , minSize ,  size);
        };

        cropImage.src = '/' + config.tmp_upload_dir + currentPhoto.fileName;
    },

    cropInit: function(ratio , minSize , size) {
        var self = this;
        $preview = $('#preview-pane'),
        $pcnt = $('#preview-pane .preview-container'),
        $pimg = $('#preview-pane .preview-container img'),

        xsize = $pcnt.width(),
        ysize = $pcnt.height();
        
        $('#cropTarget').Jcrop({
            onSelect : self.updateCordinates,
            onChange : self.updateCordinates,
            onRelease : self.resetCordinates,
            aspectRatio: ratio,
            trueSize : [size['width'] , size['height']],
            minSize : [minSize['width'] , minSize['height']],
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
            alert('Please select crop area');
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
                    $container = $('#cropModal').parent();
                    self[$container.data('callback')]($container, uploadedImages[0].fileName);
                    $('#cropModal').modal('hide');
                }
            }
        });
    },

    /*
        Default functions
     */

    uploadInitHandler : function(instance, $container) {},

    sortItems : function(e) {},

    removeItem : function(e) {},

    progressHandler: function(e, data, $container) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $container.find('#progress .progress-bar').css(
            'width',
            progress + '%'
        );
    },

    handleGalleryImage: function($container, fileName) {},

    handleSingleImage: function($container, fileName) {
        var html = this.imageTpl({
            'inputName' : $container.data('input-name'),
            'fileName' : fileName,
            'fileNameWithPath' : '/' + config.tmp_upload_dir + fileName + '?' + Math.random()
        });

        $container.find('.preview').html(html);

        this.delegateEvents();
    }

});
