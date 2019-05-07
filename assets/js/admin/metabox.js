jQuery( document ).ready( function( $ ){
	$( document ).on( 'click', '.copy-theme-settings .js-dismiss-notice', function(e) {
		e.preventDefault();
		var boxWrap = $(this).closest('.copy-theme-settings');
		boxWrap.slideUp('medium').remove();
		var baseUrl = $(this).attr('data-base_url');
		if ( 'undefined' !== baseUrl && '' !== baseUrl ) {
			history.pushState({}, '', baseUrl);
		}
	});
    // Metabox tabs
    $( document ).on( 'click', '.customify-mt-tabs-list a', function(e){
        e.preventDefault();
        var wrapper = $( this ).closest( '.customify-mt-tabs' );
        var layout = $( this ).attr( 'data-tab-id' ) || false;
        if ( layout ) {
            $( '.customify-mt-tab-cont', wrapper ).removeClass('active');
            $( '.customify-mt-tab-cont[data-tab-id="'+layout+'"]', wrapper ).addClass('active');

            $( '.customify-mt-tabs-list li', wrapper ).removeClass( 'active' );
            $( this ).closest('li').addClass( 'active' );
        }
    } );

    if ( wp  && wp.media ) {


        var CustomifyMedia = {
            setAttachment: function (attachment) {
                this.attachment = attachment;
            },
            addParamsURL: function (url, data) {
                if (!$.isEmptyObject(data)) {
                    url += (url.indexOf('?') >= 0 ? '&' : '?') + $.param(data);
                }
                return url;
            },
            getThumb: function (attachment) {
                var control = this;
                if (typeof attachment !== "undefined") {
                    this.attachment = attachment;
                }
                var t = new Date().getTime();
                if (typeof this.attachment.sizes !== "undefined") {
                    if (typeof this.attachment.sizes.medium !== "undefined") {
                        return control.addParamsURL(this.attachment.sizes.medium.url, {t: t});
                    }
                }
                return control.addParamsURL(this.attachment.url, {t: t});
            },
            getURL: function (attachment) {
                if (typeof attachment !== "undefined") {
                    this.attachment = attachment;
                }
                var t = new Date().getTime();
                return this.addParamsURL(this.attachment.url, {t: t});
            },
            getID: function (attachment) {
                if (typeof attachment !== "undefined") {
                    this.attachment = attachment;
                }
                return this.attachment.id;
            },
            getInputID: function (attachment) {
                $('.attachment-id', this.preview).val();
            },
            setPreview: function ($el) {
                this.preview = $el;
            },
            insertImage: function (attachment) {
                if (typeof attachment !== "undefined") {
                    this.attachment = attachment;
                }

                var url = this.getURL();
                var id = this.getID();
                var mime = this.attachment.mime;
                $('.customify-image-preview', this.preview).addClass('customify--has-file').html('<img src="' + url + '" alt="">');
                $('.attachment-url', this.preview).val(this.toRelativeUrl(url));
                $('.attachment-mime', this.preview).val(mime);
                $('.attachment-id', this.preview).val(id).trigger('change');
                this.preview.addClass('attachment-added');
                this.showChangeBtn();

            },
            toRelativeUrl: function (url) {
                return url;
            },
            showChangeBtn: function () {
                $('.customify--add', this.preview).addClass('customify--hide');
                $('.customify--change', this.preview).removeClass('customify--hide');
                $('.customify--remove', this.preview).removeClass('customify--hide');
            },
            insertVideo: function (attachment) {
                if (typeof attachment !== "undefined") {
                    this.attachment = attachment;
                }

                var url = this.getURL();
                var id = this.getID();
                var mime = this.attachment.mime;
                var html = '<video width="100%" height="" controls><source src="' + url + '" type="' + mime + '">Your browser does not support the video tag.</video>';
                $('.customify-image-preview', this.preview).addClass('customify--has-file').html(html);
                $('.attachment-url', this.preview).val(this.toRelativeUrl(url));
                $('.attachment-mime', this.preview).val(mime);
                $('.attachment-id', this.preview).val(id).trigger('change');
                this.preview.addClass('attachment-added');
                this.showChangeBtn();
            },
            insertFile: function (attachment) {
                if (typeof attachment !== "undefined") {
                    this.attachment = attachment;
                }
                var url = attachment.url;
                var mime = this.attachment.mime;
                var basename = url.replace(/^.*[\\\/]/, '');

                $('.customify-image-preview', this.preview).addClass('customify--has-file').html('<a href="' + url + '" class="attachment-file" target="_blank">' + basename + '</a>');
                $('.attachment-url', this.preview).val(this.toRelativeUrl(url));
                $('.attachment-mime', this.preview).val(mime);
                $('.attachment-id', this.preview).val(this.getID()).trigger('change');
                this.preview.addClass('attachment-added');
                this.showChangeBtn();
            },
            remove: function ($el) {
                if (typeof $el !== "undefined") {
                    this.preview = $el;
                }
                $('.customify-image-preview', this.preview).removeAttr('style').html('').removeClass('customify--has-file');
                $('.attachment-url', this.preview).val('');
                $('.attachment-mime', this.preview).val('');
                $('.attachment-id', this.preview).val('').trigger('change');
                this.preview.removeClass('attachment-added');

                $('.customify--add', this.preview).removeClass('customify--hide');
                $('.customify--change', this.preview).addClass('customify--hide');
                $('.customify--remove', this.preview).addClass('customify--hide');
            }

        };

        CustomifyMedia.controlMediaImage = wp.media({
            title: wp.media.view.l10n.addMedia,
            multiple: false,
            library: {type: 'image'}
        });

        CustomifyMedia.controlMediaImage.on('select', function () {
            var attachment = CustomifyMedia.controlMediaImage.state().get('selection').first().toJSON();
            CustomifyMedia.insertImage(attachment);
        });

        CustomifyMedia.controlMediaVideo = wp.media({
            title: wp.media.view.l10n.addMedia,
            multiple: false,
            library: {type: 'video'}
        });

        CustomifyMedia.controlMediaVideo.on('select', function () {
            var attachment = CustomifyMedia.controlMediaVideo.state().get('selection').first().toJSON();
            CustomifyMedia.insertVideo(attachment);
        });

        CustomifyMedia.controlMediaFile = wp.media({
            title: wp.media.view.l10n.addMedia,
            multiple: false
        });

        CustomifyMedia.controlMediaFile.on('select', function () {
            var attachment = CustomifyMedia.controlMediaFile.state().get('selection').first().toJSON();
            CustomifyMedia.insertFile(attachment);
        });


        $('.customify-mt-media').on('click', '.customify--add', function (e) {
            e.preventDefault();
            var p = $(this).closest('.customify-mt-media');
            console.log(p);
            CustomifyMedia.setPreview(p);
            CustomifyMedia.controlMediaImage.open();
        });

        $('.customify-mt-media').on('click', '.customify--remove', function (e) {
            e.preventDefault();
            var p = $(this).closest('.customify-mt-media');
            CustomifyMedia.remove(p);
        });

    }


} );