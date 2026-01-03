# Dashboard Fixes for mywatercloset Integration

**Created:** 2026-01-03
**Priority:** High - mywatercloset needs this working

---

## Critical Issues Summary

The entire dashboard is using **mock/hardcoded data** with no real API integration. All "save" functions are stubs with `console.log` only.

---

## 1. Users Page (`/dashboard/users`)

**File:** `dashboard/app/pages/dashboard/users/index.vue`
**Status:** [ ] Not Started

### Issues:
- [ ] Lines 209-263: Completely hardcoded mock users (6 fake users)
- [ ] Lines 201-206: Stats are hardcoded (1543 total, 234 online, etc.)
- [ ] Lines 365-393: All actions are `console.log` stubs:
  - [ ] `viewUserDetails()` - stub
  - [ ] `viewUserConversations()` - stub
  - [ ] `blockUser()` - stub
  - [ ] `banUser()` - stub
  - [ ] `deleteUser()` - stub
  - [ ] `exportUsers()` - stub
- [ ] No loading states
- [ ] No error handling
- [ ] Filtering is client-side only (won't scale)

### API Endpoint:
- `GET /api/dashboard/workspaces/{id}/users` - EXISTS in backend
- `POST /api/dashboard/workspaces/{id}/users/{userId}/ban` - EXISTS
- `DELETE /api/dashboard/workspaces/{id}/users/{userId}/ban` - EXISTS

### Fix Required:
1. Fetch real users from API on mount
2. Calculate real stats from user data
3. Implement ban/unban with API calls
4. Add loading/error states
5. Add server-side pagination

---

## 2. Analytics Page (`/dashboard/analytics`)

**File:** `dashboard/app/pages/dashboard/analytics.vue`
**Status:** [ ] Not Started

### Issues:
- [ ] Lines 186-223: All metrics hardcoded (24,589 messages, 1,247 conversations, etc.)
- [ ] Lines 178-184: Date range filter exists but does NOTHING
- [ ] Lines 225-264: Chart data is static/fake
- [ ] Lines 44-69: Charts are placeholders ("Chart placeholder - Integration pending")
- [ ] Line 267: Refresh button just `console.log`

### API Endpoint:
- `GET /api/dashboard/workspaces/{id}/analytics` - EXISTS
- `GET /api/dashboard/workspaces/{id}/analytics/messages` - EXISTS
- `GET /api/dashboard/workspaces/{id}/analytics/users` - EXISTS

### Fix Required:
1. Fetch real analytics from API
2. Make date range filter functional (7d, 30d, 90d, custom)
3. Implement real charts (use chart library)
4. Add loading/error states
5. Make refresh button work

---

## 3. Settings Page (`/dashboard/settings`)

**File:** `dashboard/app/pages/dashboard/settings.vue`
**Status:** [ ] Not Started

### Issues:
- [ ] Lines 243-251: `saveSettings()` is NOT implemented (TODO comment)
- [ ] Lines 254-256: `resetSettings()` just `console.log`
- [ ] Lines 228-241: API keys are hardcoded mock data
- [ ] Lines 259-261: `createApiKey()` just `console.log`
- [ ] Lines 263-265: `deleteApiKey()` just `console.log`
- [ ] Lines 267-273: Dangerous zone functions are stubs
- [ ] No input validation
- [ ] Toggles don't persist
- [ ] UI needs redesign (inputs not full width, poor layout)

### API Endpoints:
- `GET /api/dashboard/workspaces/{id}/settings` - EXISTS
- `PATCH /api/dashboard/workspaces/{id}/settings` - EXISTS
- `GET /api/dashboard/workspaces/{id}/api-keys` - EXISTS
- `POST /api/dashboard/workspaces/{id}/api-keys` - EXISTS
- `DELETE /api/dashboard/workspaces/{id}/api-keys/{keyId}` - EXISTS

### Fix Required:
1. Load settings from API on mount
2. Implement `saveSettings()` with API call
3. Implement API key CRUD (create, list, delete)
4. Add confirmation modals for dangerous actions
5. Add input validation
6. Redesign UI with frontend-design plugin

---

## 4. Theme Page (`/dashboard/theme`)

**File:** `dashboard/app/pages/dashboard/theme.vue`
**Status:** [ ] Not Started

### Issues:
- [ ] Lines 279-287: `saveTheme()` is empty (just `setTimeout` mock)
- [ ] Lines 289-299: `resetTheme()` hardcodes defaults instead of loading from server
- [ ] No distinction between saved vs unsaved state
- [ ] Theme changes lost on refresh

### API Endpoints:
- `GET /api/dashboard/workspaces/{id}/theme` - EXISTS
- `PATCH /api/dashboard/workspaces/{id}/theme` - EXISTS

### Fix Required:
1. Load theme from API on mount
2. Implement `saveTheme()` with API call
3. Implement `resetTheme()` to fetch defaults from server
4. Show unsaved changes indicator
5. Add loading/error states

---

## 5. Conversations Page (`/dashboard/conversations`)

**File:** `dashboard/app/pages/dashboard/conversations/index.vue`
**Status:** [ ] Not Started

### Issues:
- [ ] Lines 173-310: Four hardcoded mock conversations
- [ ] Lines 366-369: Delete function just `console.log`
- [ ] No API integration
- [ ] Type inconsistency (camelCase vs snake_case)

### API Endpoints:
- `GET /api/dashboard/workspaces/{id}/conversations` - EXISTS
- `GET /api/dashboard/workspaces/{id}/conversations/{convId}` - EXISTS
- `DELETE /api/dashboard/workspaces/{id}/messages/{msgId}` - EXISTS

### Fix Required:
1. Fetch real conversations from API
2. Implement delete with confirmation
3. Fix type inconsistencies
4. Add loading/error states

---

## 6. Widget Preview Page (`/dashboard/widget`)

**File:** `dashboard/app/pages/dashboard/widget.vue`
**Status:** [ ] Not Started

### Issues:
- [ ] Lines 343-347: `saveSettings()` not implemented (uses `alert()`)
- [ ] Lines 363-366: `loadSettings()` not implemented
- [ ] Only shows customer-based chat preview
- [ ] Missing user-to-user chat preview
- [ ] Missing group chat preview
- [ ] Embed code has hardcoded CDN URL

### Fix Required:
1. Implement settings save/load
2. Add preview for user-to-user chat
3. Add preview for group chat
4. Fix embed code generation
5. Remove hardcoded URLs

---

## 7. Conversation Detail Page

**File:** `dashboard/app/pages/dashboard/[workspaceId]/conversations/[id].vue`
**Status:** [ ] Not Started

### Issues:
- [ ] Lines 536-548: Delete conversation not implemented
- [ ] Line 570: Archive conversation not implemented
- [ ] API response parsing inconsistencies

### Fix Required:
1. Implement delete with confirmation
2. Implement archive functionality
3. Handle API response format consistently

---

## 8. Cross-Cutting Issues

### A. No Loading States
- [ ] Users page - no skeleton
- [ ] Analytics page - no skeleton
- [ ] Conversations page - no skeleton

### B. No Error Recovery
- [ ] No retry buttons
- [ ] No user-friendly error messages

### C. No Confirmation Modals
- [ ] Delete operations have no confirmation
- [ ] Dangerous actions have no warning

### D. Type Inconsistencies
- [ ] API returns snake_case, templates expect camelCase
- [ ] Need consistent handling

### E. WebSocket Issues
- [ ] `echo.client.ts` Line 18: `forceTLS` is hardcoded false

---

## Priority Order for mywatercloset

1. **API Keys CRUD** - They need to create API keys to integrate
2. **Settings Save** - They need to configure the widget
3. **Theme Save** - They need to customize appearance
4. **Users Page** - They need to see their users
5. **Analytics** - Nice to have for monitoring
6. **UI Redesign** - Polish after functionality works

---

## Files to Modify

```
dashboard/app/pages/dashboard/
├── users/index.vue          # Users list - MOCK DATA
├── analytics.vue            # Analytics - MOCK DATA
├── settings.vue             # Settings - NOT SAVING
├── theme.vue                # Theme - NOT SAVING
├── widget.vue               # Widget preview - INCOMPLETE
├── conversations/
│   └── index.vue            # Conversations - MOCK DATA
└── [workspaceId]/
    └── conversations/
        └── [id].vue         # Conversation detail - PARTIAL
```

---

## Progress Tracking

| Task | Code Done | Unit Tests | Browser Tested | Notes |
|------|-----------|------------|----------------|-------|
| API Keys CRUD | [x] | [x] 12 tests | [ ] | Full CRUD with modals |
| Settings save/load | [x] | [x] 20 tests | [ ] | All toggles persist |
| Theme save/load | [x] | [x] 14 tests | [ ] | Unsaved indicator added |
| Users API integration | [x] | [x] 21 tests | [ ] | Real data + ban/unban |
| Analytics API integration | [x] | [x] 22 tests | [ ] | Working date filters |
| Conversations API | [ ] | [ ] | [ ] | Lower priority |
| Widget preview types | [x] | N/A | [ ] | Support/DM/Group tabs |
| Settings UI redesign | [x] | N/A | [ ] | Professional 2-col layout |
| Loading states | [x] | N/A | [ ] | All pages have loaders |
| Error handling | [x] | N/A | [ ] | Retry buttons added |
| Confirmation modals | [x] | N/A | [ ] | Ban/delete modals |

---

## Testing Requirements

### API Unit Tests (Laravel/PHPUnit)
Location: `api/tests/Feature/Dashboard/`

Required test files:
- [x] `ApiKeyControllerTest.php` - 12 tests, 40 assertions
- [x] `SettingsControllerTest.php` - 20 tests
- [x] `ThemeControllerTest.php` - 14 tests, 38 assertions
- [x] `UserControllerTest.php` - 21 tests
- [x] `AnalyticsControllerTest.php` - 22 tests, 75 assertions
- [ ] `ConversationControllerTest.php` - Conversation management (lower priority)

### Browser Testing Checklist
Test URL: https://dashboard-production-8985.up.railway.app

**Tested 2026-01-03:**
- [x] Demo page loads
- [x] Chat room creation works
- [x] WebSocket connected (real-time events flowing)
- [x] Messages send and display correctly
- [x] File upload → CDN working (hastets.b-cdn.net)

**Dashboard (requires login):**
- [ ] Login to dashboard
- [ ] Navigate to Settings > API Keys
  - [ ] Create new API key
  - [ ] See key in list
  - [ ] Delete API key
- [ ] Navigate to Settings
  - [ ] Change a setting
  - [ ] Save settings
  - [ ] Refresh page - settings persist
- [ ] Navigate to Theme
  - [ ] Change colors
  - [ ] Save theme
  - [ ] Refresh page - theme persists
- [ ] Navigate to Users
  - [ ] See real users (not mock data)
  - [ ] Filter by status
  - [ ] Search works
- [ ] Navigate to Analytics
  - [ ] See real metrics
  - [ ] Change date filter (7d, 30d, 90d)
  - [ ] Data updates accordingly
- [ ] Navigate to Widget Preview
  - [ ] See customer chat preview
  - [ ] See user-to-user chat preview
  - [ ] See group chat preview
