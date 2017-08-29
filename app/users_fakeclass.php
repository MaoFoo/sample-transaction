<?php

/*
  получает все учётные записи
  список, в котором ассоц.массив с данными пользователей
*/
function users__getall(){
  $result = mysql_query("SELECT * FROM `users`");
  $rows = array();
  while($row = mysql_fetch_assoc($result)){
    $rows[] = $row;
  }
  return $rows;
}


/*
  Получает пользователя по id
  Возвращает ассоац. массив с данными
  Если пользователь не найден - false
*/
function users__get($user_id){
  $user_id = intval($user_id);
  $result = mysql_query("SELECT * FROM `users` WHERE `user_id` = '$user_id'");
  $row = false;
  $row = mysql_fetch_assoc($result);

  return $row;
}


/*
   Получает по массиву идентификаторов пользователей
   массив их имён, где ключ их id.
*/
function users__getNames($user_ids){
  $ids = array();
  if(!is_array($user_ids) || count($user_ids)==0){
    return array();
  }
  foreach($user_ids as $id){
    $id = intval($id);
    if($id!=0)
      $ids[$id] = $id;
  }
  $ids = implode(',',$ids);
  if(!$ids) return array();

  $rows = array();
  $result = mysql_query("SELECT `user_id`,`name` FROM `users` WHERE `user_id` IN ($ids)");
  while($row = mysql_fetch_assoc($result)){
    $rows[$row['user_id']] = $row['name'];
  }
  return $rows;
}



 ?>
