<?php
// _sidebar.php — version sous-dossier (utilisateurs/, materiaux/, etc.)
// Include depuis n'importe quel sous-dossier admin

require_once '../config.php';

$nb_users   = $conn->query("SELECT COUNT(*) FROM UTILISATEUR")->fetch_row()[0] ?? 0;
$nb_mats    = $conn->query("SELECT COUNT(*) FROM MATERIAU")->fetch_row()[0] ?? 0;
$nb_attente = $conn->query("SELECT COUNT(*) FROM RESERVATION WHERE statut_reservation='en attente'")->fetch_row()[0] ?? 0;
$nb_msgs    = 0;
$chk = $conn->query("SHOW TABLES LIKE 'MESSAGE'");
if ($chk && $chk->num_rows > 0) $nb_msgs = $conn->query("SELECT COUNT(*) FROM MESSAGE")->fetch_row()[0] ?? 0;
$nb_sigs = 0;
$chk2 = $conn->query("SHOW TABLES LIKE 'SIGNALEMENT'");
if ($chk2 && $chk2->num_rows > 0) {
    $nb_sigs = $conn->query("SELECT COUNT(*) FROM SIGNALEMENT")->fetch_row()[0] ?? 0;
}

$cur = basename(dirname($_SERVER['PHP_SELF']));

