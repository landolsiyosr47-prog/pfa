<?php
include("../config.php");

// Récupération sécurisée de l'ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM MATERIAU WHERE id_materiau=$id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if (!$row) {
    die("Matériau non trouvé.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RéEmploi BTP — Modifier Matériau</title>
    
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

        /* ════════════ NAVBAR ════════════ */
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
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        /* ════════════ FORMULAIRE ════════════ */
        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            padding: 50px 20px;
        }

        .form-card {
            background: var(--white);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(26, 60, 52, 0.08);
            width: 100%;
            max-width: 600px;
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

        input, textarea, select {
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

        input:focus, textarea:focus, select:focus {
            outline: none;
            background-color: var(--white);
            border-color: var(--primary-green);
            box-shadow: 0 5px 15px rgba(61, 140, 98, 0.1);
        }

        textarea { height: 100px; resize: vertical; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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
            margin-top: 30px;
            transition: 0.3s;
        }

        button[type="submit"]:hover {
            background-color: var(--dark-green);
            transform: translateY(-2px);
        }

        /* ════════════ FOOTER ════════════ */
        .footer {
            background-color: var(--dark-green);
            color: var(--white);
            padding: 40px 8% 20px;
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
            font-size: 0.8rem;
            opacity: 0.5;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a class="nav-logo" href="index.php">
            <span>🌿</span> RéEmploi <strong>BTP</strong>
        </a>
        <a href="index.php" class="btn-retour">← Retour inventaire</a>
    </nav>

    <main class="main-content">
        <div class="form-card">
            <h2>Modifier le matériau</h2>

            <form action="update_materiau.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $row['id_materiau']; ?>">

                <span class="label-title">Nom du matériau</span>
                <input type="text" name="nom" value="<?php echo htmlspecialchars($row['nom_materiau']); ?>" required>

                <span class="label-title">Description</span>
                <textarea name="description"><?php echo htmlspecialchars($row['description_materiau']); ?></textarea>

                <div class="form-grid">
                    <div>
                        <span class="label-title">État</span>
                        <input type="text" name="etat" value="<?php echo htmlspecialchars($row['etat_materiau']); ?>">
                    </div>
                    <div>
                        <span class="label-title">Quantité</span>
                        <input type="number" name="quantite" value="<?php echo $row['quantite_materiau']; ?>">
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <span class="label-title">Dimensions</span>
                        <input type="text" name="dimensions" value="<?php echo htmlspecialchars($row['dimensions_materiau']); ?>">
                    </div>
                    <div>
                        <span class="label-title">Mode d'échange</span>
                        <select name="mode">
                            <option value="don" <?php if($row['mode_echange_materiau']=="don") echo "selected"; ?>>Don</option>
                            <option value="vente" <?php if($row['mode_echange_materiau']=="vente") echo "selected"; ?>>Vente</option>
                            <option value="troc" <?php if($row['mode_echange_materiau']=="troc") echo "selected"; ?>>Troc</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <span class="label-title">Disponibilité</span>
                        <input type="text" name="disponibilite" value="<?php echo htmlspecialchars($row['disponibilite_materiau']); ?>">
                    </div>
                    <div>
                        <span class="label-title">Prix (DT)</span>
                        <input type="number" step="0.01" name="prix" value="<?php echo $row['prix_materiau']; ?>">
                    </div>
                </div>

                <button type="submit">Enregistrer les modifications</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <div style="font-family: 'Playfair Display', serif; font-size: 1.2rem;">🌿 RéEmploi BTP</div>
            <p style="font-size: 0.9rem;">Panel d'administration — Matériaux</p>
        </div>
        <div class="footer-bottom">
            <p>© 2026 RéEmploi BTP · Tous droits réservés</p>
        </div>
    </footer>

</body>
</html>