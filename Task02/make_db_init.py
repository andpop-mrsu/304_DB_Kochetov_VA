#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
ETL-утилита для генерации SQL-скрипта db_init.sql
Создает таблицы и загружает данные из текстовых файлов в БД SQLite
"""

import csv
import os
import re

# Директория со скриптом
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))

# Файлы данных
MOVIES_FILE = os.path.join(SCRIPT_DIR, 'movies.csv')
RATINGS_FILE = os.path.join(SCRIPT_DIR, 'ratings.csv')
TAGS_FILE = os.path.join(SCRIPT_DIR, 'tags.csv')
USERS_FILE = os.path.join(SCRIPT_DIR, 'users.txt')

# Выходной SQL-файл
OUTPUT_FILE = os.path.join(SCRIPT_DIR, 'db_init.sql')


def escape_sql_string(value):
    """Экранирование строки для SQL (замена ' на '')"""
    if value is None:
        return 'NULL'
    return value.replace("'", "''")


def extract_year_from_title(title):
    """Извлечение года из названия фильма, например 'Toy Story (1995)' -> (1995, 'Toy Story')"""
    match = re.search(r'\((\d{4})\)\s*$', title)
    if match:
        year = int(match.group(1))
        clean_title = title[:match.start()].strip()
        return year, clean_title
    return None, title


def generate_drop_tables():
    """Генерация команд удаления таблиц"""
    return """-- Удаление существующих таблиц
DROP TABLE IF EXISTS ratings;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS movies;
DROP TABLE IF EXISTS users;

"""


def generate_create_tables():
    """Генерация команд создания таблиц"""
    return """-- Создание таблиц
CREATE TABLE users (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    gender TEXT NOT NULL,
    register_date TEXT NOT NULL,
    occupation TEXT NOT NULL
);

CREATE TABLE movies (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL,
    year INTEGER,
    genres TEXT
);

CREATE TABLE ratings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    movie_id INTEGER NOT NULL,
    rating REAL NOT NULL,
    timestamp INTEGER NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (movie_id) REFERENCES movies(id)
);

CREATE TABLE tags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    movie_id INTEGER NOT NULL,
    tag TEXT NOT NULL,
    timestamp INTEGER NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (movie_id) REFERENCES movies(id)
);

"""


def generate_users_inserts():
    """Генерация INSERT для таблицы users из users.txt"""
    sql = "-- Вставка данных в таблицу users\n"
    
    with open(USERS_FILE, 'r', encoding='utf-8') as f:
        for line in f:
            line = line.strip()
            if not line:
                continue
            
            parts = line.split('|')
            if len(parts) >= 6:
                user_id = parts[0]
                name = escape_sql_string(parts[1])
                email = escape_sql_string(parts[2])
                gender = escape_sql_string(parts[3])
                register_date = escape_sql_string(parts[4])
                occupation = escape_sql_string(parts[5])
                
                sql += f"INSERT INTO users (id, name, email, gender, register_date, occupation) VALUES ({user_id}, '{name}', '{email}', '{gender}', '{register_date}', '{occupation}');\n"
    
    return sql + "\n"


def generate_movies_inserts():
    """Генерация INSERT для таблицы movies из movies.csv"""
    sql = "-- Вставка данных в таблицу movies\n"
    
    with open(MOVIES_FILE, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            movie_id = row['movieId']
            title_with_year = row['title']
            genres = escape_sql_string(row['genres'])
            
            year, title = extract_year_from_title(title_with_year)
            title = escape_sql_string(title)
            
            if year:
                sql += f"INSERT INTO movies (id, title, year, genres) VALUES ({movie_id}, '{title}', {year}, '{genres}');\n"
            else:
                sql += f"INSERT INTO movies (id, title, year, genres) VALUES ({movie_id}, '{title}', NULL, '{genres}');\n"
    
    return sql + "\n"


def generate_ratings_inserts():
    """Генерация INSERT для таблицы ratings из ratings.csv"""
    sql = "-- Вставка данных в таблицу ratings\n"
    
    with open(RATINGS_FILE, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            user_id = row['userId']
            movie_id = row['movieId']
            rating = row['rating']
            timestamp = row['timestamp']
            
            sql += f"INSERT INTO ratings (user_id, movie_id, rating, timestamp) VALUES ({user_id}, {movie_id}, {rating}, {timestamp});\n"
    
    return sql + "\n"


def generate_tags_inserts():
    """Генерация INSERT для таблицы tags из tags.csv"""
    sql = "-- Вставка данных в таблицу tags\n"
    
    with open(TAGS_FILE, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            user_id = row['userId']
            movie_id = row['movieId']
            tag = escape_sql_string(row['tag'])
            timestamp = row['timestamp']
            
            sql += f"INSERT INTO tags (user_id, movie_id, tag, timestamp) VALUES ({user_id}, {movie_id}, '{tag}', {timestamp});\n"
    
    return sql + "\n"


def main():
    """Главная функция - генерация SQL-скрипта"""
    print("Генерация SQL-скрипта db_init.sql...")
    
    sql_content = ""
    sql_content += "-- SQL-скрипт для создания и заполнения базы данных movies_rating.db\n"
    sql_content += "-- Сгенерировано автоматически утилитой make_db_init.py\n\n"
    
    # Удаление старых таблиц
    sql_content += generate_drop_tables()
    
    # Создание таблиц
    sql_content += generate_create_tables()
    
    # Начало транзакции для ускорения вставки
    sql_content += "BEGIN TRANSACTION;\n\n"
    
    # Вставка данных
    print("  Обработка users.txt...")
    sql_content += generate_users_inserts()
    
    print("  Обработка movies.csv...")
    sql_content += generate_movies_inserts()
    
    print("  Обработка ratings.csv...")
    sql_content += generate_ratings_inserts()
    
    print("  Обработка tags.csv...")
    sql_content += generate_tags_inserts()
    
    # Завершение транзакции
    sql_content += "COMMIT;\n"
    
    # Запись в файл
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
        f.write(sql_content)
    
    print(f"SQL-скрипт успешно создан: {OUTPUT_FILE}")


if __name__ == '__main__':
    main()
