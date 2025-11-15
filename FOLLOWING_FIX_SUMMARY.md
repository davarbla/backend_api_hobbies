# Following/Favorites Section Fix Summary

## Issue
Users that are being followed were not appearing in the "Following" section (may be translated as "Favorites") on the home page.

## Root Causes Identified

### 1. Backend API Issue (CRITICAL)
**Location:** `root/app/Models/FollowModel.php`

**Problem:** The `getAllFollowingByIdUser()` and `getAllFollowerByIdUser()` methods used an INNER JOIN (implicit comma syntax) to fetch user details:

```php
SELECT b.*, c.token_fcm FROM tb_user b, tb_install c 
WHERE b.id_install=c.id_install 
AND b.id_user='...'
```

This required BOTH conditions to be met:
- User exists in `tb_user`
- User has a valid `id_install` that exists in `tb_install`

**Impact:** If a followed user had a missing, NULL, or invalid `id_install`, they would NOT appear in the followings list, even though the follow relationship existed in the database.

**Fix Applied:**
Changed to LEFT JOIN to include users even if `id_install` is missing:

```php
SELECT b.*, c.token_fcm FROM tb_user b 
LEFT JOIN tb_install c ON b.id_install=c.id_install 
WHERE b.id_user='...'
```

Added safety check to only include results where the user exists:

```php
if (!empty($result1)) {
    $row['user'] = $result1[0];
    $return_array[] = $row;
}
```

### 2. Flutter App Safety Issue
**Location:** `lib/hobbiesapp/app/home_page.dart`

**Problem:** The `listFollowing()` function was checking for deleted users but not for NULL users:

```dart
followings.removeWhere(
    (element) => element.user != null && element.user!.isDeleted());
```

This meant if a following had `user == null`, it would NOT be filtered out, but later the code used `e.user!` which would cause a null pointer exception.

**Fix Applied:**
Added explicit null check to filter out followings with null users:

```dart
// Filter out followings with null users or deleted users
followings.removeWhere(
    (element) => element.user == null || element.user!.isDeleted());
```

## Files Modified

### Backend
1. `root/app/Models/FollowModel.php`
   - Fixed `getAllFollowingByIdUser()` method (lines 147-162)
   - Fixed `getAllFollowerByIdUser()` method (lines 186-201)

### Flutter App
1. `lib/hobbiesapp/app/home_page.dart`
   - Fixed `listFollowing()` method (lines 817-826)

## Testing

### Test Files Created
1. `test_followings_debug.php` - Basic followings diagnostics
2. `test_all_followings.php` - Comprehensive followings analysis
3. `test_followings_fix.php` - Verification of the fix

### How to Test
Run the verification test:
```bash
php test_followings_fix.php
```

This will compare the OLD (INNER JOIN) vs NEW (LEFT JOIN) queries and show if any additional users are now appearing.

## Other Potential Causes

If followings are still not appearing after this fix, check:

1. **No follow records exist:** User needs to follow other users first
   - Check `tb_follow` table for records where `id_user = [current_user_id]`, `status = 1`, `flag = 1`

2. **Followed users are deleted:** Check `tb_user.status` 
   - Users with `status = 0` are considered deleted and filtered out by Flutter
   - Query: `SELECT * FROM tb_user WHERE id_user IN (SELECT id_user_to FROM tb_follow WHERE id_user = [current_user_id] AND status = 1 AND flag = 1)`

3. **App needs refresh:** After the fix, the app needs to call `fetchHome()` again to get updated data
   - Pull to refresh on home page
   - Or restart the app

## How Following Works

### Database Structure
- `tb_follow.id_user` = The follower (person who clicked follow)
- `tb_follow.id_user_to` = The person being followed
- `tb_follow.status` = 1 (following), 0 (unfollowed)
- `tb_follow.flag` = 1 (active), 0 (deleted)

### API Flow
1. Flutter app calls `api/index` endpoint
2. Backend calls `FollowModel->getAllFollowingByIdUser($idUser)`
3. Returns array of follow records with embedded user details
4. Flutter stores in `XController.itemCategories.value.followings`
5. Home page displays in "Following" section

### User Actions
- **Follow a user:** Calls `follow/follow_unfollow` endpoint with `action='follow'`
- **Unfollow a user:** Calls `follow/follow_unfollow` endpoint with `action='unfollow'`
- **View all followings:** Navigate to "All People" page with title "following"

## Backward Compatibility

The fix is 100% backward compatible:
- Still returns all users that the old INNER JOIN query returned
- Additionally returns users that were previously excluded due to `id_install` issues
- No breaking changes to API response structure

## Recommendations

1. **Database Integrity:** Consider adding a database migration to ensure all users have valid `id_install` values
2. **Error Logging:** Add logging when users are excluded from followings list
3. **User Feedback:** Show a message if a user tries to view followings but hasn't followed anyone yet
4. **Performance:** Consider adding indexes on `tb_follow(id_user, status, flag)` for faster queries

## Date
Fix implemented: 2025-11-15
