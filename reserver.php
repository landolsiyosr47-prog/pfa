<?php
require_once 'config.php';
session_start();

// Rediriger si non connecté
if (!isset($_SESSION['id_user'])) {
    $id = (int)($_GET['id'] ?? 0);
    header("Location: login.php?redirect=reserver.php&id=" . $id);
    exit;
}

$id_user      = (int)$_SESSION['id_user'];
$id_materiau  = (int)($_GET['id'] ?? 0);
$error        = '';

// ── Récupérer le matériau et ses détails ──────────────────
if (!$id_materiau) {
    header("Location: catalogue.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT m.*, u.nom_user, u.prenom_user, u.email_user, c.nom_categorie
     FROM MATERIAU m
     JOIN UTILISATEUR u ON m.id_user = u.id_user
     JOIN CATEGORIE c ON m.id_categorie = c.id_categorie
     WHERE m.id_materiau = ?"
);
$stmt->bind_param('i', $id_materiau);
$stmt->execute();
$mat = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$mat) {
    header("Location: catalogue.php?error=unavailable");
    exit;
}

// Vérifier que ce n'est pas son propre matériau
if ((int)$mat['id_user'] === $id_user) {
    header("Location: catalogue.php?error=own_material");
    exit;
}

// ── Traitement POST ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_reservation = trim($_POST['date_reservation'] ?? '');

    if (empty($date_reservation)) {
        $error = 'Veuillez choisir une date de réservation.';
    } else {
        // Vérifier doublon
        $dup = $conn->prepare("SELECT id_reservation FROM RESERVATION WHERE id_materiau = ? AND id_user = ? AND statut_reservation = 'en attente'");
        $dup->bind_param('ii', $id_materiau, $id_user);
        $dup->execute();
        $dup->store_result();
        $already = $dup->num_rows > 0;
        $dup->close();

        if ($already) {
            $error = "Vous avez déjà une réservation en attente pour ce matériau.";
        } else {
            $statut = 'en attente';
            $ins = $conn->prepare("INSERT INTO RESERVATION (id_materiau, id_user, date_reservation, statut_reservation) VALUES (?, ?, ?, ?)");
            $ins->bind_param('iiss', $id_materiau, $id_user, $date_reservation, $statut);
            if ($ins->execute()) {
                $upd = $conn->prepare("UPDATE MATERIAU SET disponibilite_materiau = 'reserve' WHERE id_materiau = ?");
                $upd->bind_param('i', $id_materiau);
                $upd->execute();
                $upd->close();
                $ins->close();
                header("Location: catalogue.php?success=reservation");
                exit;
            } else {
                $error = "Erreur serveur. Veuillez réessayer.";
                $ins->close();
            }
        }
    }
}

// Icônes catégories
$catIcons = [
    'menuiserie'  => '🚪', 'carrelage'   => '🔲', 'bois'        => '🪵',
    'sanitaire'   => '🚿', 'electricite' => '⚡', 'metal'       => '🔩',
    'isolation'   => '🧱', 'autre'       => '📦',
];
$icon = $catIcons[strtolower($mat['nom_categorie'])] ?? '📦';
$prix = isset($mat['prix_materiau']) && $mat['prix_materiau'] ? number_format((float)$mat['prix_materiau'], 2, ',', ' ') . ' DT' : 'Gratuit / Sur devis';

// ── Images par mot-clé dans le nom du matériau ────────────
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

