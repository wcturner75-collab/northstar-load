SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email           VARCHAR(255) NOT NULL,
  username        VARCHAR(64)  NOT NULL,
  password_hash   VARCHAR(255) NOT NULL,
  status          ENUM('active','disabled','pending') NOT NULL DEFAULT 'active',
  created_at      DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  updated_at      DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3)
                                  ON UPDATE CURRENT_TIMESTAMP(3),
  UNIQUE KEY uq_users_email (email),
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_sessions (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         BIGINT UNSIGNED NOT NULL,
  session_id      VARCHAR(128) NOT NULL,
  ip_hash         CHAR(64) NULL,
  user_agent_hash CHAR(64) NULL,
  created_at      DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  last_seen_at    DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  expires_at      DATETIME(3) NOT NULL,
  revoked_at      DATETIME(3) NULL,
  UNIQUE KEY uq_user_sessions_sid (session_id),
  KEY idx_user_sessions_user (user_id),
  CONSTRAINT fk_user_sessions_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email_normalized VARCHAR(255) NOT NULL,
  ip_hash         CHAR(64) NOT NULL,
  succeeded       TINYINT(1) NOT NULL DEFAULT 0,
  created_at      DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  KEY idx_login_attempts_lookup (email_normalized, created_at),
  KEY idx_login_attempts_ip (ip_hash, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS entitlements (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         BIGINT UNSIGNED NOT NULL,
  product_key     VARCHAR(64) NOT NULL,
  plan_key        VARCHAR(32) NOT NULL,
  source          VARCHAR(32) NOT NULL DEFAULT 'manual',
  meta_json       JSON NULL,
  starts_at       DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  ends_at         DATETIME(3) NULL,
  created_at      DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  KEY idx_entitlements_user_product (user_id, product_key),
  CONSTRAINT fk_entitlements_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS templates (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_key     VARCHAR(64) NOT NULL DEFAULT 'load',
  slug            VARCHAR(64) NOT NULL,
  name            VARCHAR(128) NOT NULL,
  description     VARCHAR(512) NULL,
  preview_path    VARCHAR(255) NULL,
  theme_key       VARCHAR(64) NOT NULL,
  config_json     JSON NOT NULL,
  is_active       TINYINT(1) NOT NULL DEFAULT 1,
  sort_order      INT NOT NULL DEFAULT 0,
  created_at      DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  UNIQUE KEY uq_templates_slug (product_key, slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         BIGINT UNSIGNED NOT NULL,
  product_key     VARCHAR(64) NOT NULL DEFAULT 'load',
  name            VARCHAR(128) NOT NULL,
  resource_name   VARCHAR(64) NOT NULL,
  template_id     BIGINT UNSIGNED NULL,
  theme_key       VARCHAR(64) NOT NULL DEFAULT 'cinematic',
  config_json     JSON NOT NULL,
  config_version  INT UNSIGNED NOT NULL DEFAULT 1,
  runtime_version VARCHAR(32) NOT NULL DEFAULT '1.0.0',
  status          ENUM('draft','ready','archived') NOT NULL DEFAULT 'draft',
  last_saved_at   DATETIME(3) NULL,
  created_at      DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  updated_at      DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3)
                                  ON UPDATE CURRENT_TIMESTAMP(3),
  KEY idx_projects_user (user_id, product_key),
  UNIQUE KEY uq_projects_user_resource (user_id, resource_name),
  CONSTRAINT fk_projects_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_projects_template FOREIGN KEY (template_id)
    REFERENCES templates(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_versions (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id      BIGINT UNSIGNED NOT NULL,
  user_id         BIGINT UNSIGNED NOT NULL,
  version_no      INT UNSIGNED NOT NULL,
  config_json     JSON NOT NULL,
  note            VARCHAR(255) NULL,
  created_at      DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  UNIQUE KEY uq_project_versions (project_id, version_no),
  CONSTRAINT fk_project_versions_project FOREIGN KEY (project_id)
    REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_project_versions_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         BIGINT UNSIGNED NOT NULL,
  original_name   VARCHAR(255) NOT NULL,
  storage_name    VARCHAR(64) NOT NULL,
  relative_path   VARCHAR(255) NOT NULL,
  mime_type       VARCHAR(100) NOT NULL,
  kind            ENUM('image','audio','video') NOT NULL,
  size_bytes      BIGINT UNSIGNED NOT NULL,
  width           INT UNSIGNED NULL,
  height          INT UNSIGNED NULL,
  duration_ms     INT UNSIGNED NULL,
  sha256          CHAR(64) NOT NULL,
  created_at      DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  deleted_at      DATETIME(3) NULL,
  UNIQUE KEY uq_media_storage (storage_name),
  KEY idx_media_user (user_id, deleted_at),
  CONSTRAINT fk_media_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS builds (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         BIGINT UNSIGNED NOT NULL,
  project_id      BIGINT UNSIGNED NOT NULL,
  build_token     CHAR(64) NOT NULL,
  resource_name   VARCHAR(64) NOT NULL,
  file_path       VARCHAR(255) NOT NULL,
  file_size       BIGINT UNSIGNED NOT NULL,
  runtime_version VARCHAR(32) NOT NULL,
  status          ENUM('ready','expired','deleted','failed') NOT NULL DEFAULT 'ready',
  created_at      DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  expires_at      DATETIME(3) NULL,
  UNIQUE KEY uq_builds_token (build_token),
  KEY idx_builds_user (user_id, created_at),
  KEY idx_builds_project (project_id, created_at),
  CONSTRAINT fk_builds_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_builds_project FOREIGN KEY (project_id)
    REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
