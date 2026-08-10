<?php 
include("../config.php"); 
date_default_timezone_set('Africa/Tunis');

// Vérification de l'existence de l'ID
if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM IMPACT_ENVIROMMENTAL WHERE id_impact = $id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    // Si l'impact n'existe pas, redirection
    if(!$row) {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RéEmploi BTP — Modifier l'Impact</title>
    
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

        /* NAVBAR */
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
            transition: 0.3s;
        }

        .btn-retour {
            color: var(--primary-green) !important;
            font-weight: 700;
        }

        /* FORM CARD */
        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 60px 20px;
        }

        .form-card {
            background: var(--white);
            padding: 45px;
            border-radius: 30px;
            box-shadow: 0 15px 35px rgba(26, 60, 52, 0.08);
            width: 100%;
            max-width: 480px;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            color: var(--dark-green);
            text-align: center;
            margin-bottom: 5px;
            font-size: 2rem;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 35px;
            font-size: 0.9rem;
        }

        .form-group { margin-bottom: 22px; }

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
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            background-color: var(--dark-green);
            transform: translateY(-2px);
        }

        /* FOOTER EXACT */
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
            transition: 0.3s;
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
    </style>
</head>
<body>

    <nav class="navbar">
        <a class="nav-logo" href="index.php">
            <span>🌿</span> RéEmploi <strong>BTP</strong>
        </a>
        <ul class="nav-links">
            <li><a href="catalogue.php">Catalogue</a></li>
            <li><a href="index.php" class="btn-retour">← Retour à la liste</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="form-card">
            <h2>🌍 Modifier l'Impact</h2>
            <p class="subtitle">Mise à jour des performances écologiques #<?php echo $row['id_impact']; ?></p>
            
            <form action="update_impact.php" method="POST">
                <!-- ID caché pour le traitement SQL -->
                <input type="hidden" name="id" value="<?php echo $row['id_impact']; ?>">

                <div class="form-group">
                    <span class="label-title">Déchets évités (kg)</span>
                    <input type="number" step="0.01" name="dechets" value="<?php echo $row['dechets_evites_kg_impact']; ?>" required>
                </div>

                <div class="form-group">
                    <span class="label-title">CO₂ économisé (kg)</span>
                    <input type="number" step="0.01" name="co2" value="<?php echo $row['co2_economise_kg_impact']; ?>" required>
                </div>

                <div class="form-group">
                    <span class="label-title">Date de l'enregistrement</span>
                    <input type="datetime-local" name="date" value="<?php echo date('Y-m-d\TH:i', strtotime($row['date_impact'])); ?>" required>
                </div>

                <button type="submit">Enregistrer les modifications</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-col">
                <div style="font-family: 'Playfair Display', serif; font-size: 1.4rem; margin-bottom: 15px;">🌿 RéEmploi BTP</div>
                <p style="font-size: 0.9rem; opacity: 0.8;">Administration des indicateurs de durabilité pour un chantier éco-responsable.</p>
            </div>
            <div class="footer-col">
                <h5>Navigation</h5>
                <ul>
                    <li><a href="catalogue.php">Catalogue</a></li>
                    <li><a href="index.php">Notre impact</a></li>
                    <li><a href="#">Support Technique</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>Contact Admin</h5>
                <p>📧 contact@reemploi-btp.tn</p>
                <p>📍 Tunis, Tunisie</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 RéEmploi BTP · Panel de contrôle environnemental · <a href="#" style="color:rgba(255,255,255,0.5)">Confidentialité</a></p>
        </div>
    </footer>

</body>
</html>