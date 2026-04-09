INSERT INTO guestbook_entries (name, message, created_at)
VALUES (:name, :message, datetime('now'));
