<section class="shell page-dash">
  <header class="page-head">
    <div>
      <h1>Account</h1>
      <p class="muted">Profile and plan information.</p>
    </div>
  </header>
  <div class="account-grid">
    <div>
      <h2>Profile</h2>
      <dl class="kv">
        <dt>Username</dt><dd><?= \Northstar\Security::e($user['username'] ?? '') ?></dd>
        <dt>Email</dt><dd><?= \Northstar\Security::e($user['email'] ?? '') ?></dd>
        <dt>Member since</dt><dd><?= \Northstar\Security::e($user['created_at'] ?? '') ?></dd>
      </dl>
    </div>
    <div>
      <h2>Entitlements</h2>
      <dl class="kv">
        <dt>Product</dt><dd>Northstar Load</dd>
        <dt>Plan</dt><dd><?= \Northstar\Security::e(strtoupper($limits['plan'] ?? 'free')) ?></dd>
        <dt>Project limit</dt><dd><?= (int) ($limits['max_projects'] ?? 0) ?></dd>
        <dt>Media limit</dt><dd><?= (int) ($limits['max_media'] ?? 0) ?></dd>
        <dt>Builds / day</dt><dd><?= (int) ($limits['max_builds_per_day'] ?? 0) ?></dd>
      </dl>
      <p class="muted">Payment providers (Stripe / PayPal) can plug into the entitlements table later — not enabled in this phase.</p>
    </div>
  </div>
</section>
