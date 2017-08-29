<?php

/*
  На вход дата в формате Y-m-d H:i:s
  на выход дата по русски, причесонная
*/
function ruDate($date){
  $timestamp = strtotime($date);
  $months = array(
    1 => 'янв',
    2 => 'фев',
    3 => 'мар',
    4 => 'апр',
    5 => 'мая',
    6 => 'июн',
    7 => 'июл',
    8 => 'авг',
    9 => 'сен',
    10 => 'окт',
    11 => 'ноя',
    12 => 'дек'
  );

  $ru = '';

  if(date('Y-m-d')==date('Y-m-d', $timestamp)){
    $ru .= 'Сегодня';
  }elseif(date('Y-m-d',(time()-(60*60*24)))==date('Y-m-d', $timestamp)){
    $ru .= 'Вчера';
  }else{
    $ru .= date('j', $timestamp).' '.$months[date('n',$timestamp)].' '.date('Y', $timestamp);
  }
  $ru .= ' в ';
  $ru .= date('H:i', $timestamp);

  return $ru;
}


 ?>
