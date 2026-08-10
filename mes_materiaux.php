<?php
require_once 'config.php';
session_start();

// Accès réservé aux entreprises connectées
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}
if (!isset($_SESSION['type_user']) || $_SESSION['type_user'] !== 'entreprise') {
    header("Location: catalogue.php");
    exit;
}

$id_user = (int)$_SESSION['id_user'];

// ── Action : supprimer un matériau ─────────────────────────
if (isset($_GET['delete']) && (int)$_GET['delete'] > 0) {
    $del_id = (int)$_GET['delete'];
    $del = $conn->prepare("DELETE FROM MATERIAU WHERE id_materiau = ? AND id_user = ?");
    $del->bind_param('ii', $del_id, $id_user);
    $del->execute();
    $del->close();
    header("Location: mes_materiaux.php?success=deleted");
    exit;
}

// ── Récupération des matériaux de l'entreprise ─────────────
$stmt = $conn->prepare(
    "SELECT m.*, c.nom_categorie
     FROM MATERIAU m
     JOIN CATEGORIE c ON m.id_categorie = c.id_categorie
     WHERE m.id_user = ?
     ORDER BY m.date_publication_materiau DESC"
);
$stmt->bind_param('i', $id_user);
$stmt->execute();
$result    = $stmt->get_result();
$materials = [];
while ($row = $result->fetch_assoc()) $materials[] = $row;
$stmt->close();

