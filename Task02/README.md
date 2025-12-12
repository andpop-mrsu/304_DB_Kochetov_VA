# Лабораторная работа 2. Подготовка скриптов для создания таблиц и добавления данных

## Описание

ETL-утилита для переноса исходных данных в базу данных SQLite.

## Требования к окружению

Для корректной работы скрипта `db_init.bat` необходимо:

1. **Python 3.x** (версия 3.6 или выше)
   - Проверка: `python3 --version` или `python --version`
   - Установка (Windows): https://www.python.org/downloads/
   - Установка (Linux): `sudo apt install python3`

2. **SQLite 3**
   - Проверка: `sqlite3 --version`
   - Установка (Windows): https://www.sqlite.org/download.html
   - Установка (Linux): `sudo apt install sqlite3`

3. **Добавление в PATH**
   - Python и SQLite должны быть доступны из командной строки

## Структура файлов

| Файл | Описание |
|------|----------|
| `make_db_init.py` | Python-скрипт для генерации SQL-скрипта |
| `db_init.bat` | Кроссплатформенный shell-скрипт запуска |
| `db_init.sql` | Сгенерированный SQL-скрипт (создаётся автоматически) |
| `movies_rating.db` | База данных SQLite (создаётся автоматически) |
| `movies.csv` | Исходные данные о фильмах |
| `ratings.csv` | Исходные данные об оценках |
| `tags.csv` | Исходные данные о тегах |
| `users.txt` | Исходные данные о пользователях |

## Запуск

### Windows (PowerShell / CMD)
```powershell
cd Task02
python make_db_init.py
sqlite3 movies_rating.db < db_init.sql
```

### Linux / macOS
```bash
cd Task02
chmod +x db_init.bat
./db_init.bat
```

## Структура базы данных

### Таблица `users`
| Поле | Тип | Описание |
|------|-----|----------|
| id | INTEGER PRIMARY KEY | Идентификатор пользователя |
| name | TEXT | Имя пользователя |
| email | TEXT | Email |
| gender | TEXT | Пол (male/female) |
| register_date | TEXT | Дата регистрации |
| occupation | TEXT | Род занятий |

### Таблица `movies`
| Поле | Тип | Описание |
|------|-----|----------|
| id | INTEGER PRIMARY KEY | Идентификатор фильма |
| title | TEXT | Название фильма |
| year | INTEGER | Год выпуска |
| genres | TEXT | Жанры (через \|) |

### Таблица `ratings`
| Поле | Тип | Описание |
|------|-----|----------|
| id | INTEGER PRIMARY KEY | Идентификатор записи |
| user_id | INTEGER | ID пользователя (FK) |
| movie_id | INTEGER | ID фильма (FK) |
| rating | REAL | Оценка (0.5-5.0) |
| timestamp | INTEGER | Временная метка |

### Таблица `tags`
| Поле | Тип | Описание |
|------|-----|----------|
| id | INTEGER PRIMARY KEY | Идентификатор записи |
| user_id | INTEGER | ID пользователя (FK) |
| movie_id | INTEGER | ID фильма (FK) |
| tag | TEXT | Тег |
| timestamp | INTEGER | Временная метка |

## Результат

После выполнения скрипта создаётся файл `movies_rating.db` с заполненными таблицами:
- `users` — 943 записи
- `movies` — 9743 записи  
- `ratings` — 18774 записи
- `tags` — 3684 записи
