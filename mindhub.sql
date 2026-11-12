create database if not exists mindhub;
use mindhub;

create table users (
  user_id int auto_increment primary key,
  id_number varchar(20) not null unique,
  first_name varchar(50) not null,
  last_name varchar(50) not null,
  email varchar(100) not null unique,
  password_hash varchar(255) not null,
  role enum("student", "counselor", "admin") not null default "student",
  department_or_program varchar(50) default null,
  contact_number varchar(15) default null,
  created_at timestamp default current_timestamp
);

create table counselor_schedules (
  schedule_id int auto_increment primary key,
  counselor_id int not null,
  day_of_week enum("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday") not null,
  start_time time not null,
  end_time time not null,
  is_active tinyint(1) default 1,
  foreign key (counselor_id) references users(user_id)
);

create table appointments (
  appointment_id int auto_increment primary key,
  student_id int not null,
  counselor_id int default null,
  appointment_date date not null,
  start_time time not null,
  end_time time not null,
  nature_of_concern enum("Academic", "Personal", "Career", "Mental Health", "Other") not null,
  consultation_mode enum("Face-to-Face", "Online") not null default "Face-to-Face",
  meeting_link varchar(255) default null,
  status enum("pending", "approved", "rejected", "completed", "cancelled") default "pending",
  student_notes text default null,
  created_at timestamp default current_timestamp,
  foreign key (student_id) references users(user_id),
  foreign key (counselor_id) references users(user_id)
);

create table counseling_notes (
  note_id int auto_increment primary key,
  appointment_id int not null unique,
  counselor_id int not null,
  case_summary text not null,
  action_taken text default null,
  follow_up_needed tinyint(1) default 0,
  updated_at timestamp default current_timestamp on update current_timestamp,
  foreign key (appointment_id) references appointments(appointment_id),
  foreign key (counselor_id) references users(user_id)
);

create table self_help_resources (
  resource_id int auto_increment primary key,
  title varchar(150) not null,
  category varchar(50) not null,
  content text not null,
  created_by int not null,
  created_at timestamp default current_timestamp,
  foreign key (created_by) references users(user_id)
);

insert into users (
  id_number, first_name, last_name, email, password_hash, role, department_or_program, contact_number
) values
('COUNSELOR-01', 'Maria', 'Santos', 'counselor@ccc.edu.ph', '$2y$10$o43k01OfwVJaYZ8/rKo4se09BWnlREHBc64rtA9ROLouy.2BBjPlq', 'counselor', 'Guidance Office', '09123456789'),
('2024-10021', 'Juan', 'Dela Cruz', 'juan.delacruz@ccc.edu.ph', '$2y$10$o43k01OfwVJaYZ8/rKo4se09BWnlREHBc64rtA9ROLouy.2BBjPlq', 'student', 'BSIT', '09171234501'),
('2024-10045', 'Alyssa', 'Reyes', 'alyssa.reyes@ccc.edu.ph', '$2y$10$o43k01OfwVJaYZ8/rKo4se09BWnlREHBc64rtA9ROLouy.2BBjPlq', 'student', 'BSED', '09182345602'),
('2023-10112', 'Marco', 'Villanueva', 'marco.villanueva@ccc.edu.ph', '$2y$10$o43k01OfwVJaYZ8/rKo4se09BWnlREHBc64rtA9ROLouy.2BBjPlq', 'student', 'BSBA', '09193456703'),
('2025-10089', 'Sophia', 'Mendoza', 'sophia.mendoza@ccc.edu.ph', '$2y$10$o43k01OfwVJaYZ8/rKo4se09BWnlREHBc64rtA9ROLouy.2BBjPlq', 'student', 'BSCS', '09204567804');

