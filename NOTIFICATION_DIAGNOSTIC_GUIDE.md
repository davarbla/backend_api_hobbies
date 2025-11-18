# Notification Diagnostic Guide - Confirmation Reminder

## Issues Fixed (Nov 17, 2025)

### 1. **Android 13+ Runtime Permission** ✅
**Problem:** Android 13 (API 33) requires POST_NOTIFICATIONS permission to be requested at runtime.

**Fix Applied:**
- Added `POST_NOTIFICATIONS` permission to AndroidManifest.xml
- Added runtime permission request in notification_fcm_manager.dart
- User will be prompted to allow notifications on first launch

### 2. **FCM Token Validation** ✅
**Problem:** App was trying to send notifications to users without valid FCM tokens.

**Fix Applied:**
- Added validation in `notifToUserPeer()` to check if token exists
- Added detailed console logging to identify users without tokens
- Function now returns early if token is null/empty

### 3. **Backend Error Logging** ✅
**Problem:** FCM errors were silently caught without logging.

**Fix Applied:**
- Added comprehensive error logging in `UserModel.php::sendFCMMessage()`
- Logs token validation errors
- Logs FCM API responses (success/failure)
- Logs exceptions with stack traces

---

## How to Test Notifications

### Step 1: Rebuild the App
```bash
cd c:\Workspace\fboys

# Clean build artifacts
flutter clean

# Get dependencies
flutter pub get

# Run in debug mode to see logs
flutter run --verbose
```

### Step 2: Check Console Logs

**Look for these messages when app starts:**
```
✅ GOOD: "FCM Token saved successfully: [token-string]"
❌ BAD: "ERROR: FCM Token is null!"

✅ GOOD: "Notification permission status: PermissionStatus.granted"
❌ BAD: "WARNING: User denied notification permission!"
```

### Step 3: Test Confirmation Reminder Button

1. **As Event Owner:**
   - Create an event
   - Accept 1-2 join requests
   - Click "Confirmation Reminder" button
   - Check console for:
     ```
     sendNotifRequestConfirmation is running...
     Sending notification to: [User Name] ([Email])
     Notification title: Participation Confirmation to [Event Name]
     FCM Token: [first 20 chars]...
     ```

2. **As Participant:**
   - Check your device for the notification
   - Should appear within 5-10 seconds

### Step 4: Check Backend Logs

**Backend PHP logs location:** Check your PHP error log file

**Look for:**
```
✅ GOOD: "FCM Success: Notification sent successfully"
❌ BAD: "FCM Error: Invalid or empty token provided"
❌ BAD: "FCM Error: Failed to send notification"
```

---

## Common Issues & Solutions

### Issue 1: "No FCM token available"
**Symptoms:**
- Console shows: "WARNING: Cannot send notification to [User] - No FCM token available"

**Cause:**
- User's device never registered a valid FCM token
- User has old app version without token fix

**Solution:**
```bash
# User must:
1. Uninstall the app completely
2. Install the new version with fixes
3. Launch app and grant notification permission
4. Token will be automatically registered
```

### Issue 2: Notification Permission Denied
**Symptoms:**
- Console shows: "WARNING: User denied notification permission!"

**Solution:**
```
User must manually enable in device settings:
Settings → Apps → Playg → Notifications → Allow
```

### Issue 3: FCM Token Invalid
**Symptoms:**
- Backend logs: "FCM Error: Invalid or empty token provided"
- Token length < 100 characters

**Cause:**
- Database has corrupted/invalid token
- Old token from broken implementation

**Solution:**
```sql
-- Clear invalid tokens (force re-registration)
UPDATE tb_install 
SET token_fcm = NULL 
WHERE LENGTH(token_fcm) < 100 OR token_fcm IS NULL;

-- User must restart app to get new token
```

### Issue 4: Notifications Sent But Not Displayed
**Symptoms:**
- Backend logs: "FCM Success"
- But user doesn't see notification on device

**Possible Causes:**
1. **Battery Optimization:** App is being killed in background
2. **Do Not Disturb:** Device has DND mode enabled
3. **App in Background:** Some Android versions don't show notifications when app is in foreground

