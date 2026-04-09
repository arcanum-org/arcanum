-- @cast id int
-- @cast created_at string
SELECT id, name, message, created_at
FROM guestbook_entries
ORDER BY created_at DESC
LIMIT 20;
