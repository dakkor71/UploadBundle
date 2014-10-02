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
    requiredFormData : [],
    
    paths : {},

    fileTpl: _.template(
        '<input type="hidden" name="<%= inputName %>" value="<%= fileName %>" />' +
        '<div class="alert alert-success juice_upload_single_file" role="alert"><%= fileName %><div class="button remove"><span class="glyphicon glyphicon-remove"></span></div></div>'
    ),

    imageTpl: _.template(
        '<input type="hidden" name="<%= inputName %>" value="<%= fileName %>" />' +
        '<img src="<%= fileNameWithPath %>" />'
    ),

    galleryItem: _.template(
        '<div class="juice_upload_item">' +
            '<div class="featured_layer"></div>' +
                '<div class="edit_layer">' +
                    '<div class="buttons">' +
                        '<div class="button remove"><span class="glyphicon glyphicon-remove"></span></div>' +
                    '</div>' +
                '</div>' +
                '<div class="preview">' +
                    '<img src="<%= file %>" />' +
                '</div>' +
            '<div class="hidden_form"><%= form %></div>' +
        '</div>'
    ),
    
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

        $(".juice_upload_collection_container").sortable({
            update : function() {
                $(this).closest(".juice_upload_item").trigger( "sortElements");
            }
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

    progressHandler: function(e, data, $container) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $container.find('.progress_container .percent').html( progress + '%');
        if (progress == 100) {
            $container.find('.progress_container .percent').empty();
            $container.find('.progress_container span').hide();
        }
    },

    sortItems : function() {
        this.$el.find('.juice_upload_item').each(function() {
            $(this).find('.position').attr('value' , $(this).index() + 1);
        });
    },

    removeItem : function(e) {
        if($(e.currentTarget).closest('.juice_upload_item').hasClass('single_item')) {
            $(e.currentTarget).closest('.juice_upload_item').find('.juice_upload_form_container').empty();
        } else {
            $(e.currentTarget).closest('.juice_upload_item').remove();
        }
    },

    handleSingleImage: function($container, fileName) {
        var html = this.imageTpl({
            'inputName' : $container.data('input-name'),
            'fileName' : fileName,
            'fileNameWithPath' : '/' + config.tmp_upload_dir + fileName + '?' + Math.random()
        });

        $container.find('.juice_upload_form_container').html(html);

        this.delegateEvents();
    },

    handleSingleFile: function($container, fileName) {
        var html = this.fileTpl({
            'inputName' : $container.data('input-name'),
            'fileName' : fileName
        });

        $container.find('.juice_upload_form_container').html(html);

        this.delegateEvents();
    },

    handleGalleryImage: function($container, fileName) {
        // Get the data-prototype explained earlier
        var prototype = $container.data('prototype');

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        var newForm = prototype.replace(/__name__/g, 1000 + $container.find('.juice_upload_item').length);

        fileNameWithPath = '/' + config.tmp_upload_dir + fileName + '?' + Math.random();

        var $galleryItem = $(this.galleryItem({file : fileNameWithPath, form : newForm}));

        $galleryItem.find('input.photo').attr('value', fileName);
        $galleryItem.find('input.position').attr('value', $('.juice_upload_item').length+1);

        $container.find('.juice_upload_collection_container').append($galleryItem);

        this.delegateEvents();
    }

});
