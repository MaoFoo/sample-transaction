# sample-transaction

Проект для конкурса.

Live demo: http://poligon.websovet.ru/sample-transaction/

Функционал:
- Залогиниться под любым из участников
- Просмотреть созданные тобой и еще не выполненные задания
- Посмотреть доступные для выполнения задания
  (свои, естественно выполнтить нельзя)
  Задание для выполнения сразу опубликованы за вычетом комиссии системы (10%)
- Можно открыть задание и нажать выполнить. 
  Запускатеся механизм транзакций и происходит снятие денег и зачисление. (описано далее)
- Есть механизм создания заданий.
  Средства при создании задания резервируются, чтобы было нельзя создать больше заданий, чем сейчас есть денег.
- Можно нажать на баланс и посмотреть историю транзакций

Транзакции:
Алгоритм транзакций устроен так, что если в какой-то момент упадёт одна из баз - то у нас есть все данные для восстановления.
Алгоритм:
- Ставим флаг на задачу, заблокировано и отмечаем в ней исполнителя.
- Записываем транзакцию, в которой фиксируем данные 
  (заказчика, испонителя, id задачи, балансы заказчика и исполнителя до транзакций, статус транзакции)
- Списываем деньги с владельца (с основного счета и замороженого)
- Отмечаем в транзакции, что деньги списаны
- Зачисляем деньги исполнителю, за вычетом комиссии системы.
- Отмечаем в транзакции, что деньги зачислены.
- Меняем статус задачи, что она теперь исполнена.
- Меняем статус транзакции, что транзакция исполнена.

В реализации транзакций не была использована технология Memcache, все данные пишутся на диск.
Запросы все простые и подразумевают лёгкое добавление $server_or_table_postfix = $user_id % $count_server; или аналога.


Для запуска:
- Скопировать в web-директорию в папку /sample-transaction/
- Импортнуть базу /app/cw76594_poligon.sql и поменять доступ к бд в index.php
- ??
- Profit


Спасибо за внимание.
:alarm_clock: 23h :man_with_turban: 1
