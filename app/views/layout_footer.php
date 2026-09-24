</main>
<footer class="site-footer">
  <div class="shell footer-inner">
    <span><?= \Northstar\Security::e($config['app']['brand'] ?? 'Northstar Scripts') ?></span>
    <span>Loading screens without hand-editing resources.</span>
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
</body>
</html>
