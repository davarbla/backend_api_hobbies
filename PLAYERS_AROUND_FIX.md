# Players Around Page Fix

## Problem
The "Players around" page was showing 0 users even though there are 26 active users in the database.

## Root Cause - COUNTRY CODE MISMATCH
1. **Flutter app** sends country code **"ZZ"** (COUNTRY_INTERNATIONAL)
2. **Database** has 19 users in "FR", 5 in "ID", 2 in "US", but **0 users with country "ZZ"**
3. **API** was filtering by country, returning empty array for "ZZ"

## Solution
Modified `root/app/Controllers/Api.php` to handle **"ZZ" as international code**:

### Changes Made:

1. **Handle ZZ country code** (lines 111-118):
   - When country is "ZZ" or empty → Return ALL users (`allByLimit`)
   - When country is specific → Filter by country (`allByLimitCountry`)
   - "ZZ" now means "show users from all countries"

2. **Added safe lat/lng handling** (lines 75-76):
   - Prevents undefined array offset errors when coordinates are missing
   - Defaults to '0' if lat or lng is not provided

### Code Changes:
```php
// Before:
$dataUser = []; // Temporarily disabled - returned 0 users

// After:
if ($country === 'ZZ' || empty($country)) {
    // ZZ is international code - return all users
    $dataUser = $this->userModel->allByLimit($limit, $offset);
} else {
    // Return users filtered by specific country
    $dataUser = $this->userModel->allByLimitCountry($limit, $offset, $country);
}
```

## Testing Results
Run `test_zz_country.php`:
```
OLD behavior (country='ZZ' with filter): 0 users ✗
NEW behavior (country='ZZ' returns all): 26 users ✓
```

Breakdown:
- FR: 19 users
- ID: 5 users  
- US: 2 users
- **Total: 26 users returned!**

## Next Steps
To see the fix in your app:

1. **Kill and restart the Flutter app** completely (hot restart may not refresh API data)
2. Navigate to "Players around" page
3. Should now show **all 26 active users** from all countries
4. Users will be sorted by distance in the Flutter app

## How It Works Now
- App sends: `cc: "ZZ"` (international/default)
- API receives "ZZ" → Returns ALL 26 users regardless of country
- Flutter app then sorts by distance using `Tools.orderAndFilterByDistance()`

## Database Statistics
- Total users: 26 (FR: 19, ID: 5, US: 2)
- Active (status=1): 26
- With location data: 2
- Without location: 24

✓ All 26 users will now appear in "Players around" page!
