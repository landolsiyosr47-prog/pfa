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
$success = '';
$error   = '';

// ── Traitement POST ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_materiau         = trim($_POST['nom_materiau'] ?? '');
    $description_materiau = trim($_POST['description_materiau'] ?? '');
    $etat_materiau        = trim($_POST['etat_materiau'] ?? '');
    $quantite_materiau    = (int)($_POST['quantite_materiau'] ?? 0);
    $dimensions_materiau  = trim($_POST['dimensions_materiau'] ?? '');
    $id_categorie         = (int)($_POST['id_categorie'] ?? 0);

    if (empty($nom_materiau) || empty($etat_materiau) || !$quantite_materiau || !$id_categorie) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        $ins = $conn->prepare(
            "INSERT INTO MATERIAU (nom_materiau, description_materiau, etat_materiau, quantite_materiau, dimensions_materiau, id_categorie, id_user, date_publication_materiau, disponibilite_materiau)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'disponible')"
        );
        $ins->bind_param('sssisis',
            $nom_materiau,
            $description_materiau,
            $etat_materiau,
            $quantite_materiau,
            $dimensions_materiau,
            $id_categorie,
            $id_user
        );
        if ($ins->execute()) {
            $success = 'Matériau publié avec succès ! Il est maintenant visible dans le catalogue.';
        } else {
            $error = 'Erreur lors de la publication : ' . $ins->error;
        }
        $ins->close();
    }
}

