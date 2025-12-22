-- ============================================================================
-- Database: Barbershop Management System
-- Purpose: Automation of barbershop operations including staff management,
--          service catalog, appointments, completed works, and salary calculation
-- ============================================================================

-- Drop existing tables in correct order (respecting foreign key dependencies)
DROP TABLE IF EXISTS completed_works;
DROP TABLE IF EXISTS appointment_services;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS master_schedules;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS masters;
DROP TABLE IF EXISTS clients;

-- ============================================================================
-- REFERENCE TABLES
-- ============================================================================

-- Masters (staff members)
CREATE TABLE masters (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    phone TEXT NOT NULL UNIQUE,
    email TEXT UNIQUE,
    gender TEXT NOT NULL CHECK(gender IN ('M', 'F')),
    specialization TEXT NOT NULL CHECK(specialization IN ('men', 'women', 'universal')),
    salary_percent INTEGER NOT NULL CHECK(salary_percent > 0 AND salary_percent <= 100),
    hire_date TEXT NOT NULL DEFAULT (DATE('now')),
    fire_date TEXT DEFAULT NULL,
    is_active INTEGER NOT NULL DEFAULT 1 CHECK(is_active IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT (DATETIME('now')),
    CONSTRAINT valid_dates CHECK(fire_date IS NULL OR fire_date >= hire_date)
);

-- Services catalog
CREATE TABLE services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    duration_minutes INTEGER NOT NULL CHECK(duration_minutes > 0),
    price REAL NOT NULL CHECK(price > 0),
    gender_type TEXT NOT NULL CHECK(gender_type IN ('men', 'women', 'universal')),
    is_active INTEGER NOT NULL DEFAULT 1 CHECK(is_active IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT (DATETIME('now'))
);

-- Clients
CREATE TABLE clients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    phone TEXT NOT NULL UNIQUE,
    email TEXT UNIQUE,
    gender TEXT NOT NULL CHECK(gender IN ('M', 'F')),
    registered_at TEXT NOT NULL DEFAULT (DATETIME('now'))
);

-- ============================================================================
-- OPERATIONAL TABLES
-- ============================================================================

-- Master schedules (working hours)
CREATE TABLE master_schedules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    master_id INTEGER NOT NULL,
    day_of_week INTEGER NOT NULL CHECK(day_of_week >= 0 AND day_of_week <= 6),
    start_time TEXT NOT NULL,
    end_time TEXT NOT NULL,
    is_active INTEGER NOT NULL DEFAULT 1 CHECK(is_active IN (0, 1)),
    FOREIGN KEY (master_id) REFERENCES masters(id) ON DELETE CASCADE,
    CONSTRAINT valid_time CHECK(end_time > start_time),
    CONSTRAINT unique_schedule UNIQUE(master_id, day_of_week, start_time)
);

-- Appointments (предварительная запись)
CREATE TABLE appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER NOT NULL,
    master_id INTEGER NOT NULL,
    appointment_date TEXT NOT NULL,
    start_time TEXT NOT NULL,
    end_time TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'scheduled' CHECK(status IN ('scheduled', 'completed', 'cancelled')),
    notes TEXT,
    created_at TEXT NOT NULL DEFAULT (DATETIME('now')),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (master_id) REFERENCES masters(id) ON DELETE RESTRICT,
    CONSTRAINT valid_appointment_time CHECK(end_time > start_time)
);

-- Appointment services (many-to-many relationship)
CREATE TABLE appointment_services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    appointment_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    price REAL NOT NULL CHECK(price >= 0),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT,
    CONSTRAINT unique_appointment_service UNIQUE(appointment_id, service_id)
);

-- Completed works (фактически выполненные работы)
CREATE TABLE completed_works (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    appointment_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    master_id INTEGER NOT NULL,
    client_id INTEGER NOT NULL,
    completion_date TEXT NOT NULL DEFAULT (DATE('now')),
    price REAL NOT NULL CHECK(price >= 0),
    notes TEXT,
    created_at TEXT NOT NULL DEFAULT (DATETIME('now')),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT,
    FOREIGN KEY (master_id) REFERENCES masters(id) ON DELETE RESTRICT,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT
);

-- ============================================================================
-- INDEXES for performance optimization
-- ============================================================================

-- Masters indexes
CREATE INDEX idx_masters_active ON masters(is_active);
CREATE INDEX idx_masters_specialization ON masters(specialization);
CREATE INDEX idx_masters_name ON masters(last_name, first_name);

-- Services indexes
CREATE INDEX idx_services_gender ON services(gender_type);
CREATE INDEX idx_services_active ON services(is_active);

-- Clients indexes
CREATE INDEX idx_clients_name ON clients(last_name, first_name);
CREATE INDEX idx_clients_phone ON clients(phone);

-- Appointments indexes
CREATE INDEX idx_appointments_master ON appointments(master_id, appointment_date);
CREATE INDEX idx_appointments_client ON appointments(client_id);
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_appointments_status ON appointments(status);