// ── Chemin de base absolu ────────────────────────────────────────────────────
define('BASE_URL', '/reemploi-btp');
?>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{font-family:'Outfit',sans-serif;background:#f0f4f1;color:#1f2937;display:flex;min-height:100vh}
a{text-decoration:none;color:inherit}ul{list-style:none}

.adm-sidebar{width:240px;min-height:100vh;background:linear-gradient(180deg,#1b4332 0%,#2d6a4f 100%);display:flex;flex-direction:column;position:fixed;left:0;top:0;z-index:100;box-shadow:4px 0 20px rgba(0,0,0,.18)}
.adm-sidebar-logo{padding:1.4rem 1.3rem 1rem;display:flex;align-items:center;gap:.55rem;font-family:'Playfair Display',serif;font-size:1.15rem;font-weight:800;color:#fff;border-bottom:1px solid rgba(255,255,255,.1)}
.adm-sidebar-logo .lf{font-size:1.5rem}
.adm-admin-badge{margin:.85rem 1.1rem;padding:.42rem .85rem;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);border-radius:8px;font-size:.75rem;color:rgba(255,255,255,.75);display:flex;align-items:center;gap:.4rem}
.adm-nav{flex:1;padding:.5rem 0;overflow-y:auto}
.adm-sec{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.35);padding:.9rem 1.3rem .35rem}
.adm-lnk{display:flex;align-items:center;gap:.7rem;padding:.65rem 1.3rem;font-size:.875rem;font-weight:500;color:rgba(255,255,255,.68);border-left:3px solid transparent;transition:all .18s}
.adm-lnk:hover{color:#fff;background:rgba(255,255,255,.09)}
.adm-lnk.active{color:#fff;background:rgba(255,255,255,.14);border-left-color:#52b788;font-weight:600}
.adm-lnk .ic{font-size:1rem;width:22px;text-align:center;flex-shrink:0}
.adm-lnk .lb{flex:1}
.adm-badge{background:#52b788;color:#1b4332;font-size:.68rem;font-weight:800;padding:.15rem .5rem;border-radius:999px;min-width:20px;text-align:center}
.adm-badge.or{background:#fbbf24;color:#78350f}
.adm-badge.rd{background:#f87171;color:#7b0000}
.adm-bottom{padding:.9rem 1.1rem 1.2rem;border-top:1px solid rgba(255,255,255,.1)}
.adm-out{display:flex;align-items:center;gap:.55rem;padding:.55rem .85rem;border-radius:8px;font-size:.875rem;color:rgba(255,255,255,.55);transition:all .18s}
.adm-out:hover{color:#fff;background:rgba(255,255,255,.09)}

.adm-topbar{position:fixed;top:0;left:240px;right:0;height:62px;z-index:90;background:#fff;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;padding:0 2rem;box-shadow:0 1px 4px rgba(0,0,0,.06)}
.adm-topbar-title{font-family:'Playfair Display',serif;font-size:1.2rem;font-weight:800;color:#1f2937}
.adm-topbar-right{display:flex;align-items:center;gap:1rem;font-size:.85rem;color:#6b7280}
.adm-topbar-right .ae{font-weight:600;color:#2d6a4f}
.adm-topbar-right a{color:#2d6a4f;font-weight:600}

.adm-main{margin-left:240px;margin-top:62px;flex:1;min-height:calc(100vh - 62px);padding:2rem;background:#f0f4f1}

.flash-ok{background:#d8f3dc;color:#1b4332;border:1px solid #52b788;border-radius:8px;padding:.75rem 1rem;margin-bottom:1.5rem;font-size:.875rem;font-weight:600}
.flash-err{background:#ffe0e0;color:#7b0000;border:1px solid #f5a5a5;border-radius:8px;padding:.75rem 1rem;margin-bottom:1.5rem;font-size:.875rem;font-weight:600}

.adm-card{background:#fff;border-radius:14px;border:1px solid #e5e7eb;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden;margin-bottom:1.75rem}
.adm-card-header{padding:1rem 1.4rem;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;background:#fafafa}
.adm-card-title{font-weight:700;font-size:.92rem;color:#1f2937}
.adm-card-count{font-size:.75rem;color:#9ca3af;background:#f3f4f6;padding:.2rem .65rem;border-radius:999px}

table{width:100%;border-collapse:collapse;font-size:.85rem}
thead th{padding:.72rem 1.1rem;text-align:left;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;background:#f9fafb;border-bottom:1px solid #e5e7eb}
tbody tr{border-bottom:1px solid #f3f4f6;transition:background .12s}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:#f9fafb}
tbody td{padding:.78rem 1.1rem;vertical-align:middle}
.td-name{font-weight:600;color:#1f2937}
.td-muted{color:#6b7280;font-size:.82rem}
.td-actions{display:flex;gap:.4rem;flex-wrap:wrap;align-items:center}

.pill{padding:.25rem .7rem;border-radius:999px;font-size:.7rem;font-weight:700;display:inline-block}
.pill-green{background:#d8f3dc;color:#1b4332}
.pill-yellow{background:#fff8e1;color:#856404}
.pill-red{background:#ffe0e0;color:#7b0000}
.pill-blue{background:#dbeafe;color:#1e40af}
.pill-purple{background:#ede9fe;color:#5b21b6}
.pill-gray{background:#f3f4f6;color:#6b7280}

.btn{padding:.35rem .85rem;border-radius:8px;font-size:.78rem;font-weight:700;cursor:pointer;border:none;font-family:'Outfit',sans-serif;transition:all .18s;display:inline-flex;align-items:center;gap:.3rem}
.btn-danger{background:#ffe0e0;color:#7b0000}.btn-danger:hover{background:#c0392b;color:#fff}
.btn-success{background:#d8f3dc;color:#1b4332}.btn-success:hover{background:#2d6a4f;color:#fff}
.btn-primary{background:#2d6a4f;color:#fff}.btn-primary:hover{background:#1b4332}
.btn-secondary{background:#f3f4f6;color:#1f2937}.btn-secondary:hover{background:#d1d5db}
.btn-sm{padding:.25rem .65rem;font-size:.72rem}

.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1.1rem;margin-bottom:2rem}
.stat-card{background:#fff;border-radius:12px;padding:1.2rem 1.3rem;border:1px solid #e5e7eb;box-shadow:0 2px 8px rgba(0,0,0,.05);transition:transform .2s,box-shadow .2s}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,0,0,.09)}
.stat-icon{font-size:1.7rem;margin-bottom:.55rem}
.stat-value{font-family:'Playfair Display',serif;font-size:1.9rem;font-weight:800;color:#2d6a4f;line-height:1;margin-bottom:.2rem}
.stat-label{font-size:.76rem;color:#6b7280;font-weight:600}
.stat-sub{font-size:.7rem;color:#9ca3af;margin-top:.15rem}

.section-title{font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:800;color:#1f2937;margin-bottom:.9rem;padding-bottom:.55rem;border-bottom:2px solid #d8f3dc}

.sel-statut{padding:.25rem .5rem;border:1px solid #e5e7eb;border-radius:7px;font-family:'Outfit',sans-serif;font-size:.78rem;color:#1f2937;background:#f9fafb;cursor:pointer}

.add-form{background:#fff;border-radius:12px;border:1px solid #e5e7eb;box-shadow:0 2px 8px rgba(0,0,0,.05);padding:1.2rem 1.5rem;margin-bottom:1.5rem;display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap}
.add-form .fg{flex:1;min-width:180px}
.add-form label{display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:.35rem}
.add-form input,.add-form select,.add-form textarea{width:100%;padding:.6rem .9rem;border:1.5px solid #e5e7eb;border-radius:8px;font-family:'Outfit',sans-serif;font-size:.9rem;color:#1f2937;background:#f9fafb;transition:border-color .2s}
.add-form input:focus,.add-form select:focus,.add-form textarea:focus{outline:none;border-color:#2d6a4f;background:#fff}
.add-form textarea{resize:vertical;min-height:80px}

@media(max-width:768px){.adm-sidebar{transform:translateX(-100%);transition:transform .3s}.adm-sidebar.open{transform:translateX(0)}.adm-main,.adm-topbar{margin-left:0;left:0}}
</style>

<aside class="adm-sidebar" id="admSidebar">
  <div class="adm-sidebar-logo"><span class="lf">🌿</span> RéEmploi <strong>BTP</strong></div>
  <div class="adm-admin-badge">⚙️ Administration</div>
  <nav class="adm-nav">
    <div class="adm-sec">Tableau de bord</div>
    <a href="<?= BASE_URL ?>/admin/index.php" class="adm-lnk <?= $cur==='admin'?'active':'' ?>">
      <span class="ic">📊</span><span class="lb">Dashboard</span>
    </a>

    <div class="adm-sec">Gestion</div>
    <a href="<?= BASE_URL ?>/utilisateurs/index.php" class="adm-lnk <?= $cur==='utilisateurs'?'active':'' ?>">
      <span class="ic">👥</span><span class="lb">Utilisateurs</span>
      <?php if($nb_users>0):?><span class="adm-badge"><?=$nb_users?></span><?php endif;?>
    </a>
     <a href="<?= BASE_URL ?>/entreprise/index.php" class="adm-lnk <?= $cur==='entreprise'?'active':'' ?>">
      <span class="ic">👥</span><span class="lb">Entreprise</span>
      
    </a>
    <a href="<?= BASE_URL ?>/materiau/index.php" class="adm-lnk <?= $cur==='materiaux'?'active':'' ?>">
      <span class="ic">📦</span><span class="lb">Matériaux</span>
      <?php if($nb_mats>0):?><span class="adm-badge"><?=$nb_mats?></span><?php endif;?>
    </a>
    <a href="<?= BASE_URL ?>/reservation/index.php" class="adm-lnk <?= $cur==='reservation'?'active':'' ?>">
      <span class="ic">📅</span><span class="lb">Réservations</span>
      <?php if($nb_attente>0):?><span class="adm-badge or"><?=$nb_attente?></span><?php endif;?>
    </a>
    <a href="<?= BASE_URL ?>/categorie/index.php" class="adm-lnk <?= $cur==='categorie'?'active':'' ?>">
      <span class="ic">🏷️</span><span class="lb">Catégories</span>
    </a>
    <a href="<?= BASE_URL ?>/message/index.php" class="adm-lnk <?= $cur==='message'?'active':'' ?>">
      <span class="ic">💬</span><span class="lb">Messages</span>
      <?php if($nb_msgs>0):?><span class="adm-badge rd"><?=$nb_msgs?></span><?php endif;?>
    </a>
    <a href="<?= BASE_URL ?>/signalement/index.php" class="adm-lnk <?= $cur==='signalement'?'active':'' ?>">
      <span class="ic">🚩</span><span class="lb">Signalements</span>
      <?php if($nb_sigs>0):?><span class="adm-badge rd"><?=$nb_sigs?></span><?php endif;?>
    </a>
    <a href="<?= BASE_URL ?>/impact/index.php" class="adm-lnk <?= $cur==='impact'?'active':'' ?>">
      <span class="ic">🌱</span><span class="lb">Impact</span>
    </a>

    <div class="adm-sec">Plateforme</div>
    <a href="<?= BASE_URL ?>/catalogue.php" class="adm-lnk" target="_blank">
      <span class="ic">🔗</span><span class="lb">Voir le site</span>
    </a>
  </nav>
  <div class="adm-bottom">
    <a href="<?= BASE_URL ?>/admin/logout.php" class="adm-out"><span>🚪</span> Déconnexion</a>
  </div>
</aside>