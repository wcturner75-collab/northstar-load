<section class="auth-section shell register-wide">
  <div class="auth-panel auth-panel-wide">
    <h1>Create account</h1>
    <p class="muted">Free can ship a full loading screen. Paid plans add capacity and polish — not a paywall on the basics.</p>
    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?= \Northstar\Security::e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/register.php" class="stack-form" id="register-form">
      <input type="hidden" name="_csrf" value="<?= \Northstar\Security::e($csrf) ?>">

      <div class="form-grid-2">
        <label>Email
          <input type="email" name="email" required autocomplete="email" value="<?= \Northstar\Security::e($email ?? '') ?>">
        </label>
        <label>Username
          <input type="text" name="username" required pattern="[A-Za-z0-9_]{3,32}" autocomplete="username" value="<?= \Northstar\Security::e($username ?? '') ?>">
        </label>
      </div>
      <label>Password
        <input type="password" name="password" required minlength="8" autocomplete="new-password">
      </label>

      <fieldset class="choice-set">
        <legend>How do you want to customize?</legend>
        <p class="choice-lead">You can switch anytime in the editor.</p>
        <div class="choice-cards">
          <label class="choice-card">
            <input type="radio" name="editor_mode" value="simple" <?= ($editorMode ?? 'simple') === 'simple' ? 'checked' : '' ?>>
            <span class="choice-body">
              <strong>Simple</strong>
              <em>Best for newbies</em>
              <small>Guided setup: name, colors, music, rules — without wrestling the full canvas.</small>
            </span>
          </label>
          <label class="choice-card">
            <input type="radio" name="editor_mode" value="advanced" <?= ($editorMode ?? '') === 'advanced' ? 'checked' : '' ?>>
            <span class="choice-body">
              <strong>Advanced</strong>
              <em>Full creative control</em>
              <small>Drag components, layers, snap guides, and per-element properties.</small>
            </span>
          </label>
        </div>
      </fieldset>

      <fieldset class="choice-set">
        <legend>Choose a plan</legend>
        <p class="choice-lead">Billing isn’t connected yet — your selection is applied for early access. Start on Free if you’re unsure.</p>
        <div class="choice-cards plan-cards">
          <label class="choice-card recommended">
            <input type="radio" name="plan" value="free" <?= ($plan ?? 'free') === 'free' ? 'checked' : '' ?>>
            <span class="choice-body">
              <strong>Free</strong>
              <em>Recommended to start</em>
              <small>Complete loadscreen: branding, YouTube or file music, slideshow, staff, announcements, generate ZIP.</small>
            </span>
          </label>
          <label class="choice-card">
            <input type="radio" name="plan" value="standard" <?= ($plan ?? '') === 'standard' ? 'checked' : '' ?>>
            <span class="choice-body">
              <strong>Standard</strong>
              <em>More room to grow</em>
              <small>Higher project/media/build limits + Ken Burns motion.</small>
            </span>
          </label>
          <label class="choice-card">
            <input type="radio" name="plan" value="pro" <?= ($plan ?? '') === 'pro' ? 'checked' : '' ?>>
            <span class="choice-body">
              <strong>Pro</strong>
              <em>Studio capacity</em>
              <small>Video backgrounds, highest limits, everything unlocked.</small>
            </span>
          </label>
        </div>
      </fieldset>

      <button class="btn btn-primary" type="submit">Create account</button>
    </form>
    <p class="muted">Already registered? <a href="/login.php">Login</a></p>
  </div>
</section>
