<?php

/**
 * @file
 * Contains \Drupal\ckeditor_mentions\Plugin\views\row\CKEditorMentions.
 */

namespace Drupal\ckeditor_mentions\Plugin\views\row;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\row\Fields;

/**
 * CKEditor Mentions row plugin.
 *
 * @ingroup views_row_plugins
 *
 * @ViewsRow(
 *   id = "ckeditor_mentions",
 *   title = @Translation("CKEditor Mentions inline fields"),
 *   help = @Translation("Displays the fields with an optional template."),
 *   theme = "views_view_fields",
 *   register_theme = FALSE,
 *   display_types = {"ckeditor_mentions"}
 * )
 */
class CKEditorMentions extends Fields {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['separator'] = array('default' => '-');

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Removing access to fields not relevant for this row style plugin.
    $form['default_field_elements']['#access'] = FALSE;
    $form['inline']['#access'] = FALSE;
    $form['hide_empty']['#access'] = FALSE;
    $form['separator']['#type'] = 'hidden';

    // Notice about no settings in the CKEditor Mentions style options.
    $form['ckeditor_mentions_notice'] = array(
      '#type' => 'item',
      '#markup' => $this->t("<strong>Note:</strong> The 'CKEditor Mentions' displays does not have any row style options!"),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preRender($row) {
    // Force all fields to be inline by default.
    if (empty($this->options['inline'])) {
      $fields = $this->view->getHandlers('field', $this->displayHandler->display['id']);
      $names = array_keys($fields);
      $this->options['inline'] = array_combine($names, $names);
    }

    return parent::preRender($row);
  }
}
