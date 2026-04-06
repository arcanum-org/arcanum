-- Retrieve all contact form submissions, newest first.
-- @cast id int

SELECT id, name, email, message, created_at
FROM contact_messages
ORDER BY created_at DESC
