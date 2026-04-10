<?php
$admin = currentAdmin();
?>
<header class="admin-header">
  <div class="admin-header-left">
    <button id="sbToggle" style="background:none;border:none;padding:6px;cursor:pointer;border-radius:6px;display:none" onclick="document.getElementById('sidebar').classList.toggle('open')">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <div>
      <div class="page-title"><?= $pageTitle ?? 'Dashboard' ?></div>
    </div>
  </div>
  <div class="admin-header-right">
    <a href="/" target="_blank" style="display:flex;align-items:center;gap:6px;font-size:12px;color:#888;padding:6px 12px;border-radius:7px;border:1px solid #e2e5ea">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      Siteyi Gor
    </a>
    <div class="admin-user">
      <div class="admin-avatar"><?= strtoupper(substr($admin['name'] ?: $admin['username'],0,1)) ?></div>
      <span><?= htmlspecialchars($admin['name'] ?: $admin['username']) ?></span>
    </div>
    <a href="/admin/logout.php" class="btn-logout">Çıkış</a>
  </div>
</header>
<style>
@media(max-width:1024px){#sbToggle{display:flex!important}}
</style>
