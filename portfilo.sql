CREATE DATABASE IF NOT EXISTS portfolio_admin
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_admin;

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS site_content (
  content_key VARCHAR(100) PRIMARY KEY,
  content_value TEXT NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS skills (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  percentage TINYINT UNSIGNED NOT NULL DEFAULT 0,
  description VARCHAR(500) NOT NULL DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  category VARCHAR(100) NOT NULL DEFAULT 'Web',
  image_url VARCHAR(1000) NOT NULL DEFAULT '',
  live_url VARCHAR(1000) NOT NULL DEFAULT '',
  code_url VARCHAR(1000) NOT NULL DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0
);

INSERT IGNORE INTO site_content (content_key, content_value) VALUES
('logo_name','Vikash-Kumar'),
('kicker','AVAILABLE FOR FREELANCE & FULL-TIME ROLES'),
('name','Vikash Kumar'),
('role','Frontend Developer | Java Enthusiast'),
('hero_description','I build responsive web applications using Java, HTML, CSS, JavaScript, and MySQL.'),
('about','Write your About section here.'),
('profile_image',''),
('resume_url',''),
('email','vikashkumarvk8251380@gmail.com'),
('whatsapp','919060088633'),
('github_url',''),
('linkedin_url',''),
('instagram_url',''),
('footer_text','Vikash Kumar');

INSERT INTO skills (name, percentage, description, sort_order)
SELECT 'HTML', 90, 'Semantic and accessible page structure', 1
WHERE NOT EXISTS (SELECT 1 FROM skills);
INSERT INTO skills (name, percentage, description, sort_order)
SELECT 'CSS', 85, 'Responsive layouts and styling', 2
WHERE (SELECT COUNT(*) FROM skills) = 1;
INSERT INTO skills (name, percentage, description, sort_order)
SELECT 'JavaScript', 75, 'Interactive website functionality', 3
WHERE (SELECT COUNT(*) FROM skills) = 2;
INSERT INTO skills (name, percentage, description, sort_order)
SELECT 'Java', 75, 'Core Java and programming fundamentals', 4
WHERE (SELECT COUNT(*) FROM skills) = 3;
INSERT INTO skills (name, percentage, description, sort_order)
SELECT 'MySQL', 70, 'Relational database fundamentals', 5
WHERE (SELECT COUNT(*) FROM skills) = 4;
