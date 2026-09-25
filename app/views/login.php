<section class="auth-section shell">
  <div class="auth-panel">
    <h1>Login</h1>
    <p class="muted">Access your Northstar Load projects.</p>
    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?= \Northstar\Security::e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/login" class="stack-form">
      <input type="hidden" name="_csrf" value="<?= \Northstar\Security::e($csrf) ?>">
      <label>Email
        <input type="email" name="email" required autocomplete="username" value="<?= \Northstar\Security::e($email ?? '') ?>">
      </label>
      <label>Password
        <input type="password" name="password" required autocomplete="current-password">
      </label>
      <button class="btn btn-primary" type="submit">Sign in</button>
    </form>
    <p class="muted">No account? <a href="/register">Create one</a></p>
  </div>
</section>
