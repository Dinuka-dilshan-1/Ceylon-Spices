USE ceylon_spice_hub;

-- Run this only if you are keeping an existing database instead of importing database.sql again.
ALTER TABLE payments ADD COLUMN IF NOT EXISTS status VARCHAR(30) DEFAULT 'Pending';
ALTER TABLE payments ADD COLUMN IF NOT EXISTS status_message VARCHAR(255) NULL;
