<section class="shell page-dash">
  <header class="page-head">
    <div>
      <h1>Media library</h1>
      <p class="muted">PNG, JPG, WEBP, MP3, OGG, MP4 — validated uploads with random storage names.</p>
    </div>
  </header>

  <form class="upload-panel" id="media-upload-form">
    <input type="hidden" name="_csrf" value="<?= \Northstar\Security::e($csrf) ?>">
    <label class="upload-drop">
      <span>Drop files or click to upload</span>
      <input type="file" name="file" accept=".png,.jpg,.jpeg,.webp,.mp3,.ogg,.mp4" required>
    </label>
    <button class="btn btn-primary" type="submit">Upload</button>
    <p class="muted" id="upload-status"></p>
  </form>

  <div class="media-grid" id="media-grid">
    <?php foreach ($items as $m): ?>
      <article class="media-item" data-id="<?= (int) $m['id'] ?>">
        <div class="media-thumb kind-<?= \Northstar\Security::e($m['kind']) ?>">
          <?php if ($m['kind'] === 'image'): ?>
            <img src="/api/media/serve.php?id=<?= (int) $m['id'] ?>" alt="">
          <?php else: ?>
            <span><?= strtoupper(\Northstar\Security::e($m['kind'])) ?></span>
          <?php endif; ?>
        </div>
        <div class="media-meta">
          <strong title="<?= \Northstar\Security::e($m['original_name']) ?>"><?= \Northstar\Security::e($m['original_name']) ?></strong>
          <span><?= number_format(((int)$m['size_bytes']) / 1024, 1) ?> KB</span>
          <button type="button" class="btn btn-small btn-danger" data-delete-media="<?= (int) $m['id'] ?>">Delete</button>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<script src="/assets/js/media.js"></script>
