# Guide d'installation et de déploiement

Ce document explique comment installer et déployer le site web du tournoi sur votre serveur.

## Prérequis

- PHP 7.* ou supérieur
- MySQL/MariaDB
- Serveur web (Apache, Nginx, etc.)

## Étapes d'installation

1. **Décompressez l'archive** sur votre serveur web

2. **Configurez la base de données**
   - Créez une base de données MySQL
   - Modifiez les informations de connexion dans le fichier `includes/header.php` si nécessaire :
     ```php
     $db_config = [
         'host' => 'ms8063-001.eu.clouddb.ovh.net:35606',
         'dbname' => 'atscafapp',
         'username' => 'bukoapp',
         'password' => 'Sephicarotte13370405'
     ];
     ```

3. **Installez les tables de la base de données**
   - Accédez à `http://votre-domaine.com/install_db.php` pour créer les tables nécessaires
   - Une fois l'installation terminée, vous pouvez supprimer ce fichier pour des raisons de sécurité

4. **Accédez à l'interface d'administration**
   - URL : `http://votre-domaine.com/admin/`
   - Identifiants par défaut :
     - Nom d'utilisateur : `squall009`
     - Mot de passe : `Sephicarotte0405`

5. **Ajoutez du contenu à votre site**
   - Utilisez l'interface d'administration pour ajouter des matchs au calendrier, des résultats, des actualités, etc.

## Structure des fichiers

- `/admin/` - Interface d'administration
- `/css/` - Feuilles de style CSS
- `/images/` - Images du site
- `/includes/` - Fichiers d'inclusion PHP
- `/js/` - Scripts JavaScript
- `/pages/` - Pages du site

## Sécurité

- Changez le mot de passe administrateur après la première connexion
- Supprimez le fichier `install_db.php` après l'installation
- Assurez-vous que les permissions des fichiers sont correctement configurées

## Support

Si vous rencontrez des problèmes lors de l'installation ou de l'utilisation du site, veuillez contacter l'administrateur système.
"# tif-2025" 
