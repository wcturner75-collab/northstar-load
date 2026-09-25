<section class="shell page-dash">
  <header class="page-head">
    <div>
      <h1>Plans</h1>
      <p class="muted">Change your Northstar Load plan anytime. Billing isn’t connected yet — switches apply immediately for early access.</p>
    </div>
  </header>

  <p class="plan-current">Current plan: <strong id="current-plan-label"><?= \Northstar\Security::e(strtoupper($currentPlan ?? 'free')) ?></strong></p>

  <div class="choice-cards plan-cards plan-switcher" id="plan-switcher">
    <?php foreach ($catalog as $card):
      $key = $card['key'];
      $isCurrent = ($currentPlan ?? 'free') === $key;
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

  <p class="muted" style="margin-top:1.25rem">Need the comparison table? See <a href="/account">Account</a>.</p>
</section>
