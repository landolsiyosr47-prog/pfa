<?php 
include("../config.php"); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RéEmploi BTP — Gestion des Matériaux</title>
    
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
            font-weight: 600; 
        }
        .btn-add:hover { background-color: var(--dark-green) !important; transform: translateY(-2px); }

        /* ════════════ MAIN CONTENT ════════════ */
        .main-content { flex: 1; padding: 60px 8%; }
        
        .table-card {
            background: var(--white);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(26, 60, 52, 0.08);
            overflow-x: auto;
        }

        h2 { font-family: 'Playfair Display', serif; color: var(--dark-green); margin-bottom: 30px; font-size: 2rem; text-align: center; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        
        th { 
            text-align: left; 
            padding: 15px; 
            color: var(--primary-green); 
            border-bottom: 2px solid var(--soft-green); 
            text-transform: uppercase; 
            font-size: 0.85rem; 
            letter-spacing: 0.5px; 
        }
        
        td { padding: 15px; border-bottom: 1px solid var(--soft-green); color: var(--dark-green); font-size: 0.95rem; vertical-align: middle; }

        .img-preview {
            width: 65px;
            height: 65px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        /* ════════════ ACTIONS ════════════ */
        .actions a { 
            text-decoration: none; 
            font-weight: 700; 
            font-size: 0.85rem; 
            padding: 6px 12px;
            border-radius: 8px;
            transition: 0.3s;
            display: inline-block;
        }
        .btn-edit { background-color: #e3f2fd; color: #1976d2; margin-right: 5px; }
        .btn-delete { background-color: #ffebee; color: #c62828; }
        .btn-edit:hover { background-color: #bbdefb; }
        .btn-delete:hover { background-color: #ffcdd2; }

        .price-tag { font-weight: 700; color: var(--primary-green); }

        /* ════════════ FOOTER ════════════ */
        .footer { background-color: var(--dark-green); color: var(--white); padding: 60px 8% 30px; margin-top: auto; }
        .footer-inner { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .footer-bottom { text-align: center; margin-top: 50px; padding-top: 25px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 0.8rem; color: rgba(255,255,255,0.5); }
    </style>
</head>
<body>

    <nav class="navbar">
        <a class="nav-logo" href="index.php"><span>🌿</span> RéEmploi <strong>BTP</strong></a>
        <ul class="nav-links">
            <li><a href="../admin/index.php">⬅ Retour</a></li>
            <li><a href="catalogue.php">Catalogue</a></li>
            <li><a href="ajouter_materiau.php" class="btn-add">+ Ajouter un matériau</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="table-card">
            <h2>📦 Inventaire des matériaux</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Aperçu</th>
                        <th>Nom</th>
                        <th>État</th>
                        <th>Quantité</th>
                        <th>Prix</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM MATERIAU";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            
                            // 1. On récupère le nom du matériau ou de l'image stockée
                            $nomImage = !empty($row['image_materiau']) ? $row['image_materiau'] : $row['nom_materiau'];
                            
                            // 2. Vérification de l'extension (si votre BDD ne contient que "bois", on ajoute ".jpg")
                            if (!strpos($nomImage, '.')) {
                                $nomImage .= ".jpg";
                            }

                            // 3. Chemin relatif : on remonte d'un dossier (materiau -> REEMPLOI-BTP) puis vers img
                            $imagePath = "../img/" . $nomImage;

                            echo "<tr>
                                <td style='color: #888;'>#".$row['id_materiau']."</td>
                                <td>
                                    <img src='".$imagePath."' alt='image' class='img-preview' onerror=\"this.src='../img/sable.jpg';\">
                                </td>
                                <td style='font-weight: 600;'>".htmlspecialchars($row['nom_materiau'])."</td>
                                <td>".htmlspecialchars($row['etat_materiau'])."</td>
                                <td>".$row['quantite_materiau']."</td>
                                <td><span class='price-tag'>".$row['prix_materiau']." DT</span></td>
                                <td class='actions'>
                                    <a href='modifier_materiau.php?id=".$row['id_materiau']."' class='btn-edit'>Modifier</a>
                                    <a href='supprimer_materiau.php?id=".$row['id_materiau']."' class='btn-delete' onclick='return confirm(\"Voulez-vous vraiment supprimer ce matériau ?\")'>Supprimer</a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center; padding: 40px;'>Aucun matériau trouvé.</td></tr>";
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
                <p>📍 Gestion des Ressources</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 RéEmploi BTP · Panel Administration · Tous droits réservés</p>
        </div>
    </footer>

</body>
</html>