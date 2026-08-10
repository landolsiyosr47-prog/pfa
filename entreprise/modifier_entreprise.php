<?php
include("../config.php");

// Récupération sécurisée de l'ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM ENTREPRISE WHERE id_entrep=$id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if (!$row) {
    die("Entreprise non trouvée.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RéEmploi BTP — Modifier Entreprise</title>
    
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

        /* ═══════════════════════════════════════
           NAVBAR (Style exact utilisateur)
        ═══════════════════════════════════════ */
        .navbar {
            background-color: var(--white);
            padding: 1rem 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-logo {
            font-family: 'Playfair Display', serif;
            text-decoration: none;
            color: var(--dark-green);
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links {
            display: flex;
            gap: 25px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--dark-green);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .btn-retour {
            background-color: var(--primary-green);
            color: white !important;
            padding: 10px 22px;
            border-radius: 50px;
            font-weight: 600;
        }

        /* ═══════════════════════════════════════
           FORMULAIRE (Style Cute)
        ═══════════════════════════════════════ */
        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 60px 20px;
        }

        .form-card {
            background: var(--white);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(26, 60, 52, 0.08);
            width: 100%;
            max-width: 480px;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            color: var(--dark-green);
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
        }

        .label-title {
            display: block;
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        input {
            width: 100%;
            padding: 14px 18px;
            margin-bottom: 20px;
            border: 2px solid transparent;
            border-radius: 15px;
            background-color: var(--soft-green);
            font-family: 'Quicksand', sans-serif;
            font-size: 1rem;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus {
            outline: none;
            background-color: var(--white);
            border-color: var(--primary-green);
            box-shadow: 0 5px 15px rgba(61, 140, 98, 0.1);
        }

        button[type="submit"] {
            width: 100%;
            background-color: var(--primary-green);
            color: white;
            padding: 16px;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: 0.3s;
        }

        button[type="submit"]:hover {
            background-color: var(--dark-green);
            transform: translateY(-2px);
        }

        /* ═══════════════════════════════════════
           FOOTER (Style exact utilisateur)
        ═══════════════════════════════════════ */
        .footer {
            background-color: var(--dark-green);
            color: var(--white);
            padding: 60px 8% 30px;
            margin-top: auto;
        }

        .footer-inner {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-col h5 {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .footer-col ul {
            list-style: none;
            padding: 0;
        }

        .footer-col ul li { margin-bottom: 12px; }

        .footer-col a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .footer-col a:hover { color: var(--white); }

        .footer-bottom {
            text-align: center;
            margin-top: 50px;
            padding-top: 25px;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.8rem;
            color: rgba(255,255,255,0.5);
        }

        /* Cacher les <br> pour garder le spacing CSS propre */
        br { display: none; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a class="nav-logo" href="index.php">
            <span>🌿</span> RéEmploi <strong>BTP</strong>
        </a>
        <ul class="nav-links">
            <li><a href="catalogue.php">Catalogue</a></li>
            <li><a href="index.php" class="btn-retour">← Liste entreprises</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="form-card">
            <h2>Modifier l'entreprise</h2>

            <form action="update_entreprise.php" method="POST">
                <!-- ID caché pour la modification -->
                <input type="hidden" name="id" value="<?php echo $row['id_entrep']; ?>">

                <span class="label-title">ID Utilisateur Responsable</span>
                <input type="number" name="id_user" value="<?php echo $row['id_user']; ?>" required>

                <span class="label-title">Nom de l'entreprise</span>
                <input type="text" name="nom" value="<?php echo $row['nom_entrep']; ?>" required>

                <span class="label-title">Secteur d'activité</span>
                <input type="text" name="secteur" value="<?php echo $row['secteur_activite_entrep']; ?>">

                <span class="label-title">Matricule Fiscal</span>
                <input type="text" name="matricule" value="<?php echo $row['matricule_fiscale_entrep']; ?>" required>

                <button type="submit">Mettre à jour</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-col">
                <div style="font-family: 'Playfair Display', serif; font-size: 1.4rem; margin-bottom: 15px;">🌿 RéEmploi BTP</div>
                <p style="font-size: 0.9rem; opacity: 0.8;">Plateforme collaborative pour le réemploi des matériaux de construction.</p>
            </div>
            <div class="footer-col">
                <h5>Navigation</h5>
                <ul>
                    <li><a href="catalogue.php">Catalogue</a></li>
                    <li><a href="#">Comment ça marche</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>Contact</h5>
                <p>📧 contact@reemploi-btp.tn</p>
                <p>📍 Tunis, Tunisie</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 RéEmploi BTP · Tous droits réservés · <a href="#" style="color:rgba(255,255,255,0.5)">Mentions légales</a></p>
        </div>
    </footer>

</body>
</html>
</html>