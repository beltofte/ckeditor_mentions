<?php

/**
 * @file
 * Contains \Drupal\ckeditor_mentions\Plugin\CKEditorPlugin\CKEditor_Mentions.
 */

namespace Drupal\ckeditor_mentions\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginConfigurableInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the "ckeditor_mentions" plugin.
 *
 * @CKEditorPlugin(
 *   id = "ckeditor_mentions",
 *   label = @Translation("CKEditor Mentions"),
 *   module = "ckeditor_mentions"
 * )
 */
class CKEditor_Mentions extends CKEditorPluginBase implements CKEditorPluginConfigurableInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'ckeditor_mentions') . '/js/plugins/ckeditor_mentions/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return array(
      'ckeditor_mentions_view_machine_name' => '',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    $mentions_label = $this->t('Mentions');
    return [
      'Mentions' => [
        'label' => t('Mentions'),
        'image_alternative' => [
          '#type' => 'inline_template',
          '#template' => '<a href="#" role="button" aria-label="">' . $mentions_label . '</a>',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, Editor $editor) {
    $settings = $editor->getSettings();

    /*
    $all_profiles = $this->linkitProfileStorage->loadMultiple();

    $options = array();
    foreach ($all_profiles as $profile) {
      $options[$profile->id()] = $profile->label();
    }
    */



    $form['linkit_profile'] = array(
      '#type' => 'select',
      '#title' => t('Select a linkit profile'),
      '#options' => array(),
      '#default_value' => isset($settings['plugins']['linkit']) ? $settings['plugins']['linkit'] : '',
      '#empty_option' => $this->t('- Select profile -'),
      '#description' => $this->t('Select the linkit profile you wish to use with this text format.'),
      '#element_validate' => array(
        array($this, 'validateLinkitProfileSelection'),
      ),
    );

    return $form;
  }

  /**
   * #element_validate handler for the "linkit_profile" element in settingsForm().
   */
  public function validateLinkitProfileSelection(array $element, FormStateInterface $form_state) {
    $toolbar_buttons = $form_state->getValue(array('editor', 'settings', 'toolbar', 'button_groups'));
    if (strpos($toolbar_buttons, '"Linkit"') !== FALSE && empty($element['#value'])) {
      $form_state->setError($element, t('Please select the linkit profile you wish to use.'));
    }
  }

}
