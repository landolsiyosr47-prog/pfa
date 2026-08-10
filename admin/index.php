<?php
require_once '../config.php';
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { header("Location: ../login.php"); exit; }

$nb_users      = $conn->query("SELECT COUNT(*) FROM UTILISATEUR")->fetch_row()[0] ?? 0;
$nb_mats       = $conn->query("SELECT COUNT(*) FROM MATERIAU")->fetch_row()[0] ?? 0;
$nb_res        = $conn->query("SELECT COUNT(*) FROM RESERVATION")->fetch_row()[0] ?? 0;
$nb_attente    = $conn->query("SELECT COUNT(*) FROM RESERVATION WHERE statut_reservation='en attente'")->fetch_row()[0] ?? 0;
$nb_confirm    = $conn->query("SELECT COUNT(*) FROM RESERVATION WHERE statut_reservation='confirmee'")->fetch_row()[0] ?? 0;
$nb_entreprise = $conn->query("SELECT COUNT(*) FROM UTILISATEUR WHERE type_user='entreprise'")->fetch_row()[0] ?? 0;
$nb_dispo      = $conn->query("SELECT COUNT(*) FROM MATERIAU WHERE disponibilite_materiau='disponible' OR disponibilite_materiau IS NULL")->fetch_row()[0] ?? 0;
$nb_cats       = $conn->query("SELECT COUNT(*) FROM CATEGORIE")->fetch_row()[0] ?? 0;
$current_folder = 'admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Admin RéEmploi BTP</title>
</head>
<body>
<?php include '_sidebar.php'; ?>
<div class="adm-topbar">
  <div class="adm-topbar-title">📊 Tableau de bord</div>
  <div class="adm-topbar-right">
    <span class="admin-email">🔑 <?= htmlspecialchars($_SESSION['admin_email'] ?? 'admin@gmail.com') ?></span>
    <a href="../logout.php">Déconnexion</a>
  </div>
</div>
<main class="adm-main">

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-value"><?= $nb_users ?></div><div class="stat-label">Utilisateurs</div><div class="stat-sub"><?= $nb_entreprise ?> entreprise<?= $nb_entreprise>1?'s':'' ?></div></div>
    <div class="stat-card"><div class="stat-icon">📦</div><div class="stat-value"><?= $nb_mats ?></div><div class="stat-label">Matériaux</div><div class="stat-sub"><?= $nb_dispo ?> disponible<?= $nb_dispo>1?'s':'' ?></div></div>
    <div class="stat-card"><div class="stat-icon">📅</div><div class="stat-value"><?= $nb_res ?></div><div class="stat-label">Réservations</div><div class="stat-sub"><?= $nb_attente ?> en attente</div></div>
    <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-value"><?= $nb_confirm ?></div><div class="stat-label">Confirmées</div><div class="stat-sub">réservations</div></div>
    <div class="stat-card"><div class="stat-icon">🏷️</div><div class="stat-value"><?= $nb_cats ?></div><div class="stat-label">Catégories</div></div>
  </div>

  <div class="section-title">Dernières réservations</div>
  <div class="adm-card">
    <div class="adm-card-header"><span class="adm-card-title">Activité récente</span><a href="../reservations/index.php" class="btn btn-secondary btn-sm">Voir tout →</a></div>
    <table>
      <thead><tr><th>Matériau</th><th>Demandeur</th><th>Date</th><th>Statut</th></tr></thead>
      <tbody>
      <?php $rr=$conn->query("SELECT r.*,m.nom_materiau,u.nom_user,u.prenom_user FROM RESERVATION r JOIN MATERIAU m ON r.id_materiau=m.id_materiau JOIN UTILISATEUR u ON r.id_user=u.id_user ORDER BY r.id_reservation DESC LIMIT 8");
      while($row=$rr->fetch_assoc()):$sc=['en attente'=>'pill-yellow','confirmee'=>'pill-green','annulee'=>'pill-red'][$row['statut_reservation']]??'pill-gray';?>
      <tr><td class="td-name"><?=htmlspecialchars($row['nom_materiau'])?></td><td class="td-muted"><?=htmlspecialchars($row['prenom_user'].' '.$row['nom_user'])?></td><td class="td-muted"><?=htmlspecialchars($row['date_reservation'])?></td><td><span class="pill <?=$sc?>"><?=htmlspecialchars($row['statut_reservation'])?></span></td></tr>
      <?php endwhile;?>
      </tbody>
    </table>
  </div>

  <div class="section-title">Nouveaux inscrits</div>
  <div class="adm-card">
    <div class="adm-card-header"><span class="adm-card-title">Utilisateurs récents</span><a href="../utilisateurs/index.php" class="btn btn-secondary btn-sm">Voir tout →</a></div>
    <table>
      <thead><tr><th>Nom</th><th>Email</th><th>Type</th></tr></thead>
      <tbody>
      <?php $ru=$conn->query("SELECT * FROM UTILISATEUR ORDER BY id_user DESC LIMIT 6");
      while($row=$ru->fetch_assoc()):$tc=['entreprise'=>'pill-blue','particulier'=>'pill-green','artisan'=>'pill-yellow'][$row['type_user']]??'pill-gray';?>
      <tr><td class="td-name"><?=htmlspecialchars($row['prenom_user'].' '.$row['nom_user'])?></td><td class="td-muted"><?=htmlspecialchars($row['email_user'])?></td><td><span class="pill <?=$tc?>"><?=htmlspecialchars($row['type_user']??'—')?></span></td></tr>
      <?php endwhile;?>
      </tbody>
    </table>
  </div>

</main>
</body></html>