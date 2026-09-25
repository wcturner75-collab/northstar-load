<section class="shell page-dash">
  <header class="page-head">
    <div>
      <h1>Plans</h1>
      <?php if (empty($billingEnabled)): ?>
        <p class="muted">Free is live now. Paid hosted tiers are priced for real media + bandwidth cost — billing unlocks later. If Load goes open source, self-host stays free; cloud hosting stays premium.</p>
      <?php else: ?>
        <p class="muted">Hosted Northstar Load plans. Self-host / open-source builds don’t include our cloud media delivery.</p>
      <?php endif; ?>
    </div>
  </header>

  <p class="plan-current">Current plan: <strong id="current-plan-label"><?= \Northstar\Security::e(strtoupper($currentPlan ?? 'free')) ?></strong></p>

  <div class="choice-cards plan-cards plan-switcher" id="plan-switcher">
    <?php foreach ($catalog as $card):
      $key = $card['key'];
      $isCurrent = ($currentPlan ?? 'free') === $key;
      $selectable = !empty($card['selectable']);
    ?>
      <article class="choice-card plan-pick <?= $isCurrent ? 'is-current' : '' ?> <?= !$selectable ? 'is-locked' : '' ?>" data-plan="<?= \Northstar\Security::e($key) ?>">
        <span class="choice-body">
          <strong><?= \Northstar\Security::e($card['label']) ?></strong>
          <?php if (!empty($card['price'])): ?>
            <span class="plan-price"><?= \Northstar\Security::e($card['price']) ?></span>
            <?php if (!empty($card['price_note'])): ?>
              <span class="plan-price-note"><?= \Northstar\Security::e($card['price_note']) ?></span>
            <?php endif; ?>
          <?php endif; ?>
          <em><?= $selectable ? \Northstar\Security::e($card['blurb']) : 'Coming soon — billing not set up' ?></em>
          <ul class="plan-highlights">
            <?php foreach ($card['highlights'] as $h): ?>
              <li><?= \Northstar\Security::e($h) ?></li>
            <?php endforeach; ?>
          </ul>
          <?php if ($isCurrent): ?>
            <button type="button" class="btn btn-ghost" disabled>Current plan</button>
          <?php elseif ($selectable): ?>
            <button type="button" class="btn btn-primary btn-choose-plan" data-plan="<?= \Northstar\Security::e($key) ?>">
              Switch to <?= \Northstar\Security::e($card['label']) ?>
            </button>
          <?php else: ?>
            <button type="button" class="btn btn-ghost" disabled>Unavailable</button>
          <?php endif; ?>
        </span>
      </article>
    <?php endforeach; ?>
  </div>

  <p class="muted" style="margin-top:1.25rem">Need the comparison table? See <a href="/account">Account</a>.</p>
</section>
