jQuery(document).ready(function($) {
    $('#tmm_image_upload').click(function(e) {
        e.preventDefault();
        var image = wp.media({
            title: 'Upload Image',
            multiple: false
        }).open()
        .on('select', function() {
            var uploaded_image = image.state().get('selection').first();
            var image_url = uploaded_image.toJSON().url;
            $('#tmm_image').val(image_url);
            $('#tmm_image').next('img').attr('src', image_url).show();
        });
    });
});
