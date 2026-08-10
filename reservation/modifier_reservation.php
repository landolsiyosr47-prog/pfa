<?php
include("../config.php");

// Récupération sécurisée de l'ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM reservation WHERE id_reservation=$id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if (!$row) {
    die("Réservation non trouvée.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RéEmploi BTP — Modifier Réservation</title>
    
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
            font-weight: 600;
            text-decoration: none;
        }

        /* ════════════ FORM CARD ════════════ */
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

        input, select {
            width: 100%;
            padding: 14px 18px;
            margin-bottom: 5px;
            border: 2px solid transparent;
            border-radius: 15px;
            background-color: var(--soft-green);
            font-family: 'Quicksand', sans-serif;
            font-size: 1rem;
            box-sizing: border-box;
            transition: 0.3s;
            appearance: none; /* Pour un style select plus propre */
        }

        input:focus, select:focus {
            outline: none;
            background-color: var(--white);
            border-color: var(--primary-green);
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
            margin-top: 30px;
        }

        button[type="submit"]:hover {
            background-color: var(--dark-green);
            transform: translateY(-2px);
        }

        /* ════════════ FOOTER ════════════ */
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
        <a href="index.php" class="btn-retour">← Retour aux réservations</a>
    </nav>

    <main class="main-content">
        <div class="form-card">
            <h2>Modifier la réservation</h2>

            <form action="update_reservation.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $row['id_reservation']; ?>">

                <span class="label-title">📦 Matériau réservé</span>
                <select name="id_materiau" required>
                    <?php
                    $sql2 = "SELECT id_materiau, nom_materiau FROM materiau";
                    $result2 = $conn->query($sql2);
                    while($r = $result2->fetch_assoc()){
                        $selected = ($r['id_materiau'] == $row['id_materiau']) ? "selected" : "";
                        echo "<option value='".$r['id_materiau']."' $selected>".$r['nom_materiau']."</option>";
                    }
                    ?>
                </select>

                <span class="label-title">👤 Utilisateur</span>
                <select name="id_user" required>
                    <?php
                    $sql2 = "SELECT id_user, nom_user, prenom_user FROM utilisateur";
                    $result2 = $conn->query($sql2);
                    while($r = $result2->fetch_assoc()){
                        $selected = ($r['id_user'] == $row['id_user']) ? "selected" : "";
                        echo "<option value='".$r['id_user']."' $selected>".$r['nom_user']." ".$r['prenom_user']."</option>";
                    }
                    ?>
                </select>

                <span class="label-title">📅 Date de réservation</span>
                <input type="date" name="date_reservation" value="<?php echo $row['date_reservation']; ?>" required>

                <span class="label-title">⚙️ Statut actuel</span>
                <select name="statut_reservation" required>
                    <option value="en attente" <?php if($row['statut_reservation']=='en attente') echo 'selected'; ?>>En attente</option>
                    <option value="confirmee" <?php if($row['statut_reservation']=='confirmee') echo 'selected'; ?>>Confirmée</option>
                    <option value="annulee" <?php if($row['statut_reservation']=='annulee') echo 'selected'; ?>>Annulée</option>
                </select>

                <button type="submit">Enregistrer les modifications</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <div style="font-family: 'Playfair Display', serif; font-size: 1.2rem;">🌿 RéEmploi BTP</div>
            <p style="font-size: 0.9rem;">Panel de Gestion des Réservations</p>
        </div>
        <div class="footer-bottom">
            <p>© 2026 RéEmploi BTP · Tous droits réservés</p>
        </div>
    </footer>

</body>
</html>