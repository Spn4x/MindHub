CREATE DATABASE IF NOT EXISTS mindhub;
USE mindhub;

CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  id_number VARCHAR(20) NOT NULL UNIQUE,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM("student", "counselor", "admin") NOT NULL DEFAULT "student",
  department_or_program VARCHAR(50) DEFAULT NULL,
  contact_number VARCHAR(15) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE counselor_schedules (
  schedule_id INT AUTO_INCREMENT PRIMARY KEY,
  counselor_id INT NOT NULL,
  day_of_week ENUM("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday") NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  FOREIGN KEY (counselor_id) REFERENCES users(user_id)
);

CREATE TABLE appointments (
  appointment_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  counselor_id INT DEFAULT NULL,
  appointment_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  nature_of_concern ENUM("Academic", "Personal", "Career", "Mental Health", "Other") NOT NULL,
  consultation_mode ENUM("Face-to-Face", "Online") NOT NULL DEFAULT "Face-to-Face",
  meeting_link varchar(255) default null,
  status ENUM("pending", "approved", "rejected", "completed", "cancelled") DEFAULT "pending",
  student_notes TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(user_id),
  FOREIGN KEY (counselor_id) REFERENCES users(user_id)
);

CREATE TABLE counseling_notes (
  note_id INT AUTO_INCREMENT PRIMARY KEY,
  appointment_id INT NOT NULL UNIQUE,
  counselor_id INT NOT NULL,
  case_summary TEXT NOT NULL,
  action_taken TEXT DEFAULT NULL,
  follow_up_needed TINYINT(1) DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id),
  FOREIGN KEY (counselor_id) REFERENCES users(user_id)
);

CREATE TABLE self_help_resources (
  resource_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  category VARCHAR(50) NOT NULL,
  content TEXT NOT NULL,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(user_id)
);  




-- 12345678 password
insert into users (id_number, first_name, last_name, email, password_hash, role department_or_program, contact_number
) values (
  'COUNSELOR-01','Maria','Santos', 'counselor@ccc.edu.ph', '$2y$10$o43k01OfwVJaYZ8/rKo4se09BWnlREHBc64rtA9ROLouy.2BBjPlq', 'counselor', 'Guidance Office','09123456789'
);

insert into self_help_resources (title, category, content, created_by) values
('Managing Academic Stress & Burnout', 'Academic', 'College life can be demanding. When assignments pile up, break your work into smaller, 25-minute chunks using the Pomodoro technique. Take regular 5-minute screen breaks, stay hydrated, and remember that seeking guidance from professors or counselors is a strength, not a weakness.', 1),
('Overcoming Exam Anxiety', 'Academic', 'Exam anxiety is common among college students. Practice the 4-7-8 breathing exercise: inhale quietly through your nose for 4 seconds, hold your breath for 7 seconds, and exhale completely through your mouth for 8 seconds. Avoid last-minute cramming and prepare your materials the night before.', 1),
('Healthy Sleep Habits for Students', 'Mental Health', 'Quality sleep directly impacts memory consolidation and emotional regulation. Aim for 7 to 8 hours of restful sleep every night. Keep a regular sleep schedule, avoid caffeine after 3:00 PM, and turn off mobile phone screens at least 30 minutes before sleeping.', 1),
('Building Healthy Boundaries With Peers', 'Personal', 'Learning to say "no" without guilt is an essential life skill. If you are overwhelmed with your own coursework, politely decline extra responsibilities. True friends and peers will respect your personal limits and well-being.', 1);








