<?php
include("../config.php");

// Récupération sécurisée de l'ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM SIGNALEMENT WHERE id_signalement=$id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if (!$row) {
    die("Signalement non trouvé.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RéEmploi BTP — Modifier Signalement</title>
    
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
            position: sticky; top: 0; z-index: 1000;
        }

        .nav-logo {
            font-family: 'Playfair Display', serif;
            text-decoration: none;
            color: var(--dark-green);
            font-size: 1.4rem;
            display: flex; align-items: center; gap: 8px;
        }

        .btn-retour {
            background-color: var(--primary-green);
            color: white !important;
            padding: 10px 22px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
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
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(26, 60, 52, 0.08);
            width: 100%;
            max-width: 500px;
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
            margin-top: 15px;
        }

        input, textarea {
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

        textarea { height: 120px; resize: none; }

        input:focus, textarea:focus {
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
            margin-top: 25px;
        }

        button[type="submit"]:hover {
            background-color: var(--dark-green);
            transform: translateY(-2px);
        }

        /* FOOTER */
        .footer {
            background-color: var(--dark-green);
            color: var(--white);
            padding: 40px 8% 30px;
            margin-top: auto;
        }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-bottom {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
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
        <div class="nav-links">
            <a href="index.php" class="btn-retour">← Liste Signalements</a>
        </div>
    </nav>

    <main class="main-content">
        <div class="form-card">
            <h2>Modifier le signalement</h2>

            <form action="update_signalement.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $row['id_signalement']; ?>">

                <span class="label-title">ID Matériau concerné</span>
                <input type="number" name="id_materiau" value="<?php echo $row['id_materiau']; ?>" required>

                <span class="label-title">ID Utilisateur plaignant</span>
                <input type="number" name="id_user" value="<?php echo $row['id_user']; ?>" required>

                <span class="label-title">Motif du signalement</span>
                <textarea name="motif_signalement" required><?php echo htmlspecialchars($row['motif_signalement']); ?></textarea>

                <span class="label-title">Date du signalement</span>
                <input type="date" name="date_signalement" value="<?php echo $row['date_signalement']; ?>" required>

                <button type="submit">Enregistrer les modifications</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <div style="font-family: 'Playfair Display', serif; font-size: 1.4rem;">🌿 RéEmploi BTP</div>
            <p style="font-size: 0.9rem; opacity: 0.8;">Panel de modération et sécurité</p>
        </div>
        <div class="footer-bottom">
            <p>© 2026 RéEmploi BTP · Tous droits réservés</p>
        </div>
    </footer>

</body>
</html>