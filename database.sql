CREATE DATABASE IF NOT EXISTS inventory_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE inventory_db;

CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          
    role        ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    sku         VARCHAR(80)  NOT NULL UNIQUE,
    category_id INT,
    price       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    quantity    INT          NOT NULL DEFAULT 0,
    description TEXT,
    created_by  INT,                               
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)  REFERENCES users(id)      ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@inventory.com',
 '$2y$10$KzbemR7vMLmQSTgpIT61SeuD8bMv3xK4IGlvEWT9rmTHJCwsRiGiK', 'admin')
ON DUPLICATE KEY UPDATE id = id;

INSERT INTO categories (name, description) VALUES
('Electronics',  'Electronic devices and accessories'),
('Office Supplies','Stationery and office equipment'),
('Furniture',    'Desks, chairs, and storage'),
('Consumables',  'Items that are used up regularly')
ON DUPLICATE KEY UPDATE id = id;

INSERT INTO products (name, sku, category_id, price, quantity, description, created_by) VALUES
('Wireless Mouse',       'ELEC-001', 1, 850.00,  45, 'Ergonomic wireless mouse, 2.4GHz',           1),
('USB-C Hub 7-port',     'ELEC-002', 1, 1299.00, 20, '7-in-1 USB-C multiport adapter',             1),
('Ballpen Box (12pcs)',  'OFFC-001', 2,  85.00, 200, 'Blue ink ballpens, 0.5mm tip',               1),
('A4 Bond Paper (500s)', 'OFFC-002', 2, 220.00,  80, 'White bond paper, 80gsm',                    1),
('Office Chair',         'FURN-001', 3,4500.00,  10, 'Mesh back ergonomic office chair',           1),
('Standing Desk',        'FURN-002', 3,8999.00,   5, 'Height-adjustable standing desk 120x60cm',  1),
('Alcohol 70% (500ml)',  'CONS-001', 4,  75.00, 300, 'Isopropyl alcohol, 500mL bottle',            1),
('Toner Cartridge HP',   'CONS-002', 4, 980.00,  15, 'Compatible HP LaserJet toner, black',       1)
ON DUPLICATE KEY UPDATE id = id;
