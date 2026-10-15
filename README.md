# MUGBUZZ

Torn City intelligence tool. Scans bazaar listings, filters by seller age and offline time, surfaces underpriced items vs. market value.

## Files

| File | Purpose |
|------|---------|
| `index.html` | Full frontend — self-contained, no build step |
| `proxy.php` | Server-side API proxy — required for TornPal and FFscout |

## Deployment

### Option A — With proxy (recommended)
1. Upload both `index.html` and `proxy.php` to the same directory on any PHP host.
2. Open `index.html` in a browser.
3. Hit ⚙ Settings, paste your Torn API key, save.

### Option B — Without proxy (Torn only, no TornPal)
1. Open `index.html` directly in a browser (file:// or any host).
2. In the script block, set `USE_PROXY = false`.
3. TornPal listings won't load. Everything else works.

## proxy.php config

Edit the top of `proxy.php` before deploying publicly:

```php
define('ALLOWED_ORIGIN', 'https://yourdomain.com'); // or '*' for dev
```

This controls which origins can call the proxy. Lock it to your domain so your proxy isn't an open relay.

## API Keys

- **Torn API key** — [torn.com → Preferences → API](https://www.torn.com/preferences.php#tab=api). Public access level minimum.
- **FFscout key** — optional. Required for battle stats fetch.

Keys are stored in `localStorage` in your browser only. Never sent anywhere except `api.torn.com` (via proxy).

## Rate limits

Torn: 100 requests/min. The scanner sleeps 300ms between profile calls. Stay under with default settings.
