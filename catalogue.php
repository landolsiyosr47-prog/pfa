<?php
require_once 'config.php';
session_start();

$user        = isset($_SESSION['id_user']) ? $_SESSION : null;
$filterCat   = $_GET['cat'] ?? 'all';
$searchQuery = trim($_GET['q'] ?? '');

// ── Récupération des matériaux ─────────────────────────────
$params     = [];
$types      = '';
$conditions = [];

$sql = "SELECT m.*, u.nom_user, u.prenom_user, c.nom_categorie
        FROM MATERIAU m
        JOIN UTILISATEUR u ON m.id_user = u.id_user
        JOIN CATEGORIE c ON m.id_categorie = c.id_categorie";

if ($filterCat !== 'all') {
    $conditions[] = "LOWER(c.nom_categorie) = ?";
    $params[]     = strtolower($filterCat);
    $types       .= 's';
}
if ($searchQuery !== '') {
    $searchTerm   = '%' . $searchQuery . '%';
    $conditions[] = "(m.nom_materiau LIKE ? OR m.description_materiau LIKE ? OR m.dimensions_materiau LIKE ?)";
    array_push($params, $searchTerm, $searchTerm, $searchTerm);
    $types .= 'sss';
}
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY m.date_publication_materiau DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) die("Erreur SQL: " . $conn->error);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result    = $stmt->get_result();
$materials = [];
while ($row = $result->fetch_assoc()) $materials[] = $row;
$stmt->close();

// ── Catégories ─────────────────────────────────────────────
$catResult  = $conn->query("SELECT nom_categorie FROM CATEGORIE ORDER BY nom_categorie");
$categories = [];
while ($cat = $catResult->fetch_assoc()) $categories[] = $cat['nom_categorie'];

// ── Icônes ─────────────────────────────────────────────────
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

// ── Images par mot-clé dans le nom du matériau ─────────────
$matImages = [
    'acier'   => 'acier.jpg',
    'beton'   => 'beton.jpg',
    'béton'   => 'beton.jpg',
    'bois'    => 'bois.jpg',
    'brique'  => 'brique.jpg',
    'briques' => 'briques.jpg',
    'ciment'  => 'ciment.jpg',
    'pierre'  => 'pierre.jpg',
    'sable'   => 'sable.jpg',
];

function getMatImage(string $nom, array $matImages): string {
    $nomLower = strtolower($nom);
    foreach ($matImages as $motCle => $fichier) {
        if (str_contains($nomLower, $motCle)) {
            $path = __DIR__ . '/img/' . $fichier;
            if (file_exists($path)) return $fichier;
        }
    }
    return '';
}

