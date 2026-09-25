</main>
<footer class="site-footer">
  <?php
  $adsense = is_array(($config['adsense'] ?? null)) ? $config['adsense'] : [];
  $adsenseEnabled = !empty($adsense['enabled']) && !empty($adsense['client']);
  $adsenseClient = (string) ($adsense['client'] ?? '');
  $adsenseSlot = trim((string) ($adsense['slot'] ?? ''));
  ?>
  <?php if ($adsenseEnabled): ?>
  <div class="shell footer-ad" aria-label="Advertisement">
    <?php if ($adsenseSlot !== ''): ?>
      <ins class="adsbygoogle"
           style="display:block"
           data-ad-client="<?= \Northstar\Security::e($adsenseClient) ?>"
           data-ad-slot="<?= \Northstar\Security::e($adsenseSlot) ?>"
           data-ad-format="auto"
           data-full-width-responsive="true"></ins>
      <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
    <?php else: ?>
      <!-- AdSense client loaded in <head>; enable Auto ads in AdSense, or set adsense.slot in config for a display unit here. -->
      <div class="footer-ad-note muted">Sponsored</div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <div class="shell footer-inner">
    <a class="footer-brand" href="/">NORTHSTAR <span>LOAD</span></a>
    <span class="footer-note">Visual loading screens for FiveM.</span>
  </div>
</footer>
<script src="/assets/js/app.js"></script>
<?php
$scripts = [];
if (!empty($extraJs)) {
    $scripts = is_array($extraJs) ? $extraJs : [$extraJs];
}
foreach ($scripts as $src):
    if (!is_string($src) || $src === '') continue;
?>
<script src="<?= \Northstar\Security::e($src) ?>"></script>
<?php endforeach; ?>
<?php require NORTHSTAR_ROOT . '/app/views/partials/tawk.php'; ?>
</body>
</html>
