<?php

/*
  Создаёт задачу, выполняя необходимые проверки
*/
function tasks__create(){
  $user_id = intval($_REQUEST['user_id']);

  $name = trim($_POST['name']);
  $name = htmlentities((string)$name, ENT_QUOTES, ini_get("default_charset"), false);
  $name = mysql_real_escape_string($name);
  $price = intval($_POST['price']);

  // проверки, которые были сделаны на js
  if(!(strlen($name)>=5 && $price>=10)){
    return array('error'=>'Данные не соответствуют');
  }

  $user = users__get($user_id);
  if(!($user['balance']>=$price)){
    return array('error'=>'Нет средств на балансе, для создания этой задачи');
  }
  if(!($user['balance']-$user['frozen']>=$price)){
    return array('error'=>'Доступные средства заморожены под другие задачи');
  }

  // замораживаем деньги
  mysql_query("UPDATE `users` SET `frozen` = `frozen`+{$price} WHERE `user_id`='{$user_id}' AND `balance`>=`frozen`+{$price}");
  if(mysql_affected_rows()!=1){
    return array('error'=>'Не удалось заморозить средства');
  }

  $date = date('Y-m-d H:i:s');
  mysql_query("INSERT INTO `tasks` (`owner_id`, `price`, `name`, `date`) VALUES ('{$user_id}','{$price}','{$name}', '{$date}')");
  if(mysql_affected_rows()!=1){
    // в этом случае деньги остались заморожены, но их можно разморозить крон-скриптом
    return array('error'=>'Не удалось добавить задачу, попробуйте позже');
  }

  return array('response'=>1);

}


function tasks__get(){
  $user_id = intval($_REQUEST['user_id']);

  $type = ($_REQUEST['type']=='my')?'my':'available';

  $query = "SELECT `task_id`,`owner_id`, `price`, `name`, `date` FROM `tasks` WHERE ";
  if($type=='my'){
    $query.= " owner_id='$user_id' AND `status`=0";
  }
  if($type=='available'){
    $query.= " owner_id!='$user_id' AND `status`=0";
  }
  $rows = array();
  $result = mysql_query($query);
  $user_ids = array();
  while($row = mysql_fetch_assoc($result)){
    $user_ids[] = $row['owner_id'];
    $row['ru_date'] = ruDate($row['date']);
    if($type=='available'){
      $row['price'] = intval($row['price']-($row['price']/10));// показываем столько реально будет зачислено
    }
    $rows[] = $row;
  }
  $user_ids = users__getNames($user_ids);
  foreach($rows as $k=>$row){
    $rows[$k]['owner_name'] = $user_ids[$row['owner_id']];
  }
  return $rows;
}

function tasks__getbyid($task_id){
  $task_id = intval($task_id);
  $result = mysql_query("SELECT * FROM `tasks` WHERE `task_id`='{$task_id}'");
  $task = mysql_fetch_assoc($result);
  return $task;
}

//Получить список тасков по ids
function tasks__getbyids($task_ids){
  $ids = array();
  if(!is_array($task_ids) || count($task_ids)==0){
    return array();
  }
  foreach($task_ids as $id){
    $id = intval($id);
    if($id!=0)
      $ids[$id] = $id;
  }
  $ids = implode(',',$ids);
  if(!$ids) return array();

  $result = mysql_query("SELECT * FROM `tasks` WHERE `task_id` in ($ids)");
  $rows = array();
  while($row = mysql_fetch_assoc($result)){
    $rows[$row['task_id']] = $row;
  }
  return $rows;
}

function task__execute(){
  $user_id = intval($_REQUEST['user_id']);
  $task_id = intval($_POST['task_id']);

  $task = tasks__getbyid($task_id);

  if(!$task){
    return array('error'=>'Такая задача не найдена');
  }
  if($task['owner_id'] == $user_id){
    return array('error'=>'Свою задачу выполнить нельзя');
  }

  // Ставим Look на эту задачу.
  mysql_query("UPDATE `tasks` SET `status`='1', `executor_id`='{$user_id}' WHERE `task_id`='{$task_id}' AND `status`='0'");
  if(mysql_affected_rows()!=1){
    return array('error'=> 'Не удалось выполнить задачу, вероятно она уже занята кем-то');
  }

  $owner_id = $task['owner_id'];
  $executor_id = $user_id;
  $owner = users__get($owner_id);
  $origin_owner_balance = $owner['balance'];
  $executor = users__get($executor_id );
  $origin_executor_balance = $executor['balance'];
  $date = date('Y-m-d H:i:s');

  $query = "INSERT INTO `transactions`";
  $query.=" (`owner_id`, `executor_id`, `task_id`, `origin_owner_balance`, `origin_executor_balance`, `transaction_status`, `date`)";
  $query.=" VALUES ('$owner_id', '$executor_id', '$task_id', '$origin_owner_balance', '$origin_executor_balance', '0', '$date')";
  mysql_query($query);
  if(mysql_affected_rows()!=1){
    return array('error'=>'Не удалось записать транзкацию. Далее транзакция завершится чуть позже в автоматическом режиме');
  }
  $transaction_id = mysql_insert_id();

  // снимаем деньги у владельца
  $price = $task['price'];
  mysql_query("UPDATE `users` SET `balance`=`balance`-$price, `frozen`=`frozen`-$price WHERE `user_id`='$owner_id'");
  if(mysql_affected_rows()!=1){
    return array('error'=>'Не удалось списать деньги с баланса заказчика. Ожидайте завершения транзакции позже в автоматическом режиме');
  }

  // отмечаем в транзакции, что у заказчика деньги сняли
  mysql_query("UPDATE `transactions` SET `transaction_status`='1' WHERE `transaction_id`='$transaction_id'");
  if(mysql_affected_rows()!=1){
    return array('error'=>'Не удалось записать статус транзакции = 1');
  }

  // начисляем деньги испольнителю
  $price_add = intval($price - ($price/10)); // вычитаем 10% комисии системы. Округляем, пока копейки не поддерживаем.
  mysql_query("UPDATE `users` SET `balance`=`balance`+$price_add WHERE `user_id`='$executor_id'");
  if(mysql_affected_rows()!=1){
    return array('error'=>'Не удалось начислить деньги на баланса исполнителя.');
  }

  // отмечаем в транзакции, что у заказчика деньги сняли
  mysql_query("UPDATE `transactions` SET `transaction_status`='2' WHERE `transaction_id`='$transaction_id'");
  if(mysql_affected_rows()!=1){
    return array('error'=>'Не удалось записать статус транзакции = 2');
  }

  // отмечаем задачу выполненной
  mysql_query("UPDATE `tasks` SET `status`='2' WHERE `task_id`='$task_id'");
  if(mysql_affected_rows()!=1){
    return array('error'=>'Не удалось отметить задачу выполненной');
  }

  // отмечаем транзацию полностью завершенной.
  mysql_query("UPDATE `transactions` SET `transaction_status`='3' WHERE `transaction_id`='$transaction_id'");
  if(mysql_affected_rows()!=1){
    return array('error'=>'Не удалось записать статус транзакции = 3');
  }


  // ура, всё окей.
  return array('response'=>1);
}



 ?>
