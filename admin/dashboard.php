<?php
/* ═══════════════════════════════════════════════════════════
   ADMIN/DASHBOARD.PHP — Tableau de bord administrateur
═══════════════════════════════════════════════════════════ */
require_once '../config.php';
startSession();
requireRole('admin');

$user = currentUser();

// ── Handle actions ─────────────────────────────────────────
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $pdo = getDB();
    try {
        switch ($_POST['action']) {
            case 'delete_mat':
                $stmt = $pdo->prepare('DELETE FROM materiaux WHERE id = :id');
                $stmt->execute([':id' => (int)$_POST['id']]);
                $msg = 'Matériau supprimé.';
                break;
            case 'approve_mat':
                $stmt = $pdo->prepare('UPDATE materiaux SET statut = "disponible" WHERE id = :id');
                $stmt->execute([':id' => (int)$_POST['id']]);
                $msg = 'Matériau approuvé.';
                break;
            case 'toggle_user':
                $stmt = $pdo->prepare('UPDATE utilisateurs SET statut = IF(statut="actif","suspendu","actif") WHERE id = :id');
                $stmt->execute([':id' => (int)$_POST['id']]);
                $msg = 'Statut utilisateur modifié.';
                break;
            case 'delete_resa':
                $stmt = $pdo->prepare('DELETE FROM reservations WHERE id = :id');
                $stmt->execute([':id' => (int)$_POST['id']]);
                $msg = 'Réservation supprimée.';
                break;
        }
    } catch (PDOException $e) {
        error_log('[Admin] ' . $e->getMessage());
        $msg = 'Erreur lors de l\'action.';
    }
    header('Location: dashboard.php?msg=' . urlencode($msg));
    exit;
}

if (isset($_GET['msg'])) $msg = sanitize($_GET['msg']);

// ── Stats ──────────────────────────────────────────────────
try {
    $pdo = getDB();
    $stats = [];
    $stats['users']    = $pdo->query('SELECT COUNT(*) FROM utilisateurs')->fetchColumn();
    $stats['mats']     = $pdo->query('SELECT COUNT(*) FROM materiaux')->fetchColumn();
    $stats['resas']    = $pdo->query('SELECT COUNT(*) FROM reservations')->fetchColumn();
    $stats['co2']      = $pdo->query('SELECT COALESCE(SUM(co2_economise),0) FROM materiaux WHERE statut="reserve"')->fetchColumn();

    $users    = $pdo->query('SELECT * FROM utilisateurs ORDER BY created_at DESC LIMIT 15')->fetchAll();
    $mats     = $pdo->query('SELECT m.*, u.nom AS u_nom FROM materiaux m JOIN utilisateurs u ON m.utilisateur_id=u.id ORDER BY m.created_at DESC LIMIT 15')->fetchAll();
    $resas    = $pdo->query('SELECT r.*, m.nom AS mat_nom, u.nom AS user_nom FROM reservations r JOIN materiaux m ON r.materiau_id=m.id JOIN utilisateurs u ON r.utilisateur_id=u.id ORDER BY r.created_at DESC LIMIT 15')->fetchAll();
} catch (PDOException $e) {
    error_log('[Admin stats] ' . $e->getMessage());
    $stats = ['users'=>0,'mats'=>0,'resas'=>0,'co2'=>0];
    $users = $mats = $resas = [];
}

$activeTab = $_GET['tab'] ?? 'overview';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin — RéEmploi BTP</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../style.css">
</head>
<body>

<nav class="navbar" id="navbar">
  <div class="nav-inner">
    <a class="nav-logo" href="../index.php">
      <span class="logo-icon">🌿</span>
      <span class="logo-text">RéEmploi <strong>BTP</strong></span>
    </a>
    <div class="nav-actions">
      <span style="font-size:0.85rem; color:var(--gray-600)">🛡️ Admin — <?= sanitize($user['nom']) ?></span>
      <a class="btn-connexion" href="../logout.php">Déconnexion</a>
    </div>
  </div>
</nav>

