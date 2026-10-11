/*M!999999\- enable the sandbox mode */ 
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;
DROP TABLE IF EXISTS `accompagnement_actifs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `accompagnement_actifs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sae_id` bigint(20) unsigned NOT NULL,
  `entrepreneur_id` bigint(20) unsigned NOT NULL,
  `programme_accompagnement_id` bigint(20) unsigned NOT NULL,
  `date_debut` timestamp NOT NULL,
  `date_fin_prevue` timestamp NULL DEFAULT NULL,
  `date_fin_reelle` timestamp NULL DEFAULT NULL,
  `statut` varchar(255) NOT NULL DEFAULT 'en_cours',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accompagnement_actifs_sae_id_foreign` (`sae_id`),
  KEY `accompagnement_actifs_programme_accompagnement_id_foreign` (`programme_accompagnement_id`),
  KEY `accompagnement_actifs_entrepreneur_id_statut_index` (`entrepreneur_id`,`statut`),
  CONSTRAINT `accompagnement_actifs_entrepreneur_id_foreign` FOREIGN KEY (`entrepreneur_id`) REFERENCES `entrepreneurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accompagnement_actifs_programme_accompagnement_id_foreign` FOREIGN KEY (`programme_accompagnement_id`) REFERENCES `programme_accompagnements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accompagnement_actifs_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sae_id` bigint(20) unsigned NOT NULL,
  `evenement_id` bigint(20) unsigned NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activites_evenement_id_foreign` (`evenement_id`),
  KEY `activites_sae_id_foreign` (`sae_id`),
  CONSTRAINT `activites_evenement_id_foreign` FOREIGN KEY (`evenement_id`) REFERENCES `evenements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activites_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `indicatif` varchar(255) NOT NULL DEFAULT '+228',
  `telephone` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `statut_approbation` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`),
  KEY `admins_telephone_index` (`telephone`),
  KEY `admins_role_index` (`role`),
  KEY `admins_is_active_index` (`is_active`),
  KEY `admins_statut_approbation_index` (`statut_approbation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `business_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `business_plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `projet_id` bigint(20) unsigned NOT NULL,
  `num_enregistrement` varchar(255) DEFAULT NULL,
  `date_debut_activite` date DEFAULT NULL,
  `annee_demarrage` year(4) DEFAULT NULL,
  `ca_mensuel` decimal(15,0) DEFAULT NULL,
  `ca_annuel` decimal(15,0) DEFAULT NULL,
  `ca_annee_passee` decimal(15,0) DEFAULT NULL,
  `ca_annuel_passe` decimal(15,0) DEFAULT NULL,
  `depenses_mensuelles` decimal(15,0) DEFAULT NULL,
  `benefices_net_moyen` decimal(15,0) DEFAULT NULL,
  `produits_services` longtext DEFAULT NULL,
  `activites_cles` longtext DEFAULT NULL,
  `besoin_ressources` longtext DEFAULT NULL,
  `partenaires_cles` longtext DEFAULT NULL,
  `structure_couts` longtext DEFAULT NULL,
  `sources_revenus` longtext DEFAULT NULL,
  `strategie_marketing` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_plans_projet_id_foreign` (`projet_id`),
  CONSTRAINT `business_plans_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cours` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sae_id` bigint(20) unsigned NOT NULL,
  `nom_du_cours` varchar(255) NOT NULL,
  `lien_video` varchar(255) DEFAULT NULL,
  `resume_du_cours` longtext NOT NULL,
  `est_public` tinyint(1) NOT NULL,
  `etape_accompagnement` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cours_sae_id_foreign` (`sae_id`),
  CONSTRAINT `cours_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `demande_accompagnements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `demande_accompagnements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sae_id` bigint(20) unsigned NOT NULL,
  `entrepreneur_id` bigint(20) unsigned NOT NULL,
  `programme_accompagnement_id` bigint(20) unsigned NOT NULL,
  `statut_demande` varchar(255) NOT NULL DEFAULT 'en_attente',
  `date_demande` date NOT NULL DEFAULT '2025-12-06',
  `commentaire` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `demande_accompagnements_sae_id_foreign` (`sae_id`),
  KEY `demande_accompagnements_entrepreneur_id_foreign` (`entrepreneur_id`),
  KEY `demande_accompagnements_programme_accompagnement_id_foreign` (`programme_accompagnement_id`),
  CONSTRAINT `demande_accompagnements_entrepreneur_id_foreign` FOREIGN KEY (`entrepreneur_id`) REFERENCES `entrepreneurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `demande_accompagnements_programme_accompagnement_id_foreign` FOREIGN KEY (`programme_accompagnement_id`) REFERENCES `programme_accompagnements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `demande_accompagnements_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `demande_coaches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `demande_coaches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `coach_id` bigint(20) unsigned NOT NULL,
  `entrepreneur_id` bigint(20) unsigned NOT NULL,
  `sae_id` bigint(20) unsigned NOT NULL,
  `programme_accompagnement_id` bigint(20) unsigned DEFAULT NULL,
  `statut` varchar(255) NOT NULL DEFAULT 'en_attente',
  `message_coach` text DEFAULT NULL,
  `commentaire_responsable` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `demande_coaches_coach_id_foreign` (`coach_id`),
  KEY `demande_coaches_entrepreneur_id_foreign` (`entrepreneur_id`),
  KEY `demande_coaches_sae_id_foreign` (`sae_id`),
  KEY `demande_coaches_programme_accompagnement_id_foreign` (`programme_accompagnement_id`),
  CONSTRAINT `demande_coaches_coach_id_foreign` FOREIGN KEY (`coach_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `demande_coaches_entrepreneur_id_foreign` FOREIGN KEY (`entrepreneur_id`) REFERENCES `entrepreneurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `demande_coaches_programme_accompagnement_id_foreign` FOREIGN KEY (`programme_accompagnement_id`) REFERENCES `programme_accompagnements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `demande_coaches_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `documents_saes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents_saes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sae_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `type_document_sae_id` bigint(20) unsigned NOT NULL,
  `titre_document` varchar(255) NOT NULL,
  `chemin_document` varchar(255) NOT NULL,
  `text_du_lien` varchar(255) DEFAULT NULL,
  `lien_drive` longtext DEFAULT NULL,
  `est_public` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documents_saes_sae_id_foreign` (`sae_id`),
  KEY `documents_saes_user_id_foreign` (`user_id`),
  KEY `documents_saes_type_document_sae_id_foreign` (`type_document_sae_id`),
  CONSTRAINT `documents_saes_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_saes_type_document_sae_id_foreign` FOREIGN KEY (`type_document_sae_id`) REFERENCES `type_document_saes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_saes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entrepreneurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entrepreneurs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sae_id` bigint(20) unsigned DEFAULT NULL,
  `requested_sae_id` bigint(20) unsigned DEFAULT NULL,
  `statut_approbation` tinyint(1) NOT NULL DEFAULT 0,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `genre` varchar(255) NOT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `indicatif` varchar(255) NOT NULL DEFAULT '+228',
  `profession` varchar(255) NOT NULL,
  `nationalite` varchar(255) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `pays_residence` varchar(255) NOT NULL,
  `statut_professionnel` varchar(255) DEFAULT NULL,
  `niveau_etude` varchar(255) DEFAULT NULL,
  `domaine_etude` varchar(255) DEFAULT NULL,
  `passions` text DEFAULT NULL,
  `realisations_personnelles` longtext DEFAULT NULL,
  `motivation_projet` longtext DEFAULT NULL,
  `plus_grande_faiblesse` text DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entrepreneurs_requested_sae_id_foreign` (`requested_sae_id`),
  KEY `entrepreneurs_sae_id_foreign` (`sae_id`),
  CONSTRAINT `entrepreneurs_requested_sae_id_foreign` FOREIGN KEY (`requested_sae_id`) REFERENCES `saes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entrepreneurs_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evenements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `evenements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sae_id` bigint(20) unsigned NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `lieu` varchar(255) NOT NULL,
  `statut` varchar(255) NOT NULL DEFAULT 'brouillon',
  `max_participants` int(10) unsigned DEFAULT NULL,
  `affiche` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evenements_sae_id_foreign` (`sae_id`),
  CONSTRAINT `evenements_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `form_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_responses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `entrepreneur_id` bigint(20) unsigned NOT NULL,
  `demande_accompagnement_id` bigint(20) unsigned DEFAULT NULL,
  `form_id` bigint(20) unsigned NOT NULL,
  `responses_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Toutes les réponses en JSON' CHECK (json_valid(`responses_data`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Métadonnées de soumission' CHECK (json_valid(`metadata`)),
  `respondent_email` varchar(255) DEFAULT NULL,
  `respondent_name` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `completion_time` int(11) DEFAULT NULL COMMENT 'Temps en secondes',
  `is_complete` tinyint(1) NOT NULL DEFAULT 1,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `form_responses_uuid_unique` (`uuid`),
  KEY `form_responses_entrepreneur_id_foreign` (`entrepreneur_id`),
  KEY `idx_form_submitted` (`form_id`,`submitted_at`),
  KEY `idx_respondent_email` (`respondent_email`),
  KEY `form_responses_respondent_email_index` (`respondent_email`),
  KEY `form_responses_demande_accompagnement_id_foreign` (`demande_accompagnement_id`),
  CONSTRAINT `form_responses_demande_accompagnement_id_foreign` FOREIGN KEY (`demande_accompagnement_id`) REFERENCES `demande_accompagnements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `form_responses_entrepreneur_id_foreign` FOREIGN KEY (`entrepreneur_id`) REFERENCES `entrepreneurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `form_responses_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `forms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sae_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `uuid` char(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` text DEFAULT NULL,
  `fichiers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fichiers`)),
  `corps_formulaire` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Définition de tous les champs en JSON' CHECK (json_valid(`corps_formulaire`)),
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Paramètres du formulaire' CHECK (json_valid(`settings`)),
  `theme` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Personnalisation visuelle' CHECK (json_valid(`theme`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `allow_multiple_responses` tinyint(1) NOT NULL DEFAULT 1,
  `collect_email` tinyint(1) NOT NULL DEFAULT 0,
  `require_login` tinyint(1) NOT NULL DEFAULT 0,
  `closes_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `forms_uuid_unique` (`uuid`),
  KEY `forms_sae_id_foreign` (`sae_id`),
  KEY `idx_user_active` (`user_id`,`is_active`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `forms_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `forms_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `livrable_projets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `livrable_projets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `projet_id` bigint(20) unsigned NOT NULL,
  `type_livrable_entrepreneur_id` bigint(20) unsigned NOT NULL,
  `titre_livrable` varchar(255) NOT NULL,
  `chemin_livrable` varchar(255) NOT NULL,
  `text_du_lien` varchar(255) DEFAULT NULL,
  `lien_drive` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `statut` varchar(255) NOT NULL DEFAULT 'en_attente',
  `commentaire_evaluation` text DEFAULT NULL,
  `note` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `livrable_projets_projet_id_foreign` (`projet_id`),
  KEY `livrable_projets_type_livrable_entrepreneur_id_foreign` (`type_livrable_entrepreneur_id`),
  CONSTRAINT `livrable_projets_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `livrable_projets_type_livrable_entrepreneur_id_foreign` FOREIGN KEY (`type_livrable_entrepreneur_id`) REFERENCES `type_livrable_entrepreneurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `demande_accompagnement_id` bigint(20) unsigned NOT NULL,
  `score` tinyint(4) NOT NULL,
  `feedback` longtext DEFAULT NULL,
  `observations` longtext DEFAULT NULL,
  `actions_correctives` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notations_demande_accompagnement_id_foreign` (`demande_accompagnement_id`),
  CONSTRAINT `notations_demande_accompagnement_id_foreign` FOREIGN KEY (`demande_accompagnement_id`) REFERENCES `demande_accompagnements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `partenaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `partenaires` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `entreprise` varchar(255) DEFAULT NULL,
  `poste` varchar(255) DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `site_web` varchar(255) DEFAULT NULL,
  `type_partenaire` varchar(255) DEFAULT NULL,
  `secteur_activite` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `ville` varchar(255) DEFAULT NULL,
  `pays` varchar(255) DEFAULT NULL,
  `code_postal` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partenaires_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `participants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `evenement_id` bigint(20) unsigned NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `participants_evenement_id_foreign` (`evenement_id`),
  CONSTRAINT `participants_evenement_id_foreign` FOREIGN KEY (`evenement_id`) REFERENCES `evenements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sae_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_categories_slug_unique` (`slug`),
  KEY `post_categories_sae_id_foreign` (`sae_id`),
  CONSTRAINT `post_categories_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_tag` (
  `post_id` bigint(20) unsigned NOT NULL,
  `tag_id` bigint(20) unsigned NOT NULL,
  KEY `post_tag_post_id_foreign` (`post_id`),
  KEY `post_tag_tag_id_foreign` (`tag_id`),
  CONSTRAINT `post_tag_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`),
  CONSTRAINT `post_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sae_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `post_category_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'brouillon',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_slug_unique` (`slug`),
  KEY `posts_sae_id_foreign` (`sae_id`),
  KEY `posts_user_id_foreign` (`user_id`),
  KEY `posts_post_category_id_foreign` (`post_category_id`),
  CONSTRAINT `posts_post_category_id_foreign` FOREIGN KEY (`post_category_id`) REFERENCES `post_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `posts_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `programme_accompagnements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `programme_accompagnements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nom_du_programme` varchar(255) DEFAULT NULL,
  `description_du_programme` varchar(255) DEFAULT NULL,
  `sae_id` bigint(20) unsigned NOT NULL,
  `form_id` bigint(20) unsigned NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `debut_inscription` date DEFAULT NULL,
  `fin_inscription` date DEFAULT NULL,
  `etat_de_fermeture` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `programme_accompagnements_sae_id_foreign` (`sae_id`),
  KEY `programme_accompagnements_form_id_foreign` (`form_id`),
  CONSTRAINT `programme_accompagnements_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`),
  CONSTRAINT `programme_accompagnements_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `projet_programme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `projet_programme` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `projet_id` bigint(20) unsigned NOT NULL,
  `programme_accompagnement_id` bigint(20) unsigned NOT NULL,
  `rejoint_le` date DEFAULT NULL,
  `sorti_le` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projet_programme_projet_id_foreign` (`projet_id`),
  KEY `projet_programme_programme_accompagnement_id_foreign` (`programme_accompagnement_id`),
  CONSTRAINT `projet_programme_programme_accompagnement_id_foreign` FOREIGN KEY (`programme_accompagnement_id`) REFERENCES `programme_accompagnements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projet_programme_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `projet_sae`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `projet_sae` (
  `sae_id` bigint(20) unsigned NOT NULL,
  `projet_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `projet_sae_sae_id_foreign` (`sae_id`),
  KEY `projet_sae_projet_id_foreign` (`projet_id`),
  CONSTRAINT `projet_sae_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projet_sae_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='La table qui sert a enrégistrer les projet accepté en demande par un sae';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `projet_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `projet_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `projet_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `projet_user_user_id_foreign` (`user_id`),
  KEY `projet_user_projet_id_foreign` (`projet_id`),
  CONSTRAINT `projet_user_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`),
  CONSTRAINT `projet_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='La table qui sert a enrégistrer les projet suivi par un coach';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `projets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `projets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nom_du_projet` varchar(255) DEFAULT NULL,
  `entrepreneur_id` bigint(20) unsigned NOT NULL,
  `logo` text DEFAULT NULL,
  `affiche` text DEFAULT NULL,
  `problematique` text DEFAULT NULL,
  `presentation_produit_service` longtext DEFAULT NULL,
  `segments_clients` longtext DEFAULT NULL,
  `proposition_valeur` longtext DEFAULT NULL,
  `domaine_projet` varchar(255) DEFAULT NULL,
  `odd` varchar(255) DEFAULT NULL,
  `resume_projet` longtext DEFAULT NULL,
  `pitch_projet` longtext DEFAULT NULL,
  `etape_actuelle` varchar(255) DEFAULT NULL,
  `niveau_accompagnement` varchar(255) DEFAULT NULL,
  `prochaines_etapes` longtext DEFAULT NULL,
  `a_leve_des_fonds` tinyint(1) NOT NULL DEFAULT 0,
  `montant_levee_fonds` decimal(15,2) NOT NULL DEFAULT 0.00,
  `montant_investi` decimal(15,2) DEFAULT NULL,
  `ce_que_vous_avez_realise` longtext DEFAULT NULL,
  `blocages` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projets_entrepreneur_id_foreign` (`entrepreneur_id`),
  CONSTRAINT `projets_entrepreneur_id_foreign` FOREIGN KEY (`entrepreneur_id`) REFERENCES `entrepreneurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reponse_prog_accomp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reponse_prog_accomp` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `programme_accompagnement_id` bigint(20) unsigned NOT NULL,
  `entrepreneur_id` bigint(20) unsigned NOT NULL,
  `reponses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`reponses`)),
  `statut_validation` tinyint(1) NOT NULL DEFAULT 0,
  `statut_archivage` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reponse_prog_accomp_programme_accompagnement_id_foreign` (`programme_accompagnement_id`),
  KEY `reponse_prog_accomp_entrepreneur_id_foreign` (`entrepreneur_id`),
  CONSTRAINT `reponse_prog_accomp_entrepreneur_id_foreign` FOREIGN KEY (`entrepreneur_id`) REFERENCES `entrepreneurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reponse_prog_accomp_programme_accompagnement_id_foreign` FOREIGN KEY (`programme_accompagnement_id`) REFERENCES `programme_accompagnements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ressource_cours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ressource_cours` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cour_id` bigint(20) unsigned NOT NULL,
  `chemin_de_la_ressource` varchar(255) DEFAULT NULL,
  `lien_drive_de_la_ressource` varchar(255) DEFAULT NULL,
  `type_de_la_ressource` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ressource_cours_cour_id_foreign` (`cour_id`),
  CONSTRAINT `ressource_cours_cour_id_foreign` FOREIGN KEY (`cour_id`) REFERENCES `cours` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sae_entrepreneur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sae_entrepreneur` (
  `sae_id` bigint(20) unsigned NOT NULL,
  `entrepreneur_id` bigint(20) unsigned NOT NULL,
  `rejoint_le` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `sae_entrepreneur_sae_id_foreign` (`sae_id`),
  KEY `sae_entrepreneur_entrepreneur_id_foreign` (`entrepreneur_id`),
  CONSTRAINT `sae_entrepreneur_entrepreneur_id_foreign` FOREIGN KEY (`entrepreneur_id`) REFERENCES `entrepreneurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sae_entrepreneur_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cette table sert a enregistrer les entrepreneurs suivi par le sae';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sae_type_sae`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sae_type_sae` (
  `sae_id` bigint(20) unsigned NOT NULL,
  `type_sae_id` bigint(20) unsigned NOT NULL,
  KEY `sae_type_sae_sae_id_foreign` (`sae_id`),
  KEY `sae_type_sae_type_sae_id_foreign` (`type_sae_id`),
  CONSTRAINT `sae_type_sae_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sae_type_sae_type_sae_id_foreign` FOREIGN KEY (`type_sae_id`) REFERENCES `type_saes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sae_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sae_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sae_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `date_debut` datetime NOT NULL DEFAULT '2026-02-24 11:52:39',
  `date_fin` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_sae_unique` (`user_id`,`sae_id`),
  KEY `sae_user_sae_id_foreign` (`sae_id`),
  CONSTRAINT `sae_user_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sae_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Table des affectations des utilisateurs aux SAE secondaires. Pour les coachs et responsable qui appartiennent à une SAE secondaire.';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `saes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `saes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nom_sae` varchar(255) NOT NULL,
  `nom_universite` varchar(255) DEFAULT 'Aucune',
  `email_sae` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `est_officiel` tinyint(1) NOT NULL DEFAULT 0,
  `est_system_sae` tinyint(1) NOT NULL DEFAULT 0,
  `vision` text DEFAULT NULL,
  `mission` text DEFAULT NULL,
  `objectifs` text DEFAULT NULL,
  `services` longtext DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sae_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tags_sae_id_foreign` (`sae_id`),
  KEY `tags_user_id_foreign` (`user_id`),
  KEY `tags_libelle_index` (`libelle`),
  CONSTRAINT `tags_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tags_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_document_saes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `type_document_saes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sae_id` bigint(20) unsigned NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type_document_saes_sae_id_foreign` (`sae_id`),
  CONSTRAINT `type_document_saes_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_livrable_entrepreneurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `type_livrable_entrepreneurs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sae_id` bigint(20) unsigned NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `est_obligatoire` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `niveau_concerne` varchar(255) DEFAULT NULL,
  `note_max` int(11) NOT NULL DEFAULT 20,
  `note_min_validation` int(11) NOT NULL DEFAULT 10,
  PRIMARY KEY (`id`),
  KEY `type_livrable_entrepreneurs_sae_id_foreign` (`sae_id`),
  CONSTRAINT `type_livrable_entrepreneurs_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_saes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `type_saes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `type_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type_user_id` bigint(20) unsigned NOT NULL,
  `sae_id` bigint(20) unsigned DEFAULT NULL,
  `requested_sae_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `indicatif` varchar(255) NOT NULL DEFAULT '+228',
  `genre` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `statut_approbation` tinyint(1) NOT NULL DEFAULT 0,
  `profession` varchar(255) DEFAULT NULL,
  `nationalite` varchar(255) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `pays_residence` varchar(255) DEFAULT NULL,
  `conditions_utilisation` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_name_index` (`name`),
  KEY `users_prenom_index` (`prenom`),
  KEY `users_telephone_index` (`telephone`),
  KEY `users_is_active_index` (`is_active`),
  KEY `users_statut_approbation_index` (`statut_approbation`),
  KEY `users_sae_id_foreign` (`sae_id`),
  KEY `users_requested_sae_id_foreign` (`requested_sae_id`),
  CONSTRAINT `users_requested_sae_id_foreign` FOREIGN KEY (`requested_sae_id`) REFERENCES `saes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_sae_id_foreign` FOREIGN KEY (`sae_id`) REFERENCES `saes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

/*M!999999\- enable the sandbox mode */ 
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0000_01_01_000000_create_type_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_08_11_234509_create_saes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_08_12_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_08_13_005856_create_type_saes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_08_13_010250_create_sae_type_sae',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_08_13_012257_create_forms_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_08_13_012258_create_programme_accompagnements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_08_13_013638_create_entrepreneurs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_08_13_014543_create_reponse_prog_accomp_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_08_13_022902_create_projets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_08_13_023042_create_projet_programme_accompagnement',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_08_13_023558_create_type_document_saes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_08_13_023723_create_documents_saes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_08_13_024118_create_type_livrable_entrepreneurs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_08_13_024119_create_livrable_projets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_08_13_154849_create_demande_accompagnements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_08_13_155549_create_business_plans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_08_17_014113_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_08_24_144949_create_sae_entrepreneur',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_08_24_144950_create_projet_sae',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_09_07_171231_create_cours_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_09_07_171521_create_ressource_cours_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_09_10_233453_create_projet_user',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_09_18_152706_create_post_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_09_18_152711_create_posts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_09_19_002352_create_evenements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_09_19_002359_create_participants_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_09_19_005550_create_activites_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_09_30_185333_create_form_responses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_10_03_061951_create_notations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_10_04_162347_create_accompagnement_actifs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_10_08_111457_add_commentaire_field_in_demande_accompagnements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_10_09_211322_add_indicatif_column_in_user_and_entrepreneur_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_10_13_172789_create_tags_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_10_13_173746_create_post_tag',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_10_13_224334_add_sae_id_in_activites_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_10_15_081312_create_admins_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_10_15_120335_add_sae_id_in_type_document_saes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_12_12_183604_add_demande_accompagnement_id_to_form_responses_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_01_20_195840_create_partenaires_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2026_02_13_150617_add_statut_commentaire_note_to_livrable_projets_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2026_02_13_151227_add_note_max_and_note_min_validation_to_type_livrable_entrepreneurs_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_02_16_190939_make_saes_fields_nullable',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_02_16_191208_add_est_officiel_to_saes_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_02_17_130941_add_is_system_sae_to_saes_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_02_19_090610_create_sae_user_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_02_23_103131_create_demande_coaches_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_02_24_142400_add_cascade_delete_to_sae_id_in_users_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_03_17_210814_add_affiche_to_evenements_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_03_21_000000_make_sae_id_nullable_in_users_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_03_22_000000_add_requested_sae_id_and_approval_to_entrepreneurs_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2026_03_23_000000_make_sae_id_nullable_in_entrepreneurs_table',7);
