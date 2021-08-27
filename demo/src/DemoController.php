<?php
namespace Drupal\demo;
class DemoController {
  public function content() {
    return array(
      '#type' => 'markup',
      '#markup' => t('Página Data, nada que ver aquí...'),
    );
  }
}