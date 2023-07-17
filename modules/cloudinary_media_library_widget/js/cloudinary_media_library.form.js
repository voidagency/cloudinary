(function ($, Drupal, once, drupalSettings, cloudinary) {

  'use strict';

  /**
   * Defines a behaviour to handle custom cloudinary form element.
   */
  Drupal.behaviors.cloudinaryMediaLibraryFormElement = {
    attach: function (context) {
      const self = this;
      const widgetSettings = drupalSettings.cloudinary_media_library || false;

      if (!widgetSettings) {
        console.error('No settings available for the form element.');
        return;
      }

      once('cloudinaryMediaLibrary', $(context).find('input.form-cloudinary')).forEach(function (input) {
        const $input = $(input);

        // Build preview based on default value.
        if ($input.val().length && $input.hasClass('has-preview')) {
          const previewImage = self.buildPreviewImageByInput(input);
          $input.before(previewImage);
        }

        // Build open media button.
        $input.after('<div class="js-cloudinary-button-wrapper"><button class="button cloudinary-media-library-button">' + Drupal.t('Open media library', {}, { context: 'Cloudinary media' }), + '</button></div>');
        $input.parent().find('.cloudinary-media-library-button').on('click', function (e) {
          e.preventDefault();

          // Display loading spinner.
          $input.after($(Drupal.theme('ajaxProgressThrobber')));

          const options = {
            cloud_name: widgetSettings.cloud_name,
            api_key: widgetSettings.api_key,
            use_saml: widgetSettings.use_saml || false,
            multiple: false,
            button_caption: Drupal.t('Open media library', {}, { context: 'Cloudinary media' }),
            button_class: 'button cloudinary-media-library-button',
            insert_caption: Drupal.t('Insert', {}, { context: 'Cloudinary media' }),
            folder: {
              resource_type: $input.data('resource-type'),
            }
          };

          if (widgetSettings.starting_folder) {
            options.folder.path = widgetSettings.starting_folder;
          }

          // Better support of files including PDF files.
          if ($input.data('resource-type') === 'raw') {
            delete options.folder;
            options.search = {
              expression: 'resource_type:raw OR format=pdf'
            };
          }

          const handlers = {
            insertHandler: function (data) {
              const input = document.getElementById(this.inputId);

              if (!input) {
                console.error('Cloudinary input element not found.');
                return;
              }

              data.assets.forEach(function (asset) {
                input.value = [
                  'cloudinary',
                  asset.resource_type,
                  asset.type,
                  asset.public_id
                ].join(':');

                if (input.classList.contains('has-preview')) {
                  const previewImage = self.buildPreviewImageByAsset(asset);
                  const oldPreview = input.parentElement.querySelector('.cloudinary-preview');

                  if (oldPreview) {
                    oldPreview.remove();
                  }

                  $(input).before(previewImage);
                }
              })
            },
            showHandler: function () {
              const input = document.getElementById(this.inputId);

              if (!input) {
                console.error('Cloudinary input element not found.');
                return;
              }

              // Delete ajax progress spinner.
              const spinner = input.parentElement.querySelector('.ajax-progress');

              if (spinner) {
                spinner.remove();
              }
            }
          };

          const element = cloudinary.openMediaLibrary(options, handlers);
          element.inputId = $(this).closest('.js-form-item').find('input')[0].id;
        });
      });
    },
    buildPreviewImageByInput: function (input) {
      const imageUrl = input.dataset.previewImage;

      if (typeof imageUrl === 'undefined') {
        return '';
      }

      return '<div class="cloudinary-preview"><img loading="lazy" width="200" alt="Preview image" src="' + imageUrl + '"></div>';
    },
    buildPreviewImageByAsset: function (asset) {
      // No preview images available for raw files.
      if (asset.resource_type === 'raw') {
        return '';
      }

      let imageUrl = asset.secure_url;
      let alt = asset.public_id;

      if (asset.context && asset.context.custom && asset.context.custom.alt) {
        alt = asset.context.custom.alt;
      }

      // Get thumbnail from video or PDF.
      if (asset.resource_type === 'video' || (asset.resource_type === 'image' && asset.format === 'pdf')) {
        imageUrl = imageUrl.replace('.' + asset.format, '.jpeg');
      }

      // Crop image by 400px to improve performance.
      const splitter = '/' + asset.resource_type + '/' + asset.type;
      const urlParts = imageUrl.split(splitter);
      const rawTransformation = 'c_fill,w_400';
      imageUrl = urlParts[0] + splitter + '/' + rawTransformation + urlParts[1];

      return '<div class="cloudinary-preview"><img loading="lazy" width="200" alt="' + alt + '" src="' + imageUrl + '"></div>';
    }
  };

})(jQuery, Drupal, once, drupalSettings, cloudinary);
