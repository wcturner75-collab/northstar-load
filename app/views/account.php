<section class="shell page-dash">
  <header class="page-head">
    <div>
      <h1>Account</h1>
      <p class="muted">Profile, editor preference, and plan — Free can ship a complete loading screen.</p>
    </div>
  </header>
  <div class="account-grid">
    <div>
      <h2>Profile</h2>
      <dl class="kv">
        <dt>Username</dt><dd><?= \Northstar\Security::e($user['username'] ?? '') ?></dd>
        <dt>Email</dt><dd><?= \Northstar\Security::e($user['email'] ?? '') ?></dd>
        <dt>Member since</dt><dd><?= \Northstar\Security::e($user['created_at'] ?? '') ?></dd>
        <dt>Editor mode</dt><dd><?= \Northstar\Security::e(ucfirst((string) ($user['editor_mode'] ?? 'simple'))) ?></dd>
      </dl>
      <form class="stack-form" id="editor-mode-form" style="margin-top:1rem;max-width:320px">
        <label>Customization style
          <select name="editor_mode">
            <option value="simple" <?= (($user['editor_mode'] ?? '') === 'simple') ? 'selected' : '' ?>>Simple (guided)</option>
            <option value="advanced" <?= (($user['editor_mode'] ?? '') === 'advanced') ? 'selected' : '' ?>>Advanced (canvas)</option>
          </select>
        </label>
        <button class="btn btn-primary" type="submit">Save preference</button>
        <p class="muted" id="mode-save-status"></p>
      </form>
    </div>
    <div>
      <h2>Entitlements</h2>
      <dl class="kv">
        <dt>Product</dt><dd>Northstar Load</dd>
        <dt>Plan</dt><dd id="account-plan-label"><?= \Northstar\Security::e(strtoupper($limits['plan'] ?? 'free')) ?></dd>
        <dt>Project limit</dt><dd><?= (int) ($limits['max_projects'] ?? 0) ?></dd>
        <dt>Media limit</dt><dd><?= (int) ($limits['max_media'] ?? 0) ?></dd>
        <dt>Components / project</dt><dd><?= (int) ($limits['max_components'] ?? 0) ?></dd>
        <dt>Builds / day</dt><dd><?= (int) ($limits['max_builds_per_day'] ?? 0) ?></dd>
      </dl>
      <p style="margin-top:1rem"><a class="btn btn-primary" href="/plans">Change plan</a></p>
    </div>
  </div>

  <section class="plan-matrix" id="change-plan">
    <h2>Change plan</h2>
    <p class="muted">Pick a plan below — applies immediately (billing not connected yet).</p>
    <div class="choice-cards plan-cards plan-switcher">
      <?php
      $catalog = \Northstar\Entitlement::catalog();
      $currentPlan = $limits['plan'] ?? 'free';
      foreach ($catalog as $card):
        $key = $card['key'];
        $isCurrent = $currentPlan === $key;
      ?>
        <article class="choice-card plan-pick <?= $isCurrent ? 'is-current' : '' ?>" data-plan="<?= \Northstar\Security::e($key) ?>">
          <span class="choice-body">
            <strong><?= \Northstar\Security::e($card['label']) ?></strong>
            <em><?= \Northstar\Security::e($card['blurb']) ?></em>
            <ul class="plan-highlights">
              <?php foreach ($card['highlights'] as $h): ?>
                <li><?= \Northstar\Security::e($h) ?></li>
              <?php endforeach; ?>
            </ul>
            <?php if ($isCurrent): ?>
              <button type="button" class="btn btn-ghost" disabled>Current plan</button>
            <?php else: ?>
              <button type="button" class="btn btn-primary btn-choose-plan" data-plan="<?= \Northstar\Security::e($key) ?>">
                Switch to <?= \Northstar\Security::e($card['label']) ?>
              </button>
            <?php endif; ?>
          </span>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="plan-matrix">
    <h2>What each plan is for</h2>
    <p class="muted">We don’t lock the core experience behind a paywall. Paid plans buy headroom and studio extras.</p>
    <table>
      <thead>
        <tr>
          <th>Feature</th>
          <th>Your plan</th>
          <th>Free</th>
          <th>Standard</th>
          <th>Pro</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $rows = [
            ['Complete loadscreen + ZIP generate', null, true, true, true],
            ['Uploaded music (MP3/OGG)', 'music_file'],
            ['YouTube music (hidden embed)', 'youtube_music'],
            ['Slideshow backgrounds', 'slideshow_background'],
            ['Staff + announcements', 'staff'],
            ['Ken Burns motion', 'ken_burns'],
            ['Video backgrounds', 'video_background'],
        ];
        $check = static function (bool $on): string {
            return $on ? 'Yes' : '—';
        };
        $your = $limits['features'] ?? [];
        $plans = $config['entitlements'] ?? [];
        foreach ($rows as $row):
            $label = $row[0];
            $key = $row[1];
            if ($key === null) {
                $mine = $free = $std = $pro = true;
            } else {
                $free = !empty($plans['free']['features'][$key]);
                $std = !empty($plans['standard']['features'][$key]);
                $pro = !empty($plans['pro']['features'][$key]);
                // staff row also represents announcements
                if ($key === 'staff') {
                    $free = $free && !empty($plans['free']['features']['announcements']);
                    $std = $std && !empty($plans['standard']['features']['announcements']);
                    $pro = $pro && !empty($plans['pro']['features']['announcements']);
                    $mine = !empty($your['staff']) && !empty($your['announcements']);
                } else {
                    $mine = !empty($your[$key]);
                }
            }
        ?>
        <tr>
          <td><?= \Northstar\Security::e($label) ?></td>
          <td><?= $check($mine) ?></td>
          <td><?= $check($free) ?></td>
          <td><?= $check($std) ?></td>
          <td><?= $check($pro) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
          <td>Projects / media / daily builds</td>
          <td><?= (int)$limits['max_projects'] ?> / <?= (int)$limits['max_media'] ?> / <?= (int)$limits['max_builds_per_day'] ?></td>
          <td>5 / 60 / 15</td>
          <td>25 / 250 / 50</td>
          <td>200 / 2000 / 200</td>
        </tr>
      </tbody>
    </table>
    <p class="muted">Stripe/PayPal can replace early-access plan grants later.</p>
  </section>
</section>
