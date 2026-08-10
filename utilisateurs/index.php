<?php
include '../config.php'; // auth + sidebar

// Liste des utilisateurs
$users = [];
$r = $conn->query("SELECT * FROM UTILISATEUR ORDER BY id_user DESC");
if($r) {
    while($row = $r->fetch_assoc()) $users[] = $row;
}

// Récupération des messages via l'URL
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs — Admin RéEmploi BTP</title>
    
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
            --danger: #c62828;
            --blue-edit: #1976d2;
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
            font-weight: 700;
        }

        .btn-add:hover { background-color: var(--dark-green) !important; transform: translateY(-2px); }

        /* MAIN */
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

        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th { text-align: left; padding: 18px; color: var(--primary-green); border-bottom: 2px solid var(--soft-green); font-weight: 700; text-transform: uppercase; font-size: 0.8rem; }
        td { padding: 18px; border-bottom: 1px solid var(--soft-green); color: var(--dark-green); }

        /* ACTIONS */
        .btn-action { text-decoration: none; padding: 8px 15px; border-radius: 12px; font-weight: 700; font-size: 0.8rem; transition: 0.3s; display: inline-block; }
        .edit { background-color: #e3f2fd; color: var(--blue-edit); }
        .delete { background-color: #ffebee; color: var(--danger); margin-left: 5px; }
        .edit:hover { background-color: #bbdefb; }
        .delete:hover { background-color: #ffcdd2; }

        .pill { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
        .pill-blue { background: #e3f2fd; color: #1976d2; }
        .pill-green { background: #e8f5e9; color: #2e7d32; }

        /* FOOTER */
        .footer { background-color: var(--dark-green); color: var(--white); padding: 60px 8% 30px; margin-top: auto; }
        .footer-inner { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .footer-bottom { text-align: center; margin-top: 50px; padding-top: 25px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 0.8rem; color: rgba(255,255,255,0.5); }
    </style>
</head>
<body>

    <nav class="navbar">
        <a class="nav-logo" href="index.php">
            <span>🌿</span> RéEmploi <strong>BTP</strong>
        </a>
        <ul class="nav-links">
            <li><a href="../admin/index.php">⬅ Retour Admin</a></li>
            <li><a href="../catalogue.php">Catalogue</a></li>
            <li><a href="ajouter_user.php" class="btn-add">+ Ajouter un utilisateur</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <?php if($msg):?><div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center;">✅ <?=htmlspecialchars($msg)?></div><?php endif;?>

        <div class="table-card">
            <h2>👥 Gestion des utilisateurs</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom & Prénom</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Ville</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($users) > 0): ?>
                        <?php foreach($users as $u): 
                            $tc = ($u['type_user'] == 'entreprise') ? 'pill-blue' : 'pill-green';
                        ?>
                        <tr>
                            <td style="color: #888;">#<?= $u['id_user'] ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($u['prenom_user'].' '.$u['nom_user']) ?></td>
                            <td style="color: #666;"><?= htmlspecialchars($u['email_user']) ?></td>
                            <td><span class="pill <?= $tc ?>"><?= htmlspecialchars($u['type_user']) ?></span></td>
                            <td><?= htmlspecialchars($u['ville_user'] ?? '—') ?></td>
                            <td>
                                <a href="modifier_user.php?id=<?= $u['id_user'] ?>" class="btn-action edit">Modifier</a>
                                <a href="delete_user.php?id=<?= $u['id_user'] ?>" class="btn-action delete" 
                                   onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; padding: 30px;">Aucun utilisateur trouvé.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <div style="font-family: 'Playfair Display', serif; font-size: 1.4rem;">🌿 RéEmploi BTP</div>
            <p>📧 contact@reemploi-btp.tn</p>
        </div>
        <div class="footer-bottom">
            <p>© 2026 RéEmploi BTP · Panel Administration</p>
        </div>
    </footer>

</body>
</html>