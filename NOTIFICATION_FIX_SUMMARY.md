# Push Notification Fix Summary

## Date: Nov 17, 2025

## Issues Fixed

### 1. **Flutter App - Invalid FCM Token Generation** ✅
**File:** `c:\Workspace\fboys\lib\core\notification_fcm_manager.dart`

**Problem:**
- Using `getToken(vapidKey: MyTheme.serverKeyFCM)` which is WRONG for mobile apps
- `vapidKey` parameter is only for web push notifications, not Android/iOS
- Passing a server key as `vapidKey` caused invalid token generation
- Devices were getting null or invalid FCM tokens

**Fix:**
```dart
// BEFORE (WRONG):
firebaseMessaging.getToken(vapidKey: MyTheme.serverKeyFCM)

// AFTER (CORRECT):
firebaseMessaging.getToken()  // No parameters for mobile
```

**Impact:** FCM tokens are now properly generated based on `google-services.json` configuration.

---

### 2. **Flutter App - Notification Channel ID Mismatch** ✅
**File:** `c:\Workspace\fboys\android\app\src\main\AndroidManifest.xml`

**Problem:**
- AndroidManifest used `"HobbiesHBBID"`
- Flutter code generates `"PlaygHBBID"` (based on `MyTheme.appName`)
- Mismatch prevented notifications from displaying properly

**Fix:**
```xml
<!-- BEFORE -->
<meta-data android:name="com.google.firebase.messaging.default_notification_channel_id"
    android:value="HobbiesHBBID" />

<!-- AFTER -->
<meta-data android:name="com.google.firebase.messaging.default_notification_channel_id"
    android:value="PlaygHBBID" />
```

---

### 3. **Backend - Undefined Variable Causing PHP Error** ✅
**File:** `backend_api_hobbies\root\app\Controllers\Api.php`
**Function:** `request_unjoin_post()` (Line ~640)

**Problem:**
- Variable `$categPost` was used but never defined
- Caused PHP fatal error when trying to join/request events
- Prevented ALL notifications from being sent during event join

**Fix:**
```php
// Added these lines before using $categPost:
$idCateg = $singlePost['id_category'];
$categPost = $this->categModel->getById($idCateg);
```

**Impact:** This was the **PRIMARY BUG** preventing notifications when joining events!

---

### 4. **Backend - Missing Null Safety Checks** ✅
**Files:** 
- `backend_api_hobbies\root\app\Controllers\Api.php` (multiple functions)
- `backend_api_hobbies\root\app\Controllers\Post.php`

**Problem:**
- Attempting to send notifications to null/empty FCM tokens
- Caused silent failures when users didn't have valid tokens registered

**Functions Fixed:**
1. `request_unjoin_post()` - Request to join event
2. `join_unjoin_post()` - Accept/reject join request
3. `unjoin_post()` - Reject participation
4. `join_post()` - Validate participation
5. `request_show_gallery()` - Gallery access request
6. `send_notif_post()` in Post.php - Post likes/comments

**Fix Pattern:**
```php
// BEFORE:
$this->userModel->sendFCMMessage($user['token_fcm'], $dataFcm);

// AFTER:
if (!empty($user['token_fcm'])) {
    $this->userModel->sendFCMMessage($user['token_fcm'], $dataFcm);
}
```

---

## Testing Checklist

### Flutter App
- [x] Rebuild app: `flutter clean && flutter pub get && flutter run`
- [ ] Check console logs for: "FCM Token saved successfully: [token]"
- [ ] Verify token is NOT null
- [ ] Test notification permissions granted

### Backend
- [ ] Check PHP error logs (should be clear now)
- [ ] Verify `tb_install.token_fcm` is populated with valid tokens
- [ ] Test notification sending:
  - [ ] Join event request → Event owner should receive notification
  - [ ] Accept join request → Requester should receive notification
  - [ ] Reject join request → Requester should receive notification
  - [ ] Like post → Post owner should receive notification
  - [ ] Comment on post → Post owner should receive notification

### Database Check
```sql
-- Check if users have valid FCM tokens
SELECT u.id_user, u.fullname, i.token_fcm 
FROM tb_user u 
LEFT JOIN tb_install i ON u.id_install = i.id_install 
WHERE u.status = 1 
LIMIT 10;

-- Should return non-null token_fcm values
```

---

## Root Cause Analysis

### Why Notifications Were Not Working:

1. **Client Side (Flutter):**
   - Invalid token generation prevented proper device registration
   - Even if backend tried to send, tokens were wrong/null

2. **Server Side (Backend):**
   - PHP fatal error in `request_unjoin_post()` crashed the request before notification could be sent
   - Missing null checks caused silent failures when tokens were empty

### Critical Path Fixed:
```
User joins event → Flutter calls backend API 
→ Backend: request_unjoin_post() 
→ [BUG WAS HERE: undefined $categPost] 
→ Now Fixed: Fetch category data 
→ Build notification payload 
→ [BUG WAS HERE: send to null token] 
→ Now Fixed: Check token exists 
→ Send FCM notification 
→ User receives notification ✅
```

---

## Additional Notes

- FCM Server Key in `UserModel.php` ($keyServerFCM) is correct and unchanged
- Legacy API still works for sending notifications
- The `vapidKey` confusion was a common mistake mixing web and mobile FCM APIs
- All safety checks follow the pattern already used in some functions (e.g., `request_show_gallery()`)

---

## Files Modified

### Flutter App (c:\Workspace\fboys\)
1. `lib\core\notification_fcm_manager.dart` - Fixed token generation
2. `android\app\src\main\AndroidManifest.xml` - Fixed channel ID

### Backend API (c:\Workspace\hobbies\AllSource_Code_HobbiesApp_v102\backend_api_hobbies\)
1. `root\app\Controllers\Api.php` - Fixed undefined variable and added safety checks
2. `root\app\Controllers\Post.php` - Added safety check

---

## Next Steps

1. Deploy backend changes to production server
2. Rebuild and redeploy Flutter app to devices
3. Test notification flow end-to-end
4. Monitor logs for any remaining issues
5. Consider adding notification delivery tracking/logging for debugging

---

## Prevention

To prevent similar issues in the future:

1. **Always validate variables before use** - Enable PHP strict mode
2. **Always check for null/empty tokens** before sending notifications
3. **Read FCM documentation carefully** - vapidKey is ONLY for web
4. **Add error logging** in notification sending functions
5. **Test notification flow** in staging before production
