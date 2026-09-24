-- If 002 already applied with CHAR(48), widen the column
ALTER TABLE projects
  MODIFY COLUMN publish_token VARCHAR(64) NULL;
