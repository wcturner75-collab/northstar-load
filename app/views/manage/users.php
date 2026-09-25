<section class="shell page-dash page-manage">
  <header class="page-head">
    <div>
      <p class="eyebrow">Management</p>
      <h1>Users</h1>
      <p class="muted">Disable accounts or grant manager access.</p>
    </div>
    <a class="btn btn-ghost" href="/manage/">Back</a>
  </header>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>User</th>
        <th>Plan</th>
        <th>Role</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (($users ?? []) as $u): ?>
        <tr data-user-id="<?= (int) $u['id'] ?>">
          <td class="mono"><?= (int) $u['id'] ?></td>
          <td>
            <strong><?= \Northstar\Security::e((string) $u['username']) ?></strong><br>
            <span class="muted"><?= \Northstar\Security::e((string) $u['email']) ?></span>
          </td>
          <td><?= \Northstar\Security::e(strtoupper((string) ($u['plan_key'] ?? 'free'))) ?></td>
          <td><?= \Northstar\Security::e((string) ($u['role'] ?? 'user')) ?></td>
          <td><?= \Northstar\Security::e((string) ($u['status'] ?? 'active')) ?></td>
          <td class="row-actions">
            <?php if (($u['status'] ?? '') === 'active'): ?>
              <button type="button" class="btn btn-small btn-danger" data-act="status" data-status="disabled">Disable</button>
            <?php else: ?>
              <button type="button" class="btn btn-small" data-act="status" data-status="active">Enable</button>
            <?php endif; ?>
            <?php if (($role ?? '') === 'admin'): ?>
              <button type="button" class="btn btn-small" data-act="role" data-role="manager">Make manager</button>
              <button type="button" class="btn btn-small" data-act="role" data-role="user">Make user</button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