$errorMessages = [
    'unavailable'      => "⚠️ Ce matériau n'est plus disponible.",
    'own_material'     => "⚠️ Vous ne pouvez pas réserver votre propre matériau.",
    'already_reserved' => "⚠️ Vous avez déjà une réservation en cours pour ce matériau.",
    'server'           => "❌ Une erreur s'est produite. Veuillez réessayer.",
    'access'           => "🔒 Vous devez être connecté pour réserver.",
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Catalogue — RéEmploi BTP</title>
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
    body {
      font-family: 'Outfit', sans-serif; background: var(--gray-100);
      color: var(--gray-800); min-height: 100vh; display: flex; flex-direction: column;
    }
    a { text-decoration: none; color: inherit; }
    ul { list-style: none; }

    /* NAVBAR */
    .navbar {
      position: sticky; top: 0; z-index: 100;
      background: rgba(255,255,255,.96); backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--gray-200); transition: box-shadow .3s;
    }
    .navbar.scrolled { box-shadow: var(--shadow-md); }
    .nav-inner {
      max-width: 1200px; margin: 0 auto; padding: 0 2rem; height: 68px;
      display: flex; align-items: center; gap: 1.5rem;
    }
    .nav-logo { display: flex; align-items: center; gap: .5rem; font-size: 1.15rem; font-weight: 700; color: var(--green-dark); flex-shrink: 0; }
    .nav-links { display: flex; gap: 2rem; margin-left: auto; }
    .nav-links a { font-size: .9rem; font-weight: 500; color: var(--gray-600); transition: color .2s; }
    .nav-links a:hover, .nav-links a.active { color: var(--green-main); }
    .nav-actions { display: flex; align-items: center; gap: .75rem; }
    .nav-user-info { font-size: .85rem; color: var(--gray-600); }
    .btn-nav-outline {
      padding: .42rem 1rem; border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
      font-size: .85rem; font-weight: 500; color: var(--gray-800);
      transition: border-color .2s, color .2s; background: transparent; font-family: 'Outfit', sans-serif;
    }
    .btn-nav-outline:hover { border-color: var(--green-main); color: var(--green-main); }
    .btn-nav-green {
      padding: .42rem 1rem; background: var(--green-main); color: #fff; border: none;
      border-radius: var(--radius-sm); font-size: .85rem; font-weight: 600;
      transition: background .2s; font-family: 'Outfit', sans-serif;
    }
    .btn-nav-green:hover { background: var(--green-dark); }
    .btn-nav-publish {
      padding: .42rem 1rem; background: var(--green-pale); color: var(--green-dark);
      border: 1.5px solid var(--green-light); border-radius: var(--radius-sm);
      font-size: .85rem; font-weight: 600; transition: all .2s; font-family: 'Outfit', sans-serif;
    }
    .btn-nav-publish:hover { background: var(--green-main); color: #fff; border-color: var(--green-main); }
    .nav-burger {
      display: none; flex-direction: column; gap: 5px;
      background: none; border: none; cursor: pointer; padding: 4px; margin-left: auto;
    }
    .nav-burger span { display: block; width: 24px; height: 2px; background: var(--gray-800); border-radius: 2px; transition: .3s; }
    .nav-mobile {
      display: none; flex-direction: column; background: #fff;
      padding: 1rem 2rem; border-bottom: 1px solid var(--gray-200);
    }
    .nav-mobile.open { display: flex; }
    .nav-mobile a { padding: .65rem 0; color: var(--gray-600); font-size: .9rem; border-bottom: 1px solid var(--gray-200); }
    .nav-mobile a:last-child { border-bottom: none; }

    /* PAGE HEADER */
    .page-header {
      background: linear-gradient(135deg, var(--green-dark) 0%, var(--green-main) 60%, var(--green-light) 100%);
      padding: 4rem 2rem 3rem; position: relative; overflow: hidden;
    }
    .page-header::before { content: ''; position: absolute; top: -60px; right: -60px; width: 320px; height: 320px; border-radius: 50%; background: rgba(255,255,255,.06); }
    .page-header::after  { content: ''; position: absolute; bottom: -80px; left: 20%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,.04); }
    .page-header .container { max-width: 1200px; margin: 0 auto; position: relative; z-index: 1; }
    .page-header h1 { font-family: 'Playfair Display', serif; font-size: clamp(2rem,4vw,3rem); font-weight: 800; color: #fff; margin-bottom: .75rem; line-height: 1.15; }
    .page-header p  { font-size: 1.05rem; color: rgba(255,255,255,.8); font-weight: 300; }

    /* CATALOGUE */
    .catalogue-wrap { flex: 1; padding: 3rem 2rem; }
    .container { max-width: 1200px; margin: 0 auto; }
    .cat-controls { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .search-box {
      display: flex; align-items: center; gap: .6rem; background: #fff;
      border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
      padding: .5rem 1rem; flex: 1; min-width: 220px; transition: border-color .2s;
    }
    .search-box:focus-within { border-color: var(--green-main); }
    .search-box input { border: none; outline: none; font-family: 'Outfit', sans-serif; font-size: .9rem; width: 100%; background: transparent; color: var(--gray-800); }
    .btn-search { padding: .55rem 1.4rem; background: var(--green-main); color: #fff; border: none; border-radius: var(--radius-sm); font-family: 'Outfit', sans-serif; font-weight: 600; font-size: .9rem; cursor: pointer; transition: background .2s; }
    .btn-search:hover { background: var(--green-dark); }
    .btn-reset { padding: .55rem 1rem; border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm); font-size: .875rem; color: var(--gray-600); background: #fff; cursor: pointer; transition: border-color .2s; display: inline-flex; align-items: center; }
    .btn-reset:hover { border-color: var(--gray-400); }

    .filter-tabs { display: flex; gap: .6rem; flex-wrap: wrap; margin-bottom: 2rem; }
    .filter-tab { padding: .4rem 1rem; border-radius: 999px; border: 1.5px solid var(--gray-200); font-size: .825rem; font-weight: 500; color: var(--gray-600); background: #fff; transition: all .2s; }
    .filter-tab:hover { border-color: var(--green-light); color: var(--green-main); }
    .filter-tab.active { background: var(--green-main); border-color: var(--green-main); color: #fff; }

    .results-count { font-size: .875rem; color: var(--gray-600); margin-bottom: 1.5rem; }
    .alert { padding: .9rem 1.2rem; border-radius: var(--radius-sm); font-weight: 500; margin-bottom: 1.5rem; }
    .alert-success { background: var(--green-pale); color: var(--green-dark); border: 1px solid var(--green-light); }
    .alert-error { background: #ffe0e0; color: #7b0000; border: 1px solid #f5a5a5; }

    /* GRID */
    .materials-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 1.5rem; }
    .mat-card { background: #fff; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden; transition: transform .25s, box-shadow .25s; border: 1px solid var(--gray-200); }
    .mat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
    .mat-card-img { background: var(--green-pale); height: 130px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
    .mat-card-img > span:first-child { font-size: 3rem; }
    .mat-badge { position: absolute; top: .75rem; right: .75rem; padding: .25rem .65rem; border-radius: 999px; font-size: .72rem; font-weight: 600; }
    .badge-dispo { background: #d8f3dc; color: var(--green-dark); }
    .mat-card-body { padding: 1.2rem; }
    .mat-cat { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .07em; color: var(--green-mid); margin-bottom: .3rem; }
    .mat-title { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 700; color: var(--gray-800); margin-bottom: .75rem; }
    .mat-meta { display: flex; gap: .75rem; flex-wrap: wrap; font-size: .78rem; color: var(--gray-600); margin-bottom: .6rem; }
    .mat-eco { font-size: .75rem; color: var(--green-mid); background: var(--green-pale); border-radius: var(--radius-sm); padding: .3rem .7rem; margin-bottom: 1rem; display: inline-block; }
    .mat-actions { display: flex; gap: .6rem; }
    .btn-reserver {
      flex: 1; padding: .55rem .8rem; background: var(--green-main); color: #fff; border: none;
      border-radius: var(--radius-sm); font-family: 'Outfit', sans-serif; font-weight: 600; font-size: .85rem;
      cursor: pointer; transition: background .2s; text-align: center;
      display: flex; align-items: center; justify-content: center;
    }
    .btn-reserver:hover { background: var(--green-dark); }
    .btn-reserver:disabled { opacity: .5; cursor: not-allowed; background: var(--gray-400); }
    .btn-detail { padding: .55rem .8rem; border: 1.5px solid var(--gray-200); background: transparent; border-radius: var(--radius-sm); font-family: 'Outfit', sans-serif; font-size: .85rem; color: var(--gray-600); cursor: pointer; transition: border-color .2s, color .2s; }
    .btn-detail:hover { border-color: var(--green-light); color: var(--green-main); }

    /* MODAL */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 200; align-items: center; justify-content: center; padding: 1rem; }
    .modal-overlay.open { display: flex; }
    .modal-box { background: #fff; border-radius: var(--radius-md); max-width: 480px; width: 100%; padding: 2rem; box-shadow: 0 8px 40px rgba(0,0,0,.18); position: relative; max-height: 90vh; overflow-y: auto; }
    .modal-close { position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--gray-400); transition: color .2s; }
    .modal-close:hover { color: var(--gray-800); }
    .modal-icon { font-size: 2.5rem; margin-bottom: 1rem; }
    .modal-title { font-family: 'Playfair Display', serif; font-size: 1.3rem; font-weight: 800; color: var(--gray-800); margin-bottom: 1rem; }
    .modal-row { display: flex; justify-content: space-between; align-items: flex-start; padding: .55rem 0; border-bottom: 1px solid var(--gray-200); font-size: .875rem; }
    .modal-row:last-child { border-bottom: none; }
    .modal-label { color: var(--gray-600); font-weight: 500; flex-shrink: 0; margin-right: 1rem; }
    .modal-value { color: var(--gray-800); text-align: right; }

    /* EMPTY STATE */
    .empty-state { text-align: center; padding: 5rem 1rem; color: var(--gray-400); }
    .empty-state .empty-icon { font-size: 3rem; margin-bottom: 1rem; }
    .empty-state a { color: var(--green-main); font-weight: 600; display: inline-block; margin-top: 1.5rem; }

    /* FOOTER */
    .footer { background: var(--green-dark); color: rgba(255,255,255,.75); padding: 3rem 2rem 1.5rem; margin-top: auto; }
    .footer-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 2.5rem; padding-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,.12); }
    .footer-logo { font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: .75rem; display: flex; align-items: center; gap: .4rem; }
    .footer-col p { font-size: .875rem; line-height: 1.7; max-width: 260px; }
    .footer-col h5 { font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: rgba(255,255,255,.45); margin-bottom: 1rem; }
    .footer-col ul li { margin-bottom: .5rem; }
    .footer-col ul a { font-size: .875rem; color: rgba(255,255,255,.7); transition: color .2s; }
    .footer-col ul a:hover { color: #fff; }
    .footer-bottom { max-width: 1200px; margin: 1.5rem auto 0; font-size: .8rem; color: rgba(255,255,255,.35); }

    @media (max-width: 768px) {
      .nav-links, .nav-actions { display: none; }
      .nav-burger { display: flex; }
      .footer-inner { grid-template-columns: 1fr 1fr; }
      .materials-grid { grid-template-columns: 1fr; }
      .page-header, .catalogue-wrap { padding-left: 1.2rem; padding-right: 1.2rem; }
    }
    @media (max-width: 480px) { .footer-inner { grid-template-columns: 1fr; } }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
  <div class="nav-inner">
    <a class="nav-logo" href="index.php">🌿 RéEmploi <strong>BTP</strong></a>
    <ul class="nav-links">
      <li><a href="catalogue.php" class="active">Catalogue</a></li>
      <?php if ($user && isset($_SESSION['type_user']) && $_SESSION['type_user'] === 'entreprise'): ?>
        <li><a href="mes_materiaux.php">Mes matériaux</a></li>
      <?php endif; ?>
    </ul>
    <div class="nav-actions">
      <?php if ($user): ?>
        <span class="nav-user-info">👤 <?= htmlspecialchars($_SESSION['prenom_user'] ?? $_SESSION['nom_user'] ?? 'Utilisateur') ?></span>
        <?php if (isset($_SESSION['type_user']) && $_SESSION['type_user'] === 'entreprise'): ?>
          <a href="publier_entreprise.php" class="btn-nav-publish">➕ Publier</a>
        <?php endif; ?>
        <a href="logout.php" class="btn-nav-outline">Déconnexion</a>
      <?php else: ?>
        <a href="login.php" class="btn-nav-outline">Connexion</a>
        <a href="register.php" class="btn-nav-green">S'inscrire</a>
      <?php endif; ?>
    </div>
    <button class="nav-burger" onclick="toggleMenu()"><span></span><span></span><span></span></button>
  </div>
  <div class="nav-mobile" id="navMobile">
    <a href="catalogue.php">Catalogue</a>
    <?php if ($user): ?>
      <?php if (isset($_SESSION['type_user']) && strtolower($_SESSION['type_user']) === 'entreprise'): ?>
        <a href="publier_entreprise.php">➕ Publier un matériau</a>
        <a href="mes_materiaux.php">Mes matériaux</a>
      <?php endif; ?>
      <a href="logout.php">Déconnexion</a>
    <?php else: ?>
      <a href="login.php">Connexion</a>
      <a href="register.php">S'inscrire</a>
    <?php endif; ?>
  </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
  <div class="container">
    <h1>Catalogue des matériaux</h1>
    <p>Parcourez les surplus de construction disponibles près de chez vous</p>
  </div>
</div>

<!-- CATALOGUE -->
<div class="catalogue-wrap">
  <div class="container">
    <div class="cat-controls">
      <form method="GET" action="catalogue.php" style="display:flex;gap:1rem;flex:1;flex-wrap:wrap;align-items:center;">
        <div class="search-box">
          <span>🔍</span>
          <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Rechercher un matériau...">
        </div>
        <?php if ($filterCat !== 'all'): ?>
          <input type="hidden" name="cat" value="<?= htmlspecialchars($filterCat) ?>">
        <?php endif; ?>
        <button type="submit" class="btn-search">Rechercher</button>
        <?php if ($searchQuery || $filterCat !== 'all'): ?>
          <a href="catalogue.php" class="btn-reset">✕ Réinitialiser</a>
        <?php endif; ?>
      </form>
    </div>

    <div class="filter-tabs">
      <a href="catalogue.php<?= $searchQuery ? '?q='.urlencode($searchQuery) : '' ?>"
         class="filter-tab <?= $filterCat === 'all' ? 'active' : '' ?>">Tous</a>
      <?php foreach ($categories as $catName): ?>
        <a href="catalogue.php?cat=<?= urlencode($catName) ?><?= $searchQuery ? '&q='.urlencode($searchQuery) : '' ?>"
           class="filter-tab <?= strtolower($filterCat) === strtolower($catName) ? 'active' : '' ?>">
          <?= htmlspecialchars($catName) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <p class="results-count">
      <?= count($materials) ?> matériau<?= count($materials) > 1 ? 'x' : '' ?> trouvé<?= count($materials) > 1 ? 's' : '' ?>
      <?php if ($filterCat !== 'all'): ?> dans <strong><?= htmlspecialchars($filterCat) ?></strong><?php endif; ?>
      <?php if ($searchQuery): ?> pour "<strong><?= htmlspecialchars($searchQuery) ?></strong>"<?php endif; ?>
    </p>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'reservation'): ?>
      <div class="alert alert-success">✅ Réservation effectuée ! Le fournisseur vous contactera bientôt.</div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && isset($errorMessages[$_GET['error']])): ?>
      <div class="alert alert-error"><?= $errorMessages[$_GET['error']] ?></div>
    <?php endif; ?>

    <?php if (empty($materials)): ?>
      <div class="empty-state">
        <div class="empty-icon">📦</div>
        <p>Aucun matériau disponible pour cette recherche.</p>
        <a href="catalogue.php">Voir tous les matériaux →</a>
      </div>
    <?php else: ?>
      <div class="materials-grid">
        <?php foreach ($materials as $m):
          $icon    = $catIcons[strtolower($m['nom_categorie'])] ?? '📦';
          $isOwner = $user && (int)$_SESSION['id_user'] === (int)$m['id_user'];
          $imgFile = getMatImage($m['nom_materiau'], $matImages);
        ?>
        <div class="mat-card">
          <div class="mat-card-img">
            <?php if ($imgFile !== ''): ?>
              <img src="img/<?= htmlspecialchars($imgFile) ?>"
                   alt="<?= htmlspecialchars($m['nom_materiau']) ?>"
                   style="width:100%;height:100%;object-fit:cover;display:block;">
            <?php else: ?>
              <span><?= $icon ?></span>
            <?php endif; ?>
            <span class="mat-badge badge-dispo">Disponible</span>
          </div>
          <div class="mat-card-body">
            <div class="mat-cat"><?= htmlspecialchars($m['nom_categorie']) ?></div>
            <div class="mat-title"><?= htmlspecialchars($m['nom_materiau']) ?></div>
            <div class="mat-meta">
              <span>📦 <?= (int)$m['quantite_materiau'] ?></span>
              <span>📐 <?= htmlspecialchars($m['dimensions_materiau'] ?? '—') ?></span>
            </div>
            <div class="mat-eco">🌱 État : <?= htmlspecialchars($m['etat_materiau']) ?></div>
            <div class="mat-actions">
              <?php if ($isOwner): ?>
                <button class="btn-reserver" disabled>📌 Votre annonce</button>
              <?php else: ?>
                <a href="reserver.php?id=<?= (int)$m['id_materiau'] ?>" class="btn-reserver">📅 Réserver</a>
              <?php endif; ?>
              <button class="btn-detail" onclick='openModal(
                "<?= $icon ?>",
                "<?= htmlspecialchars(addslashes($m['nom_materiau'])) ?>",
                "<?= htmlspecialchars(addslashes($m['nom_categorie'])) ?>",
                "<?= (int)$m['quantite_materiau'] ?>",
                "<?= htmlspecialchars(addslashes($m['dimensions_materiau'] ?? '—')) ?>",
                "<?= htmlspecialchars(addslashes($m['etat_materiau'])) ?>",
                "<?= htmlspecialchars(addslashes($m['nom_user'].' '.$m['prenom_user'])) ?>",
                "<?= htmlspecialchars(addslashes(substr($m['description_materiau'] ?? '', 0, 200))) ?>"
              )'>📋 Détail</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL DETAIL -->
<div class="modal-overlay" id="detailModal" onclick="closeModalOutside(event)">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal()">✕</button>
    <div class="modal-icon" id="mIcon"></div>
    <div class="modal-title" id="mTitle"></div>
    <div id="mRows"></div>
  </div>
</div>

<!-- FOOTER -->
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-col">
      <div class="footer-logo">🌿 RéEmploi <strong>BTP</strong></div>
      <p>Plateforme collaborative pour le réemploi des matériaux de construction et la réduction des déchets du secteur BTP.</p>
    </div>
    <div class="footer-col">
      <h5>Navigation</h5>
      <ul>
        <li><a href="index.php">Accueil</a></li>
        <li><a href="catalogue.php">Catalogue</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h5>Contact</h5>
      <ul>
        <li><a href="mailto:contact@reemploi-btp.fr">contact@reemploi-btp.fr</a></li>
        <li><a href="#">Mentions légales</a></li>
        <li><a href="#">Politique de confidentialité</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2025 RéEmploi BTP · Tous droits réservés</p>
  </div>
</footer>

<script>
  window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 20);
  });

  function toggleMenu() {
    document.getElementById('navMobile').classList.toggle('open');
  }

  function openModal(icon, nom, cat, qte, dim, etat, fournisseur, desc) {
    document.getElementById('mIcon').textContent  = icon;
    document.getElementById('mTitle').textContent = nom;
    const rows = [
      ['Catégorie',   cat],
      ['Quantité',    qte],
      ['Dimensions',  dim],
      ['État',        etat],
      ['Fournisseur', fournisseur],
      ['Description', desc || '—'],
    ];
    document.getElementById('mRows').innerHTML = rows.map(([l,v]) =>
      `<div class="modal-row"><span class="modal-label">${l}</span><span class="modal-value">${v}</span></div>`
    ).join('');
    document.getElementById('detailModal').classList.add('open');
  }

  function closeModal() { document.getElementById('detailModal').classList.remove('open'); }
  function closeModalOutside(e) { if (e.target.id === 'detailModal') closeModal(); }
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
</body>
</html>