**Solutions:**
```
1. Disable battery optimization:
   Settings → Battery → Battery Optimization → Playg → Don't optimize

2. Check notification channels:
   Settings → Apps → Playg → Notifications → Check all channels enabled

3. Test with app closed:
   Force stop app, then send notification
```

---

## Database Queries for Diagnosis

### Check User Tokens
```sql
-- See which users have valid FCM tokens
SELECT 
    u.id_user,
    u.fullname,
    u.email,
    i.token_fcm,
    LENGTH(i.token_fcm) as token_length,
    CASE 
        WHEN i.token_fcm IS NULL THEN '❌ NULL'
        WHEN i.token_fcm = '' THEN '❌ EMPTY'
        WHEN LENGTH(i.token_fcm) < 100 THEN '⚠️ TOO SHORT'
        ELSE '✅ VALID'
    END as token_status,
    i.date_updated as last_token_update
FROM tb_user u
LEFT JOIN tb_install i ON u.id_install = i.id_install
WHERE u.status = '1'
ORDER BY i.date_updated DESC
LIMIT 20;
```

### Find Users Without Tokens
```sql
-- Users who need to update their app/token
SELECT 
    u.id_user,
    u.fullname,
    u.email
FROM tb_user u
LEFT JOIN tb_install i ON u.id_install = i.id_install
WHERE u.status = '1'
  AND (i.token_fcm IS NULL OR i.token_fcm = '' OR LENGTH(i.token_fcm) < 100)
ORDER BY u.fullname;
```

---

## Test Script

Use the existing test script with proper user:

```bash
cd c:\Workspace\hobbies\AllSource_Code_HobbiesApp_v102\backend_api_hobbies

# Edit test_send_notification.php - change target email
# Then run:
php test_send_notification.php
```

**Expected output:**
```
=== FCM NOTIFICATION TEST ===

Looking for user: your@email.com
✅ User found: Your Name (ID: 123)
✅ FCM Token found: eABCDEFGHI123456789...
   Token length: 152 characters

Sending notification...
Response from FCM:
{
    "multicast_id": 123456789,
    "success": 1,
    "failure": 0,
    "canonical_ids": 0,
    "results": [{"message_id": "0:1234567890"}]
}

✅ SUCCESS! Notification sent successfully.
```

---

## Monitoring & Prevention

### Add This to Your Monitoring
1. **Track FCM failures in backend logs**
2. **Alert when token_fcm is NULL for active users**
3. **Monitor notification success rate**

### Prevent Future Issues
1. **Always validate tokens before sending**
2. **Log all notification attempts**
3. **Test on multiple Android versions (especially 13+)**
4. **Keep FCM library versions up to date**
5. **Periodically clean invalid tokens from database**

---

## Quick Reference Commands

```bash
# Flutter: Check logs while running
flutter run --verbose | grep -i "fcm\|notification\|token"

# Backend: Monitor PHP error log (Linux)
tail -f /var/log/php_errors.log | grep -i "FCM"

# Database: Quick token check
mysql -u user -p database -e "SELECT COUNT(*) as users_with_tokens FROM tb_user u JOIN tb_install i ON u.id_install = i.id_install WHERE u.status='1' AND i.token_fcm IS NOT NULL AND LENGTH(i.token_fcm) > 100;"
```

---

## Support Contacts

- **Flutter FCM Manager:** `c:\Workspace\fboys\lib\core\notification_fcm_manager.dart`
- **Token Saving Logic:** `c:\Workspace\fboys\lib\core\xcontroller.dart::asyncUuidToken()`
- **Backend FCM Send:** `backend_api_hobbies\root\app\Models\UserModel.php::sendFCMMessage()`
- **Notification Trigger:** `c:\Workspace\fboys\lib\hobbiesapp\pages\detail_post.dart::showButtonRequestConfirmation()`

---

## Verification Checklist

Before deploying to production:

- [ ] App requests notification permission on first launch
- [ ] FCM tokens are saved to database (check with SQL query)
- [ ] Console logs show "FCM Token saved successfully"
- [ ] Backend logs show "FCM Success" when sending
- [ ] Test user receives notification on device
- [ ] Notifications work when app is closed
- [ ] Notifications work when app is in background
- [ ] Notifications work when app is in foreground
- [ ] Multiple participants all receive notifications

---

**Last Updated:** November 17, 2025
**Status:** All critical fixes applied ✅
