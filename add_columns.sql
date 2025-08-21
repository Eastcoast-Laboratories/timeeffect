-- Add missing columns to te_auth table
ALTER TABLE te_auth 
ADD COLUMN confirmed TINYINT(1) NOT NULL DEFAULT 1 AFTER facsimile,
ADD COLUMN confirmation_token VARCHAR(64) NULL AFTER confirmed,
ADD COLUMN reset_token VARCHAR(64) NULL AFTER confirmation_token,
ADD COLUMN reset_expires DATETIME NULL AFTER reset_token;

-- Add indexes for performance
ALTER TABLE te_auth
ADD KEY confirmation_token (confirmation_token),
ADD KEY reset_token (reset_token);

-- Mark all existing users as confirmed
UPDATE te_auth SET confirmed = 1 WHERE confirmed = 0;
