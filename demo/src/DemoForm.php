<?php

namespace Drupal\demo;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements an example form.
 */
class DemoForm extends FormBase {

  /**
   * Database connection object.
   *
   * @var Drupal\Core\Database\Connection
   */
  protected $database;

  /*
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiate this form class.
    return new static (
      // Load the service required to construct this class.
      $container->get('database')
    );
  }

  /**
   * Class constructor.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'demo_form';
  }
  
  public static function getFormSpec(&$form=NULL){
    if(!$form) $form = [];
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => t('Nombre'),
      '#attributes' => [
        'pattern' => "[A-Za-z0-9áéíóúÁÉÍÓÚñÑ ]+",
        'placeholder' => ('Solo letras y números')
      ],
      '#required' => TRUE,
    ];
    $form['idNum'] = [
      '#type' => 'textfield',
      '#title' => t('Identificación'),
      '#attributes' => [
        'pattern' => "[0-9]+",
        'placeholder' => t('Solo números')
      ],
      '#required' => TRUE,
    ];
    $form['dob'] = [
      '#type' => 'date',
      '#title' =>t('Fecha de nacimiento'),
      '#description' => t('En UTC, tenga en cuenta si nació en Colombia hacia el final o el inicio del día, su fecha de nacimiento en UTC puede ser diferente a la que usted cree.'),
    ];
    $form['role'] = [
      '#type' => 'select',
      '#options' => [
        'admin' => t('Administrador'),
        'webmaster' => t('Webmaster'),
        'dev' => t('Desarrollador'),
      ],
      '#title' => t('Cargo'),
      '#empty_value' => '',
      '#empty_option' => '- Ninguno -',
      '#required' => TRUE
    ];
    return $form;
  }
  
  public static function getErrors($values){
    $errors = [];
    $formSpec = static::getFormSpec();
    foreach($formSpec as $k => $spec){
      if(($spec['#required']??FALSE) && !$values[$k]){
        $errors[$k][] = t("Campo requerido");
        continue;
      }else if(!$values[$k]){
        continue;
      }
      if($spec['#type'] == 'textfield' && !preg_match("/".$spec['#attributes']['pattern']."/", $values[$k])){
        $errors[$k][] = $spec['#attributes']['placeholder'];
      }
      if($spec['#type'] == 'date' && (
          !preg_match('/\d{4}-\d{2}-\d{2}/', $values[$k]) ||
          !strtotime($values[$k])
          ) ){
        $errors[$k][] = t("Fecha no válida:");
      }
      if($spec['#type'] == 'select' && !isset($spec['#options'][$values[$k]])){
        $errors[$k][] = t("Selección no válida: ").$values[$k];
      }
    }
    foreach($values as $k=>$v) if(!isset($formSpec[$k])){
      $errors[$k][] = t("Campo no soportado");
    }
    return $errors;
  }
  
  public static function presave($values){
    $values['status'] = (($values['role']??'') == 'admin')?1:0;
    foreach($values as $k => $v){
      // Ignore Emppty Values
      if($v === '') unset($values[$k]);
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = static::getFormSpec($form);
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Solicitar Acceso'),
      '#button_type' => 'primary',
    ];

    $form['#theme'] = ['demo_form_wrapper'];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->cleanValues()->getValues();
    $errors = static::getErrors($values);
    if(count($errors)){
      foreach($errors as $field => $ls) foreach($ls as $error){
        $form_state->setErrorByName($field, $error);  
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {    
    $values = array_filter($form_state->cleanValues()->getValues());
    try{
      $id = $this->database->insert('example_users')->fields(static::presave($values))->execute();              
    }catch(\Throwable $e){
      drupal_set_message($this->t('Error inesperado: ') . $e->getMessage(), 'error');
      return;
    }
    $this->messenger()->addStatus($this->t('Información recibida con número: :id, pronto revisaremos su solicitud...', [':id'=>$id]));
  }

}