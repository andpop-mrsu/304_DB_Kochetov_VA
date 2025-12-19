#!/bin/bash
chcp 65001

sqlite3 movies_rating.db < db_init.sql

echo "1. Найти все пары пользователей, оценивших один и тот же фильм. Устранить дубликаты, проверить отсутствие пар с самим собой. Для каждой пары должны быть указаны имена пользователей и название фильма, который они оценили. В списке оставить первые 100 записей."
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT DISTINCT u1.name AS user1, u2.name AS user2, m.title AS movie FROM ratings r1 JOIN ratings r2 ON r1.movie_id = r2.movie_id AND r1.user_id < r2.user_id JOIN users u1 ON r1.user_id = u1.id JOIN users u2 ON r2.user_id = u2.id JOIN movies m ON r1.movie_id = m.id ORDER BY m.title, u1.name, u2.name LIMIT 100;"
echo " "

echo "2. Найти 10 самых старых оценок от разных пользователей, вывести названия фильмов, имена пользователей, оценку, дату отзыва в формате ГГГГ-ММ-ДД."
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT m.title AS movie, u.name AS user, r.rating, DATE(r.timestamp, 'unixepoch') AS review_date FROM ratings r JOIN users u ON r.user_id = u.id JOIN movies m ON r.movie_id = m.id GROUP BY r.user_id ORDER BY r.timestamp ASC LIMIT 10;"
echo " "

echo "3. Вывести в одном списке все фильмы с максимальным средним рейтингом и все фильмы с минимальным средним рейтингом. Общий список отсортировать по году выпуска и названию фильма. В зависимости от рейтинга в колонке Рекомендуем для фильмов должно быть написано Да или Нет."
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "WITH avg_ratings AS (SELECT m.id, m.title, m.year, AVG(r.rating) AS avg_rating FROM movies m JOIN ratings r ON m.id = r.movie_id GROUP BY m.id), max_min AS (SELECT MAX(avg_rating) AS max_rating, MIN(avg_rating) AS min_rating FROM avg_ratings) SELECT ar.title, ar.year, ROUND(ar.avg_rating, 2) AS avg_rating, CASE WHEN ar.avg_rating = mm.max_rating THEN 'Да' ELSE 'Нет' END AS 'Рекомендуем' FROM avg_ratings ar, max_min mm WHERE ar.avg_rating = mm.max_rating OR ar.avg_rating = mm.min_rating ORDER BY ar.year, ar.title;"
echo " "

echo "4. Вычислить количество оценок и среднюю оценку, которую дали фильмам пользователи-мужчины в период с 2011 по 2014 год."
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT COUNT(*) AS total_ratings, ROUND(AVG(r.rating), 2) AS avg_rating FROM ratings r JOIN users u ON r.user_id = u.id WHERE u.gender = 'male' AND DATE(r.timestamp, 'unixepoch') BETWEEN '2011-01-01' AND '2014-12-31';"
echo " "

echo "5. Составить список фильмов с указанием средней оценки и количества пользователей, которые их оценили. Полученный список отсортировать по году выпуска и названиям фильмов. В списке оставить первые 20 записей."
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT m.title, m.year, ROUND(AVG(r.rating), 2) AS avg_rating, COUNT(DISTINCT r.user_id) AS users_count FROM movies m JOIN ratings r ON m.id = r.movie_id GROUP BY m.id ORDER BY m.year, m.title LIMIT 20;"
echo " "

echo "6. Определить самый распространенный жанр фильма и количество фильмов в этом жанре. Отдельную таблицу для жанров не использовать, жанры нужно извлекать из таблицы movies."
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "WITH RECURSIVE split(id, genre, rest) AS (SELECT id, '', genres FROM movies WHERE genres IS NOT NULL UNION ALL SELECT id, SUBSTR(rest, 0, CASE WHEN INSTR(rest,'|')>0 THEN INSTR(rest,'|') ELSE LENGTH(rest)+1 END), SUBSTR(rest, CASE WHEN INSTR(rest,'|')>0 THEN INSTR(rest,'|')+1 ELSE LENGTH(rest)+1 END) FROM split WHERE rest != '') SELECT genre, COUNT(DISTINCT id) AS movie_count FROM split WHERE genre != '' GROUP BY genre ORDER BY movie_count DESC LIMIT 1;"
echo " "

echo "7. Вывести список из 10 последних зарегистрированных пользователей в формате Фамилия Имя|Дата регистрации (сначала фамилия, потом имя)."
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT SUBSTR(name, INSTR(name, ' ') + 1) || ' ' || SUBSTR(name, 1, INSTR(name, ' ') - 1) || '|' || register_date AS 'Фамилия Имя|Дата регистрации' FROM users ORDER BY register_date DESC LIMIT 10;"
echo " "

echo "8. С помощью рекурсивного CTE определить, на какие дни недели приходился ваш день рождения в каждом году."
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "WITH RECURSIVE birthday_years AS (SELECT 2004 AS year UNION ALL SELECT year + 1 FROM birthday_years WHERE year < 2025) SELECT year, DATE(year || '-06-15') AS birthday, CASE CAST(STRFTIME('%w', year || '-06-15') AS INTEGER) WHEN 0 THEN 'Воскресенье' WHEN 1 THEN 'Понедельник' WHEN 2 THEN 'Вторник' WHEN 3 THEN 'Среда' WHEN 4 THEN 'Четверг' WHEN 5 THEN 'Пятница' WHEN 6 THEN 'Суббота' END AS day_of_week FROM birthday_years;"
echo " "