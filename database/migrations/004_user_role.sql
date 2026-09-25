-- Management team roles
ALTER TABLE users
  ADD COLUMN role ENUM('user','manager','admin') NOT NULL DEFAULT 'user'
  AFTER editor_mode;
