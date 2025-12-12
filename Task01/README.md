# Описание структуры файлов данных

## Файлы данных

### movies.csv
Файл с информацией о фильмах.

| Поле | Тип | Описание |
|------|-----|----------|
| movieId | integer | Уникальный идентификатор фильма |
| title | string | Название фильма с годом выпуска в скобках |
| genres | string | Жанры фильма, разделённые символом `\|` |

**Пример записи:**
```
1,Toy Story (1995),Adventure|Animation|Children|Comedy|Fantasy
```

---

### ratings.csv
Файл с оценками фильмов от пользователей.

| Поле | Тип | Описание |
|------|-----|----------|
| userId | integer | Идентификатор пользователя |
| movieId | integer | Идентификатор фильма |
| rating | float | Оценка фильма (от 0.5 до 5.0 с шагом 0.5) |
| timestamp | integer | Временная метка (UNIX timestamp) |

**Пример записи:**
```
1,1,4.0,964982703
```

---

### tags.csv
Файл с пользовательскими тегами к фильмам.

| Поле | Тип | Описание |
|------|-----|----------|
| userId | integer | Идентификатор пользователя |
| movieId | integer | Идентификатор фильма |
| tag | string | Текстовый тег (ключевое слово) |
| timestamp | integer | Временная метка (UNIX timestamp) |

**Пример записи:**
```
2,60756,funny,1445714994
```

---

### users.txt
Файл с информацией о пользователях. Разделитель полей: `|`

| Поле | Тип | Описание |
|------|-----|----------|
| userId | integer | Уникальный идентификатор пользователя |
| name | string | Имя и фамилия пользователя |
| email | string | Электронная почта |
| gender | string | Пол (male/female) |
| birthdate | date | Дата рождения (YYYY-MM-DD) |
| occupation | string | Род занятий |

**Пример записи:**
```
1|Devonte Stamm|marianne.krajcik@bartoletti.com|male|2010-09-19|technician
```

---

### genres.txt
Список всех доступных жанров фильмов (по одному на строку):
- Action, Adventure, Animation, Children's, Comedy, Crime
- Documentary, Drama, Fantasy, Film-Noir, Horror, Musical
- Mystery, Romance, Sci-Fi, Thriller, War, Western

---

### occupation.txt
Список всех возможных родов занятий пользователей (по одному на строку):
- administrator, artist, doctor, educator, engineer
- entertainment, executive, healthcare, homemaker, lawyer
- librarian, marketing, none, other, programmer
- retired, salesman, scientist, student, technician, writer

---

## Связи между данными

```
users.txt (userId) ──┬──> ratings.csv (userId)
                     └──> tags.csv (userId)

movies.csv (movieId) ──┬──> ratings.csv (movieId)
                       └──> tags.csv (movieId)
```

## Статистика по файлам

| Файл | Количество записей |
|------|-------------------|
| movies.csv | 9743 фильма |
| ratings.csv | 18774 оценки |
| tags.csv | 3684 тега |
| users.txt | 943 пользователя |
| genres.txt | 18 жанров |
| occupation.txt | 21 профессия |