insert into self_help_resources (title, category, content, created_by) values
('Managing Academic Stress & Burnout', 'Academic', 'College life can be demanding. When assignments pile up, break your work into smaller, 25-minute chunks using the Pomodoro technique. Take regular 5-minute screen breaks, stay hydrated, and remember that seeking guidance from professors or counselors is a strength, not a weakness.', 1),
('Overcoming Exam Anxiety', 'Academic', 'Exam anxiety is common among college students. Practice the 4-7-8 breathing exercise: inhale quietly through your nose for 4 seconds, hold your breath for 7 seconds, and exhale completely through your mouth for 8 seconds. Avoid last-minute cramming and prepare your materials the night before.', 1),
('Healthy Sleep Habits for Students', 'Mental Health', 'Quality sleep directly impacts memory consolidation and emotional regulation. Aim for 7 to 8 hours of restful sleep every night. Keep a regular sleep schedule, avoid caffeine after 3:00 PM, and turn off mobile phone screens at least 30 minutes before sleeping.', 1),
('Building Healthy Boundaries With Peers', 'Personal', 'Learning to say "no" without guilt is an essential life skill. If you are overwhelmed with your own coursework, politely decline extra responsibilities. True friends and peers will respect your personal limits and well-being.', 1);

insert into appointments (
  student_id, counselor_id, appointment_date, start_time, end_time, nature_of_concern, consultation_mode, meeting_link, status, student_notes
) values
(
  (select user_id from users where id_number = '2024-10021' limit 1),
  (select user_id from users where id_number = 'COUNSELOR-01' limit 1),
  '2026-11-04', '09:00:00', '10:00:00', 'Academic', 'Face-to-Face', null, 'completed',
  'Struggling with time management across major programming subjects.'
),
(
  (select user_id from users where id_number = '2024-10045' limit 1),
  (select user_id from users where id_number = 'COUNSELOR-01' limit 1),
  '2026-11-09', '14:00:00', '15:00:00', 'Mental Health', 'Face-to-Face', null, 'completed',
  'Experiencing severe anxiety before upcoming practice teaching demo.'
),
(
  (select user_id from users where id_number = '2023-10112' limit 1),
  (select user_id from users where id_number = 'COUNSELOR-01' limit 1),
  '2026-11-12', '08:00:00', '09:00:00', 'Personal', 'Face-to-Face', null, 'approved',
  'Balancing part-time work schedule with family responsibilities.'
),
(
  (select user_id from users where id_number = '2024-10021' limit 1),
  (select user_id from users where id_number = 'COUNSELOR-01' limit 1),
  '2026-11-16', '10:00:00', '11:00:00', 'Career', 'Online', 'https://meet.google.com/ccc-mindhub-juan', 'approved',
  'Seeking advice on OJT internship preparation and resume review.'
),
(
  (select user_id from users where id_number = '2025-10089' limit 1),
  null,
  '2026-11-17', '15:00:00', '16:00:00', 'Academic', 'Face-to-Face', null, 'pending',
  'Considering shifting degree programs and would like academic advice.'
),
(
  (select user_id from users where id_number = '2024-10045' limit 1),
  null,
  '2026-11-18', '13:00:00', '14:00:00', 'Mental Health', 'Online', null, 'pending',
  'Follow-up check-in regarding stress levels and sleep routine.'
),
(
  (select user_id from users where id_number = '2025-10089' limit 1),
  null,
  '2026-11-19', '09:00:00', '10:00:00', 'Other', 'Online', null, 'pending',
  'Inquiring about peer support group schedules for freshmen.'
);

insert into counseling_notes (
  appointment_id, counselor_id, case_summary, action_taken, follow_up_needed
) values
(
  (select appointment_id from appointments where appointment_date = '2026-11-04' and start_time = '09:00:00' limit 1),
  (select user_id from users where id_number = 'COUNSELOR-01' limit 1),
  'Student reported feeling overwhelmed by overlapping lab deadlines in IT221 and database courses. Discussed study blocking and prioritization.',
  'Introduced Pomodoro study schedule and weekly task planner. Student agreed to track deadlines for two weeks.',
  0
),
(
  (select appointment_id from appointments where appointment_date = '2026-11-09' and start_time = '14:00:00' limit 1),
  (select user_id from users where id_number = 'COUNSELOR-01' limit 1),
  'Student experiencing heightened performance anxiety and difficulty sleeping ahead of teaching evaluations.',
  'Practiced 4-7-8 breathing exercises and recommended sleep hygiene article from Self-Help Hub. Scheduled follow-up assessment.',
  1
);