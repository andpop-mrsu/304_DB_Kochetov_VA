-- SQL-скрипт для добавления новых данных
-- Лабораторная работа 5

PRAGMA foreign_keys = ON;

-- ============================================
-- Добавление 5 новых пользователей
-- (себя и 4 ближайших соседей по списку группы)
-- Дата регистрации определяется по системному времени
-- ============================================

-- 1. Кочетов В.А. (я)
INSERT INTO users (name, email, gender, register_date, occupation_id)
SELECT 'Vladislav Kochetov', 'kochetov.va@example.com', 'male', DATE('now'), o.id
FROM occupations o WHERE o.name = 'student';

-- 2. Сосед 1 по списку группы
INSERT INTO users (name, email, gender, register_date, occupation_id)
SELECT 'Ivan Ivanov', 'ivanov.i@example.com', 'male', DATE('now'), o.id
FROM occupations o WHERE o.name = 'student';

-- 3. Сосед 2 по списку группы
INSERT INTO users (name, email, gender, register_date, occupation_id)
SELECT 'Petr Petrov', 'petrov.p@example.com', 'male', DATE('now'), o.id
FROM occupations o WHERE o.name = 'student';

-- 4. Сосед 3 по списку группы
INSERT INTO users (name, email, gender, register_date, occupation_id)
SELECT 'Anna Sidorova', 'sidorova.a@example.com', 'female', DATE('now'), o.id
FROM occupations o WHERE o.name = 'student';

-- 5. Сосед 4 по списку группы
INSERT INTO users (name, email, gender, register_date, occupation_id)
SELECT 'Maria Kuznetsova', 'kuznetsova.m@example.com', 'female', DATE('now'), o.id
FROM occupations o WHERE o.name = 'student';

-- ============================================
-- Добавление 3 новых фильмов разных жанров
-- ============================================

-- Фильм 1: Боевик/Фантастика
INSERT INTO movies (title, year) VALUES ('The New Action Movie', 2024);
INSERT INTO movie_genres (movie_id, genre_id) 
SELECT (SELECT MAX(id) FROM movies), g.id FROM genres g WHERE g.name = 'Action';
INSERT INTO movie_genres (movie_id, genre_id) 
SELECT (SELECT MAX(id) FROM movies), g.id FROM genres g WHERE g.name = 'Sci-Fi';

-- Фильм 2: Комедия/Романтика
INSERT INTO movies (title, year) VALUES ('Love and Laughter', 2023);
INSERT INTO movie_genres (movie_id, genre_id) 
SELECT (SELECT MAX(id) FROM movies), g.id FROM genres g WHERE g.name = 'Comedy';
INSERT INTO movie_genres (movie_id, genre_id) 
SELECT (SELECT MAX(id) FROM movies), g.id FROM genres g WHERE g.name = 'Romance';

-- Фильм 3: Драма/Триллер
INSERT INTO movies (title, year) VALUES ('Dark Secrets', 2024);
INSERT INTO movie_genres (movie_id, genre_id) 
SELECT (SELECT MAX(id) FROM movies), g.id FROM genres g WHERE g.name = 'Drama';
INSERT INTO movie_genres (movie_id, genre_id) 
SELECT (SELECT MAX(id) FROM movies), g.id FROM genres g WHERE g.name = 'Thriller';

-- ============================================
-- Добавление 3 отзывов о новых фильмах от себя (Кочетов В.А.)
-- ============================================

-- Отзыв 1: на фильм "The New Action Movie"
INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT 
    (SELECT id FROM users WHERE email = 'kochetov.va@example.com'),
    (SELECT id FROM movies WHERE title = 'The New Action Movie'),
    4.5,
    STRFTIME('%s', 'now');

-- Отзыв 2: на фильм "Love and Laughter"
INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT 
    (SELECT id FROM users WHERE email = 'kochetov.va@example.com'),
    (SELECT id FROM movies WHERE title = 'Love and Laughter'),
    3.5,
    STRFTIME('%s', 'now');

-- Отзыв 3: на фильм "Dark Secrets"
INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT 
    (SELECT id FROM users WHERE email = 'kochetov.va@example.com'),
    (SELECT id FROM movies WHERE title = 'Dark Secrets'),
    5.0,
    STRFTIME('%s', 'now');

-- ============================================
-- Проверка добавленных данных
-- ============================================
-- SELECT * FROM users WHERE register_date = DATE('now');
-- SELECT m.*, GROUP_CONCAT(g.name) as genres FROM movies m 
--   JOIN movie_genres mg ON m.id = mg.movie_id 
--   JOIN genres g ON mg.genre_id = g.id 
--   WHERE m.year >= 2023 GROUP BY m.id;
-- SELECT * FROM ratings WHERE user_id = (SELECT id FROM users WHERE email = 'kochetov.va@example.com');
