-- phpMyAdmin SQL Dump
-- version 4.6.6
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Авг 29 2017 г., 15:50
-- Версия сервера: 5.5.52-38.3
-- Версия PHP: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `cw76594_poligon`
--

-- --------------------------------------------------------

--
-- Структура таблицы `tasks`
--

CREATE TABLE IF NOT EXISTS `tasks` (
  `task_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `name` varchar(511) NOT NULL,
  `executor_id` int(11) NOT NULL,
  `status` int(11) NOT NULL COMMENT '0:available, 1:look, 2:complete',
  `date` datetime NOT NULL,
  PRIMARY KEY (`task_id`),
  KEY `owner_id` (`owner_id`),
  KEY `executor_id` (`executor_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `tasks`
--

INSERT INTO `tasks` (`task_id`, `owner_id`, `price`, `name`, `executor_id`, `status`, `date`) VALUES
(1, 3, 30, 'Украсть любую принцессу', 1, 2, '2017-08-27 17:57:18'),
(2, 1, 10, 'Спасти гору БУ', 2, 2, '2017-08-27 21:06:14'),
(3, 3, 100, 'Узнать где живет Джейк', 1, 2, '2017-08-27 21:12:41'),
(4, 3, 300, 'Поймать пингвина', 1, 2, '2017-08-27 21:19:21'),
(5, 3, 20, 'Наворовать яблок из сада', 1, 2, '2017-08-28 01:04:23'),
(6, 3, 40, 'Лепить снеговиков в форме пингвинов', 2, 2, '2017-08-28 01:12:04'),
(7, 1, 43, 'Спасти принцессу', 4, 2, '2017-08-28 01:46:02'),
(8, 3, 45, 'Поймать пингвина', 6, 2, '2017-08-28 01:56:10'),
(9, 6, 20, 'Найти красных яблок', 7, 2, '2017-08-28 01:57:15'),
(10, 1, 10, 'Найти секретный проход', 4, 2, '2017-08-28 02:41:31'),
(11, 5, 50, 'Тестт', 1, 2, '2017-08-28 08:35:18'),
(12, 6, 20, 'Купить вина', 7, 2, '2017-08-29 11:19:03'),
(13, 1, 50, 'Проследить по следу в тёмном лесу', 5, 2, '2017-08-29 13:26:39'),
(14, 1, 50, 'Купить сладкую вату', 6, 2, '2017-08-29 13:32:11'),
(15, 2, 40, 'Узнать секрет Снежного короля', 0, 0, '2017-08-29 15:25:50');

-- --------------------------------------------------------

--
-- Структура таблицы `transactions`
--

CREATE TABLE IF NOT EXISTS `transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `executor_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `origin_owner_balance` int(11) NOT NULL,
  `origin_executor_balance` int(11) NOT NULL,
  `transaction_status` int(11) NOT NULL COMMENT '0:new, 1:complete subtract money, 2: complete add money, 3: transaction complete (close task)',
  `date` datetime NOT NULL,
  PRIMARY KEY (`transaction_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `owner_id`, `executor_id`, `task_id`, `origin_owner_balance`, `origin_executor_balance`, `transaction_status`, `date`) VALUES
(1, 3, 1, 1, 959, 10, 3, '2017-08-28 02:01:00'),
(2, 3, 1, 3, 929, 37, 3, '2017-08-28 02:12:00'),
(3, 3, 1, 4, 829, 127, 3, '2017-08-28 02:15:00'),
(4, 3, 1, 5, 529, 397, 3, '2017-08-28 02:17:00'),
(5, 1, 4, 7, 415, 0, 3, '2017-08-28 02:19:00'),
(6, 1, 2, 2, 372, 36, 3, '2017-08-28 02:21:00'),
(7, 3, 2, 6, 509, 45, 3, '2017-08-28 02:22:00'),
(8, 3, 6, 8, 469, 0, 3, '2017-08-28 02:30:00'),
(9, 6, 7, 9, 40, 0, 3, '2017-08-28 02:40:00'),
(10, 1, 4, 10, 362, 38, 3, '2017-08-28 02:42:00'),
(11, 5, 1, 11, 50, 352, 3, '2017-08-28 08:35:59'),
(12, 6, 7, 12, 20, 18, 3, '2017-08-29 11:19:16'),
(13, 1, 5, 13, 397, 0, 3, '2017-08-29 13:26:50'),
(14, 1, 6, 14, 347, 0, 3, '2017-08-29 13:32:20');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `balance` int(11) NOT NULL,
  `frozen` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`user_id`, `name`, `balance`, `frozen`) VALUES
(1, 'Финн', 297, -10),
(2, 'Джейк', 81, 40),
(3, 'Снежный король', 424, -490),
(4, 'Принцесса Бубльгум', 47, 0),
(5, 'БиМО', 45, 0),
(6, 'Марселин', 45, 0),
(7, 'Мятный лакей', 36, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
