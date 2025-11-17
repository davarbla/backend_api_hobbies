# QUICK FIX CHECKLIST - Notifications Not Working

## Current Status
- ✅ Backend code fixed (undefined variable in Api.php)
- ✅ Backend null safety checks added
- ✅ Flutter FCM token generation code fixed
- ⚠️ **CRITICAL: App needs rebuild and reinstall!**

---

## DO THIS NOW (Step-by-Step)

### ✅ Step 1: Verify Backend Is Running (2 min)
```bash
cd c:\Workspace\hobbies\AllSource_Code_HobbiesApp_v102\backend_api_hobbies
php debug_notification.php
```

**What to look for:**
- ✅ Event found
- ✅ Owner found
- ❌ **"FCM Token is NULL or EMPTY"** ← This is likely the problem!

---

### ✅ Step 2: Check Database (1 min)
Run this SQL query in your database:

```sql
SELECT 
    u.email, 
    u.fullname,
    CASE 
        WHEN i.token_fcm IS NULL THEN '❌ NULL'
        WHEN i.token_fcm = '' THEN '❌ EMPTY'
        ELSE CONCAT('✅ EXISTS (', LENGTH(i.token_fcm), ' chars)')
    END as token_status,
    i.date_updated as last_updated
FROM tb_user u
LEFT JOIN tb_install i ON u.id_install = i.id_install
WHERE u.status = 1
ORDER BY i.date_updated DESC
LIMIT 10;
```

**Expected Problem:** Most users show "❌ NULL" or "❌ EMPTY"

**Why?** Old app version with broken FCM token generation is still installed!

---

### ✅ Step 3: Rebuild Flutter App (5 min) **← DO THIS!**

```powershell
cd c:\Workspace\fboys

# Clean everything
flutter clean
Remove-Item -Recurse -Force build -ErrorAction SilentlyContinue

# Get dependencies
flutter pub get

# Build for debug with verbose logging
flutter run --debug --verbose
```

**Watch the logs carefully!** You should see:

```
✅ GOOD: "FCM Token saved successfully: eyJhbGciOiJSUz..."
❌ BAD:  "ERROR: FCM Token is null!"
```

If you see the ERROR, the token generation is still broken.

---

### ✅ Step 4: Verify Token in Logs (During Step 3)

While the app is running, look for these lines:

```dart
// In console output:
I/flutter: get token FCM eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...
I/flutter: FCM Token saved successfully: eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...
I/flutter: Saving FCM Token to backend...
```

**If you don't see these:** Token generation failed!

---

### ✅ Step 5: Check Backend Logs (After login in app)

The Flutter app calls `install/saveUpdate` API to save the token. Check if this happens:

```bash
# Check PHP logs or add logging to:
# root/app/Controllers/Install.php::saveUpdate()
```

Or run this query to see if token was saved:
```sql
SELECT * FROM tb_install 
WHERE token_fcm IS NOT NULL 
ORDER BY date_updated DESC 
LIMIT 5;
```

---

### ✅ Step 6: Manual Test (3 min)

Once you have valid tokens in the database:

```bash
cd c:\Workspace\hobbies\AllSource_Code_HobbiesApp_v102\backend_api_hobbies
php test_send_notification.php
```

This sends a real test notification. If this works ✅, your setup is correct!

---

## Common Issues & Solutions

### Issue 1: "FCM Token is null" in app logs
**Cause:** `google-services.json` might be wrong or Firebase not initialized

**Fix:**
```bash
# Check file exists
ls c:\Workspace\fboys\android\app\google-services.json

# If missing or wrong, download from Firebase Console:
# Firebase Console → Project Settings → Your Apps → Download google-services.json
```

---

### Issue 2: Token saved but notification not received
**Possible causes:**
1. **Device notifications disabled**
   - Settings → Apps → Playg → Notifications → Enable all

2. **Battery optimization killing app**
   - Settings → Battery → Battery optimization → Playg → Don't optimize

3. **Wrong Firebase Server Key**
   - Check: `root/app/Models/UserModel.php` line 28
   - Compare with: Firebase Console → Project Settings → Cloud Messaging → Server Key

4. **App in background/killed**
   - Background notifications should still work
   - But test with app in foreground first

---

### Issue 3: Database shows old NULL tokens
**Fix:** Users need to reinstall the app!

For each user:
1. Uninstall old Playg app
2. Install newly built version
3. Login
4. Check database for new token

---

## Verification Script

Run this to check everything at once:

```bash
cd c:\Workspace\hobbies\AllSource_Code_HobbiesApp_v102\backend_api_hobbies

echo "=== CHECKING BACKEND ==="
php -v
php debug_notification.php

echo ""
echo "=== CHECKING FLUTTER APP ==="
cd c:\Workspace\fboys
flutter doctor -v
flutter --version
```

---

## Expected Timeline

- **Now:** Most tokens are NULL (old app installed)
- **After Step 3 (rebuild):** You see token in console logs ✅
- **After app runs:** Token saved to database ✅
- **After test:** Manual notification works ✅
- **After users reinstall:** All users get notifications ✅

---

## Critical Files

### Flutter App:
```
c:\Workspace\fboys\lib\core\notification_fcm_manager.dart  ← Token generation
c:\Workspace\fboys\lib\core\xcontroller.dart              ← Token saving
c:\Workspace\fboys\android\app\google-services.json        ← Firebase config
```

### Backend:
```
root\app\Controllers\Api.php                               ← Join notification
root\app\Controllers\Install.php                           ← Token storage
root\app\Models\UserModel.php                              ← FCM sending
```

### Database:
```
tb_install.token_fcm     ← Where tokens are stored
tb_user.id_install       ← Links user to install
```

---

## Test Scenario

After fixing everything:

1. **User A** (with new app) joins Event 3
2. **Backend** receives request → Gets Event 3 owner
3. **Backend** checks owner's FCM token → ✅ EXISTS!
4. **Backend** sends FCM notification
5. **FCM** delivers to device
6. **Owner** receives notification: "Event request join by [User A]"

**Current problem:** Step 3 finds NULL token because old app installed!

---

## Need Help?

If still not working after Step 3:

1. Copy the console output from `flutter run --verbose`
2. Copy the output from `php debug_notification.php`
3. Copy this SQL result:
   ```sql
   SELECT u.email, i.token_fcm FROM tb_user u 
   LEFT JOIN tb_install i ON u.id_install = i.id_install 
   WHERE u.email = 'davarbla.g@gmail.com';
   ```

---

## TLDR - Just Do This:

```powershell
# 1. Rebuild app
cd c:\Workspace\fboys
flutter clean
flutter pub get
flutter run --verbose

# 2. Watch logs for "FCM Token saved successfully"

# 3. Check database
# Run: SELECT email, token_fcm FROM tb_user u LEFT JOIN tb_install i ON u.id_install = i.id_install WHERE u.status=1;

# 4. If tokens exist, test manually
cd c:\Workspace\hobbies\AllSource_Code_HobbiesApp_v102\backend_api_hobbies
php test_send_notification.php

# 5. If that works, your setup is correct!
# All users need to reinstall the app.
```

**Bottom line: The app MUST be rebuilt and reinstalled. The backend is already fixed.**
