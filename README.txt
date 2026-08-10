README – Application de lutte contre le gaspillage dans le secteur de construction

Référence : PFA1-2026-07

*Description du projet:

Cette application web est dédiée à la lutte contre le gaspillage des matériaux de construction. Elle vise à faciliter la mise en relation entre les différents acteurs du secteur (entreprises du BTP, artisans et particuliers) afin de promouvoir le réemploi et la réutilisation des matériaux disponibles.
Le projet comprend :
-Une interface web développée avec HTML, CSS et JavaScript
-Des scripts backend développés en PHP
-Une base de données Oracle Database 11g

*Structure du projet:

*Prérequis:

Avant l’exécution du projet, installer les logiciels suivants :
-Oracle Database 11g
-Oracle SQL Developer
-XAMPP ou WampServer
-Navigateur web (Google Chrome recommandé)

*Installation et exécution:

1. Importation de la base de données:
Étape 1 : Ouvrir Oracle SQL Developer: se connecter à votre base de données Oracle.
Étape 2 : Exécuter le script de création: exécuter le fichier : scriptpfa.sql (ce script crée toutes les tables de la base de données).
Étape 3 : Alimenter la base de données: exécuter ensuite le fichier : alim.sql (afin d’ajouter les données initiales).

*Configuration du projet web:

Étape 1 : Copier le dossier 'pfa' du projet 'PFA1-2026-05' dans le dossier :
Pour XAMPP : htdocs
Pour WampServer : www
Étape 2 : Configurer la connexion Oracle
Modifier les informations de connexion dans le fichier PHP de connexion :
$conn = oci_connect("username","password","localhost/XE");
Remplacer :
-username par votre nom d’utilisateur Oracle
-password par votre mot de passe Oracle
-XE par le SID de votre base si nécessaire

*Lancement de l’application:

Avec XAMPP/WampServer :
1.Démarrer Apache
2.Ouvrir le navigateur
3.Accéder au lien : http://localhost/PFA1-2026-05/PFA/

*Fonctionnalités principales:

-Inscription et authentification des utilisateurs par profil (Entreprise, Artisan, Particulier, Admin).

-Publication et gestion des annonces de matériaux de surplus.

-Catalogue avec recherche et filtrage des matériaux (catégorie, état, localisation).

-Réservation en ligne des matériaux disponibles.

-Messagerie interne pour organiser la récupération des matériaux.

-Suivi de l'impact environnemental (calcul des déchets évités et du CO2 économisé).

-Interface d'administration pour la modération et la gestion globale.

*Technologies utilisées:

-HTML
-CSS
-JavaScript
-PHP
-Oracle SQL
-Oracle Database 11g

*Auteurs:

Yosr landolsi 
Sana fezzeni

ENSIT – Génie Informatique
Année universitaire 2025-2026