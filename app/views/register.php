<section class="auth-section shell register-wide">
  <div class="auth-panel auth-panel-wide">
    <h1>Create account</h1>
    <p class="muted">Free includes a complete loading-screen builder. Paid plans will open when billing is ready.</p>
    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?= \Northstar\Security::e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/register" class="stack-form" id="register-form">
      <input type="hidden" name="_csrf" value="<?= \Northstar\Security::e($csrf) ?>">
      <input type="hidden" name="plan" value="free">

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
        <legend>Plan</legend>
        <p class="choice-lead">Billing is not set up yet — new accounts start on Free.</p>
        <div class="choice-cards plan-cards">
          <?php foreach (($catalog ?? \Northstar\Entitlement::catalog()) as $card):
            $key = $card['key'];
            $selectable = !empty($card['selectable']);
            $isFree = $key === 'free';
          ?>
            <label class="choice-card <?= $isFree ? 'recommended' : '' ?> <?= !$selectable ? 'is-locked' : '' ?>">
              <input type="radio"
                     name="plan_ui"
                     value="<?= \Northstar\Security::e($key) ?>"
                     <?= $isFree ? 'checked' : '' ?>
                     <?= $selectable ? '' : 'disabled' ?>
                     <?= $selectable ? '' : 'tabindex="-1"' ?>>
              <span class="choice-body">
                <strong><?= \Northstar\Security::e($card['label']) ?></strong>
                <em><?= $selectable ? \Northstar\Security::e($card['blurb']) : 'Coming soon' ?></em>
                <small><?= $selectable
                  ? 'Complete loadscreen: branding, YouTube or file music, slideshow, staff, announcements, generate ZIP.'
                  : 'Unavailable until billing is connected.' ?></small>
              </span>
            </label>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <button class="btn btn-primary" type="submit">Create account</button>
    </form>
    <p class="muted">Already registered? <a href="/login">Login</a></p>
  </div>
</section>
