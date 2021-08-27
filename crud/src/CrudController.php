<?php
namespace Drupal\crud;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\demo\DemoForm;
use Symfony\Component\HttpFoundation\Request;


class CrudController extends ControllerBase{
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
  
  public function rest(Request $request, $id=NULL){
    try{
      $query = $this->database->select('example_users')->fields('example_users');
      $item = NULL;
      if($id){
        $query->condition('id', $id);
        $qAll = $query->execute()->fetchAll();
        $item = array_pop($qAll);
        if(!$item){
          return JsonResponse::create($item, 404);        
        }
      }

      $method = $request->getMethod();

      if($method == 'GET'){
        return JsonResponse::create($item ?: $query->execute()->fetchAll(), 200);
      }else if(in_array($method,['PATCH','POST'])){
        try{
          $values = json_decode($request->getContent(), TRUE);      
        }catch(\Throwable $e){
        }
        if(!$values){      
          return JsonResponse::create($request->getContent(), 422);
        }
        $errors = DemoForm::getErrors($values);
        if(count($errors)){
          return JsonResponse::create($errors, 400);
        }
        if($method == 'PATCH') return $this->update($id, $values);
        return $this->add($values);
      }else if($method == 'DELETE'){
        return $this->delete($id);
      }else{
        return JsonResponse::create('Not Allowed', 405);
      }
    }catch(\Throwable $e){
      return JsonResponse::create($e->getMessage(), 500);
    }

  }
    
  public function update($id, $values) {
    $this->database
        ->update('example_users')
        ->fields(DemoForm::presave($values))
        ->condition('id', $id)
        ->execute();
    return JsonResponse::create(['id' => $id, 'op' => 'update'], 200);
  }

  public function add($values) {
    return JsonResponse::create([
      'id'=>$this->database
        ->insert('example_users')
        ->fields(DemoForm::presave($values))
        ->execute(),
      'op' => 'create'
    ], 201);
  }

  public function delete($id) {
    $this->database
        ->delete('example_users')
        ->condition('id', $id)      
        ->execute();    
    return JsonResponse::create(['id'=>$id, 'op' => 'delete'], 200);
  }
  
  
  public function content() {
    return array(
      '#type' => 'markup',
      '#markup' => t('this is a demo!'),
    );
  }
}