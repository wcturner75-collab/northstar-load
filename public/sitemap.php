<?php

declare(strict_types=1);

/**
 * Minimal sitemap for search / AdSense crawlers.
 */
header('Content-Type: application/xml; charset=UTF-8');
header('Cache-Control: public, max-age=3600');

$base = 'https://load.northstarscripts.us';
$pages = [
    '/',
    '/docs',
    '/plans',
    '/login',
    '/register',
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as $path): ?>
  <url>
    <loc><?= htmlspecialchars($base . $path, ENT_XML1) ?></loc>
    <changefreq>weekly</changefreq>
  </url>
<?php endforeach; ?>
</urlset>
