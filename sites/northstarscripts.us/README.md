# northstarscripts.us (apex site)

Static site for Google AdSense verification and the studio homepage.

## Upload to DirectAdmin

1. Open File Manager → `domains/northstarscripts.us/public_html/` (main domain, **not** the `load` subdomain).
2. Upload **everything inside** this folder to that `public_html` root:
   - `index.html`, `privacy.html`, `contact.html`
   - `ads.txt`, `robots.txt`, `sitemap.xml`, `.htaccess`
   - `assets/site.css`
3. Confirm these URLs return **200**:
   - https://northstarscripts.us/
   - https://northstarscripts.us/ads.txt
   - https://northstarscripts.us/robots.txt
4. In AdSense → Sites, add `northstarscripts.us` and choose verification (script, ads.txt, or meta — all three are already on this site).
5. Cloudflare: purge cache; do **not** cache `ads.txt`.

## ads.txt line

```
google.com, pub-2047679408348701, DIRECT, f08c47fec0942fa0
```

Load subdomain has the same line at `load.northstarscripts.us/ads.txt`.
