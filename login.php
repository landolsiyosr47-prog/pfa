<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['id_user'])) {
    header("Location: catalogue.php");
    exit;
}
if (isset($_SESSION['admin'])) {
    header("Location: admin/index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        // ── Vérification compte admin ──────────────────────
        if ($email === 'admin@gmail.com' && $password === 'admin') {
            $_SESSION['admin']       = true;
            $_SESSION['admin_email'] = $email;
            header("Location: admin/index.php");
            exit;
        }

        // ── Vérification utilisateur normal ───────────────
        $stmt = $conn->prepare("SELECT * FROM UTILISATEUR WHERE email_user = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && $password === $user['mot_de_passe_user']) {
            $_SESSION['id_user']     = $user['id_user'];
            $_SESSION['nom_user']    = $user['nom_user'];
            $_SESSION['prenom_user'] = $user['prenom_user'];
            $_SESSION['email_user']  = $user['email_user'];
            $_SESSION['type_user']   = $user['type_user'];

            $redirect = $_GET['redirect'] ?? 'catalogue.php';
            $id_param = isset($_GET['id']) ? '?id=' . (int)$_GET['id'] : '';
            header("Location: " . $redirect . $id_param);
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion — RéEmploi BTP</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --green-main:  #2d6a4f;
      --green-dark:  #1b4332;
      --green-light: #52b788;
      --green-pale:  #d8f3dc;
      --gray-100:    #f8f9fa;
      --gray-200:    #e9ecef;
      --gray-600:    #6c757d;
      --gray-800:    #343a40;
      --shadow-md:   0 4px 24px rgba(0,0,0,.12);
      --radius-sm:   8px;
      --radius-md:   14px;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Outfit', sans-serif;
      background: var(--gray-100);
      color: var(--gray-800);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    a { text-decoration: none; color: inherit; }

    .navbar { background: rgba(255,255,255,.97); border-bottom: 1px solid var(--gray-200); }
    .nav-inner { max-width: 1100px; margin: 0 auto; padding: 0 2rem; height: 64px; display: flex; align-items: center; }
    .nav-logo { font-size: 1.15rem; font-weight: 700; color: var(--green-dark); display: flex; align-items: center; gap: .45rem; }
    .nav-right { margin-left: auto; font-size: .875rem; color: var(--gray-600); }
    .nav-right a { color: var(--green-main); font-weight: 600; }

    .page-wrap {
      flex: 1; display: flex; align-items: center; justify-content: center;
      padding: 3rem 1.5rem;
      background: linear-gradient(150deg, var(--green-pale) 0%, #fff 55%);
    }
    .login-box {
      background: #fff; border-radius: var(--radius-md); box-shadow: var(--shadow-md);
      padding: 2.5rem 2rem; width: 100%; max-width: 420px;
    }
    .login-logo { text-align: center; font-size: 2.5rem; margin-bottom: .75rem; }
    .login-title { font-family: 'Playfair Display', serif; font-size: 1.7rem; font-weight: 800; color: var(--gray-800); text-align: center; margin-bottom: .3rem; }
    .login-sub { font-size: .875rem; color: var(--gray-600); text-align: center; margin-bottom: 2rem; }

    .alert-error { background: #ffe0e0; color: #7b0000; border: 1px solid #f5a5a5; border-radius: var(--radius-sm); padding: .9rem 1rem; font-size: .875rem; font-weight: 500; margin-bottom: 1.5rem; }

    .form-group { margin-bottom: 1.25rem; }
    .form-group label { display: block; font-size: .8rem; font-weight: 700; color: var(--gray-600); margin-bottom: .4rem; text-transform: uppercase; letter-spacing: .05em; }
    .form-group input {
      width: 100%; padding: .72rem 1rem;
      border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
      font-family: 'Outfit', sans-serif; font-size: .95rem; color: var(--gray-800);
      background: var(--gray-100); transition: border-color .2s, background .2s;
    }
    .form-group input:focus { outline: none; border-color: var(--green-main); background: #fff; }

    .password-wrapper { position: relative; }
    .password-wrapper input { padding-right: 3rem; }
    .toggle-pass {
      position: absolute; right: .9rem; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer; font-size: 1.1rem; color: var(--gray-400);
      transition: color .2s; padding: 0; line-height: 1;
    }
    .toggle-pass:hover { color: var(--green-main); }

    .btn-login {
      width: 100%; padding: .88rem; background: var(--green-main); color: #fff; border: none;
      border-radius: var(--radius-sm); font-family: 'Outfit', sans-serif; font-size: 1rem;
      font-weight: 700; cursor: pointer; transition: background .2s; margin-top: .5rem;
    }
    .btn-login:hover { background: var(--green-dark); }

    .divider { display: flex; align-items: center; gap: .75rem; margin: 1.5rem 0; color: var(--gray-400); font-size: .8rem; }
    .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--gray-200); }

    .login-footer { text-align: center; font-size: .875rem; color: var(--gray-600); }
    .login-footer a { color: var(--green-main); font-weight: 600; }

    .footer { background: var(--green-dark); color: rgba(255,255,255,.5); text-align: center; padding: 1.2rem; font-size: .8rem; }
  </style>
</head>
<body>

<nav class="navbar">
  <div class="nav-inner">
    <a class="nav-logo" href="index.php">🌿 RéEmploi <strong>BTP</strong></a>
    <div class="nav-right">Pas encore inscrit ? <a href="register.php">Créer un compte</a></div>
  </div>
</nav>

<div class="page-wrap">
  <div class="login-box">
    <div class="login-logo">🌿</div>
    <div class="login-title">Connexion</div>
    <div class="login-sub">Accédez à votre espace RéEmploi BTP</div>

    <?php if ($error): ?>
      <div class="alert-error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php<?= isset($_GET['redirect']) ? '?redirect='.urlencode($_GET['redirect']).(isset($_GET['id']) ? '&id='.(int)$_GET['id'] : '') : '' ?>">
      <div class="form-group">
        <label for="email">Adresse e-mail</label>
        <input type="email" name="email" id="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               placeholder="votre@email.com" required autofocus>
      </div>
      <div class="form-group">
        <label for="password">Mot de passe</label>
        <div class="password-wrapper">
          <input type="password" name="password" id="password" placeholder="••••••••" required>
          <button type="button" class="toggle-pass" onclick="togglePassword()" title="Afficher/masquer">👁️</button>
        </div>
      </div>
      <button type="submit" class="btn-login">🔐 Se connecter</button>
    </form>

    <div class="divider">ou</div>

    <div class="login-footer">
      Pas encore de compte ? <a href="register.php">S'inscrire gratuitement</a><br>
      <a href="catalogue.php" style="display:inline-block;margin-top:.6rem;font-size:.8rem;color:var(--gray-600);">← Retour au catalogue</a>
    </div>
  </div>
</div>

<footer class="footer">
  <p>© 2025 RéEmploi BTP · Tous droits réservés</p>
</footer>

<script>
  function togglePassword() {
    const inp = document.getElementById('password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
  }
</script>
</body>
</html>