<div class="admin-layout">

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="admin-logo">🛡️ Administration</div>
    <nav class="admin-nav">
      <a href="?tab=overview" class="<?= $activeTab==='overview'?'active':'' ?>">📊 Vue d'ensemble</a>
      <a href="?tab=users"    class="<?= $activeTab==='users'   ?'active':'' ?>">👥 Utilisateurs</a>
      <a href="?tab=mats"     class="<?= $activeTab==='mats'    ?'active':'' ?>">📦 Matériaux</a>
      <a href="?tab=resas"    class="<?= $activeTab==='resas'   ?'active':'' ?>">📅 Réservations</a>
      <a href="../catalogue.php">🔍 Voir le catalogue</a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="admin-main">
    <?php if ($msg): ?>
      <div class="alert alert-success" style="margin-bottom:1.5rem;">✅ <?= $msg ?></div>
    <?php endif; ?>

    <?php if ($activeTab === 'overview'): ?>
    <!-- ── OVERVIEW ── -->
    <h1 class="admin-title">Tableau de bord</h1>
    <div class="admin-stats">
      <div class="astat"><div class="astat-icon">👥</div><div class="astat-val"><?= $stats['users'] ?></div><div class="astat-lbl">Utilisateurs inscrits</div></div>
      <div class="astat"><div class="astat-icon">📦</div><div class="astat-val"><?= $stats['mats'] ?></div><div class="astat-lbl">Matériaux publiés</div></div>
      <div class="astat"><div class="astat-icon">📅</div><div class="astat-val"><?= $stats['resas'] ?></div><div class="astat-lbl">Réservations totales</div></div>
      <div class="astat"><div class="astat-icon">🌿</div><div class="astat-val"><?= number_format((float)$stats['co2'], 1) ?> t</div><div class="astat-lbl">CO₂ économisé</div></div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-top:1rem;">
      <div class="admin-table">
        <div class="admin-table-head"><h3>Derniers utilisateurs</h3></div>
        <table>
          <tr><th>Nom</th><th>Rôle</th><th>Statut</th></tr>
          <?php foreach (array_slice($users, 0, 5) as $u): ?>
          <tr>
            <td><?= sanitize($u['nom']) ?></td>
            <td><span class="status-pill sp-green"><?= ucfirst($u['role']) ?></span></td>
            <td><span class="status-pill <?= $u['statut']==='actif'?'sp-green':'sp-red' ?>"><?= ucfirst($u['statut']) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </table>
      </div>
      <div class="admin-table">
        <div class="admin-table-head"><h3>Derniers matériaux</h3></div>
        <table>
          <tr><th>Matériau</th><th>Lieu</th><th>Statut</th></tr>
          <?php foreach (array_slice($mats, 0, 5) as $m): ?>
          <tr>
            <td><?= sanitize($m['nom']) ?></td>
            <td><?= sanitize($m['lieu']) ?></td>
            <td><span class="status-pill <?= $m['statut']==='disponible'?'sp-green':'sp-yellow' ?>"><?= ucfirst($m['statut']) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>

    <?php elseif ($activeTab === 'users'): ?>
    <!-- ── USERS ── -->
    <h1 class="admin-title">Gestion des utilisateurs</h1>
    <div class="admin-table">
      <div class="admin-table-head"><h3><?= count($users) ?> utilisateur(s)</h3></div>
      <table>
        <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Inscription</th><th>Statut</th><th>Actions</th></tr>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= sanitize($u['nom']) ?> <?= sanitize($u['prenom'] ?? '') ?></td>
          <td><?= sanitize($u['email']) ?></td>
          <td><span class="status-pill sp-green"><?= ucfirst($u['role']) ?></span></td>
          <td><?= formatDate($u['created_at']) ?></td>
          <td><span class="status-pill <?= $u['statut']==='actif'?'sp-green':'sp-red' ?>"><?= ucfirst($u['statut']) ?></span></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="action" value="toggle_user">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <button class="btn-admin-action btn-approve" type="submit"><?= $u['statut']==='actif'?'Suspendre':'Activer' ?></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>

    <?php elseif ($activeTab === 'mats'): ?>
    <!-- ── MATERIALS ── -->
    <h1 class="admin-title">Gestion des matériaux</h1>
    <div class="admin-table">
      <div class="admin-table-head"><h3><?= count($mats) ?> matériau(x)</h3></div>
      <table>
        <tr><th>Matériau</th><th>Catégorie</th><th>Fournisseur</th><th>Lieu</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
        <?php foreach ($mats as $m): ?>
        <tr>
          <td><?= sanitize($m['nom']) ?></td>
          <td><?= CATEGORIES[$m['categorie']] ?? $m['categorie'] ?></td>
          <td><?= sanitize($m['u_nom']) ?></td>
          <td><?= sanitize($m['lieu']) ?></td>
          <td><span class="status-pill <?= $m['statut']==='disponible'?'sp-green':'sp-yellow' ?>"><?= ucfirst($m['statut']) ?></span></td>
          <td><?= formatDate($m['created_at']) ?></td>
          <td>
            <?php if ($m['statut'] !== 'disponible'): ?>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="action" value="approve_mat">
              <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
              <button class="btn-admin-action btn-approve" type="submit">Approuver</button>
            </form>
            <?php endif; ?>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce matériau ?');">
              <input type="hidden" name="action" value="delete_mat">
              <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
              <button class="btn-admin-action btn-delete" type="submit">Supprimer</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>

    <?php elseif ($activeTab === 'resas'): ?>
    <!-- ── RESERVATIONS ── -->
    <h1 class="admin-title">Gestion des réservations</h1>
    <div class="admin-table">
      <div class="admin-table-head"><h3><?= count($resas) ?> réservation(s)</h3></div>
      <table>
        <tr><th>Matériau</th><th>Réservé par</th><th>Date</th><th>Statut</th><th>Actions</th></tr>
        <?php foreach ($resas as $r): ?>
        <tr>
          <td><?= sanitize($r['mat_nom']) ?></td>
          <td><?= sanitize($r['user_nom']) ?></td>
          <td><?= formatDate($r['created_at']) ?></td>
          <td><span class="status-pill <?= $r['statut']==='confirmee'?'sp-green':'sp-yellow' ?>"><?= ucfirst($r['statut']) ?></span></td>
          <td>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette réservation ?');">
              <input type="hidden" name="action" value="delete_resa">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn-admin-action btn-delete" type="submit">Supprimer</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
    <?php endif; ?>

  </main>
</div>

<script src="../script.js"></script>
</body>
</html>
