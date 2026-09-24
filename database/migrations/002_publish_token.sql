-- Hosted loadscreen public tokens
ALTER TABLE projects
  ADD COLUMN publish_token CHAR(48) NULL AFTER runtime_version,
  ADD UNIQUE KEY uq_projects_publish_token (publish_token);