$imgFile = getMatImage($mat['nom_materiau'], $matImages);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Réserver — <?= htmlspecialchars($mat['nom_materiau']) ?> — RéEmploi BTP</title>
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
      --shadow-md:   0 6px 28px rgba(0,0,0,.11);
      --radius-sm:   8px;
      --radius-md:   14px;
      --radius-lg:   20px;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body { font-family: 'Outfit', sans-serif; background: var(--gray-100); color: var(--gray-800); min-height: 100vh; display: flex; flex-direction: column; }
    a { text-decoration: none; color: inherit; }

    /* ── NAVBAR ── */
    .navbar { background: rgba(255,255,255,.97); border-bottom: 1px solid var(--gray-200); position: sticky; top: 0; z-index: 100; box-shadow: var(--shadow-sm); }
    .nav-inner { max-width: 1200px; margin: 0 auto; padding: 0 2rem; height: 66px; display: flex; align-items: center; gap: 1.5rem; }
    .nav-logo { font-size: 1.15rem; font-weight: 700; color: var(--green-dark); display: flex; align-items: center; gap: .45rem; flex-shrink: 0; }
    .nav-actions { display: flex; align-items: center; gap: .75rem; margin-left: auto; }
    .nav-user-info { font-size: .85rem; color: var(--gray-600); }
    .btn-nav { padding: .42rem 1rem; border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm); font-size: .85rem; font-weight: 500; color: var(--gray-800); transition: border-color .2s, color .2s; }
    .btn-nav:hover { border-color: var(--green-main); color: var(--green-main); }
    .btn-nav-back { display: flex; align-items: center; gap: .4rem; padding: .42rem 1rem; border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm); font-size: .85rem; font-weight: 500; color: var(--gray-600); transition: all .2s; }
    .btn-nav-back:hover { border-color: var(--green-main); color: var(--green-main); }

    /* ── PAGE HEADER ── */
    .page-header {
      background: linear-gradient(135deg, var(--green-dark) 0%, var(--green-main) 60%, var(--green-light) 100%);
      padding: 3rem 2rem 2.5rem; position: relative; overflow: hidden;
    }
    .page-header::before { content: ''; position: absolute; top: -50px; right: -50px; width: 280px; height: 280px; border-radius: 50%; background: rgba(255,255,255,.06); }
    .page-header::after  { content: ''; position: absolute; bottom: -60px; left: 10%; width: 180px; height: 180px; border-radius: 50%; background: rgba(255,255,255,.04); }
    .page-header .inner  { max-width: 1200px; margin: 0 auto; position: relative; z-index: 1; }
    .breadcrumb { font-size: .82rem; color: rgba(255,255,255,.65); margin-bottom: .75rem; display: flex; align-items: center; gap: .4rem; }
    .breadcrumb a { color: rgba(255,255,255,.75); transition: color .2s; }
    .breadcrumb a:hover { color: #fff; }
    .page-header h1 { font-family: 'Playfair Display', serif; font-size: clamp(1.5rem, 3vw, 2.2rem); font-weight: 800; color: #fff; line-height: 1.2; }

    /* ── MAIN LAYOUT ── */
    .main-wrap { flex: 1; padding: 2.5rem 2rem; }
    .page-grid {
      max-width: 1100px; margin: 0 auto;
      display: grid; grid-template-columns: 1fr 420px; gap: 2rem; align-items: start;
    }

    /* ── MATERIAU CARD ── */
    .mat-detail-card {
      background: #fff; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);
      border: 1px solid var(--gray-200); overflow: hidden;
    }
    .mat-visual {
      background: var(--green-pale); height: 200px;
      display: flex; align-items: center; justify-content: center;
      position: relative; overflow: hidden;
    }
    .mat-visual img {
      width: 100%; height: 100%; object-fit: cover;
      display: block; position: absolute; inset: 0;
    }
    .mat-visual-icon { font-size: 5rem; position: relative; z-index: 1; }
    .mat-badge-wrap { position: absolute; top: 1rem; right: 1rem; display: flex; gap: .5rem; flex-direction: column; align-items: flex-end; z-index: 2; }
    .badge { padding: .3rem .75rem; border-radius: 999px; font-size: .72rem; font-weight: 700; }
    .badge-dispo  { background: #d8f3dc; color: var(--green-dark); }
    .badge-cat    { background: rgba(255,255,255,.85); color: var(--green-main); }

    .mat-detail-body { padding: 1.75rem; }
    .mat-cat-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--green-mid); margin-bottom: .4rem; }
    .mat-name { font-family: 'Playfair Display', serif; font-size: 1.55rem; font-weight: 800; color: var(--gray-800); margin-bottom: .5rem; line-height: 1.2; }
    .mat-price-wrap { margin-bottom: 1.25rem; }
    .mat-price { font-size: 1.6rem; font-weight: 700; color: var(--green-main); font-family: 'Playfair Display', serif; }
    .mat-price-label { font-size: .78rem; color: var(--gray-600); margin-top: .1rem; }

    .detail-grid {
      display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; margin-bottom: 1.25rem;
    }
    .detail-item { background: var(--gray-100); border-radius: var(--radius-sm); padding: .7rem .9rem; }
    .detail-item-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--gray-400); margin-bottom: .2rem; }
    .detail-item-value { font-size: .9rem; font-weight: 600; color: var(--gray-800); }

    .detail-desc { margin-bottom: 1.25rem; }
    .detail-desc-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--gray-400); margin-bottom: .4rem; }
    .detail-desc-text { font-size: .9rem; color: var(--gray-600); line-height: 1.65; }

    .fournisseur-block {
      display: flex; align-items: center; gap: .85rem;
      padding: .9rem 1rem; background: var(--green-pale);
      border-radius: var(--radius-sm); border: 1px solid rgba(45,106,79,.15);
    }
    .fournisseur-avatar {
      width: 40px; height: 40px; border-radius: 50%;
      background: var(--green-main); color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: .95rem; flex-shrink: 0;
    }
    .fournisseur-name { font-weight: 600; font-size: .9rem; color: var(--green-dark); }
    .fournisseur-label { font-size: .75rem; color: var(--green-mid); }

    /* ── FORM CARD ── */
    .form-card {
      background: #fff; border-radius: var(--radius-md); box-shadow: var(--shadow-md);
      border: 1px solid var(--gray-200); padding: 2rem;
      position: sticky; top: 86px;
    }
    .form-card-title { font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 800; color: var(--gray-800); margin-bottom: .3rem; }
    .form-card-sub { font-size: .82rem; color: var(--gray-600); margin-bottom: 1.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--gray-200); }
    .form-card-sub strong { color: var(--green-main); }

    /* Matériau fixe affiché */
    .selected-mat-box {
      background: var(--green-pale); border: 1px solid rgba(82,183,136,.35);
      border-radius: var(--radius-sm); padding: .85rem 1rem;
      margin-bottom: 1.25rem; display: flex; align-items: center; gap: .75rem;
    }
    .selected-mat-icon { font-size: 1.8rem; flex-shrink: 0; }
    .selected-mat-name { font-weight: 700; font-size: .95rem; color: var(--green-dark); line-height: 1.2; }
    .selected-mat-cat  { font-size: .75rem; color: var(--green-mid); }
    .selected-mat-lock { margin-left: auto; font-size: .72rem; color: var(--green-mid); background: rgba(45,106,79,.1); padding: .2rem .55rem; border-radius: 999px; }

    /* Form elements */
    .form-group { margin-bottom: 1.25rem; }
    .form-label { display: block; font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--gray-600); margin-bottom: .45rem; }
    .form-input {
      width: 100%; padding: .7rem 1rem;
      border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
      font-family: 'Outfit', sans-serif; font-size: .95rem; color: var(--gray-800);
      background: var(--gray-100); transition: border-color .2s, background .2s;
    }
    .form-input:focus { outline: none; border-color: var(--green-main); background: #fff; }

    .statut-badge { display: inline-flex; align-items: center; gap: .4rem; padding: .4rem .9rem; border-radius: 999px; font-size: .8rem; font-weight: 600; background: #fff8e1; color: #856404; border: 1px solid #ffc107; }

    .alert-error { background: #ffe0e0; color: #7b0000; border: 1px solid #f5a5a5; border-radius: var(--radius-sm); padding: .85rem 1rem; font-size: .875rem; font-weight: 500; margin-bottom: 1.25rem; }

    .btn-submit {
      width: 100%; padding: .9rem; background: var(--green-main); color: #fff; border: none;
      border-radius: var(--radius-sm); font-family: 'Outfit', sans-serif; font-size: 1rem;
      font-weight: 700; cursor: pointer; transition: background .2s, transform .1s; margin-top: .5rem;
      display: flex; align-items: center; justify-content: center; gap: .5rem;
    }
    .btn-submit:hover  { background: var(--green-dark); }
    .btn-submit:active { transform: scale(.98); }

    .form-back { text-align: center; margin-top: 1rem; font-size: .82rem; }
    .form-back a { color: var(--green-main); font-weight: 600; }

    /* ── FOOTER ── */
    .footer { background: var(--green-dark); color: rgba(255,255,255,.75); padding: 2.5rem 2rem 1.5rem; margin-top: auto; }
    .footer-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 2rem; padding-bottom: 1.75rem; border-bottom: 1px solid rgba(255,255,255,.12); }
    .footer-logo { font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: .65rem; }
    .footer-col p { font-size: .84rem; line-height: 1.7; max-width: 240px; }
    .footer-col h5 { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: rgba(255,255,255,.4); margin-bottom: .85rem; }
    .footer-col ul li { margin-bottom: .45rem; }
    .footer-col ul a { font-size: .84rem; color: rgba(255,255,255,.65); transition: color .2s; }
    .footer-col ul a:hover { color: #fff; }
    .footer-bottom { max-width: 1200px; margin: 1.25rem auto 0; font-size: .78rem; color: rgba(255,255,255,.3); }

    @media (max-width: 860px) {
      .page-grid { grid-template-columns: 1fr; }
      .form-card { position: static; }
      .footer-inner { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 480px) {
      .main-wrap { padding: 1.5rem 1rem; }
      .page-header { padding: 2rem 1.2rem; }
      .detail-grid { grid-template-columns: 1fr; }
      .footer-inner { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="nav-inner">
    <a class="nav-logo" href="index.php">🌿 RéEmploi <strong>BTP</strong></a>
    <div class="nav-actions">
      <span class="nav-user-info">👤 <?= htmlspecialchars($_SESSION['prenom_user'] ?? $_SESSION['nom_user'] ?? '') ?></span>
      <a href="catalogue.php" class="btn-nav-back">← Catalogue</a>
      <a href="logout.php" class="btn-nav">Déconnexion</a>
    </div>
  </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
  <div class="inner">
    <div class="breadcrumb">
      <a href="catalogue.php">Catalogue</a>
      <span>›</span>
      <span>Réservation</span>
    </div>
    <h1>Réserver : <?= htmlspecialchars($mat['nom_materiau']) ?></h1>
  </div>
</div>

<!-- MAIN -->
<div class="main-wrap">
  <div class="page-grid">

    <!-- COLONNE GAUCHE : Détails du matériau -->
    <div class="mat-detail-card">
      <div class="mat-visual">
        <?php if ($imgFile !== ''): ?>
          <img src="img/<?= htmlspecialchars($imgFile) ?>"
               alt="<?= htmlspecialchars($mat['nom_materiau']) ?>">
        <?php else: ?>
          <span class="mat-visual-icon"><?= $icon ?></span>
        <?php endif; ?>
        <div class="mat-badge-wrap">
          <span class="badge badge-dispo">✅ Disponible</span>
          <span class="badge badge-cat"><?= htmlspecialchars($mat['nom_categorie']) ?></span>
        </div>
      </div>
      <div class="mat-detail-body">
        <div class="mat-cat-label"><?= htmlspecialchars($mat['nom_categorie']) ?></div>
        <div class="mat-name"><?= htmlspecialchars($mat['nom_materiau']) ?></div>

        <!-- Prix -->
        <div class="mat-price-wrap">
          <div class="mat-price"><?= $prix ?></div>
          <div class="mat-price-label">Prix indicatif · contactez le fournisseur pour finaliser</div>
        </div>

        <!-- Grille de détails -->
        <div class="detail-grid">
          <div class="detail-item">
            <div class="detail-item-label">Quantité</div>
            <div class="detail-item-value">📦 <?= (int)$mat['quantite_materiau'] ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-item-label">État</div>
            <div class="detail-item-value">🔧 <?= htmlspecialchars($mat['etat_materiau']) ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-item-label">Dimensions</div>
            <div class="detail-item-value">📐 <?= htmlspecialchars($mat['dimensions_materiau'] ?? '—') ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-item-label">Catégorie</div>
            <div class="detail-item-value">🏷️ <?= htmlspecialchars($mat['nom_categorie']) ?></div>
          </div>
          <?php if (!empty($mat['lieu_materiau'])): ?>
          <div class="detail-item" style="grid-column:1/-1;">
            <div class="detail-item-label">Lieu de récupération</div>
            <div class="detail-item-value">📍 <?= htmlspecialchars($mat['lieu_materiau']) ?></div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Description -->
        <?php if (!empty($mat['description_materiau'])): ?>
        <div class="detail-desc">
          <div class="detail-desc-label">Description</div>
          <div class="detail-desc-text"><?= nl2br(htmlspecialchars($mat['description_materiau'])) ?></div>
        </div>
        <?php endif; ?>

        <!-- Fournisseur -->
        <div class="fournisseur-block">
          <?php
            $initials = strtoupper(substr($mat['prenom_user'], 0, 1) . substr($mat['nom_user'], 0, 1));
          ?>
          <div class="fournisseur-avatar"><?= $initials ?></div>
          <div>
            <div class="fournisseur-name"><?= htmlspecialchars($mat['prenom_user'] . ' ' . $mat['nom_user']) ?></div>
            <div class="fournisseur-label">Fournisseur · <?= htmlspecialchars($mat['email_user']) ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- COLONNE DROITE : Formulaire de réservation -->
    <div class="form-card">
      <div class="form-card-title">📅 Formulaire de réservation</div>
      <div class="form-card-sub">
        Connecté en tant que <strong><?= htmlspecialchars(($_SESSION['prenom_user'] ?? '') . ' ' . ($_SESSION['nom_user'] ?? '')) ?></strong>
      </div>

      <?php if ($error): ?>
        <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="reserver.php?id=<?= $id_materiau ?>">
        <input type="hidden" name="id_materiau" value="<?= $id_materiau ?>">

        <!-- Matériau fixe (lecture seule) -->
        <div class="form-group">
          <label class="form-label">Matériau sélectionné</label>
          <div class="selected-mat-box">
            <span class="selected-mat-icon"><?= $icon ?></span>
            <div>
              <div class="selected-mat-name"><?= htmlspecialchars($mat['nom_materiau']) ?></div>
              <div class="selected-mat-cat"><?= htmlspecialchars($mat['nom_categorie']) ?></div>
            </div>
            <span class="selected-mat-lock">🔒 Fixé</span>
          </div>
        </div>

        <!-- Date -->
        <div class="form-group">
          <label class="form-label" for="date_reservation">Date souhaitée <span style="color:#c0392b">*</span></label>
          <input type="date" name="date_reservation" id="date_reservation" class="form-input"
                 value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
        </div>

        <!-- Statut -->
        <div class="form-group">
          <label class="form-label">Statut de la réservation</label>
          <div><span class="statut-badge">⏳ En attente de confirmation</span></div>
        </div>

        <button type="submit" class="btn-submit">
          <span>📅</span> Confirmer la réservation
        </button>
      </form>

      <div class="form-back">
        <a href="catalogue.php">← Retour au catalogue</a>
      </div>
    </div>

  </div>
</div>

<!-- FOOTER -->
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-col">
      <div class="footer-logo">🌿 RéEmploi <strong>BTP</strong></div>
      <p>Plateforme collaborative pour le réemploi des matériaux de construction.</p>
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
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2025 RéEmploi BTP · Tous droits réservés</p>
  </div>
</footer>

</body>
</html>