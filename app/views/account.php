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
        <dt>Components / project</dt><dd><?= (int) ($limits['max_components'] ?? 0) ?></dd>
        <dt>Builds / day</dt><dd><?= (int) ($limits['max_builds_per_day'] ?? 0) ?></dd>
      </dl>
    </div>
  </div>

  <section class="plan-matrix">
    <h2>Feature access</h2>
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
            ['Uploaded music (MP3/OGG)', 'music_file'],
            ['YouTube music (hidden embed)', 'youtube_music'],
            ['Slideshow backgrounds', 'slideshow_background'],
            ['Video backgrounds', 'video_background'],
            ['Staff component', 'staff'],
            ['Announcements component', 'announcements'],
            ['Ken Burns effect', 'ken_burns'],
        ];
        $check = static function (bool $on): string {
            return $on ? 'Yes' : '—';
        };
        $your = $limits['features'] ?? [];
        $plans = $config['entitlements'] ?? [];
        foreach ($rows as [$label, $key]):
            $free = !empty($plans['free']['features'][$key]);
            $std = !empty($plans['standard']['features'][$key]);
            $pro = !empty($plans['pro']['features'][$key]);
            $mine = !empty($your[$key]);
        ?>
        <tr>
          <td><?= \Northstar\Security::e($label) ?></td>
          <td><?= $check($mine) ?></td>
          <td><?= $check($free) ?></td>
          <td><?= $check($std) ?></td>
          <td><?= $check($pro) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p class="muted">Payment providers (Stripe / PayPal) can plug into the entitlements table later — not enabled in this phase. Admins can change a user’s plan in MySQL <code>entitlements</code>.</p>
  </section>
</section>
