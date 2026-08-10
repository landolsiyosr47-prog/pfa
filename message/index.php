<?php include("../config.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RéEmploi BTP — Gestion des Messages</title>
    
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
        .nav-links a { text-decoration: none; color: var(--dark-green); font-weight: 600; font-size: 0.95rem; transition: 0.3s; }
        .nav-links a:hover { color: var(--primary-green); }
        
        .btn-add { 
            background-color: var(--primary-green); 
            color: white !important; 
            padding: 10px 22px; 
            border-radius: 50px; 
            font-weight: 700; 
        }
        .btn-add:hover { background-color: var(--dark-green) !important; transform: translateY(-2px); }

        /* ════════════ TABLE CARD ════════════ */
        .main-content { flex: 1; padding: 60px 8%; }
        
        .table-card {
            background: var(--white);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(26, 60, 52, 0.08);
            overflow-x: auto;
        }

        h2 { font-family: 'Playfair Display', serif; color: var(--dark-green); margin-bottom: 30px; font-size: 2.2rem; text-align: center; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 15px; color: var(--primary-green); border-bottom: 2px solid var(--soft-green); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
        td { padding: 15px; border-bottom: 1px solid var(--soft-green); color: var(--dark-green); font-size: 0.95rem; vertical-align: top; }
        
        .user-tag { font-weight: 700; color: var(--dark-green); display: flex; align-items: center; gap: 5px; }
        .msg-content { color: #555; font-style: italic; max-width: 300px; line-height: 1.4; }
        .date-tag { font-size: 0.8rem; color: #888; }

        .actions a { 
            text-decoration: none; 
            font-weight: 700; 
            font-size: 0.8rem; 
            padding: 8px 12px;
            border-radius: 10px;
            transition: 0.3s;
            display: inline-block;
        }
        .btn-edit { background-color: #e3f2fd; color: #1976d2; margin-right: 5px; }
        .btn-delete { background-color: #ffebee; color: #c62828; }
        .btn-edit:hover { background-color: #bbdefb; }
        .btn-delete:hover { background-color: #ffcdd2; }

        /* ════════════ FOOTER ════════════ */
        .footer { background-color: var(--dark-green); color: var(--white); padding: 40px 8% 30px; margin-top: auto; }
        .footer-inner { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .footer-bottom { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 0.8rem; color: rgba(255,255,255,0.5); }
    </style>
</head>
<body>

    <nav class="navbar">
        <a class="nav-logo" href="index.php"><span>🌿</span> RéEmploi <strong>BTP</strong></a>
        <ul class="nav-links">
            <li><a href="../admin/index.php">⬅ Retour Admin</a></li>
            <li><a href="catalogue.php">Catalogue</a></li>
            <li><a href="ajouter_message.php" class="btn-add">+ Nouveau message</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="table-card">
            <h2>📬 Gestion de la messagerie</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>De (Émetteur)</th>
                        <th>À (Destinataire)</th>
                        <th>Message</th>
                        <th>Envoyé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT m.id_message, u1.nom_user AS emetteur, u2.nom_user AS destinataire, 
                            m.contenu_message, m.date_envoi_message
                            FROM MESSAGE m
                            JOIN UTILISATEUR u1 ON m.uti_id_user = u1.id_user
                            JOIN UTILISATEUR u2 ON m.id_user = u2.id_user
                            ORDER BY m.date_envoi_message DESC";
                    
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td style='color: #bbb;'>#".$row['id_message']."</td>";
                            echo "<td><div class='user-tag'>👤 ".$row['emetteur']."</div></td>";
                            echo "<td><div class='user-tag'>📩 ".$row['destinataire']."</div></td>";
                            echo "<td><div class='msg-content'>\"".htmlspecialchars($row['contenu_message'])."\"</div></td>";
                            echo "<td><span class='date-tag'>".date('d/m/Y H:i', strtotime($row['date_envoi_message']))."</span></td>";
                            echo "<td class='actions'>
                                    <a href='modifier_message.php?id=".$row['id_message']."' class='btn-edit'>Modifier</a>
                                    <a href='supprimer_message.php?id=".$row['id_message']."' class='btn-delete' onclick='return confirm(\"Supprimer ce message définitivement ?\")'>Supprimer</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; padding: 40px;'>Aucun message trouvé.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <div style="font-family: 'Playfair Display', serif; font-size: 1.4rem;">🌿 RéEmploi BTP</div>
            <p style="font-size: 0.9rem; opacity: 0.8;">Panel de communication interne</p>
        </div>
        <div class="footer-bottom">
            <p>© 2026 RéEmploi BTP · Administration Système · <a href="#" style="color:rgba(255,255,255,0.5); text-decoration: none;">Support</a></p>
        </div>
    </footer>

</body>
</html>