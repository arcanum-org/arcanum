-- Save a contact form submission.
-- @param name string
-- @param email string
-- @param message string

INSERT INTO contact_messages (name, email, message, created_at)
VALUES (:name, :email, :message, datetime('now'))
