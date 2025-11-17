-- Check FCM token for specific user
-- Run this query to see if davarbla.g@gmail.com has a valid FCM token

-- 1. Find user and their install record
SELECT 
    u.id_user,
    u.fullname,
    u.email,
    u.id_install,
    u.status as user_status,
    i.id_install as install_id,
    i.token_fcm,
    LENGTH(i.token_fcm) as token_length,
    i.uuid,
    i.os_platform,
    i.date_updated as token_last_updated
FROM tb_user u
LEFT JOIN tb_install i ON u.id_install = i.id_install
WHERE u.email = 'davarbla.g@gmail.com'
ORDER BY u.id_user DESC;

-- 2. Check event 3 details
SELECT 
    p.id_post,
    p.title,
    p.description,
    p.id_user as owner_id,
    p.id_category,
    u.fullname as owner_name,
    u.email as owner_email,
    c.title as category_name
FROM tb_post p
LEFT JOIN tb_user u ON p.id_user = u.id_user
LEFT JOIN tb_category c ON p.id_category = c.id_category
WHERE p.id_post = 3;

-- 3. Check who has requested to join event 3
SELECT 
    up.id_user_post,
    up.id_user,
    up.id_post,
    up.status,
    up.date_created,
    u.fullname,
    u.email,
    CASE 
        WHEN up.status = 0 THEN 'Unjoined'
        WHEN up.status = 1 THEN 'Joined/Confirmed'
        WHEN up.status = 3 THEN 'Requested'
        WHEN up.status = 4 THEN 'Accepted'
        ELSE 'Unknown'
    END as status_text
FROM tb_user_post up
LEFT JOIN tb_user u ON up.id_user = u.id_user
WHERE up.id_post = 3
ORDER BY up.date_created DESC;

-- 4. Check recent installs with tokens
SELECT 
    i.id_install,
    i.uuid,
    i.os_platform,
    CASE 
        WHEN i.token_fcm IS NULL THEN 'NULL'
        WHEN i.token_fcm = '' THEN 'EMPTY'
        ELSE CONCAT('EXISTS (', LENGTH(i.token_fcm), ' chars)')
    END as token_status,
    i.date_created,
    i.date_updated,
    u.fullname,
    u.email
FROM tb_install i
LEFT JOIN tb_user u ON i.id_install = u.id_install
ORDER BY i.date_updated DESC
LIMIT 10;

-- 5. If token is missing, this shows what needs to happen:
-- The Flutter app needs to:
--   1. Generate a valid FCM token (fixed in notification_fcm_manager.dart)
--   2. Call asyncUuidToken() which calls install/saveUpdate API
--   3. Backend saves token to tb_install.token_fcm
--
-- Check if token is being sent in recent API calls by looking at date_updated
