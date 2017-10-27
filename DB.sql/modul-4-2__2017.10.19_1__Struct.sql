-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Окт 19 2017 г., 12:15
-- Версия сервера: 5.7.16
-- Версия PHP: 5.6.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `modul-4-2`
--

-- --------------------------------------------------------

--
-- Структура таблицы `articles`
--

CREATE TABLE `articles` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `author` varchar(255) DEFAULT NULL,
  `text` text,
  `is_published` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `date_created` varchar(255) DEFAULT NULL,
  `date_published` varchar(255) DEFAULT NULL,
  `tags` text,
  `category` varchar(255) DEFAULT NULL,
  `visited` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `comments_count` int(5) UNSIGNED NOT NULL DEFAULT '0',
  `price` float UNSIGNED DEFAULT NULL,
  `action_type` varchar(255) DEFAULT NULL,
  `action_price` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `banners`
--

CREATE TABLE `banners` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` tinyint(3) UNSIGNED NOT NULL,
  `firm` varchar(255) NOT NULL,
  `image_name` varchar(255) NOT NULL,
  `code_discaunt` varchar(255) NOT NULL,
  `discaunt_percent` tinyint(3) UNSIGNED NOT NULL,
  `position` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `category_name` varchar(255) NOT NULL,
  `displayed` tinyint(3) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `categories_of_article`
--

CREATE TABLE `categories_of_article` (
  `id_article` int(10) UNSIGNED NOT NULL,
  `id_category` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

CREATE TABLE `comments` (
  `id_comment` int(10) UNSIGNED NOT NULL,
  `id_parent_comment` int(10) UNSIGNED DEFAULT NULL,
  `nested_level` int(11) NOT NULL DEFAULT '0',
  `id_article` int(10) UNSIGNED DEFAULT NULL,
  `id_user` int(10) UNSIGNED DEFAULT NULL,
  `text` text,
  `date` datetime DEFAULT NULL,
  `is_published` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `like_ok` text,
  `dislike` int(10) UNSIGNED DEFAULT NULL,
  `not_recommend` tinyint(2) DEFAULT '0',
  `recommend` tinyint(2) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `dislikes`
--

CREATE TABLE `dislikes` (
  `dislike_comment` int(10) UNSIGNED NOT NULL,
  `dislike_user` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Структура таблицы `images_of_article`
--

CREATE TABLE `images_of_article` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_article` int(10) UNSIGNED NOT NULL,
  `num` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `likes`
--

