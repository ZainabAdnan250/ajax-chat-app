USE chatapp;

-- Track last read message per user per group (for unread counts)
CREATE TABLE IF NOT EXISTS group_read_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    last_read_id INT DEFAULT 0,
    UNIQUE KEY unique_read (group_id, user_id),
    FOREIGN KEY (group_id) REFERENCES chat_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Change reaction column to TEXT to store JSON reactions
ALTER TABLE private_messages MODIFY COLUMN reaction TEXT DEFAULT NULL;
ALTER TABLE group_messages MODIFY COLUMN reaction TEXT DEFAULT NULL;
