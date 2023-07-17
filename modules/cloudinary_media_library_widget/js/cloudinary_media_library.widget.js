(function ($, Drupal, once, drupalSettings, cloudinary) {
  Drupal.behaviors.CloudinaryMediaLibraryWidget = {
    attach: function (context) {
      const widgetSettings = drupalSettings.cloudinary_media_library_widget || false;

      if (!widgetSettings) {
        console.error('No settings available for the widget.');
        return;
      }

      const $progress_element = $(Drupal.theme('ajaxProgressThrobber'));

      let mediaInsertHandler = function (data) {
        const settings = widgetSettings[this.fieldName];
        const media_creation_url = 'cloudinary_media_library_widget/' + settings.bundle;
        const $fieldElement = $('#' + this.fieldWrapperId);

        $fieldElement.find('.js-media-library-selection').after($progress_element);

        $.ajax({
          url: Drupal.url(media_creation_url),
          type: 'POST',
          data: JSON.stringify(data.assets),
          dataType: 'json',
          success: function (response) {
            $fieldElement.find('.js-cloudinary-library-selection').val(response.ids.join(','));
            $fieldElement.find('.js-cloudinary-library-update-widget').trigger('mousedown');
          }
        });
      }

      once('cloudinaryMediaLibraryWidget', '.js-cloudinary-library-widget', context).forEach(function (element) {
        const settings = widgetSettings[element.dataset.fieldName];
        const cloudinary_settings = {
          cloud_name: settings.cloud_name,
          api_key: settings.api_key,
          button_caption: Drupal.t('Open media library', {}, {context: 'Cloudinary media'}),
          button_class: 'button cloudinary-media-library-button',
          insert_caption: Drupal.t('Insert', {}, {context: 'Cloudinary media'}),
          multiple: settings.multiple,
          use_saml: settings.use_saml || false,
          folder: {
            resource_type: settings.resource_type
          }
        };

        // Better support of files including PDF files.
        if (settings.resource_type === 'raw') {
          delete cloudinary_settings.folder;
          cloudinary_settings.search = {
            expression: 'resource_type:raw OR format=pdf'
          };
        }

        if (settings.max_files) {
          cloudinary_settings.max_files = settings.max_files;
        }

        if (settings.starting_folder) {
          cloudinary_settings.folder.path = settings.starting_folder;
        }

        const widget = cloudinary.createMediaLibrary(
          cloudinary_settings,
          {
            insertHandler: mediaInsertHandler
          },
          element.querySelector('.js-cloudinary-library-open-button')
        )

        widget.fieldWrapperId = element.id;
        widget.fieldName = element.dataset.fieldName;
      });
    }
  };
})(jQuery, Drupal, once, drupalSettings, cloudinary);
