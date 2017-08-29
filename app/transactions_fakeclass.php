<?php

function transactions__history($user_id){
  $user_id = intval($user_id);

  $result = mysql_query("SELECT * FROM `transactions` WHERE `owner_id`='$user_id' OR `executor_id`='$user_id'");
  $rows = array();
  $user_ids = array();
  $task_ids = array();

  while($row = mysql_fetch_assoc($result)){
    $rows[] = $row;

    $user_ids[$row['owner_id']] = $row['owner_id'];
    $user_ids[$row['executor_id']] = $row['executor_id'];
    $task_ids[$row['task_id']] = $row['task_id'];


  }
  $user_ids = users__getNames($user_ids);
  $tasks = tasks__getbyids($task_ids);

  $newrows = array();// причесанный к выводу архив транзакций
  foreach($rows as $k=>$row){
    $newrow = array();
    $newrow['price'] = $tasks[$row['task_id']]['price'];

    if($user_id == $row['owner_id']){
      // если владелец, то снятие денег
      $newrow['color'] = 'red';
      $newrow['price'] = '-'.$newrow['price'];

      $newrow['user_id'] = $row['executor_id'];
      $newrow['user_name'] = $user_ids[$row['executor_id']];
    }else{
      $newrow['color'] = 'green';
      $newrow['price'] = '+'.intval($newrow['price']-($newrow['price']/10));//за вычетом комиссии системы

      $newrow['user_id'] = $row['owner_id'];
      $newrow['user_name'] = $user_ids[$row['owner_id']];
    }
    $newrow['task_name'] = $tasks[$row['task_id']]['name'];
    $newrow['transaction_status'] = $row['transaction_status'];
    $newrow['transaction_status_ru'] = ($row['transaction_status']==3)?'Выполнено':'В процессе';
    $newrow['date'] = ruDate($row['date']);

    $newrows[] = $newrow;
  }




  return $newrows;
}


 ?>
