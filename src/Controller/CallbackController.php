<?php

/**
 * @file
 * Contains \Drupal\ckeditor_mentions\Controller\CallbackController.
 */

namespace Drupal\ckeditor_mentions\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\views\Views;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CallbackController
 * @package Drupal\ckeditor_mentions\Controller
 */
class CallbackController extends ControllerBase {

  /**
   * @param $view_name
   * @return JsonResponse
   */
  public function callback($view_name, Request $request) {
    $search = $request->query->get('q');
    $renderFields = array();
    // Load view
    /** @var \Drupal\views\ViewEntityInterface $view */
    $view = \Drupal::entityManager()->getStorage('view')->load($view_name);
    if ($view) {
      $executable = $view->getExecutable();

      // Get the default (master) display definition.
      $executable->setDisplay('default');
      $view_display = $view->getDisplay('default');
      // Check permission to the view display default(master)
      if ($this->access($view_name, $view_display)) {
        // Loop on each exposed filter.
        $filters = array();
        foreach ($executable->display_handler->options['filters'] AS $key => $options) {
          if ($options['exposed']) {
            $filters[$key] = $search;
          }
        }
        // Set request to auto-fill exposed filters.
        $query = $request->query;
        $query->add($filters);
        // Execute the view to get results.
        $executable->render();
        //Gets the current style plugin object.
        $currentStylePlugin = $executable->getStyle();
        // Transform the current style plugin object to array.
        $stylePlugin = (array) $currentStylePlugin;
        // Search for the rendered fields array.
        foreach ($stylePlugin as $row) {
          if (is_array($row)) {
            $renderFields[] = $row;
          }
        }
        // The rendered fields array contains all fields of the view.
        $rendered_fields = $renderFields[1];
        // Initialise the view data (the data which we returned in the fields autocomplete).
        $viewData = array();
        // Initialise the view data which we formatted to return a correct key-Value
        $viewDataFormatted = array();

        foreach ($rendered_fields as $row) {
          // Content of rendered fields.
          $rowValues = array_values($row);
          $count = count($rowValues);
          $key = $val = $rowValues[count($row) - 2];
          //Take the last field to allow to call more that one and "Rewrite field" and call them all.
          if ($count > 1) {
            $val = $rowValues[$count - 1];
          }
          // The String Which search for.
          $viewData['value'] = $key;
          $viewData['label'] = $val;
          $viewDataFormatted[] = $viewData;
        }
        return new JsonResponse($viewDataFormatted);
      }
    }
  }
  /**
   * @param $view_name
   * @param $view_display
   * @return AccessResult
   */
  public function access($view_name, $view_display) {
    // Determine if the given user has access to the view. Note that
    // this sets the display handler if it hasn't been.
    $view = Views::getView($view_name);
    if ($view->access($view_display)) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }
}
