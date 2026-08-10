<?php include("../config.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RéEmploi BTP — Gestion des Impacts</title>
    
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
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--dark-green);
            font-weight: 600;
            font-size: 0.95rem;
            transition: 0.3s;
        }

        .nav-links a:hover { color: var(--primary-green); }

        .btn-add {
            background-color: var(--primary-green);
            color: white !important;
            padding: 10px 22px;
            border-radius: 50px;
            transition: 0.3s;
            font-weight: 700;
        }

        .btn-add:hover { background-color: var(--dark-green); transform: translateY(-2px); }

        /* ════════════ TABLE CARD ════════════ */
        .main-content { flex: 1; padding: 60px 8%; }
        
        .table-card {
            background: var(--white);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(26, 60, 52, 0.08);
            overflow-x: auto;
        }

        h2 { 
            font-family: 'Playfair Display', serif; 
            color: var(--dark-green); 
            margin-bottom: 30px; 
            font-size: 2.2rem; 
            text-align: center;
        }

        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        
        th { 
            text-align: left; 
            padding: 18px; 
            color: var(--primary-green); 
            border-bottom: 2px solid var(--soft-green); 
            font-weight: 700; 
            text-transform: uppercase; 
            font-size: 0.8rem; 
            letter-spacing: 0.5px;
        }
        
        td { 
            padding: 18px; 
            border-bottom: 1px solid var(--soft-green); 
            color: var(--dark-green); 
            font-size: 0.95rem; 
        }

        .impact-val { font-weight: 700; color: var(--primary-green); }
        .date-val { color: #666; font-size: 0.85rem; }

        .btn-action { 
            text-decoration: none; 
            padding: 8px 15px; 
            border-radius: 12px; 
            font-weight: 700; 
            font-size: 0.8rem; 
            transition: 0.3s; 
            display: inline-block; 
        }
        
        .edit { background-color: #e3f2fd; color: #1976d2; }
        .delete { background-color: #ffebee; color: #c62828; margin-left: 5px; }
        .edit:hover { background-color: #bbdefb; }
        .delete:hover { background-color: #ffcdd2; }

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
            <li><a href="impact.php">Notre Impact</a></li>
            <li><a href="ajouter_impact.php" class="btn-add">+ Ajouter un impact</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="table-card">
            <h2>🌍 Suivi des impacts environnementaux</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Déchets évités</th>
                        <th>CO₂ économisé</th>
                        <th>Date d'enregistrement</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM IMPACT_ENVIROMMENTAL ORDER BY id_impact DESC";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td style='color: #888;'>#".$row['id_impact']."</td>";
                            echo "<td><span class='impact-val'>♻️ ".$row['dechets_evites_kg_impact']." kg</span></td>";
                            echo "<td><span class='impact-val'>☁️ ".$row['co2_economise_kg_impact']." kg</span></td>";
                            echo "<td><span class='date-val'>📅 ".date('d/m/Y H:i', strtotime($row['date_impact']))."</span></td>";
                            echo "<td>
                                    <a href='modifier_impact.php?id=".$row['id_impact']."' class='btn-action edit'>Modifier</a>
                                    <a href='supprimer_impact.php?id=".$row['id_impact']."' class='btn-action delete' onclick='return confirm(\"Voulez-vous supprimer cet enregistrement ?\")'>Supprimer</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; padding: 40px;'>Aucun impact enregistré pour le moment.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
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