$catIcons = [
    'menuiserie'  => '🚪',
    'carrelage'   => '🔲',
    'bois'        => '🪵',
    'sanitaire'   => '🚿',
    'electricite' => '⚡',
    'metal'       => '🔩',
    'isolation'   => '🧱',
    'autre'       => '📦',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes matériaux — RéEmploi BTP</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --green-main:  #2d6a4f;
      --green-dark:  #1b4332;
      --green-light: #52b788;
      --green-pale:  #d8f3dc;
      --green-mid:   #40916c;
      --gray-100:    #f8f9fa;
      --gray-200:    #e9ecef;
      --gray-400:    #adb5bd;
      --gray-600:    #6c757d;
      --gray-800:    #343a40;
      --shadow-sm:   0 2px 8px rgba(0,0,0,.07);
      --shadow-md:   0 4px 20px rgba(0,0,0,.10);
      --radius-sm:   8px;
      --radius-md:   14px;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body { font-family: 'Outfit', sans-serif; background: var(--gray-100); color: var(--gray-800); min-height: 100vh; display: flex; flex-direction: column; }
    a { text-decoration: none; color: inherit; }
    ul { list-style: none; }

    /* NAVBAR */
    .navbar { position: sticky; top: 0; z-index: 100; background: rgba(255,255,255,.96); backdrop-filter: blur(12px); border-bottom: 1px solid var(--gray-200); transition: box-shadow .3s; }
    .navbar.scrolled { box-shadow: var(--shadow-md); }
    .nav-inner { max-width: 1200px; margin: 0 auto; padding: 0 2rem; height: 68px; display: flex; align-items: center; gap: 1.5rem; }
    .nav-logo { display: flex; align-items: center; gap: .5rem; font-size: 1.15rem; font-weight: 700; color: var(--green-dark); flex-shrink: 0; }
    .nav-links { display: flex; gap: 1.5rem; margin-left: auto; }
    .nav-links a { font-size: .9rem; font-weight: 500; color: var(--gray-600); transition: color .2s; }
    .nav-links a:hover, .nav-links a.active { color: var(--green-main); }
    .nav-actions { display: flex; align-items: center; gap: .75rem; }
    .btn-nav-outline { padding: .42rem 1rem; border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm); font-size: .85rem; font-weight: 500; color: var(--gray-800); transition: border-color .2s, color .2s; }
    .btn-nav-outline:hover { border-color: var(--green-main); color: var(--green-main); }
    .btn-nav-publish { padding: .42rem 1rem; background: var(--green-main); color: #fff; border: none; border-radius: var(--radius-sm); font-size: .85rem; font-weight: 600; transition: background .2s; }
    .btn-nav-publish:hover { background: var(--green-dark); }

    /* PAGE HEADER */
    .page-header { background: linear-gradient(135deg, var(--green-dark) 0%, var(--green-main) 60%, var(--green-light) 100%); padding: 3.5rem 2rem 2.5rem; position: relative; overflow: hidden; }
    .page-header::before { content: ''; position: absolute; top: -60px; right: -60px; width: 320px; height: 320px; border-radius: 50%; background: rgba(255,255,255,.06); }
    .page-header .container { max-width: 1200px; margin: 0 auto; position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
    .page-header h1 { font-family: 'Playfair Display', serif; font-size: clamp(1.8rem, 3.5vw, 2.6rem); font-weight: 800; color: #fff; margin-bottom: .5rem; }
    .page-header p { color: rgba(255,255,255,.8); font-size: .95rem; }
    .btn-header-publish { padding: .7rem 1.5rem; background: rgba(255,255,255,.15); color: #fff; border: 2px solid rgba(255,255,255,.5); border-radius: var(--radius-sm); font-size: .9rem; font-weight: 600; transition: background .2s; backdrop-filter: blur(4px); }
    .btn-header-publish:hover { background: rgba(255,255,255,.28); }

    /* STATS BAR */
    .stats-bar { background: #fff; border-bottom: 1px solid var(--gray-200); padding: 1.2rem 2rem; }
    .stats-inner { max-width: 1200px; margin: 0 auto; display: flex; gap: 2rem; flex-wrap: wrap; }
    .stat-item { display: flex; flex-direction: column; }
    .stat-value { font-size: 1.5rem; font-weight: 700; color: var(--green-main); font-family: 'Playfair Display', serif; }
    .stat-label { font-size: .78rem; color: var(--gray-600); text-transform: uppercase; letter-spacing: .06em; }

    /* MAIN */
    .main-wrap { flex: 1; padding: 2.5rem 2rem; }
    .container { max-width: 1200px; margin: 0 auto; }

    /* ALERT */
    .alert { padding: .9rem 1.2rem; border-radius: var(--radius-sm); font-weight: 500; margin-bottom: 1.5rem; }
    .alert-success { background: var(--green-pale); color: var(--green-dark); border: 1px solid var(--green-light); }

    /* GRID */
    .materials-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }

    /* CARDS */
    .mat-card { background: #fff; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden; border: 1px solid var(--gray-200); transition: transform .2s, box-shadow .2s; }
    .mat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .mat-card-img { background: var(--green-pale); height: 120px; display: flex; align-items: center; justify-content: center; position: relative; }
    .mat-card-img > span:first-child { font-size: 2.8rem; }
    .mat-badge { position: absolute; top: .75rem; right: .75rem; padding: .25rem .65rem; border-radius: 999px; font-size: .72rem; font-weight: 600; }
    .badge-dispo   { background: #d8f3dc; color: var(--green-dark); }
    .badge-reserve { background: #fff3cd; color: #856404; }
    .mat-card-body { padding: 1.2rem; }
    .mat-cat { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .07em; color: var(--green-mid); margin-bottom: .3rem; }
    .mat-title { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 700; color: var(--gray-800); margin-bottom: .6rem; }
    .mat-meta { display: flex; gap: .75rem; flex-wrap: wrap; font-size: .78rem; color: var(--gray-600); margin-bottom: .6rem; }
    .mat-date { font-size: .72rem; color: var(--gray-400); margin-bottom: 1rem; }
    .mat-actions { display: flex; gap: .6rem; }
    .btn-edit { flex: 1; padding: .5rem .8rem; background: var(--green-pale); color: var(--green-dark); border: 1.5px solid var(--green-light); border-radius: var(--radius-sm); font-family: 'Outfit', sans-serif; font-size: .83rem; font-weight: 600; cursor: pointer; transition: all .2s; text-align: center; display: flex; align-items: center; justify-content: center; }
    .btn-edit:hover { background: var(--green-main); color: #fff; border-color: var(--green-main); }
    .btn-delete { padding: .5rem .8rem; border: 1.5px solid #f5a5a5; background: transparent; border-radius: var(--radius-sm); font-family: 'Outfit', sans-serif; font-size: .83rem; color: #c0392b; cursor: pointer; transition: all .2s; }
    .btn-delete:hover { background: #ffe0e0; }

    /* EMPTY */
    .empty-state { text-align: center; padding: 5rem 1rem; color: var(--gray-400); }
    .empty-state .empty-icon { font-size: 3.5rem; margin-bottom: 1rem; }
    .empty-state h3 { font-family: 'Playfair Display', serif; font-size: 1.4rem; color: var(--gray-600); margin-bottom: .75rem; }
    .empty-state p { font-size: .95rem; margin-bottom: 2rem; }
    .btn-empty-publish { display: inline-block; padding: .8rem 2rem; background: var(--green-main); color: #fff; border-radius: var(--radius-sm); font-weight: 600; transition: background .2s; }
    .btn-empty-publish:hover { background: var(--green-dark); }

    /* CONFIRM MODAL */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 200; align-items: center; justify-content: center; }
    .modal-overlay.open { display: flex; }
    .modal-box { background: #fff; border-radius: var(--radius-md); max-width: 420px; width: 90%; padding: 2rem; box-shadow: 0 8px 40px rgba(0,0,0,.2); text-align: center; }
    .modal-box h3 { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--gray-800); margin-bottom: .75rem; }
    .modal-box p { font-size: .9rem; color: var(--gray-600); margin-bottom: 1.5rem; }
    .modal-actions { display: flex; gap: .75rem; justify-content: center; }
    .btn-modal-cancel { padding: .6rem 1.4rem; border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm); background: transparent; color: var(--gray-600); font-family: 'Outfit', sans-serif; cursor: pointer; transition: border-color .2s; }
    .btn-modal-cancel:hover { border-color: var(--gray-400); }
    .btn-modal-confirm { padding: .6rem 1.4rem; background: #c0392b; color: #fff; border: none; border-radius: var(--radius-sm); font-family: 'Outfit', sans-serif; font-weight: 600; cursor: pointer; transition: background .2s; }
    .btn-modal-confirm:hover { background: #922b21; }

    /* FOOTER */
    .footer { background: var(--green-dark); color: rgba(255,255,255,.75); padding: 2rem; text-align: center; font-size: .8rem; margin-top: auto; }

    @media (max-width: 768px) {
      .nav-links, .nav-actions { display: none; }
      .materials-grid { grid-template-columns: 1fr; }
      .page-header { padding: 2.5rem 1.2rem 2rem; }
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
  <div class="nav-inner">
    <a class="nav-logo" href="index.php">🌿 RéEmploi <strong>BTP</strong></a>
    <ul class="nav-links">
      <li><a href="catalogue.php">Catalogue</a></li>
      <li><a href="mes_materiaux.php" class="active">Mes matériaux</a></li>
    </ul>
    <div class="nav-actions">
      <span style="font-size:.85rem;color:var(--gray-600);">🏢 <?= htmlspecialchars($_SESSION['nom_user'] ?? '') ?></span>
      <a href="publier_entreprise.php" class="btn-nav-publish">➕ Publier</a>
      <a href="logout.php" class="btn-nav-outline">Déconnexion</a>
    </div>
  </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
  <div class="container">
    <div>
      <h1>Mes matériaux publiés</h1>
      <p>Gérez vos surplus de construction mis à disposition</p>
    </div>
    <a href="publier_entreprise.php" class="btn-header-publish">➕ Publier un nouveau matériau</a>
  </div>
</div>

<!-- STATS BAR -->
<div class="stats-bar">
  <div class="stats-inner">
    <?php
      $total    = count($materials);
      $reserve  = count(array_filter($materials, fn($m) => ($m['disponibilite_materiau'] ?? '') === 'reserve'));
      $dispo    = $total - $reserve;
    ?>
    <div class="stat-item">
      <span class="stat-value"><?= $total ?></span>
      <span class="stat-label">Total publié<?= $total > 1 ? 's' : '' ?></span>
    </div>
    <div class="stat-item">
      <span class="stat-value"><?= $dispo ?></span>
      <span class="stat-label">Disponible<?= $dispo > 1 ? 's' : '' ?></span>
    </div>
    <div class="stat-item">
      <span class="stat-value"><?= $reserve ?></span>
      <span class="stat-label">Réservé<?= $reserve > 1 ? 's' : '' ?></span>
    </div>
  </div>
</div>

<!-- MAIN -->
<div class="main-wrap">
  <div class="container">

    <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
      <div class="alert alert-success">✅ Matériau supprimé avec succès.</div>
    <?php endif; ?>

    <?php if (empty($materials)): ?>
      <div class="empty-state">
        <div class="empty-icon">📦</div>
        <h3>Aucun matériau publié</h3>
        <p>Vous n'avez pas encore mis de matériaux en ligne.<br>Commencez dès maintenant à partager vos surplus !</p>
      </div>
    <?php else: ?>
      <div class="materials-grid">
        <?php foreach ($materials as $m):
          $icon   = $catIcons[strtolower($m['nom_categorie'])] ?? '📦';
          $isResa = ($m['disponibilite_materiau'] ?? '') === 'reserve';
          $date   = $m['date_publication_materiau'] ? date('d/m/Y', strtotime($m['date_publication_materiau'])) : '—';
        ?>
        <div class="mat-card">
          <div class="mat-card-img">
            <span><?= $icon ?></span>
            <span class="mat-badge <?= $isResa ? 'badge-reserve' : 'badge-dispo' ?>">
              <?= $isResa ? '⏳ Réservé' : '✅ Disponible' ?>
            </span>
          </div>
          <div class="mat-card-body">
            <div class="mat-cat"><?= htmlspecialchars($m['nom_categorie']) ?></div>
            <div class="mat-title"><?= htmlspecialchars($m['nom_materiau']) ?></div>
            <div class="mat-meta">
              <span>📦 <?= (int)$m['quantite_materiau'] ?></span>
              <span>📐 <?= htmlspecialchars($m['dimensions_materiau'] ?? '—') ?></span>
              <span>🔧 <?= htmlspecialchars($m['etat_materiau']) ?></span>
            </div>
            <div class="mat-date">Publié le <?= $date ?></div>
            <div class="mat-actions">
              <a href="modifier_entreprise.php?id=<?= (int)$m['id_materiau'] ?>" class="btn-edit">✏️ Modifier</a>
              <button class="btn-delete"
                onclick="confirmDelete(<?= (int)$m['id_materiau'] ?>, '<?= htmlspecialchars(addslashes($m['nom_materiau'])) ?>')">
                🗑️ Supprimer
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL CONFIRMATION SUPPRESSION -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal-box">
    <h3>🗑️ Supprimer ce matériau ?</h3>
    <p id="deleteMsg">Cette action est irréversible.</p>
    <div class="modal-actions">
      <button class="btn-modal-cancel" onclick="closeDeleteModal()">Annuler</button>
      <a href="#" id="deleteConfirmLink" class="btn-modal-confirm">Supprimer</a>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer class="footer">
  <p>© 2025 RéEmploi BTP · Tous droits réservés</p>
</footer>

<script>
  window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 20);
  });

  function confirmDelete(id, nom) {
    document.getElementById('deleteMsg').textContent = 'Voulez-vous vraiment supprimer "' + nom + '" ? Cette action est irréversible.';
    document.getElementById('deleteConfirmLink').href = 'mes_materiaux.php?delete=' + id;
    document.getElementById('deleteModal').classList.add('open');
  }

  function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('open');
  }

  document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
  });
</script>
</body>
</html>