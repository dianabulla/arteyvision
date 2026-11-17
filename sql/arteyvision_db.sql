CREATE DATABASE IF NOT EXISTS arteyvision_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE arteyvision_db;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS site_sections (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  section_key VARCHAR(50) NOT NULL UNIQUE,
  title VARCHAR(180) NOT NULL,
  body TEXT NOT NULL,
  image_url VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS donations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  donor_name VARCHAR(120) NOT NULL,
  donor_email VARCHAR(150) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  message TEXT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'registrada',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO users (full_name, email, password_hash, role)
VALUES ('Administrador', 'admin@arteyvision.org', '$2y$10$sjPp.sZBJzG/kO5tyh17yuRG2AqTIyZsG59NK4kptZPyUD9ialo52', 'admin')
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  password_hash = VALUES(password_hash),
  role = VALUES(role);

INSERT INTO site_sections (section_key, title, body, image_url, sort_order)
VALUES
('hero', 'Fundacion Social Arte y Vision', 'Promovemos expresiones artistico-culturales y procesos de transformacion social con enfoque humano.', '/assets/img/logo-arteyvision.png', 1),
('about', 'Quienes somos', 'Somos una fundacion comprometida con la inclusion, la cultura y la construccion de oportunidades a traves del arte.', '', 2),
('impact', 'Impacto comunitario', 'Desarrollamos talleres, semilleros y acciones participativas para ninos, jovenes y adultos.', '', 3)
ON DUPLICATE KEY UPDATE
  title = VALUES(title),
  body = VALUES(body),
  image_url = VALUES(image_url),
  sort_order = VALUES(sort_order);

CREATE TABLE IF NOT EXISTS foundation_files (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  category VARCHAR(80) NOT NULL DEFAULT 'labores',
  original_name VARCHAR(255) NOT NULL,
  stored_name VARCHAR(255) NOT NULL,
  mime_type VARCHAR(120) NOT NULL,
  file_size INT UNSIGNED NOT NULL DEFAULT 0,
  uploaded_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_foundation_files_user
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