-- Completed works indexes
CREATE INDEX idx_completed_works_master ON completed_works(master_id, completion_date);
CREATE INDEX idx_completed_works_date ON completed_works(completion_date);

-- ============================================================================
-- TEST DATA
-- ============================================================================

-- Insert test masters
INSERT INTO masters (first_name, last_name, phone, email, gender, specialization, salary_percent, hire_date) VALUES
('Иван', 'Петров', '+79001234567', 'ivan.petrov@barbershop.ru', 'M', 'men', 40, '2020-01-15'),
('Мария', 'Сидорова', '+79001234568', 'maria.sidorova@barbershop.ru', 'F', 'women', 45, '2020-03-20'),
('Алексей', 'Иванов', '+79001234569', 'alexey.ivanov@barbershop.ru', 'M', 'universal', 50, '2019-06-10'),
('Елена', 'Смирнова', '+79001234570', 'elena.smirnova@barbershop.ru', 'F', 'universal', 42, '2021-02-01'),
('Дмитрий', 'Козлов', '+79001234571', 'dmitry.kozlov@barbershop.ru', 'M', 'men', 38, '2022-05-15');

-- Insert test services
INSERT INTO services (name, description, duration_minutes, price, gender_type) VALUES
-- Men's services
('Мужская стрижка', 'Классическая мужская стрижка', 30, 800.00, 'men'),
('Стрижка + борода', 'Стрижка и оформление бороды', 45, 1200.00, 'men'),
('Бритье', 'Классическое бритье опасной бритвой', 30, 600.00, 'men'),
('Детская стрижка', 'Стрижка для мальчиков до 12 лет', 25, 500.00, 'men'),

-- Women's services
('Женская стрижка', 'Стрижка на короткие волосы', 40, 1500.00, 'women'),
('Стрижка на длинные волосы', 'Стрижка на средние и длинные волосы', 60, 2000.00, 'women'),
('Окрашивание', 'Полное окрашивание волос', 120, 3500.00, 'women'),
('Укладка', 'Профессиональная укладка волос', 30, 800.00, 'women'),
('Мелирование', 'Мелирование волос', 90, 3000.00, 'women'),

-- Universal services
('Стрижка универсальная', 'Универсальная стрижка', 35, 1000.00, 'universal'),
('Мытье головы', 'Профессиональное мытье головы', 15, 300.00, 'universal'),
('Укладка феном', 'Укладка волос феном', 20, 500.00, 'universal');

-- Insert test clients
INSERT INTO clients (first_name, last_name, phone, email, gender, registered_at) VALUES
('Сергей', 'Николаев', '+79101111111', 'sergey.nikolaev@email.ru', 'M', '2023-01-10 10:30:00'),
('Анна', 'Волкова', '+79102222222', 'anna.volkova@email.ru', 'F', '2023-01-15 14:20:00'),
('Михаил', 'Соколов', '+79103333333', 'mikhail.sokolov@email.ru', 'M', '2023-02-05 11:00:00'),
('Ольга', 'Морозова', '+79104444444', 'olga.morozova@email.ru', 'F', '2023-02-10 16:45:00'),
('Владимир', 'Новikov', '+79105555555', 'vladimir.novikov@email.ru', 'M', '2023-03-01 09:15:00'),
('Екатерина', 'Федорова', '+79106666666', 'ekaterina.fedorova@email.ru', 'F', '2023-03-15 13:30:00'),
('Артем', 'Павлов', '+79107777777', 'artem.pavlov@email.ru', 'M', '2023-04-20 10:00:00'),
('Наталья', 'Михайлова', '+79108888888', 'natalia.mikhailova@email.ru', 'F', '2023-05-12 15:20:00');

-- Insert master schedules (example: working hours for each master)
-- Day of week: 0=Sunday, 1=Monday, ..., 6=Saturday

-- Иван Петров (master_id=1) - works Mon-Fri
INSERT INTO master_schedules (master_id, day_of_week, start_time, end_time) VALUES
(1, 1, '09:00', '18:00'), -- Monday
(1, 2, '09:00', '18:00'), -- Tuesday
(1, 3, '09:00', '18:00'), -- Wednesday
(1, 4, '09:00', '18:00'), -- Thursday
(1, 5, '09:00', '18:00'); -- Friday

-- Мария Сидорова (master_id=2) - works Tue-Sat
INSERT INTO master_schedules (master_id, day_of_week, start_time, end_time) VALUES
(2, 2, '10:00', '19:00'), -- Tuesday
(2, 3, '10:00', '19:00'), -- Wednesday
(2, 4, '10:00', '19:00'), -- Thursday
(2, 5, '10:00', '19:00'), -- Friday
(2, 6, '10:00', '17:00'); -- Saturday

