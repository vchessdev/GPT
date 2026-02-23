# Security Fixes - Development Notes

## Issues Fixed

### 1. XSS (Cross-Site Scripting) Vulnerability - CRITICAL
**Status:** ✅ FIXED

**Problem:** User-supplied content was directly injected into the DOM using `innerHTML` without HTML escaping. Attackers could execute arbitrary JavaScript in users' browsers.

**Solution:** 
- Added `escapeHtml()` function in `script.js` that uses `textContent` to safely escape HTML entities
- Applied escaping to all user-controlled content:
  - Post titles, excerpts, content
  - Comment author names and content
  
**Code Changes:**
```javascript
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
```

**Impact:** All user content is now safely escaped before rendering, preventing injection attacks.

---

### 2. Race Condition in File-Based Storage - HIGH
**Status:** ✅ FIXED

**Problem:** The file-based storage system used synchronous `readFileSync()` and `writeFileSync()` without locking. When multiple concurrent requests modified data (votes, comments, posts), the last write would overwrite previous writes, causing data loss.

**Solution:**
- Modified `api/utils/store.js` to implement a write queue using Promises
- Changed `writeJson()` to asynchronous with per-file queue mechanism
- Updated all API endpoints to `await writeJson()` calls

**Code Changes:**
```javascript
const writeQueue = {};

function writeJson(type, data) {
  if (!writeQueue[type]) {
    writeQueue[type] = Promise.resolve();
  }
  writeQueue[type] = writeQueue[type].then(() => {
    return new Promise((resolve, reject) => {
      fs.writeFile(FILES[type], JSON.stringify(data, null, 2), (err) => {
        if (err) reject(err);
        else resolve();
      });
    });
  });
  return writeQueue[type];
}
```

**Affected Endpoints:** `/api/add_posts`, `/api/votes`, `/api/add_comment`, `/api/delete_posts`, `/api/delete_comment`

**Impact:** Concurrent writes are now serialized per file, preventing data loss.

---

### 3. Hardcoded JWT Secret - HIGH
**Status:** ✅ FIXED

**Problem:** JWT_SECRET was hardcoded with a default value `'devda-super-secret'`. If the environment variable wasn't set in production, anyone could forge authentication tokens.

**Solution:**
- Removed hardcoded default value
- Made JWT_SECRET a required environment variable
- Server throws fatal error if JWT_SECRET is not set
- Added `.env` and `.env.example` for local configuration
- Installed and configured `dotenv` package

**Code Changes:**
```javascript
const JWT_SECRET = process.env.JWT_SECRET;
if (!JWT_SECRET) {
  throw new Error('Fatal: JWT_SECRET environment variable must be set. Do not use hardcoded secrets in production!');
}
```

**Setup Instructions:**
1. Copy `.env.example` to `.env` (for development)
2. Generate a strong JWT_SECRET: `openssl rand -base64 32`
3. Set `JWT_SECRET` in `.env` or environment

**Impact:** Authentication tokens cannot be forged without knowing the secret.

---

## Remaining Considerations

### Medium Priority: Incomplete Comment Voting System
Comments don't have a vote mechanism like posts do. Consider:
- Adding votes field to comments data structure
- Creating `/api/votes/:commentId` endpoint
- Updating UI to display comment votes

### Recommendations for Production

1. **Database Migration**: Replace file-based storage with a real database (MongoDB, PostgreSQL, SQLite)
   - Provides transaction support and true concurrency control
   - Better performance and scalability
   
2. **Secrets Management**: Use a proper secrets manager (AWS Secrets Manager, HashiCorp Vault)
   - Don't store `.env` in git
   - Rotate JWT_SECRET regularly

3. **Input Validation**: Add server-side validation for all user inputs
   - Currently only frontend validation exists
   - Validate length, format, content type

4. **CSRF Protection**: Implement CSRF tokens for state-changing requests
   
5. **Rate Limiting**: Add rate limiting to prevent abuse

6. **Logging & Monitoring**: Add security event logging

---

## Testing the Fixes

### XSS Protection Test
```javascript
// Try creating post with malicious title
const payload = {
  title: 'Test<img src=x onerror="alert(1)">',
  excerpt: 'excerpt',
  content: 'content'
};
// Should display escaped HTML, not execute script
```

### Race Condition Test
```bash
# Run concurrent requests
for i in {1..10}; do
  curl -X POST http://localhost:3000/api/votes/1 \
    -H "Authorization: Bearer $TOKEN" &
done
wait
# All votes should be counted, no data loss
```

### JWT_SECRET Requirement Test
```bash
# Without JWT_SECRET, server should fail to start
unset JWT_SECRET
npm start  # Should throw error
```
