-- Upsert owner account: willcturner (Pro plan)
-- Default password: Northstar123!  (change after login)
-- Run in phpMyAdmin on database northstar_load

INSERT INTO users (email, username, password_hash, status, editor_mode)
VALUES ('willcturner@northstar.local', 'willcturner', '$2y$10$fbAAn9esd9x1Pd13MsBVj.zeaPJIqYrFqgISbc5LjXLonbj8CgFWa', 'active', 'simple')
ON DUPLICATE KEY UPDATE
  password_hash = VALUES(password_hash),
  editor_mode = 'simple',
  status = 'active';

SET @uid = (SELECT id FROM users WHERE username = 'willcturner' LIMIT 1);

INSERT INTO entitlements (user_id, product_key, plan_key, source, meta_json)
SELECT @uid, 'load', 'pro', 'manual', JSON_OBJECT('note', 'owner account')
WHERE NOT EXISTS (
  SELECT 1 FROM entitlements WHERE user_id = @uid AND product_key = 'load'
);

UPDATE entitlements
SET plan_key = 'pro', source = 'manual'
WHERE user_id = @uid AND product_key = 'load';

SELECT u.id, u.username, u.email, u.editor_mode, e.plan_key
FROM users u
LEFT JOIN entitlements e ON e.user_id = u.id AND e.product_key = 'load'
WHERE u.username = 'willcturner';