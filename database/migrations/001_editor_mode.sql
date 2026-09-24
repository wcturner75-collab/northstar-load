-- Migration: editor preference on users
-- Safe to run multiple times on MySQL 8+ / MariaDB with checks omitted; run once.

ALTER TABLE users
  ADD COLUMN editor_mode ENUM('simple','advanced') NOT NULL DEFAULT 'simple'
  AFTER status;