// ── Récupération catégories ────────────────────────────────
$cats = $conn->query("SELECT id_categorie, nom_categorie FROM CATEGORIE ORDER BY nom_categorie");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Publier un matériau — RéEmploi BTP</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --green-main:  #2d6a4f;
      --green-dark:  #1b4332;
      --green-light: #52b788;
      --green-pale:  #d8f3dc;
      --gray-100:    #f8f9fa;
      --gray-200:    #e9ecef;
      --gray-400:    #adb5bd;
      --gray-600:    #6c757d;
      --gray-800:    #343a40;
      --shadow-sm:   0 2px 8px rgba(0,0,0,.07);
      --shadow-md:   0 4px 24px rgba(0,0,0,.12);
      --radius-sm:   8px;
      --radius-md:   14px;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body { font-family: 'Outfit', sans-serif; background: var(--gray-100); color: var(--gray-800); min-height: 100vh; display: flex; flex-direction: column; }
    a { text-decoration: none; color: inherit; }

    /* NAVBAR */
    .navbar { background: rgba(255,255,255,.97); border-bottom: 1px solid var(--gray-200); position: sticky; top: 0; z-index: 100; box-shadow: var(--shadow-sm); }
    .nav-inner { max-width: 1100px; margin: 0 auto; padding: 0 2rem; height: 64px; display: flex; align-items: center; gap: 1.5rem; }
    .nav-logo { font-size: 1.15rem; font-weight: 700; color: var(--green-dark); display: flex; align-items: center; gap: .45rem; }
    .nav-right { display: flex; gap: .75rem; align-items: center; margin-left: auto; }
    .btn-nav-outline { padding: .42rem 1rem; border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm); font-size: .85rem; font-weight: 500; color: var(--gray-800); transition: border-color .2s, color .2s; }
    .btn-nav-outline:hover { border-color: var(--green-main); color: var(--green-main); }
    .btn-nav-green { padding: .42rem 1rem; background: var(--green-main); color: #fff; border: none; border-radius: var(--radius-sm); font-size: .85rem; font-weight: 600; transition: background .2s; }
    .btn-nav-green:hover { background: var(--green-dark); }

    /* PAGE HEADER */
    .page-header {
      background: linear-gradient(135deg, var(--green-dark), var(--green-main) 60%, var(--green-light));
      padding: 3.5rem 2rem 2.5rem; position: relative; overflow: hidden;
    }
    .page-header::before { content: ''; position: absolute; top: -50px; right: -50px; width: 280px; height: 280px; border-radius: 50%; background: rgba(255,255,255,.06); }
    .page-header .inner { max-width: 1100px; margin: 0 auto; position: relative; z-index: 1; }
    .page-header h1 { font-family: 'Playfair Display', serif; font-size: clamp(1.6rem, 3vw, 2.4rem); font-weight: 800; color: #fff; margin-bottom: .5rem; }
    .page-header p { color: rgba(255,255,255,.78); font-size: .95rem; }

    /* MAIN */
    .main { flex: 1; padding: 3rem 2rem; }

    /* FORM CARD */
    .form-card { max-width: 620px; margin: 0 auto; background: #fff; border-radius: var(--radius-md); box-shadow: var(--shadow-md); padding: 2.5rem 2rem; }
    .form-card-header { margin-bottom: 2rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--gray-200); }
    .form-card-header h2 { font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 700; color: var(--gray-800); margin-bottom: .3rem; }
    .form-card-header p { font-size: .875rem; color: var(--gray-600); }

    /* ALERTS */
    .alert { padding: .9rem 1.2rem; border-radius: var(--radius-sm); font-weight: 500; font-size: .9rem; margin-bottom: 1.5rem; }
    .alert-success { background: var(--green-pale); color: var(--green-dark); border: 1px solid var(--green-light); }
    .alert-error   { background: #ffe0e0; color: #7b0000; border: 1px solid #f5a5a5; }

    /* FORM GROUPS */
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .form-group { margin-bottom: 1.4rem; }
    .form-group label { display: block; font-size: .82rem; font-weight: 600; color: var(--gray-600); margin-bottom: .45rem; text-transform: uppercase; letter-spacing: .05em; }
    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group select,
    .form-group textarea {
      width: 100%; padding: .7rem 1rem;
      border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
      font-family: 'Outfit', sans-serif; font-size: .95rem; color: var(--gray-800);
      background: var(--gray-100); transition: border-color .2s, background .2s;
    }
    .form-group textarea { resize: vertical; min-height: 100px; }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus { outline: none; border-color: var(--green-main); background: #fff; }
    .required-mark { color: #c0392b; margin-left: 2px; }

    .btn-submit { width: 100%; padding: .85rem; background: var(--green-main); color: #fff; border: none; border-radius: var(--radius-sm); font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700; cursor: pointer; transition: background .2s; margin-top: .5rem; }
    .btn-submit:hover { background: var(--green-dark); }

    .form-footer { display: flex; gap: 1rem; justify-content: center; margin-top: 1.2rem; font-size: .85rem; color: var(--gray-600); }
    .form-footer a { color: var(--green-main); font-weight: 600; }

    /* FOOTER */
    .footer { background: var(--green-dark); color: rgba(255,255,255,.6); text-align: center; padding: 1.5rem 2rem; font-size: .8rem; margin-top: auto; }

    @media (max-width: 600px) {
      .form-card { padding: 1.8rem 1.2rem; }
      .page-header { padding: 2rem 1.2rem; }
      .form-row { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="nav-inner">
    <a class="nav-logo" href="index.php">🌿 RéEmploi <strong>BTP</strong></a>
    <div class="nav-right">
      <span style="font-size:.85rem;color:var(--gray-600);">
        🏢 <?= htmlspecialchars($_SESSION['nom_user'] ?? 'Entreprise') ?>
      </span>
      <a href="mes_materiaux.php" class="btn-nav-outline">Mes matériaux</a>
      <a href="catalogue.php" class="btn-nav-outline">Catalogue</a>
      <a href="logout.php" class="btn-nav-outline">Déconnexion</a>
    </div>
  </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
  <div class="inner">
    <h1>Publier un matériau</h1>
    <p>Mettez vos surplus de construction à disposition de la communauté BTP</p>
  </div>
</div>

<!-- MAIN -->
<div class="main">
  <div class="form-card">
    <div class="form-card-header">
      <h2>📦 Nouveau matériau</h2>
      <p>Les champs marqués <span style="color:#c0392b">*</span> sont obligatoires</p>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?>
        <br><a href="mes_materiaux.php" style="color:var(--green-dark);font-weight:600;">→ Voir mes matériaux publiés</a>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="publier.php">

      <!-- Nom -->
      <div class="form-group">
        <label for="nom_materiau">Nom du matériau <span class="required-mark">*</span></label>
        <input type="text" name="nom_materiau" id="nom_materiau"
               value="<?= htmlspecialchars($_POST['nom_materiau'] ?? '') ?>"
               placeholder="Ex : Briques réfractaires, Parquet chêne..." required>
      </div>

      <!-- Catégorie -->
      <div class="form-group">
        <label for="id_categorie">Catégorie <span class="required-mark">*</span></label>
        <select name="id_categorie" id="id_categorie" required>
          <option value="">— Choisir une catégorie —</option>
          <?php if ($cats): while ($cat = $cats->fetch_assoc()): ?>
            <option value="<?= (int)$cat['id_categorie'] ?>"
              <?= (isset($_POST['id_categorie']) && $_POST['id_categorie'] == $cat['id_categorie']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['nom_categorie']) ?>
            </option>
          <?php endwhile; endif; ?>
        </select>
      </div>

      <!-- Quantité + État -->
      <div class="form-row">
        <div class="form-group">
          <label for="quantite_materiau">Quantité <span class="required-mark">*</span></label>
          <input type="number" name="quantite_materiau" id="quantite_materiau"
                 value="<?= htmlspecialchars($_POST['quantite_materiau'] ?? '') ?>"
                 min="1" placeholder="Ex : 50" required>
        </div>
        <div class="form-group">
          <label for="etat_materiau">État <span class="required-mark">*</span></label>
          <select name="etat_materiau" id="etat_materiau" required>
            <option value="">— État —</option>
            <?php foreach (['Neuf','Très bon état','Bon état','Usagé','À rénover'] as $etat): ?>
              <option value="<?= $etat ?>" <?= (isset($_POST['etat_materiau']) && $_POST['etat_materiau'] === $etat) ? 'selected' : '' ?>><?= $etat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Dimensions -->
      <div class="form-group">
        <label for="dimensions_materiau">Dimensions / Unité</label>
        <input type="text" name="dimensions_materiau" id="dimensions_materiau"
               value="<?= htmlspecialchars($_POST['dimensions_materiau'] ?? '') ?>"
               placeholder="Ex : 60x60 cm, 2m×1m, unité, m², kg...">
      </div>

      <!-- Description -->
      <div class="form-group">
        <label for="description_materiau">Description</label>
        <textarea name="description_materiau" id="description_materiau"
                  placeholder="Décrivez le matériau : origine, caractéristiques, conditions de récupération..."><?= htmlspecialchars($_POST['description_materiau'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn-submit">🌿 Publier le matériau</button>
    </form>

    <div class="form-footer">
      <a href="catalogue.php">← Catalogue</a>
      <span>·</span>
      <a href="mes_materiaux.php">Mes matériaux publiés</a>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer class="footer">
  <p>© 2025 RéEmploi BTP · Économie circulaire pour le secteur du bâtiment</p>
</footer>

</body>
</html>