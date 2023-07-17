<?php

namespace Drupal\cloudinary_sdk\Form;

use Cloudinary\Api\Admin\AdminApi;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cloudinary_sdk\CloudinarySdkConstantsInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Define a config form for Cloudinary SDK.
 */
class CloudinarySdkSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cloudinary_sdk_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['cloudinary_sdk.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cloudinary_sdk.settings');
    // Make sure Cloudinary SDK installed.
    // If not, display messages and disable API settings.
    [$status, $version, $error_message] = cloudinary_sdk_check(TRUE);

    if ($status == CloudinarySdkConstantsInterface::CLOUDINARY_SDK_NOT_LOADED) {
      $this->messenger()
        ->addError($this->t('Please make sure the Cloudinary SDK library is installed in the libraries directory.'));
      if ($error_message) {
        $this->messenger()->addError($error_message);
      }
      return $form;
    }
    elseif ($status == CloudinarySdkConstantsInterface::CLOUDINARY_SDK_OLD_VERSION) {
      $this->messenger()
        ->addWarning($this->t('Please make sure the Cloudinary SDK library installed is @version or greater. Current version is @current_version.', [
          '@version' => CloudinarySdkConstantsInterface::CLOUDINARY_SDK_MINIMUM_VERSION,
          '@current_version' => $version,
        ]));
      return $form;
    }

    // Build API settings form.
    $form['settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('API Settings'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#description' => $this->t('You should enable "Auto-create folders" on cloudinary account. In order to check the validity of the API, system will be auto ping your Cloudinary account after change API settings.'),
    ];

    $form['settings']['cloudinary_sdk_cloud_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cloud name'),
      '#required' => TRUE,
      '#default_value' => $config->get('cloudinary_sdk_cloud_name'),
      '#description' => $this->t('Cloud name of Cloudinary.'),
    ];

    $form['settings']['cloudinary_sdk_upload_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API endpoint'),
      '#required' => TRUE,
      '#default_value' => $config->get('cloudinary_sdk_upload_prefix'),
      '#description' => $this->t('API endpoint of Cloudinary. Get more details by checking official @documentation.', [
        '@documentation' => Link::fromTextAndUrl(
          $this->t('documentation'),
          Url::fromUri('https://cloudinary.com/documentation/image_upload_api_reference#alternative_data_centers_and_endpoints_premium_feature')
            ->setOption('attributes', ['target' => '_blank'])
        )->toString(),
      ]),
    ];

    $form['settings']['cloudinary_sdk_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key'),
      '#required' => TRUE,
      '#default_value' => $config->get('cloudinary_sdk_api_key'),
      '#description' => $this->t('API key of Cloudinary.'),
    ];

    $form['settings']['cloudinary_sdk_api_secret'] = [
      '#type' => 'password',
      '#title' => $this->t('API secret'),
      '#required' => !$config->get('cloudinary_sdk_api_secret'),
      '#description' => $this->t('API secret of Cloudinary.'),
    ];

    if ($config->get('cloudinary_sdk_api_secret')) {
      $form['settings']['cloudinary_sdk_api_secret']['#description'] = $this->t('Required if you want to change the API secret.');
    }

    $form['settings']['cloudinary_sdk_cname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CNAME'),
      '#default_value' => $config->get('cloudinary_sdk_cname'),
      '#description' => $this->t('Change the default delivery URL. Provide host name without schema e.g. <strong>demo.cloudinary.be</strong><br>Get more details by checking official @documentation.', [
        '@documentation' => Link::fromTextAndUrl(
          $this->t('documentation'),
          Url::fromUri('https://cloudinary.com/documentation/advanced_url_delivery_options#private_cdns_and_cnames')
            ->setOption('attributes', ['target' => '_blank'])
        )->toString(),
      ]),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('cloudinary_sdk.settings');
    $cloud_name = trim($form_state->getValue('cloudinary_sdk_cloud_name'));
    $api_key = trim($form_state->getValue('cloudinary_sdk_api_key'));
    $api_secret = trim($form_state->getValue('cloudinary_sdk_api_secret'));
    $upload_prefix = trim($form_state->getValue('cloudinary_sdk_upload_prefix'));
    $cname = trim($form_state->getValue('cloudinary_sdk_cname'));

    if (!$api_secret) {
      $api_secret = $config->get('cloudinary_sdk_api_secret') ?? '';
    }

    // Validate the API settings with ping.
    if ($cloud_name && $api_key && $api_secret && $upload_prefix) {
      $key = $cloud_name . $api_key . $api_secret . $upload_prefix . $cname;
      $old_key = $config->get('cloudinary_sdk_cloud_name');
      $old_key .= $config->get('cloudinary_sdk_api_key');
      $old_key .= $config->get('cloudinary_sdk_api_secret');
      $old_key .= $config->get('cloudinary_sdk_upload_prefix');
      $old_key .= $config->get('cloudinary_sdk_cname') ?? '';

      // Return if no changes.
      if ($key == $old_key) {
        return;
      }

      $config = [
        'cloud' => [
          'cloud_name' => $cloud_name,
          'api_key' => $api_key,
          'api_secret' => $api_secret,
        ],
        'url' => array_filter([
          'secure_distribution' => $cname,
          'private_cdn' => !empty($cname),
        ]),
        'api' => [
          'upload_prefix' => $upload_prefix,
        ],
      ];

      // Init cloudinary sdk with new API settings.
      cloudinary_sdk_init($config);

      try {
        (new AdminApi())->ping();
      }
      catch (\Exception $e) {
        $this->logger('cloudinary_sdk')->error($e->getMessage());
        $form_state->setErrorByName('', $e->getMessage());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('cloudinary_sdk.settings');

    $fields = [
      'cloudinary_sdk_cloud_name',
      'cloudinary_sdk_upload_prefix',
      'cloudinary_sdk_api_key',
      'cloudinary_sdk_cname',
    ];

    foreach ($fields as $field) {
      $config->set($field, $form_state->getValue($field));
    }

    $secret = $form_state->getValue('cloudinary_sdk_api_secret');

    if (!$secret) {
      $secret = $config->get('cloudinary_sdk_api_secret');
    }

    $config->set('cloudinary_sdk_api_secret', $secret);
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