-- Алексей Иванов (master_id=3) - works Mon-Sat
INSERT INTO master_schedules (master_id, day_of_week, start_time, end_time) VALUES
(3, 1, '10:00', '20:00'), -- Monday
(3, 2, '10:00', '20:00'), -- Tuesday
(3, 3, '10:00', '20:00'), -- Wednesday
(3, 4, '10:00', '20:00'), -- Thursday
(3, 5, '10:00', '20:00'), -- Friday
(3, 6, '11:00', '18:00'); -- Saturday

-- Елена Смирнова (master_id=4) - works Wed-Sun
INSERT INTO master_schedules (master_id, day_of_week, start_time, end_time) VALUES
(4, 3, '09:00', '18:00'), -- Wednesday
(4, 4, '09:00', '18:00'), -- Thursday
(4, 5, '09:00', '18:00'), -- Friday
(4, 6, '09:00', '18:00'), -- Saturday
(4, 0, '10:00', '16:00'); -- Sunday

-- Дмитрий Козлов (master_id=5) - works Mon-Fri
INSERT INTO master_schedules (master_id, day_of_week, start_time, end_time) VALUES
(5, 1, '11:00', '20:00'), -- Monday
(5, 2, '11:00', '20:00'), -- Tuesday
(5, 3, '11:00', '20:00'), -- Wednesday
(5, 4, '11:00', '20:00'), -- Thursday
(5, 5, '11:00', '20:00'); -- Friday

-- Insert test appointments
INSERT INTO appointments (client_id, master_id, appointment_date, start_time, end_time, status) VALUES
(1, 1, '2024-12-20', '10:00', '10:30', 'completed'),
(2, 2, '2024-12-20', '11:00', '11:40', 'completed'),
(3, 3, '2024-12-20', '14:00', '14:45', 'completed'),
(4, 2, '2024-12-21', '15:00', '16:00', 'completed'),
(5, 1, '2024-12-21', '10:30', '11:00', 'completed'),
(6, 4, '2024-12-21', '11:00', '12:00', 'completed'),
(7, 5, '2024-12-22', '12:00', '12:30', 'scheduled'),
(8, 2, '2024-12-22', '16:00', '17:30', 'scheduled');

-- Insert appointment services
INSERT INTO appointment_services (appointment_id, service_id, price) VALUES
(1, 1, 800.00),  -- Сергей - Мужская стрижка
(2, 5, 1500.00), -- Анна - Женская стрижка
(3, 2, 1200.00), -- Михаил - Стрижка + борода
(4, 6, 2000.00), -- Ольга - Стрижка на длинные волосы
(5, 1, 800.00),  -- Владимир - Мужская стрижка
(6, 7, 3500.00), -- Екатерина - Окрашивание
(7, 4, 500.00),  -- Артем - Детская стрижка
(8, 9, 3000.00); -- Наталья - Мелирование

-- Insert completed works (for completed appointments)
INSERT INTO completed_works (appointment_id, service_id, master_id, client_id, completion_date, price, notes) VALUES
(1, 1, 1, 1, '2024-12-20', 800.00, 'Отличная работа'),
(2, 5, 2, 2, '2024-12-20', 1500.00, 'Клиент доволен'),
(3, 2, 3, 3, '2024-12-20', 1200.00, 'Стрижка + борода'),
(4, 6, 2, 4, '2024-12-21', 2000.00, 'Длинные волосы'),
(5, 1, 1, 5, '2024-12-21', 800.00, 'Регулярный клиент'),
(6, 7, 4, 6, '2024-12-21', 3500.00, 'Полное окрашивание');

-- ============================================================================
-- USEFUL VIEWS
-- ============================================================================

-- View: Active masters with their specialization
CREATE VIEW v_active_masters AS
SELECT 
    id,
    last_name || ' ' || first_name AS full_name,
    phone,
    email,
    specialization,
    salary_percent,
    hire_date
FROM masters
WHERE is_active = 1
ORDER BY last_name, first_name;

-- View: Master earnings (for salary calculation)
CREATE VIEW v_master_earnings AS
SELECT 
    m.id AS master_id,
    m.last_name || ' ' || m.first_name AS master_name,
    m.salary_percent,
    cw.completion_date,
    SUM(cw.price) AS daily_revenue,
    ROUND(SUM(cw.price) * m.salary_percent / 100.0, 2) AS daily_salary
FROM masters m
INNER JOIN completed_works cw ON m.id = cw.master_id
GROUP BY m.id, cw.completion_date
ORDER BY cw.completion_date DESC, m.last_name;

-- View: Service statistics
CREATE VIEW v_service_statistics AS
SELECT 
    s.id,
    s.name,
    s.gender_type,
    s.price,
    COUNT(cw.id) AS times_performed,
    SUM(cw.price) AS total_revenue
FROM services s
LEFT JOIN completed_works cw ON s.id = cw.service_id
WHERE s.is_active = 1
GROUP BY s.id
ORDER BY times_performed DESC;

-- ============================================================================
-- End of script
-- ============================================================================
