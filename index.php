<?php
require_once 'config.php';
session_start();

// ── Stats réelles depuis la BDD ──────────────────────────────────────────────
$r = $conn->query("SELECT COUNT(*) AS nb FROM MATERIAU");
$stat_materiaux = ($r && $row = $r->fetch_assoc()) ? (int)$row['nb'] : 0;

$r = $conn->query("SELECT COUNT(*) AS nb FROM UTILISATEUR");
$stat_utilisateurs = ($r && $row = $r->fetch_assoc()) ? (int)$row['nb'] : 0;

$stat_dechets = round($stat_materiaux * 0.05, 1);
$stat_co2     = round($stat_dechets * 0.3, 1);

// ── 6 derniers matériaux ─────────────────────────────────────────────────────
$materiaux_recents = [];
$stmt = $conn->prepare("
    SELECT m.id_materiau, m.nom_materiau, m.quantite_materiau,
           m.dimensions_materiau, m.etat_materiau,
           c.nom_categorie
    FROM MATERIAU m
    JOIN UTILISATEUR u ON m.id_user = u.id_user
    JOIN CATEGORIE c ON m.id_categorie = c.id_categorie
    ORDER BY m.date_publication_materiau DESC
    LIMIT 6
");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $materiaux_recents[] = $row;
    $stmt->close();
}

$catIcons = [
    'menuiserie'  => '🚪', 'carrelage' => '🔲', 'bois'        => '🪵',
    'sanitaire'   => '🚿', 'metal'     => '🔩', 'electricite' => '⚡',
    'isolation'   => '🧱', 'autre'     => '📦',
];
$bgColors = ['pf-green', 'pf-clay', 'pf-sage', 'pf-earth', 'pf-sky', 'pf-warm'];

