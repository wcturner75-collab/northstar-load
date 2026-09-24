<section class="auth-section shell">
  <div class="auth-panel">
    <h1>Create account</h1>
    <p class="muted">Free plan includes projects, media, and resource generation.</p>
    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?= \Northstar\Security::e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/register.php" class="stack-form">
      <input type="hidden" name="_csrf" value="<?= \Northstar\Security::e($csrf) ?>">
      <label>Email
        <input type="email" name="email" required autocomplete="email" value="<?= \Northstar\Security::e($email ?? '') ?>">
      </label>
      <label>Username
        <input type="text" name="username" required pattern="[A-Za-z0-9_]{3,32}" autocomplete="username" value="<?= \Northstar\Security::e($username ?? '') ?>">
      </label>
      <label>Password
        <input type="password" name="password" required minlength="8" autocomplete="new-password">
      </label>
      <button class="btn btn-primary" type="submit">Create account</button>
    </form>
    <p class="muted">Already registered? <a href="/login.php">Login</a></p>
  </div>
</section>