CREATE TABLE `likes` (
  `like_comment` int(10) UNSIGNED NOT NULL,
  `like_user` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Структура таблицы `menu`
--

CREATE TABLE `menu` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_parent` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `menu_name` varchar(255) DEFAULT NULL,
  `menu_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `messages`
--

CREATE TABLE `messages` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `options_of_product`
--

CREATE TABLE `options_of_product` (
  `id_of_product` int(10) UNSIGNED DEFAULT NULL,
  `id_of_specification` int(10) UNSIGNED DEFAULT NULL,
  `id_of_option` int(10) UNSIGNED DEFAULT NULL,
  `custom_value_text` varchar(255) DEFAULT NULL,
  `custom_value_numeric` float DEFAULT NULL,
  `custom_value_timestamp` int(17) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `options_of_specification`
--

CREATE TABLE `options_of_specification` (
  `option_id` int(10) UNSIGNED NOT NULL,
  `specificat_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `option_value_text` varchar(255) DEFAULT NULL,
  `option_value_numeric` float DEFAULT NULL,
  `option_value_timestamp` int(17) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `order_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_count` int(11) NOT NULL,
  `sum_total` int(11) NOT NULL,
  `order_date` int(11) NOT NULL,
  `status` varchar(255) CHARACTER SET utf32 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `pages`
--

CREATE TABLE `pages` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `alias` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text,
  `is_published` tinyint(1) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `specifications`
--

CREATE TABLE `specifications` (
  `specification_id` int(10) UNSIGNED NOT NULL,
  `specification_name` varchar(255) DEFAULT NULL,
  `specification_type` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `specifications_of_category`
--

CREATE TABLE `specifications_of_category` (
  `id_of_specification` int(10) UNSIGNED NOT NULL,
  `id_of_category` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `tags`
--

CREATE TABLE `tags` (
  `id_article` int(10) UNSIGNED NOT NULL,
  `tag` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `top_menu`
--

CREATE TABLE `top_menu` (
  `id` int(10) UNSIGNED NOT NULL,
  `text` varchar(255) NOT NULL,
  `parent_id` int(10) UNSIGNED NOT NULL,
  `uri` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` smallint(3) UNSIGNED NOT NULL,
  `login` varchar(45) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(45) NOT NULL DEFAULT 'admin',
  `password` char(32) NOT NULL,
  `is_active` tinyint(1) UNSIGNED DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Индексы таблицы `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `categories_of_article`
--
ALTER TABLE `categories_of_article`
  ADD KEY `id_article` (`id_article`),
  ADD KEY `id_category` (`id_category`);

--
-- Индексы таблицы `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id_comment`),
  ADD KEY `id_article` (`id_article`);

--
-- Индексы таблицы `dislikes`
--
ALTER TABLE `dislikes`
  ADD KEY `like_comment` (`dislike_comment`);

--
-- Индексы таблицы `images_of_article`
--
ALTER TABLE `images_of_article`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_article` (`id_article`);

--
-- Индексы таблицы `likes`
--
ALTER TABLE `likes`
  ADD KEY `like_comment` (`like_comment`);

--
-- Индексы таблицы `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `options_of_product`
--
ALTER TABLE `options_of_product`
  ADD KEY `id_of_product` (`id_of_product`),
  ADD KEY `id_of_options` (`id_of_option`),
  ADD KEY `id_of_specification` (`id_of_specification`);

--
-- Индексы таблицы `options_of_specification`
--
ALTER TABLE `options_of_specification`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `specificat_id` (`specificat_id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Индексы таблицы `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `specifications`
--
ALTER TABLE `specifications`
  ADD PRIMARY KEY (`specification_id`);

--
-- Индексы таблицы `specifications_of_category`
--
ALTER TABLE `specifications_of_category`
  ADD KEY `id_of_specification` (`id_of_specification`),
  ADD KEY `id_of_category` (`id_of_category`) USING BTREE;

--
-- Индексы таблицы `tags`
--
ALTER TABLE `tags`
  ADD KEY `id_article` (`id_article`);

--
-- Индексы таблицы `top_menu`
--
ALTER TABLE `top_menu`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=942;
--
-- AUTO_INCREMENT для таблицы `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;
--
-- AUTO_INCREMENT для таблицы `comments`
--
ALTER TABLE `comments`
  MODIFY `id_comment` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10538;
--
-- AUTO_INCREMENT для таблицы `images_of_article`
--
ALTER TABLE `images_of_article`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2232;
--
-- AUTO_INCREMENT для таблицы `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
--
-- AUTO_INCREMENT для таблицы `messages`
--
ALTER TABLE `messages`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `options_of_specification`
--
ALTER TABLE `options_of_specification`
  MODIFY `option_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;
--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `pages`
--
ALTER TABLE `pages`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `specifications`
--
ALTER TABLE `specifications`
  MODIFY `specification_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT для таблицы `top_menu`
--
ALTER TABLE `top_menu`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` smallint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;
--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `categories_of_article`
--
ALTER TABLE `categories_of_article`
  ADD CONSTRAINT `categories_of_article_ibfk_1` FOREIGN KEY (`id_article`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `categories_of_article_ibfk_2` FOREIGN KEY (`id_category`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`id_article`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `dislikes`
--
ALTER TABLE `dislikes`
  ADD CONSTRAINT `dislikes_ibfk_1` FOREIGN KEY (`dislike_comment`) REFERENCES `comments` (`id_comment`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `images_of_article`
--
ALTER TABLE `images_of_article`
  ADD CONSTRAINT `images_of_article_ibfk_1` FOREIGN KEY (`id_article`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`like_comment`) REFERENCES `comments` (`id_comment`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `options_of_product`
--
ALTER TABLE `options_of_product`
  ADD CONSTRAINT `options_of_product_ibfk_1` FOREIGN KEY (`id_of_product`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `options_of_product_ibfk_3` FOREIGN KEY (`id_of_specification`) REFERENCES `specifications` (`specification_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `options_of_specification`
--
ALTER TABLE `options_of_specification`
  ADD CONSTRAINT `options_of_specification_ibfk_1` FOREIGN KEY (`specificat_id`) REFERENCES `specifications` (`specification_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `specifications_of_category`
--
ALTER TABLE `specifications_of_category`
  ADD CONSTRAINT `specifications_of_category_ibfk_3` FOREIGN KEY (`id_of_category`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `specifications_of_category_ibfk_4` FOREIGN KEY (`id_of_specification`) REFERENCES `specifications` (`specification_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tags`
--
ALTER TABLE `tags`
  ADD CONSTRAINT `tags_ibfk_1` FOREIGN KEY (`id_article`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
