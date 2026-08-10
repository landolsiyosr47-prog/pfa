<?php include("../config.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RéEmploi BTP — Envoyer un Message</title>
    
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

        .nav-links { display: flex; gap: 25px; list-style: none; margin: 0; padding: 0; align-items: center; }
        .nav-links a { text-decoration: none; color: var(--dark-green); font-weight: 600; font-size: 0.95rem; }

        .btn-list {
            background-color: var(--primary-green);
            color: white !important;
            padding: 10px 22px;
            border-radius: 50px;
            font-weight: 600;
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

        select, textarea {
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
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        select:focus, textarea:focus {
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

        /* ════════════ FOOTER ════════════ */
        .footer {
            background-color: var(--dark-green);
            color: var(--white);
            padding: 60px 8% 30px;
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
            margin-top: 50px;
            padding-top: 25px;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.8rem;
            color: rgba(255,255,255,0.5);
        }

        br { display: none; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a class="nav-logo" href="index.php">
            <span>🌿</span> RéEmploi <strong>BTP</strong>
        </a>
        <ul class="nav-links">
            <li><a href="../admin/index.php">⬅ Retour Admin</a></li>
            <li><a href="catalogue.php">Catalogue</a></li>
            <li><a href="index.php" class="btn-list">Mes Messages</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="form-card">
            <h2>Nouveau Message</h2>

            <form action="insert_message.php" method="POST">
                
                <span class="label-title">De la part de :</span>
                <select name="uti_id_user" required>
                    <option value="" disabled selected>Sélectionnez l'émetteur</option>
                    <?php
                    $res = $conn->query("SELECT id_user, nom_user FROM UTILISATEUR");
                    while($row = $res->fetch_assoc()){
                        echo "<option value='".$row['id_user']."'>".$row['nom_user']."</option>";
                    }
                    ?>
                </select>

                <span class="label-title">Destiné à :</span>
                <select name="id_user" required>
                    <option value="" disabled selected>Sélectionnez le destinataire</option>
                    <?php
                    $res2 = $conn->query("SELECT id_user, nom_user FROM UTILISATEUR");
                    while($row = $res2->fetch_assoc()){
                        echo "<option value='".$row['id_user']."'>".$row['nom_user']."</option>";
                    }
                    ?>
                </select>

                <span class="label-title">Votre message</span>
                <textarea name="contenu_message" placeholder="Écrivez votre message ici..." required></textarea>

                <button type="submit">Envoyer le message ✉️</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <div style="font-family: 'Playfair Display', serif; font-size: 1.4rem;">🌿 RéEmploi BTP</div>
            <div class="footer-contact">
                <p>📧 contact@reemploi-btp.tn</p>
                <p>📍 Tunis, Tunisie</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 RéEmploi BTP · Panel Administration · Tous droits réservés</p>
        </div>
    </footer>

</body>
</html>