// ── Images par mot-clé dans le nom du matériau ─────────────────────────────
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

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role === 'entreprise') header('Location: entreprise/ajout.php');
    elseif ($role === 'admin') header('Location: admin/dashboard.php');
    else header('Location: catalogue.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RéEmploi BTP — Plateforme de réemploi des matériaux</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ═══════════════════════════════════════
     NAVBAR
═══════════════════════════════════════ -->
<nav class="navbar" id="navbar">
  <div class="nav-inner">
    <a class="nav-logo" href="index.php">
      <span class="logo-icon">🌿</span>
      <span class="logo-text">RéEmploi <strong>BTP</strong></span>
    </a>
    <ul class="nav-links">
      <li><a href="catalogue.php">Catalogue</a></li>
    </ul>
    <div class="nav-actions">
      <a class="btn-connexion" href="#" onclick="scrollToLogin(); return false;">Connexion</a>
      <a class="btn-inscrire" href="register.php">S'inscrire</a>
    </div>
    <button class="nav-burger" id="navBurger" onclick="toggleMenu()">
      <span></span><span></span><span></span>
    </button>
  </div>
  <div class="nav-mobile" id="navMobile">
    <a href="catalogue.php">Catalogue</a>
    <a href="#" onclick="scrollToLogin(); return false;">Connexion</a>
    <a href="register.php" class="mobile-cta">S'inscrire</a>
  </div>
</nav>

<!-- ═══════════════════════════════════════
     HERO SECTION
═══════════════════════════════════════ -->
<section class="hero">
  <div class="hero-bg">
    <svg class="hero-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 700" preserveAspectRatio="xMidYMid slice">
      <defs>
        <radialGradient id="sunGrad" cx="60%" cy="30%" r="40%">
          <stop offset="0%" stop-color="#fff8e1" stop-opacity="0.9"/>
          <stop offset="60%" stop-color="#ffcc02" stop-opacity="0.2"/>
          <stop offset="100%" stop-color="transparent"/>
        </radialGradient>
        <linearGradient id="skyGrad" x1="0%" y1="0%" x2="0%" y2="100%">
          <stop offset="0%" stop-color="#1a3a1a"/>
          <stop offset="40%" stop-color="#2d6a2d"/>
          <stop offset="100%" stop-color="#4a9e4a"/>
        </linearGradient>
        <linearGradient id="groundGrad" x1="0%" y1="0%" x2="0%" y2="100%">
          <stop offset="0%" stop-color="#2d5a1e"/>
          <stop offset="100%" stop-color="#1a3a0e"/>
        </linearGradient>
      </defs>
      <rect width="1440" height="700" fill="url(#skyGrad)"/>
      <circle cx="864" cy="210" r="200" fill="url(#sunGrad)"/>
      <ellipse cx="720" cy="700" rx="900" ry="250" fill="#1d4a10" opacity="0.95"/>
      <ellipse cx="720" cy="750" rx="800" ry="200" fill="#16380c" opacity="0.8"/>
      <rect x="60" y="280" width="28" height="220" fill="#1a3a0e"/>
      <ellipse cx="74" cy="260" rx="60" ry="90" fill="#2d6a1e"/>
      <ellipse cx="74" cy="240" rx="45" ry="70" fill="#3a8a28"/>
      <rect x="140" y="320" width="22" height="190" fill="#1a3a0e"/>
      <ellipse cx="151" cy="305" rx="48" ry="72" fill="#245c18"/>
      <rect x="0" y="380" width="18" height="150" fill="#1a3a0e"/>
      <ellipse cx="9" cy="368" rx="40" ry="60" fill="#2d6a1e"/>
      <rect x="1340" y="290" width="26" height="210" fill="#1a3a0e"/>
      <ellipse cx="1353" cy="270" rx="55" ry="85" fill="#2d6a1e"/>
      <ellipse cx="1353" cy="252" rx="42" ry="65" fill="#3a8a28"/>
      <rect x="1270" y="340" width="20" height="170" fill="#1a3a0e"/>
      <ellipse cx="1280" cy="325" rx="45" ry="68" fill="#245c18"/>
      <rect x="1400" y="370" width="15" height="130" fill="#1a3a0e"/>
      <ellipse cx="1408" cy="360" rx="35" ry="52" fill="#2d6a1e"/>
      <ellipse cx="200" cy="540" rx="80" ry="40" fill="#1d4a10"/>
      <ellipse cx="350" cy="560" rx="60" ry="30" fill="#245c18"/>
      <ellipse cx="1100" cy="545" rx="75" ry="38" fill="#1d4a10"/>
      <ellipse cx="1250" cy="558" rx="55" ry="28" fill="#245c18"/>
      <ellipse cx="720" cy="590" rx="220" ry="45" fill="#2d8a5a" opacity="0.35"/>
      <ellipse cx="720" cy="588" rx="180" ry="30" fill="#4abe8a" opacity="0.15"/>
      <circle cx="300" cy="510" r="5" fill="#e8603c" opacity="0.8"/>
      <circle cx="320" cy="505" r="4" fill="#ff9070" opacity="0.7"/>
      <circle cx="280" cy="515" r="3" fill="#e8a23c" opacity="0.8"/>
      <circle cx="1120" cy="512" r="5" fill="#e8603c" opacity="0.8"/>
      <circle cx="1140" cy="507" r="4" fill="#ff9070" opacity="0.7"/>
      <ellipse cx="720" cy="660" rx="60" ry="120" fill="#3d8a2a" opacity="0.4"/>
    </svg>
    <div class="hero-overlay"></div>
  </div>

  <div class="hero-content">
    <div class="hero-badge">🌱 Économie circulaire · Secteur BTP</div>
    <h1 class="hero-title">Donnez une seconde vie<br>aux matériaux de chantier</h1>
    <p class="hero-subtitle">Connectez entreprises, artisans et particuliers pour réemployer les surplus de construction et réduire l'empreinte environnementale du secteur.</p>
    <div class="hero-btns">
      <a href="#role-section" class="hero-cta-primary" onclick="smoothScroll('#role-section')">Commencer maintenant</a>
      <a href="catalogue.php" class="hero-cta-secondary">Voir le catalogue →</a>
    </div>
    <div class="hero-stats">
      <div class="hstat"><span class="hstat-n"><?= number_format($stat_materiaux, 0, ',', ' ') ?></span><span class="hstat-l">Matériaux disponibles</span></div>
      <div class="hstat-sep"></div>
      <div class="hstat"><span class="hstat-n"><?= $stat_dechets ?> t</span><span class="hstat-l">Déchets évités</span></div>
      <div class="hstat-sep"></div>
      <div class="hstat"><span class="hstat-n"><?= number_format($stat_utilisateurs, 0, ',', ' ') ?></span><span class="hstat-l">Utilisateurs actifs</span></div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     FEATURES STRIP
═══════════════════════════════════════ -->
<section class="features-strip">
  <div class="container">
    <div class="features-grid">
      <div class="feature-item">
        <div class="fi-icon">🏗️</div>
        <h3>Publication facile</h3>
        <p>Les entreprises publient leurs surplus en quelques clics depuis le chantier.</p>
      </div>
      <div class="feature-item">
        <div class="fi-icon">🔍</div>
        <h3>Catalogue filtrable</h3>
        <p>Recherchez par catégorie, localisation ou type de matériau.</p>
      </div>
      <div class="feature-item">
        <div class="fi-icon">📅</div>
        <h3>Réservation en ligne</h3>
        <p>Réservez et coordonnez la récupération avec le fournisseur.</p>
      </div>
      <div class="feature-item">
        <div class="fi-icon">🌿</div>
        <h3>Impact mesuré</h3>
        <p>Chaque transaction génère un bilan carbone et déchets évités.</p>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     ROLE SECTION
═══════════════════════════════════════ -->
<section class="role-section" id="role-section">
  <div class="container">
    <div class="section-head">
      <h2>Comment souhaitez-vous utiliser la plateforme ?</h2>
      <p>Choisissez votre profil pour découvrir une expérience adaptée à vos besoins.</p>
    </div>

    <div class="role-cards" id="roleCards">
      <div class="role-card" id="card-entreprise" onclick="selectRole('entreprise')">
        <div class="rc-icon-wrap"><div class="rc-icon">🏢</div></div>
        <h3>Entreprise BTP</h3>
        <p>Déposez vos surplus de chantier, réduisez vos coûts de traitement des déchets et contribuez à l'économie circulaire.</p>
        <ul class="rc-features">
          <li>✓ Publication de matériaux</li>
          <li>✓ Gestion des stocks</li>
          <li>✓ Suivi des transactions</li>
        </ul>
        <div class="rc-cta">Se connecter <span>→</span></div>
      </div>

      <div class="role-card role-card-featured" id="card-artisan" onclick="selectRole('artisan')">
        <div class="rc-badge">Le plus populaire</div>
        <div class="rc-icon-wrap"><div class="rc-icon">🔨</div></div>
        <h3>Artisan / Particulier</h3>
        <p>Trouvez des matériaux de qualité à prix réduit ou gratuitement pour vos projets de construction ou rénovation.</p>
        <ul class="rc-features">
          <li>✓ Catalogue complet</li>
          <li>✓ Réservation en ligne</li>
          <li>✓ Recherche par zone</li>
        </ul>
        <div class="rc-cta">Se connecter <span>→</span></div>
      </div>

      <div class="role-card" id="card-admin" onclick="selectRole('admin')">
        <div class="rc-icon-wrap"><div class="rc-icon">🛡️</div></div>
        <h3>Administrateur</h3>
        <p>Gérez la plateforme, modérez les annonces et supervisez l'impact environnemental global.</p>
        <ul class="rc-features">
          <li>✓ Gestion des utilisateurs</li>
          <li>✓ Modération des annonces</li>
          <li>✓ Tableau de bord global</li>
        </ul>
        <div class="rc-cta">Se connecter <span>→</span></div>
      </div>
    </div>

    <!-- LOGIN FORM (glassmorphism) -->
    <div class="login-glass" id="loginBox" style="display:none;">
      <div class="login-glass-inner">
        <button class="login-close" onclick="closeLogin()">✕</button>
        <div class="login-role-badge" id="loginRoleBadge">🏢 Entreprise BTP</div>
        <h3 class="login-title">Connexion</h3>
        <p class="login-sub" id="loginSub">Accédez à votre espace de publication</p>

        <form action="login.php" method="POST" class="login-form" id="loginForm">
          <input type="hidden" name="role" id="loginRoleInput" value="entreprise">

          <div class="form-group">
            <label for="email">Adresse e-mail</label>
            <div class="input-wrap">
              <span class="input-icon">✉️</span>
              <input type="email" id="email" name="email" placeholder="votre@email.com" required autocomplete="email">
            </div>
          </div>

          <div class="form-group">
            <label for="password">Mot de passe</label>
            <div class="input-wrap">
              <span class="input-icon">🔒</span>
              <input type="password" id="password" name="password" placeholder="••••••••" required>
              <button type="button" class="toggle-pw" onclick="togglePw()">👁</button>
            </div>
          </div>

          <?php if (isset($_GET['error'])): ?>
          <div class="form-error">❌ <?= htmlspecialchars($_GET['error']) ?></div>
          <?php endif; ?>

          <a href="forgot.php" class="form-forgot">Mot de passe oublié ?</a>

          <button type="submit" class="btn-login" id="btnLogin">
            <span>Se connecter</span>
            <span class="btn-arrow">→</span>
          </button>

          <div class="form-register">
            Pas encore de compte ? <a href="register.php" id="registerLink">Créer un compte</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     HOW IT WORKS
═══════════════════════════════════════ -->
<section class="how-section">
  <div class="container">
    <div class="section-head">
      <h2>Comment ça fonctionne</h2>
    </div>
    <div class="steps-grid">
      <div class="step">
        <div class="step-num">01</div>
        <h4>Créez votre compte</h4>
        <p>Choisissez votre profil (entreprise, artisan ou particulier) et inscrivez-vous gratuitement.</p>
      </div>
      <div class="step-arrow">→</div>
      <div class="step">
        <div class="step-num">02</div>
        <h4>Publiez ou recherchez</h4>
        <p>Entreprises : publiez vos surplus. Artisans : parcourez le catalogue et filtrez selon vos besoins.</p>
      </div>
      <div class="step-arrow">→</div>
      <div class="step">
        <div class="step-num">03</div>
        <h4>Réservez & récupérez</h4>
        <p>Contactez le fournisseur, réservez et récupérez les matériaux directement sur le chantier.</p>
      </div>
      <div class="step-arrow">→</div>
      <div class="step">
        <div class="step-num">04</div>
        <h4>Mesurez votre impact</h4>
        <p>Suivez les tonnes de déchets évités et le CO₂ économisé grâce à votre participation.</p>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     PORTFOLIO / RECENT MATERIALS
═══════════════════════════════════════ -->
<section class="portfolio-section">
  <div class="container">
    <div class="section-head">
      <h2>Matériaux disponibles</h2>
      <p>Découvrez une sélection des derniers matériaux publiés sur la plateforme</p>
    </div>
    <?php if (empty($materiaux_recents)): ?>
      <div style="text-align:center; padding:2rem; color:#888;">Aucun matériau publié pour le moment.</div>
    <?php else: ?>
    <div class="portfolio-grid">
      <?php foreach ($materiaux_recents as $i => $m):
        $icon    = $catIcons[strtolower($m['nom_categorie'])] ?? '📦';
        $bg      = $bgColors[$i % count($bgColors)];
        $imgFile = getMatImage($m['nom_materiau'], $matImages);
      ?>
      <div class="pf-card">
        <div class="pf-img <?= $bg ?>" style="overflow:hidden; position:relative;">
          <?php if ($imgFile !== ''): ?>
            <img src="img/<?= htmlspecialchars($imgFile) ?>"
                 alt="<?= htmlspecialchars($m['nom_materiau']) ?>"
                 style="width:100%;height:100%;object-fit:cover;display:block;position:absolute;inset:0;">
          <?php else: ?>
            <span><?= $icon ?></span>
          <?php endif; ?>
          <div class="pf-overlay">
            <div class="pf-tag"><?= htmlspecialchars($m['nom_categorie']) ?></div>
          </div>
        </div>
        <h4><?= htmlspecialchars($m['nom_materiau']) ?></h4>
        <p>
          <?= (int)$m['quantite_materiau'] ?> unité(s)
          <?php if (!empty($m['dimensions_materiau'])): ?> · <?= htmlspecialchars($m['dimensions_materiau']) ?><?php endif; ?>
          · <?= htmlspecialchars($m['etat_materiau']) ?>
        </p>
        <a href="catalogue.php" class="pf-link">Voir le détail →</a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div style="text-align:center; margin-top:2.5rem;">
      <a href="catalogue.php" class="btn-catalogue">Voir tout le catalogue</a>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     ECO COUNTER
═══════════════════════════════════════ -->
<section class="eco-section">
  <div class="container">
    <div class="eco-grid">
      <div class="eco-item">
        <div class="eco-icon">♻️</div>
        <div class="eco-val" data-target="<?= $stat_materiaux ?>">0</div>
        <div class="eco-lbl">Matériaux réemployés</div>
      </div>
      <div class="eco-item">
        <div class="eco-icon">🌿</div>
        <div class="eco-val" data-target="<?= $stat_dechets ?>">0</div>
        <div class="eco-lbl">Tonnes de déchets évitées</div>
      </div>
      <div class="eco-item">
        <div class="eco-icon">🌍</div>
        <div class="eco-val" data-target="<?= $stat_co2 ?>">0</div>
        <div class="eco-lbl">Tonnes de CO₂ économisées</div>
      </div>
      <div class="eco-item">
        <div class="eco-icon">🤝</div>
        <div class="eco-val" data-target="<?= $stat_utilisateurs ?>">0</div>
        <div class="eco-lbl">Utilisateurs actifs</div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     FOOTER
═══════════════════════════════════════ -->
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-col">
      <div class="footer-logo">🌿 RéEmploi <strong>BTP</strong></div>
      <p>Plateforme collaborative pour le réemploi des matériaux de construction.</p>
      <div class="footer-socials">
        <a href="#">📘</a><a href="#">🐦</a><a href="#">💼</a>
      </div>
    </div>
    <div class="footer-col">
      <h5>Navigation</h5>
      <ul>
        <li><a href="catalogue.php">Catalogue</a></li>
        <li><a href="impact.php">Notre impact</a></li>
        <li><a href="register.php">S'inscrire</a></li>
        <li><a href="#">Comment ça marche</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h5>Contact</h5>
      <p>📧 contact@reemploi-btp.tn</p>
      <p>📞 +216 71 000 000</p>
      <p>📍 Tunis, Tunisie</p>
    </div>
    <div class="footer-col">
      <h5>Newsletter</h5>
      <p>Recevez les nouveaux matériaux disponibles</p>
      <form class="footer-form" onsubmit="return false;">
        <input type="email" placeholder="votre@email.com">
        <button type="submit">→</button>
      </form>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2025 RéEmploi BTP · Tous droits réservés · <a href="#">Mentions légales</a></p>
  </div>
</footer>

<script src="script.js"></script>
</body>
</html>