-- Show Users Table Structure
SELECT 'Users Table Structure' as '';
DESCRIBE users;

-- Show Users Table Data
SELECT 'Users Table Data' as '';
SELECT 
    id,
    username,
    SUBSTRING(password, 1, 20) as password_hash,
    SUBSTRING(encryption_key, 1, 20) as encryption_key,
    created_at
FROM users;

-- Show Passwords Table Structure
SELECT 'Passwords Table Structure' as '';
DESCRIBE passwords;

-- Show Passwords Table Data
SELECT 'Passwords Table Data' as '';
SELECT 
    id,
    user_id,
    website,
    SUBSTRING(password, 1, 20) as encrypted_password,
    created_at
FROM passwords; 