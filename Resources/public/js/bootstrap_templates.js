if (uploadErrors == false) {
    var cropPopupTemplate = _.template(
        '<div class="mask"></div>' +
        '<div id="cropPopup" class="popup">' +
            '<div class="popup-header">' +
                '<span class="close"></span>' +
                '<h4 class="popup-title">Crop image</h4>' +
            '</div>' +
            '<div class="popup-body">' +
                '<img class="hidden" src="<%= file %>" id="cropTarget" alt="[Jcrop Example]" />'+
            '</div>' +
            '<div class="popup-footer">' +
                '<button type="button" class="btn btn-primary crop-button">Crop</button>' +
            '</div>' +
        '</div>'
    );

    var fileTpl = _.template(
        '<input type="hidden" name="<%= inputName %>" value="<%= fileName %>" />' +
        '<div class="alert alert-success juice_upload_single_file" role="alert">' +
            '<%= fileName %>' +
            '<div class="button remove">' +
                '<span class="glyphicon glyphicon-remove"></span>' +
            '</div>' +
        '</div>'
    );

    var imageTpl = _.template(
        '<input type="hidden" name="<%= inputName %>" value="<%= fileName %>" />' +
        '<img src="<%= fileNameWithPath %>" />'
    );
}