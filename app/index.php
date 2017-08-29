<?php

//show error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);
ini_set( 'default_charset', 'UTF-8');

// connect db
mysql_connect('localhost', 'cw76594_poligon', 'rTT2Je4a');
mysql_select_db('cw76594_poligon');
mysql_set_charset('utf8');

// грузим модули. (в данном случае разруливать и грузить только нужные не будем)
include('tasks_fakeclass.php');
include('users_fakeclass.php');
include('transactions_fakeclass.php');
include('function_lib.php');

// вымышленная проверка авторизации
$auth = false;
if(users__get($_REQUEST['user_id'])!==false){
  $auth = true;
}

$method = $_REQUEST['method'];
$response = array();

// анонимные методы api
switch($method){
  case 'accaunts.get':
    $response['list'] = users__getall();
    break;

}

//методы api, требующие авторизацию
if(auth==true){
  switch($method){
    case 'tasks.create':
      $result = tasks__create();
      if($result['error']){
        $response['error'] = $result['error'];
        $response['response'] = 0;
      }else{
        $response['response'] = 1;
      }
      break;

    case 'tasks.get':
      $response['list'] = tasks__get();

      break;

    case 'tasks.execute':
      $result = task__execute();
      if($result['error']){
        $response['error'] = $result['error'];
        $response['response'] = 0;
      }else{
        $response['response'] = 1;
      }
      break;

    case 'users.getBalance':
      $result = users__get($_REQUEST['user_id']);
      $response['balance'] = $result['balance'];
      break;

      case 'transactions.history':
        $result = transactions__history($_REQUEST['user_id']);
        $response['list'] = $result;
        break;
  }
  //--
}


header("Content-type:application/json");
echo json_encode($response);

 ?>
