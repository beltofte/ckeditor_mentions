<?php

/**
 * @file
 * Contains \Drupal\ckeditor_mentions\Plugin\CKEditorPlugin\CKEditor_Mentions.
 */

namespace Drupal\ckeditor_mentions\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginConfigurableInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\Entity\Editor;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityManagerInterface;


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
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')
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
      'view_display' => '',
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
    $options = $this->getViewDisplays();

    $form['view_display'] = array(
      '#type' => 'select',
      '#title' => t('View used to search in the users'),
      '#options' => $options,
      '#default_value' => isset($settings['plugins']['ckeditor_mentions']['view_display']) ? $settings['plugins']['ckeditor_mentions']['view_display'] : '',
      '#empty_option' => $this->t('- Select view display -'),
      '#description' => '<p>' . $this->t('Choose the view and display that searches in the users that can be mentioned in this text format.<br />Only views with a display of type "CKEditor Mentions" are eligible.') . '</p>',
      '#element_validate' => array(
        array($this, 'validateViewDisplaySelection'),
      ),
    );

    return $form;
  }

  /**
   * #element_validate handler for the "ckeditor_mentions_view_display" element in settingsForm().
   */
  public function validateViewDisplaySelection(array $element, FormStateInterface $form_state) {
    $toolbar_buttons = $form_state->getValue(array('editor', 'settings', 'toolbar', 'button_groups'));
    if (strpos($toolbar_buttons, '"Mentions"') !== FALSE && empty($element['#value'])) {
      $form_state->setError($element, t('Please select the view display you wish to use.'));
    }
  }

  /**
   * Find view displays of the type 'ckeditor_mentions' and prepare options array with the result.
   *
   * @return array
   *   Return array with view displays.
   */
  private function getViewDisplays() {
    $displays = Views::getApplicableViews('ckeditor_mentions_display');
    $view_storage = $this->entityManager->getStorage('view');

    $options = array();
    foreach ($displays as $data) {
      list($view_id, $display_id) = $data;
      $view = $view_storage->load($view_id);
      $display = $view->get('display');
      $options[$view_id . ':' . $display_id] = $view->get('label') . '(' . $view_id . ') - ' . $display[$display_id]['display_title'];
    }

    return $options;
  }

}
