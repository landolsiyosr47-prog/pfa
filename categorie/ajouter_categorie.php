<?php include("../config.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RéEmploi BTP — Ajouter Catégorie</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Outfit:wght@300;400;500;600&family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-green: #3D8C62;
            --dark-green: #1A3C34;
            --soft-green: #eef7f2;
            --cream-bg: #fdfbf4;
            --white: #ffffff;
        }

        body {
            font-family: 'Quicksand', sans-serif;
            background-color: var(--cream-bg);
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* NAVBAR RÉFÉRENCE */
        .navbar {
            background-color: var(--white);
            padding: 1rem 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            position: sticky; top: 0; z-index: 1000;
        }

        .nav-logo {
            font-family: 'Playfair Display', serif;
            text-decoration: none;
            color: var(--dark-green);
            font-size: 1.4rem;
            display: flex; align-items: center; gap: 8px;
        }

        .nav-links { display: flex; gap: 25px; list-style: none; margin: 0; padding: 0; }
        .nav-links a { text-decoration: none; color: var(--dark-green); font-weight: 600; font-size: 0.95rem; }

        /* FORM CARD */
        .main-content { flex: 1; display: flex; justify-content: center; align-items: center; padding: 60px 20px; }
        .form-card {
            background: var(--white);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(26, 60, 52, 0.08);
            width: 100%;
            max-width: 450px;
        }

        h2 { font-family: 'Playfair Display', serif; color: var(--dark-green); text-align: center; margin-bottom: 30px; font-size: 2rem; }

        .label-title { display: block; font-weight: 700; color: var(--primary-green); margin-bottom: 8px; font-size: 0.9rem; }
        
        input {
            width: 100%; padding: 14px 18px; margin-bottom: 25px;
            border: 2px solid transparent; border-radius: 15px;
            background-color: var(--soft-green); font-family: inherit; font-size: 1rem; box-sizing: border-box; transition: 0.3s;
        }

        input:focus { outline: none; background-color: var(--white); border-color: var(--primary-green); box-shadow: 0 5px 15px rgba(61, 140, 98, 0.1); }

        button[type="submit"] {
            width: 100%; background-color: var(--primary-green); color: white;
            padding: 16px; border: none; border-radius: 50px; font-weight: 700; cursor: pointer; transition: 0.3s;
        }

        button[type="submit"]:hover { background-color: var(--dark-green); transform: translateY(-2px); }

        /* FOOTER RÉFÉRENCE */
        .footer { background-color: var(--dark-green); color: var(--white); padding: 60px 8% 30px; margin-top: auto; }
        .footer-inner { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; max-width: 1200px; margin: 0 auto; }
        .footer-col h5 { font-family: 'Playfair Display', serif; font-size: 1.2rem; margin-bottom: 20px; }
        .footer-col ul { list-style: none; padding: 0; }
        .footer-col a { color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.9rem; }
        .footer-bottom { text-align: center; margin-top: 50px; padding-top: 25px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 0.8rem; color: rgba(255,255,255,0.5); }
    </style>
</head>
<body>

    <nav class="navbar">
        <a class="nav-logo" href="index.php"><span>🌿</span> RéEmploi <strong>BTP</strong></a>
        <ul class="nav-links">
            <li><a href="catalogue.php">Catalogue</a></li>
            <li><a href="index.php" style="color: var(--primary-green);">← Liste</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="form-card">
            <h2>Ajouter une catégorie</h2>
            <form action="insert_categorie.php" method="POST">
                <span class="label-title">Nom de la catégorie</span>
                <input type="text" name="nom_categorie" placeholder="Ex: Menuiserie, Isolation..." required>

                <button type="submit">Enregistrer la catégorie</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-col">
                <div style="font-family: 'Playfair Display', serif; font-size: 1.4rem; margin-bottom: 15px;">🌿 RéEmploi BTP</div>
                <p style="font-size: 0.9rem; opacity: 0.8;">Participez à l'économie circulaire du bâtiment.</p>
            </div>
            <div class="footer-col">
                <h5>Navigation</h5>
                <ul>
                    <li><a href="catalogue.php">Catalogue</a></li>
                    <li><a href="index.php">Toutes les catégories</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>Contact</h5>
                <p>📧 contact@reemploi-btp.tn</p>
                <p>📍 Tunis, Tunisie</p>
            </div>
        </div>
        <div class="footer-bottom"><p>© 2026 RéEmploi BTP · Tous droits réservés</p></div>
    </footer>
</body>
</html>