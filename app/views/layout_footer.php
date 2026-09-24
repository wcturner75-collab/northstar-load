</main>
<footer class="site-footer">
  <div class="shell footer-inner">
    <span><?= \Northstar\Security::e($config['app']['brand'] ?? 'Northstar Scripts') ?></span>
    <span>Loading screens without hand-editing resources.</span>
  </div>
</footer>
<script src="/assets/js/app.js"></script>
<?php if (!empty($extraJs)): ?>
<script src="<?= \Northstar\Security::e($extraJs) ?>"></script>
<?php endif; ?>
</body>
</html>
