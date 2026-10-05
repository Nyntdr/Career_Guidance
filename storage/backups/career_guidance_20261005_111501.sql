-- MySQL dump 10.13  Distrib 8.4.10, for Linux (x86_64)
--
-- Host: localhost    Database: career_guidance
-- ------------------------------------------------------
-- Server version	8.4.10

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ats_matched_keywords`
--

DROP TABLE IF EXISTS `ats_matched_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ats_matched_keywords` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ats_scan_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keyword` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `matched` tinyint(1) NOT NULL DEFAULT '0',
  `frequency_in_resume` int unsigned NOT NULL DEFAULT '0',
  `frequency_in_jd` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ats_matched_keywords_ats_scan_id_foreign` (`ats_scan_id`),
  CONSTRAINT `ats_matched_keywords_ats_scan_id_foreign` FOREIGN KEY (`ats_scan_id`) REFERENCES `ats_scans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ats_matched_keywords`
--

LOCK TABLES `ats_matched_keywords` WRITE;
/*!40000 ALTER TABLE `ats_matched_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `ats_matched_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ats_scan_details`
--

DROP TABLE IF EXISTS `ats_scan_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ats_scan_details` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ats_scan_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` text COLLATE utf8mb4_unicode_ci,
  `severity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'low',
  `suggestion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ats_scan_details_ats_scan_id_foreign` (`ats_scan_id`),
  CONSTRAINT `ats_scan_details_ats_scan_id_foreign` FOREIGN KEY (`ats_scan_id`) REFERENCES `ats_scans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ats_scan_details`
--

LOCK TABLES `ats_scan_details` WRITE;
/*!40000 ALTER TABLE `ats_scan_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `ats_scan_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ats_scans`
--

DROP TABLE IF EXISTS `ats_scans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ats_scans` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resume_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_posting_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `overall_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `keyword_match_score` decimal(5,2) DEFAULT NULL,
  `formatting_score` decimal(5,2) DEFAULT NULL,
  `structure_score` decimal(5,2) DEFAULT NULL,
  `readability_score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ats_scans_resume_id_foreign` (`resume_id`),
  KEY `ats_scans_job_posting_id_foreign` (`job_posting_id`),
  CONSTRAINT `ats_scans_job_posting_id_foreign` FOREIGN KEY (`job_posting_id`) REFERENCES `job_postings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ats_scans_resume_id_foreign` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ats_scans`
--

LOCK TABLES `ats_scans` WRITE;
/*!40000 ALTER TABLE `ats_scans` DISABLE KEYS */;
/*!40000 ALTER TABLE `ats_scans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `career_paths`
--

DROP TABLE IF EXISTS `career_paths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `career_paths` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_role_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_role_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `typical_years` int unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `career_paths_from_role_id_to_role_id_unique` (`from_role_id`,`to_role_id`),
  KEY `career_paths_to_role_id_foreign` (`to_role_id`),
  CONSTRAINT `career_paths_from_role_id_foreign` FOREIGN KEY (`from_role_id`) REFERENCES `career_roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `career_paths_to_role_id_foreign` FOREIGN KEY (`to_role_id`) REFERENCES `career_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_paths`
--

LOCK TABLES `career_paths` WRITE;
/*!40000 ALTER TABLE `career_paths` DISABLE KEYS */;
INSERT INTO `career_paths` VALUES ('01a0e6c0-386b-70ae-bf82-1c63693b1a40','01a0e6c0-375b-737b-b57f-7a7a273b9d06','01a0e6c0-36d7-703e-8789-691b0c5f5275',2,'Deepen statistics and Python skills, move into predictive modeling.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-386f-70dc-aed4-ef6cf6e46d19','01a0e6c0-375b-737b-b57f-7a7a273b9d06','01a0e6c0-3774-70af-840b-86fdb3602832',3,'Leverage business insight and stakeholder communication to own product decisions.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-387e-70e8-941e-9601630f4a5f','01a0e6c0-36d7-703e-8789-691b0c5f5275','01a0e6c0-3707-72c0-b872-1687b4b19696',2,'Focus on productionizing models, pipelines, and deployed ML systems.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3887-7399-88da-62e5602fe709','01a0e6c0-3707-72c0-b872-1687b4b19696','01a0e6c0-36d7-703e-8789-691b0c5f5275',2,'Shift toward experimentation, research, and advanced statistical methods.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-388a-7178-b0b3-f137d8d8ead9','01a0e6c0-3730-72b6-b569-e2612458071e','01a0e6c0-3784-7352-af2c-910e1e23bfe8',2,'Move into infrastructure, CI/CD, and cloud automation.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-388d-732a-8cc0-efdee607d52e','01a0e6c0-3730-72b6-b569-e2612458071e','01a0e6c0-3707-72c0-b872-1687b4b19696',3,'Add ML modeling depth to strong software engineering fundamentals.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3891-71b9-8920-e60cae4ae239','01a0e6c0-37a2-726b-9410-7ce4aff16bd9','01a0e6c0-3730-72b6-b569-e2612458071e',2,'Broaden into full-stack engineering with backend and architecture skills.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3894-7090-be1b-17ccda4a8cb5','01a0e6c0-37c0-7266-bc2d-f8200abd1ff1','01a0e6c0-3730-72b6-b569-e2612458071e',2,'Round out frontend and system design to become a full-stack engineer.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3897-727f-b7ac-b5a2167068d1','01a0e6c0-3730-72b6-b569-e2612458071e','01a0e6c0-37c0-7266-bc2d-f8200abd1ff1',1,'Specialize in server-side logic, databases, and API design.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3899-72ec-95e3-95b022bd022c','01a0e6c0-3730-72b6-b569-e2612458071e','01a0e6c0-37a2-726b-9410-7ce4aff16bd9',1,'Specialize in frameworks, UI engineering, and web performance.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-389c-7241-8c0a-d194889da463','01a0e6c0-3774-70af-840b-86fdb3602832','01a0e6c0-36d7-703e-8789-691b0c5f5275',4,'Complement delivery leadership with deep quantitative analysis.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-389e-71a5-8879-0d820873a5ea','01a0e6c0-37a2-726b-9410-7ce4aff16bd9','01a0e6c0-37d8-719c-a80b-00d5d5d1b51d',1,'Transfer UI skills to Flutter or React Native — a fast-growing niche in Nepal.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38a1-71d6-92fa-676b2b8451be','01a0e6c0-37d8-719c-a80b-00d5d5d1b51d','01a0e6c0-3730-72b6-b569-e2612458071e',2,'Broaden into web and backend systems for full-stack versatility.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38a3-7317-8107-001e0b16a8eb','01a0e6c0-37a2-726b-9410-7ce4aff16bd9','01a0e6c0-37e6-7005-9193-d9ca1880c9c9',2,'Move from building interfaces to designing them with user research skills.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38a6-73aa-8fb4-ceb1f8ed5b53','01a0e6c0-37e6-7005-9193-d9ca1880c9c9','01a0e6c0-3774-70af-840b-86fdb3602832',3,'Turn design thinking and user empathy into product ownership.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38a8-7284-b536-8b1ea81cd25f','01a0e6c0-384d-7301-b006-161ecee1fa69','01a0e6c0-37e6-7005-9193-d9ca1880c9c9',2,'Learn Figma, prototyping, and usability to move from visuals to product design.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38ab-729b-9ab5-9742856a4afa','01a0e6c0-37f2-730c-9ae2-df2731d43334','01a0e6c0-3730-72b6-b569-e2612458071e',2,'Use testing knowledge of codebases to transition into development.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38ad-71dc-8f93-5562cbf0cff4','01a0e6c0-37f2-730c-9ae2-df2731d43334','01a0e6c0-3784-7352-af2c-910e1e23bfe8',3,'Extend test automation into CI/CD pipelines and infrastructure.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38b0-700e-8657-3ab6ee33b630','01a0e6c0-37c0-7266-bc2d-f8200abd1ff1','01a0e6c0-3784-7352-af2c-910e1e23bfe8',2,'Leverage server-side experience for infrastructure and deployment roles.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38b3-7167-8e60-eb3d637b5865','01a0e6c0-37fe-7155-b77b-620e09b59216','01a0e6c0-3774-70af-840b-86fdb3602832',3,'Apply market insight and customer understanding to product strategy.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38b5-7230-8356-ee1a9cd6c9f4','01a0e6c0-3857-735b-8a15-4937f5748005','01a0e6c0-37fe-7155-b77b-620e09b59216',1,'Extend writing skills into SEO strategy, ads, and campaign management.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38b7-72ab-b8a2-1f1ae2ed025b','01a0e6c0-3845-7274-87e7-b016598e3b08','01a0e6c0-37fe-7155-b77b-620e09b59216',2,'Use customer insight and communication skills in brand and social media roles.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38b9-706e-945f-90f62b42ca5e','01a0e6c0-380c-7381-a8fe-233a0a6de6dc','01a0e6c0-382e-7228-8a57-34058e89982f',2,'Move into banking credit or operations — accounting is a natural feeder role in Nepal.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38bb-706a-aecc-e13a02eaa967','01a0e6c0-382e-7228-8a57-34058e89982f','01a0e6c0-380c-7381-a8fe-233a0a6de6dc',2,'Transition into corporate finance roles in businesses or NGOs.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38bd-7088-ab79-7e8bb949079f','01a0e6c0-380c-7381-a8fe-233a0a6de6dc','01a0e6c0-375b-737b-b57f-7a7a273b9d06',2,'Build on Excel and reporting skills with SQL and data visualization.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38c1-73dc-b25e-6a54fecb6df1','01a0e6c0-382e-7228-8a57-34058e89982f','01a0e6c0-375b-737b-b57f-7a7a273b9d06',3,'Banks value domain knowledge combined with data skills for analytics teams.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38c3-7063-b2d6-3a723375298e','01a0e6c0-3839-7017-a6af-9744f959e57a','01a0e6c0-3784-7352-af2c-910e1e23bfe8',2,'Pair networking depth with cloud and automation skills.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38c6-71f5-8145-1da88c4544e9','01a0e6c0-3845-7274-87e7-b016598e3b08','01a0e6c0-382e-7228-8a57-34058e89982f',2,'Client-facing experience is valued for bank teller and relationship roles.','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38c8-71d1-8fbf-5449cdea21e6','01a0e6c0-37e6-7005-9193-d9ca1880c9c9','01a0e6c0-37a2-726b-9410-7ce4aff16bd9',2,'Learn HTML/CSS/JS to implement your own designs.','2026-09-28 06:42:32','2026-09-28 06:42:32');
/*!40000 ALTER TABLE `career_paths` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `career_recommendations`
--

DROP TABLE IF EXISTS `career_recommendations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `career_recommendations` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `career_role_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `match_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `rationale` text COLLATE utf8mb4_unicode_ci,
  `generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `rationale_source` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'template',
  PRIMARY KEY (`id`),
  KEY `career_recommendations_profile_id_foreign` (`profile_id`),
  KEY `career_recommendations_career_role_id_foreign` (`career_role_id`),
  CONSTRAINT `career_recommendations_career_role_id_foreign` FOREIGN KEY (`career_role_id`) REFERENCES `career_roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `career_recommendations_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_recommendations`
--

LOCK TABLES `career_recommendations` WRITE;
/*!40000 ALTER TABLE `career_recommendations` DISABLE KEYS */;
INSERT INTO `career_recommendations` VALUES ('01a0eb92-cd93-72da-a40d-02609673f74a','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-375b-737b-b57f-7a7a273b9d06',44.84,'The Data Analyst role is realistic because it values Python, communication and problem‑solving, which you already bring. Your strongest match is the ability to write Python code and convey insights clearly, positioning you to help build data pipelines and stakeholder reports. The biggest gaps are SQL, Excel, statistics, data‑visualization, critical thinking and formal data‑analysis practice, and you can close them by completing an introductory SQL course, earning a basic Excel certification, and creating a portfolio of projects that combine Python data cleaning with visual dashboards using libraries like matplotlib or a tool such as Tableau. A concrete way to demonstrate those missing skills is to volunteer to clean and visualize a small dataset for a local NGO, documenting the process and results in a shareable notebook. The single most useful next step this week is to sign up for a free online SQL tutorial and start a mini‑project that imports a CSV into Python, performs basic analysis, and creates a chart that you can export to Excel.','2026-09-29 05:11:01','2026-09-29 05:11:01','2026-09-29 05:11:01','llm'),('01a0eb92-cd98-71fd-9291-aa2472178768','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-3730-72b6-b569-e2612458071e',43.28,'This role is a realistic fit because your solid background in Python, Java and SQL aligns directly with the backend and full‑stack work that Nepali IT firms and fintech outsourcers need, and your communication skill will help you collaborate with both local teams and international clients. You already bring the strongest matches in core programming languages, database handling, and clear communication, which are essential for designing and maintaining software systems. The most important gaps are JavaScript, Git, Agile methodologies and systematic problem‑solving; you can close them by building a small full‑stack project that uses a Python or Java API, a JavaScript front end, version control with Git, and a two‑week sprint plan to practice Agile while solving coding challenges on a site like LeetCode. Enrolling in a free Git tutorial and an introductory Agile fundamentals course, then adding the completed project to a portfolio, will give you concrete evidence of those skills. As a single next step this week, create a GitHub repository, initialize it, and commit the first version of a simple API and a basic HTML/JavaScript client, tracking the work on a free Kanban board to start practicing Agile.','2026-09-29 05:11:01','2026-09-29 05:11:01','2026-09-29 05:11:11','llm'),('01a0eb92-cd9b-7246-9f57-fc08b9ecd1c8','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-37f2-730c-9ae2-df2731d43334',34.22,'Your existing skills in Communication, Python and SQL align well with this role. Closing the Agile Methodologies, Problem Solving and Quality Assurance gap would move you toward a strong match. Industry outlook: Steady demand — quality processes are becoming standard in Nepali IT companies.','2026-09-29 05:11:01','2026-09-29 05:11:01','2026-09-29 05:11:01','template'),('01a0eb92-cd9e-70ac-8307-9663b0f2fe38','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-36d7-703e-8789-691b0c5f5275',27.58,'Your existing skills in SQL and Communication align well with this role. Closing the Python, Machine Learning, Statistics, Critical Thinking, Problem Solving and Data Analysis gap would move you toward a strong match. Industry outlook: High demand — an emerging field in Nepal with few qualified professionals.','2026-09-29 05:11:01','2026-09-29 05:11:01','2026-09-29 05:11:01','template'),('01a0eb92-cda1-7023-b3f4-44862ddeb8b1','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-37e6-7005-9193-d9ca1880c9c9',26.33,'Your existing skills in Communication align well with this role. Closing the UI/UX Design and Figma gap would move you toward a strong match. Industry outlook: Growing demand as Nepali companies invest in digital products.','2026-09-29 05:11:01','2026-09-29 05:11:01','2026-09-29 05:11:01','template'),('01a0eb92-cda4-7248-b523-b91994300570','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-3845-7274-87e7-b016598e3b08',24.57,'Your existing skills in Problem Solving align well with this role. Closing the Communication and Customer Service gap would move you toward a strong match. Industry outlook: Growing demand — Nepal\'s BPO industry is expanding with international clients.','2026-09-29 05:11:01','2026-09-29 05:11:01','2026-09-29 05:11:01','template'),('01a0eb92-cda8-7212-8eac-56a47daa0f71','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-37fe-7155-b77b-620e09b59216',21.79,'Your existing skills in Communication align well with this role. Closing the Digital Marketing, SEO and Social Media Marketing gap would move you toward a strong match. Industry outlook: High demand — every Nepali business is moving online.','2026-09-29 05:11:01','2026-09-29 05:11:01','2026-09-29 05:11:01','template'),('01a0eb92-cdac-72bf-9de6-806b248cddbd','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-3819-7312-a609-c940fabb87e5',20.55,'Your existing skills in Problem Solving align well with this role. Closing the AutoCAD and Surveying gap would move you toward a strong match. Industry outlook: Steady demand — driven by hydropower, road, and urban development projects.','2026-09-29 05:11:01','2026-09-29 05:11:01','2026-09-29 05:11:01','template'),('01a0eb92-cdb2-7309-b310-9994612141cc','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-3825-73c2-92d9-93de72d89dc0',19.50,'Your existing skills in Problem Solving align well with this role. Closing the Communication, Critical Thinking and Patient Care gap would move you toward a strong match. Industry outlook: High demand — chronic shortage of nurses in Nepali hospitals and abroad.','2026-09-29 05:11:01','2026-09-29 05:11:01','2026-09-29 05:11:01','template'),('01a0eb92-cdb6-721e-861d-1fa22c9a2f0e','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-3784-7352-af2c-910e1e23bfe8',18.50,'Your existing skills in Python align well with this role. Closing the AWS, Docker, Kubernetes, Git, CI/CD and Problem Solving gap would move you toward a strong match. Industry outlook: High demand — cloud adoption is accelerating among Nepali enterprises.','2026-09-29 05:11:01','2026-09-29 05:11:01','2026-09-29 05:11:01','template');
/*!40000 ALTER TABLE `career_recommendations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `career_role_skills`
--

DROP TABLE IF EXISTS `career_role_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `career_role_skills` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `career_role_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `skill_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `importance` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'required',
  `min_proficiency` smallint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `career_role_skills_career_role_id_skill_id_unique` (`career_role_id`,`skill_id`),
  KEY `career_role_skills_skill_id_foreign` (`skill_id`),
  CONSTRAINT `career_role_skills_career_role_id_foreign` FOREIGN KEY (`career_role_id`) REFERENCES `career_roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `career_role_skills_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_role_skills`
--

LOCK TABLES `career_role_skills` WRITE;
/*!40000 ALTER TABLE `career_role_skills` DISABLE KEYS */;
INSERT INTO `career_role_skills` VALUES ('01a0e6c0-36de-7218-829c-816e23661693','01a0e6c0-36d7-703e-8789-691b0c5f5275','01a0e6c0-35f2-7181-a65c-b178a8e29886','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-36e4-72cf-b605-b0c287a0af1a','01a0e6c0-36d7-703e-8789-691b0c5f5275','01a0e6c0-35f5-7390-bcae-48a1244683dd','essential',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-36e9-71fa-a6c8-214cc96c6f28','01a0e6c0-36d7-703e-8789-691b0c5f5275','01a0e6c0-35f7-7158-ac90-fa87bba4699c','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-36ed-721e-a121-78cbee594794','01a0e6c0-36d7-703e-8789-691b0c5f5275','01a0e6c0-35f9-702c-9038-32b8badb545d','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-36f1-71ec-ad66-9eabab7a0391','01a0e6c0-36d7-703e-8789-691b0c5f5275','01a0e6c0-35fb-70d6-bb8f-47578366fd4c','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-36f5-7358-9d19-d4a66b5b7693','01a0e6c0-36d7-703e-8789-691b0c5f5275','01a0e6c0-35fd-7099-9c0f-acfa9672940e','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-36f9-702e-8db1-5991874fa220','01a0e6c0-36d7-703e-8789-691b0c5f5275','01a0e6c0-3625-7112-8595-b785a4fea8e6','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-36fe-7247-baf2-49f319be6bba','01a0e6c0-36d7-703e-8789-691b0c5f5275','01a0e6c0-362d-70c2-88ff-89ea9abaea66','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3701-711c-ae45-557dc9d9ce28','01a0e6c0-36d7-703e-8789-691b0c5f5275','01a0e6c0-362b-7248-95a4-1327d7839264','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3705-73ca-9101-d87af165b305','01a0e6c0-36d7-703e-8789-691b0c5f5275','01a0e6c0-3629-7041-b7d0-e6237205e8eb','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-370a-723a-8151-15941582b720','01a0e6c0-3707-72c0-b872-1687b4b19696','01a0e6c0-35f2-7181-a65c-b178a8e29886','essential',5,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-370d-7036-b93c-d688de065e06','01a0e6c0-3707-72c0-b872-1687b4b19696','01a0e6c0-35f7-7158-ac90-fa87bba4699c','essential',5,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3710-7248-b425-1e3289833646','01a0e6c0-3707-72c0-b872-1687b4b19696','01a0e6c0-35fd-7099-9c0f-acfa9672940e','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3714-7013-a415-ad85f9d1a46a','01a0e6c0-3707-72c0-b872-1687b4b19696','01a0e6c0-35f5-7390-bcae-48a1244683dd','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3718-7137-a175-6c463e21f66d','01a0e6c0-3707-72c0-b872-1687b4b19696','01a0e6c0-361a-7317-a319-748bf2e5c1a0','essential',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-371c-72bc-87ed-d25108cc98cd','01a0e6c0-3707-72c0-b872-1687b4b19696','01a0e6c0-3618-7246-b587-ba1ee64d7045','essential',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-371f-731e-afd9-e32927ca0653','01a0e6c0-3707-72c0-b872-1687b4b19696','01a0e6c0-361d-71f5-98ad-4b6dbd2d5d13','essential',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3723-71e3-b704-c2874cab65d1','01a0e6c0-3707-72c0-b872-1687b4b19696','01a0e6c0-3620-72a7-8760-86d16086540e','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3728-738b-b9ab-69a2f2cfeddc','01a0e6c0-3707-72c0-b872-1687b4b19696','01a0e6c0-362b-7248-95a4-1327d7839264','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-372c-72f5-a5ac-b8bfda78ad54','01a0e6c0-3707-72c0-b872-1687b4b19696','01a0e6c0-35f9-702c-9038-32b8badb545d','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3734-7381-af66-6cfe2f09926a','01a0e6c0-3730-72b6-b569-e2612458071e','01a0e6c0-35f2-7181-a65c-b178a8e29886','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3738-71c7-8b42-5fbe7242a47e','01a0e6c0-3730-72b6-b569-e2612458071e','01a0e6c0-3605-736f-94f8-df8aff767363','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-373c-725d-968e-faa4172244a4','01a0e6c0-3730-72b6-b569-e2612458071e','01a0e6c0-3608-708d-af8f-f7c58115f235','essential',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3740-7239-a9b2-673e7dbd77c2','01a0e6c0-3730-72b6-b569-e2612458071e','01a0e6c0-35f5-7390-bcae-48a1244683dd','essential',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3744-7336-b5bb-7975b5b782bb','01a0e6c0-3730-72b6-b569-e2612458071e','01a0e6c0-361d-71f5-98ad-4b6dbd2d5d13','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3748-71cc-a603-f6a86f4dd203','01a0e6c0-3730-72b6-b569-e2612458071e','01a0e6c0-3621-7326-bb47-443fc3ade530','essential',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-374c-7295-b7b3-2b188207c526','01a0e6c0-3730-72b6-b569-e2612458071e','01a0e6c0-362b-7248-95a4-1327d7839264','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-374f-7247-aadd-9e7911972ce6','01a0e6c0-3730-72b6-b569-e2612458071e','01a0e6c0-3625-7112-8595-b785a4fea8e6','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3753-713b-9eaa-255888041053','01a0e6c0-3730-72b6-b569-e2612458071e','01a0e6c0-362f-7173-92f2-1955adbd3860','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3757-7218-accb-ab472f681401','01a0e6c0-3730-72b6-b569-e2612458071e','01a0e6c0-3620-72a7-8760-86d16086540e','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-375f-70a1-a76d-02218501e884','01a0e6c0-375b-737b-b57f-7a7a273b9d06','01a0e6c0-35f5-7390-bcae-48a1244683dd','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3762-7034-b8c5-3547094d0e57','01a0e6c0-375b-737b-b57f-7a7a273b9d06','01a0e6c0-362d-70c2-88ff-89ea9abaea66','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3764-7308-8f82-961b9686ecf6','01a0e6c0-375b-737b-b57f-7a7a273b9d06','01a0e6c0-35fb-70d6-bb8f-47578366fd4c','essential',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3767-7062-8469-a77187abf5d2','01a0e6c0-375b-737b-b57f-7a7a273b9d06','01a0e6c0-35f9-702c-9038-32b8badb545d','essential',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3769-73d3-8f7e-b5551451d3dc','01a0e6c0-375b-737b-b57f-7a7a273b9d06','01a0e6c0-363e-7170-b084-26b0ad23d302','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-376b-712e-beb1-0d426564f57d','01a0e6c0-375b-737b-b57f-7a7a273b9d06','01a0e6c0-35f2-7181-a65c-b178a8e29886','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-376d-71b6-b97e-34fdb0391497','01a0e6c0-375b-737b-b57f-7a7a273b9d06','01a0e6c0-3625-7112-8595-b785a4fea8e6','essential',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3770-7045-a194-3edb9ce5d97e','01a0e6c0-375b-737b-b57f-7a7a273b9d06','01a0e6c0-3629-7041-b7d0-e6237205e8eb','essential',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3772-711e-a229-b4d78d5f03d0','01a0e6c0-375b-737b-b57f-7a7a273b9d06','01a0e6c0-362b-7248-95a4-1327d7839264','essential',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3776-736b-bc37-933789f3f220','01a0e6c0-3774-70af-840b-86fdb3602832','01a0e6c0-3623-72fd-9687-58098d44d6af','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3779-721c-88af-f31f93d20f2b','01a0e6c0-3774-70af-840b-86fdb3602832','01a0e6c0-3625-7112-8595-b785a4fea8e6','essential',5,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-377b-7310-92aa-83386b48fa58','01a0e6c0-3774-70af-840b-86fdb3602832','01a0e6c0-3628-730e-9001-25e806b5eec9','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-377c-7021-8432-db02ccb58996','01a0e6c0-3774-70af-840b-86fdb3602832','01a0e6c0-3621-7326-bb47-443fc3ade530','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-377e-7084-8ca5-1542a33f467f','01a0e6c0-3774-70af-840b-86fdb3602832','01a0e6c0-362d-70c2-88ff-89ea9abaea66','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3780-702c-9271-c86c278ca227','01a0e6c0-3774-70af-840b-86fdb3602832','01a0e6c0-362b-7248-95a4-1327d7839264','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3783-70fa-9dca-d8ba95da43c9','01a0e6c0-3774-70af-840b-86fdb3602832','01a0e6c0-3629-7041-b7d0-e6237205e8eb','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3787-7290-ac70-687fbfcd70b2','01a0e6c0-3784-7352-af2c-910e1e23bfe8','01a0e6c0-3618-7246-b587-ba1ee64d7045','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3789-72f3-bcf8-70f5fb7bf7d8','01a0e6c0-3784-7352-af2c-910e1e23bfe8','01a0e6c0-361a-7317-a319-748bf2e5c1a0','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-378c-73a3-88cc-0d323ea33009','01a0e6c0-3784-7352-af2c-910e1e23bfe8','01a0e6c0-361b-73c7-b9da-2a14aa5b2d53','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-378e-7346-b016-d566e0486cd7','01a0e6c0-3784-7352-af2c-910e1e23bfe8','01a0e6c0-3620-72a7-8760-86d16086540e','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3790-7247-8622-9f36bd279d48','01a0e6c0-3784-7352-af2c-910e1e23bfe8','01a0e6c0-361d-71f5-98ad-4b6dbd2d5d13','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3793-702a-a656-e4ae6a0cc90d','01a0e6c0-3784-7352-af2c-910e1e23bfe8','01a0e6c0-35f2-7181-a65c-b178a8e29886','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-379b-700e-a82f-e81eb40a73f5','01a0e6c0-3784-7352-af2c-910e1e23bfe8','01a0e6c0-3641-72d1-a110-d5d2e2683714','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-379f-7098-9ce5-da3df507fc08','01a0e6c0-3784-7352-af2c-910e1e23bfe8','01a0e6c0-362b-7248-95a4-1327d7839264','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-37a5-71b7-bb6e-38653aadd0bd','01a0e6c0-37a2-726b-9410-7ce4aff16bd9','01a0e6c0-3608-708d-af8f-f7c58115f235','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-37a9-736f-bc1d-a0eafee9b0fc','01a0e6c0-37a2-726b-9410-7ce4aff16bd9','01a0e6c0-360a-71ff-bfb5-990002c37f1b','essential',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-37ad-725e-9b53-d00e5717ea63','01a0e6c0-37a2-726b-9410-7ce4aff16bd9','01a0e6c0-360c-7298-8ff4-331995ad00ce','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-37b1-70c2-b7fd-efac9ba02846','01a0e6c0-37a2-726b-9410-7ce4aff16bd9','01a0e6c0-360e-7048-af3d-e7191ff95094','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-37b4-72d0-a93b-4867b9f5e0ac','01a0e6c0-37a2-726b-9410-7ce4aff16bd9','01a0e6c0-361d-71f5-98ad-4b6dbd2d5d13','essential',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-37b6-7209-925d-20a05e15d6b4','01a0e6c0-37a2-726b-9410-7ce4aff16bd9','01a0e6c0-3644-732b-ace8-88fba8d7ff79','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-37b8-7224-b46c-c87b247514a6','01a0e6c0-37a2-726b-9410-7ce4aff16bd9','01a0e6c0-362f-7173-92f2-1955adbd3860','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-37ba-7140-afd5-98c46fe613f5','01a0e6c0-37a2-726b-9410-7ce4aff16bd9','01a0e6c0-3625-7112-8595-b785a4fea8e6','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-37bb-719f-94bf-be44c4675631','01a0e6c0-37a2-726b-9410-7ce4aff16bd9','01a0e6c0-362b-7248-95a4-1327d7839264','essential',4,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-37be-7110-a19c-76cd12055531','01a0e6c0-37a2-726b-9410-7ce4aff16bd9','01a0e6c0-3621-7326-bb47-443fc3ade530','preferred',3,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-37c3-733d-b875-25915cdcf5c8','01a0e6c0-37c0-7266-bc2d-f8200abd1ff1','01a0e6c0-3613-7054-9390-bf62b06dae57','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37c5-7001-8467-348c48d3f0ef','01a0e6c0-37c0-7266-bc2d-f8200abd1ff1','01a0e6c0-3616-73ea-8ae0-976dabeab294','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37c8-7327-aab7-0f86ff3fd308','01a0e6c0-37c0-7266-bc2d-f8200abd1ff1','01a0e6c0-35f2-7181-a65c-b178a8e29886','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37ca-70a3-a540-5a13fd708eb9','01a0e6c0-37c0-7266-bc2d-f8200abd1ff1','01a0e6c0-3611-70aa-87da-1c05e53cb832','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37cd-736c-8bf5-839bfb003e7c','01a0e6c0-37c0-7266-bc2d-f8200abd1ff1','01a0e6c0-35f5-7390-bcae-48a1244683dd','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37cf-7108-ac21-f3b293f52079','01a0e6c0-37c0-7266-bc2d-f8200abd1ff1','01a0e6c0-362f-7173-92f2-1955adbd3860','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37d2-7062-97da-e399a1eddfc4','01a0e6c0-37c0-7266-bc2d-f8200abd1ff1','01a0e6c0-361d-71f5-98ad-4b6dbd2d5d13','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37d4-71f3-b3b2-db29b24e6b04','01a0e6c0-37c0-7266-bc2d-f8200abd1ff1','01a0e6c0-361a-7317-a319-748bf2e5c1a0','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37d5-7316-ae97-154a2a797e5a','01a0e6c0-37c0-7266-bc2d-f8200abd1ff1','01a0e6c0-3618-7246-b587-ba1ee64d7045','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37d7-73e4-ae55-881675b5194e','01a0e6c0-37c0-7266-bc2d-f8200abd1ff1','01a0e6c0-362b-7248-95a4-1327d7839264','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37da-7050-8233-2cf2d7578041','01a0e6c0-37d8-719c-a80b-00d5d5d1b51d','01a0e6c0-3630-7315-bc70-5ad68ca4a347','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37dc-7212-8cc2-4b957a9a9dc3','01a0e6c0-37d8-719c-a80b-00d5d5d1b51d','01a0e6c0-3632-7156-bc8b-2a552f38cb3c','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37de-7005-a405-96a870ec1bb9','01a0e6c0-37d8-719c-a80b-00d5d5d1b51d','01a0e6c0-3633-7166-86b9-f417b4e746ce','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37e0-73e2-860a-e49f2c12b964','01a0e6c0-37d8-719c-a80b-00d5d5d1b51d','01a0e6c0-3608-708d-af8f-f7c58115f235','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37e2-71c8-9137-8645c7c3e570','01a0e6c0-37d8-719c-a80b-00d5d5d1b51d','01a0e6c0-362f-7173-92f2-1955adbd3860','essential',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37e3-72e3-86c7-a9539c12b493','01a0e6c0-37d8-719c-a80b-00d5d5d1b51d','01a0e6c0-361d-71f5-98ad-4b6dbd2d5d13','essential',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37e5-7321-bf5a-76f8b0a591cb','01a0e6c0-37d8-719c-a80b-00d5d5d1b51d','01a0e6c0-362b-7248-95a4-1327d7839264','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37e8-725e-8f7a-3e716ba0e1e3','01a0e6c0-37e6-7005-9193-d9ca1880c9c9','01a0e6c0-3644-732b-ace8-88fba8d7ff79','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37e9-722b-8c3b-2e956419ec3d','01a0e6c0-37e6-7005-9193-d9ca1880c9c9','01a0e6c0-3646-72ba-b9e5-08e61f69c38b','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37eb-73ec-9ad4-49598ea491f3','01a0e6c0-37e6-7005-9193-d9ca1880c9c9','01a0e6c0-3647-7362-befa-3f2db9a9b263','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37ed-7329-8b61-d046bb3117f1','01a0e6c0-37e6-7005-9193-d9ca1880c9c9','01a0e6c0-3642-7286-8f91-c158e433f1f6','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37ee-71d1-9473-20a792263058','01a0e6c0-37e6-7005-9193-d9ca1880c9c9','01a0e6c0-3625-7112-8595-b785a4fea8e6','essential',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37f0-7177-a41f-606929e61a1a','01a0e6c0-37e6-7005-9193-d9ca1880c9c9','01a0e6c0-3629-7041-b7d0-e6237205e8eb','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37f3-7277-ad34-cb1dc3768f7f','01a0e6c0-37f2-730c-9ae2-df2731d43334','01a0e6c0-364d-70a2-8a0b-e1017b0bf56b','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37f5-733e-bcf3-f26cef15f5a7','01a0e6c0-37f2-730c-9ae2-df2731d43334','01a0e6c0-3621-7326-bb47-443fc3ade530','essential',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37f7-7092-8bf6-aa0e7a42d808','01a0e6c0-37f2-730c-9ae2-df2731d43334','01a0e6c0-35f5-7390-bcae-48a1244683dd','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37f8-7267-beed-1012072360a4','01a0e6c0-37f2-730c-9ae2-df2731d43334','01a0e6c0-35f2-7181-a65c-b178a8e29886','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37fa-7357-81de-dc3ed34d9546','01a0e6c0-37f2-730c-9ae2-df2731d43334','01a0e6c0-362f-7173-92f2-1955adbd3860','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37fb-703f-a2bb-368906eb1b0e','01a0e6c0-37f2-730c-9ae2-df2731d43334','01a0e6c0-3625-7112-8595-b785a4fea8e6','essential',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37fd-71e1-b115-45fe62092da9','01a0e6c0-37f2-730c-9ae2-df2731d43334','01a0e6c0-362b-7248-95a4-1327d7839264','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3800-7170-a853-9026e87e512b','01a0e6c0-37fe-7155-b77b-620e09b59216','01a0e6c0-3635-7382-9f58-49510a91fc29','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3802-7142-9dd2-16ac1d1ba80b','01a0e6c0-37fe-7155-b77b-620e09b59216','01a0e6c0-3637-73ae-a37e-1f9f4d4c28fc','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3804-7386-bd0a-b3582211cb01','01a0e6c0-37fe-7155-b77b-620e09b59216','01a0e6c0-3638-713b-9670-c7e263ae68df','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3806-71d1-b614-e85f3310dcee','01a0e6c0-37fe-7155-b77b-620e09b59216','01a0e6c0-3649-7386-821b-31bafa74aceb','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3807-73d1-aa1a-3ea128d9e551','01a0e6c0-37fe-7155-b77b-620e09b59216','01a0e6c0-362d-70c2-88ff-89ea9abaea66','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3809-7186-8d25-612e95a16481','01a0e6c0-37fe-7155-b77b-620e09b59216','01a0e6c0-363e-7170-b084-26b0ad23d302','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-380b-7028-ab98-1abce5bf6478','01a0e6c0-37fe-7155-b77b-620e09b59216','01a0e6c0-3625-7112-8595-b785a4fea8e6','essential',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-380e-70b9-baf4-95eaddcf3ac1','01a0e6c0-380c-7381-a8fe-233a0a6de6dc','01a0e6c0-363a-7325-aaf8-9395fd7dd481','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3811-73f6-b0a1-569d4cc96a08','01a0e6c0-380c-7381-a8fe-233a0a6de6dc','01a0e6c0-363c-7096-8d4f-bac06ca77c5c','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3812-706c-9401-ea98b2b743e9','01a0e6c0-380c-7381-a8fe-233a0a6de6dc','01a0e6c0-363e-7170-b084-26b0ad23d302','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3814-7305-9478-465010109da6','01a0e6c0-380c-7381-a8fe-233a0a6de6dc','01a0e6c0-362d-70c2-88ff-89ea9abaea66','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3816-70eb-b7b5-427231540292','01a0e6c0-380c-7381-a8fe-233a0a6de6dc','01a0e6c0-3625-7112-8595-b785a4fea8e6','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3817-72e8-9ed9-227add87f2f9','01a0e6c0-380c-7381-a8fe-233a0a6de6dc','01a0e6c0-3629-7041-b7d0-e6237205e8eb','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-381a-7197-acfa-a3b5e0c56bea','01a0e6c0-3819-7312-a609-c940fabb87e5','01a0e6c0-364e-7095-b3f7-0be41503fbae','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-381c-71b6-98dd-22967fbd2b48','01a0e6c0-3819-7312-a609-c940fabb87e5','01a0e6c0-3650-7125-bd4c-10c429309277','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-381e-731c-807a-b0a0b95e07f7','01a0e6c0-3819-7312-a609-c940fabb87e5','01a0e6c0-363e-7170-b084-26b0ad23d302','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-381f-718e-89e6-89d0f6736e3a','01a0e6c0-3819-7312-a609-c940fabb87e5','01a0e6c0-3623-72fd-9687-58098d44d6af','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3821-7170-9917-b8389bffa5b7','01a0e6c0-3819-7312-a609-c940fabb87e5','01a0e6c0-362b-7248-95a4-1327d7839264','essential',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3823-71c2-9052-66d668918cbb','01a0e6c0-3819-7312-a609-c940fabb87e5','01a0e6c0-3628-730e-9001-25e806b5eec9','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3826-731e-9aa8-33e05b254472','01a0e6c0-3825-73c2-92d9-93de72d89dc0','01a0e6c0-3651-721e-bfba-701473464648','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3828-726b-869f-474652a38610','01a0e6c0-3825-73c2-92d9-93de72d89dc0','01a0e6c0-3625-7112-8595-b785a4fea8e6','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3829-731c-8f38-de5c5799239e','01a0e6c0-3825-73c2-92d9-93de72d89dc0','01a0e6c0-3629-7041-b7d0-e6237205e8eb','essential',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-382b-729b-997e-cb1577307d53','01a0e6c0-3825-73c2-92d9-93de72d89dc0','01a0e6c0-362b-7248-95a4-1327d7839264','essential',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-382c-70b9-a6ec-d8fefb1861d9','01a0e6c0-3825-73c2-92d9-93de72d89dc0','01a0e6c0-3628-730e-9001-25e806b5eec9','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-382f-71a1-a3e8-554ded55ffec','01a0e6c0-382e-7228-8a57-34058e89982f','01a0e6c0-363a-7325-aaf8-9395fd7dd481','essential',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3831-7368-881e-e3cff84e9152','01a0e6c0-382e-7228-8a57-34058e89982f','01a0e6c0-363e-7170-b084-26b0ad23d302','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3832-7189-9a98-56b22bac6d38','01a0e6c0-382e-7228-8a57-34058e89982f','01a0e6c0-3625-7112-8595-b785a4fea8e6','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3834-71e7-b1e2-c8813a17f7b3','01a0e6c0-382e-7228-8a57-34058e89982f','01a0e6c0-364b-70f8-a6aa-b3dc64144859','essential',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3835-737c-9a03-5dbb49bbdade','01a0e6c0-382e-7228-8a57-34058e89982f','01a0e6c0-362d-70c2-88ff-89ea9abaea66','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3837-7338-9544-a7919342c07d','01a0e6c0-382e-7228-8a57-34058e89982f','01a0e6c0-3629-7041-b7d0-e6237205e8eb','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-383b-72e5-a02e-7f691b476115','01a0e6c0-3839-7017-a6af-9744f959e57a','01a0e6c0-3641-72d1-a110-d5d2e2683714','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-383d-7333-a6ae-247c7a6227cd','01a0e6c0-3839-7017-a6af-9744f959e57a','01a0e6c0-363f-70ae-9537-ba00a2ef73ad','essential',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-383f-7327-b0bf-ad63ed9c248f','01a0e6c0-3839-7017-a6af-9744f959e57a','01a0e6c0-3618-7246-b587-ba1ee64d7045','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3840-7258-91c3-938d71ae33f5','01a0e6c0-3839-7017-a6af-9744f959e57a','01a0e6c0-361a-7317-a319-748bf2e5c1a0','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3842-72a0-9c48-9d91c43283d0','01a0e6c0-3839-7017-a6af-9744f959e57a','01a0e6c0-362b-7248-95a4-1327d7839264','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3844-73a7-b1ce-de76ed9d2e19','01a0e6c0-3839-7017-a6af-9744f959e57a','01a0e6c0-3625-7112-8595-b785a4fea8e6','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3847-7217-b285-a5f4d83fb856','01a0e6c0-3845-7274-87e7-b016598e3b08','01a0e6c0-364b-70f8-a6aa-b3dc64144859','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3849-72d1-bb04-5c7a8ef8b46a','01a0e6c0-3845-7274-87e7-b016598e3b08','01a0e6c0-3625-7112-8595-b785a4fea8e6','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-384a-72f9-aca8-3dd127f50d3f','01a0e6c0-3845-7274-87e7-b016598e3b08','01a0e6c0-362b-7248-95a4-1327d7839264','essential',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-384c-73bf-8262-92e5ee8f2c12','01a0e6c0-3845-7274-87e7-b016598e3b08','01a0e6c0-363e-7170-b084-26b0ad23d302','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-384f-72a9-89db-0005fe6cbe94','01a0e6c0-384d-7301-b006-161ecee1fa69','01a0e6c0-3642-7286-8f91-c158e433f1f6','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3851-71f6-9308-c0d2089efd31','01a0e6c0-384d-7301-b006-161ecee1fa69','01a0e6c0-3647-7362-befa-3f2db9a9b263','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3852-73fe-aabf-dad8126c3bd6','01a0e6c0-384d-7301-b006-161ecee1fa69','01a0e6c0-3646-72ba-b9e5-08e61f69c38b','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3854-7345-9121-482ecd34198e','01a0e6c0-384d-7301-b006-161ecee1fa69','01a0e6c0-3625-7112-8595-b785a4fea8e6','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3855-7036-897c-9e6682558f30','01a0e6c0-384d-7301-b006-161ecee1fa69','01a0e6c0-3629-7041-b7d0-e6237205e8eb','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3859-7156-8792-101fd1a14b7f','01a0e6c0-3857-735b-8a15-4937f5748005','01a0e6c0-3649-7386-821b-31bafa74aceb','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-385e-70df-be99-7cd990568434','01a0e6c0-3857-735b-8a15-4937f5748005','01a0e6c0-3637-73ae-a37e-1f9f4d4c28fc','essential',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3860-7211-8de8-02be311b21bf','01a0e6c0-3857-735b-8a15-4937f5748005','01a0e6c0-3625-7112-8595-b785a4fea8e6','essential',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3862-73ea-834b-b1fb86b46695','01a0e6c0-3857-735b-8a15-4937f5748005','01a0e6c0-3635-7382-9f58-49510a91fc29','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32');
/*!40000 ALTER TABLE `career_role_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `career_roles`
--

DROP TABLE IF EXISTS `career_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `career_roles` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `typical_seniority` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avg_salary_range` json DEFAULT NULL,
  `growth_outlook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `career_roles_title_unique` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_roles`
--

LOCK TABLES `career_roles` WRITE;
/*!40000 ALTER TABLE `career_roles` DISABLE KEYS */;
INSERT INTO `career_roles` VALUES ('01a0e6c0-36d7-703e-8789-691b0c5f5275','Data Scientist','Analyze and interpret complex data to help Nepali banks, telecoms (NTC, Ncell), and e-commerce platforms make decisions. Combines statistics, machine learning, and domain expertise.','Mid to Senior','{\"max\": 1920000, \"min\": 480000}','High demand — an emerging field in Nepal with few qualified professionals','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3707-72c0-b872-1687b4b19696','Machine Learning Engineer','Design and build production ML systems for Nepali AI startups, fintechs, and remote international teams. Focus on scaling models, pipelines, and deploying AI solutions.','Mid to Senior','{\"max\": 2400000, \"min\": 600000}','Very high demand — AI and remote roles pay top of the Nepali market','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3730-72b6-b569-e2612458071e','Software Engineer','Design, develop, and maintain software systems for Nepali IT companies, banks, and international clients (outsourcing/remote). Work across the full stack or specialize in backend or frontend.','Entry to Senior','{\"max\": 1800000, \"min\": 384000}','Strong demand — Nepal\'s IT outsourcing and fintech sectors are growing rapidly','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-375b-737b-b57f-7a7a273b9d06','Data Analyst','Collect, process, and analyze data for Nepali banks, MFIs, NGOs/INGOs, and businesses. Create reports and dashboards for stakeholders. Excel and SQL skills are highly valued locally.','Entry to Mid','{\"max\": 960000, \"min\": 288000}','Strong demand — banks, MFIs, and NGOs in Nepal are investing in data roles','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3774-70af-840b-86fdb3602832','Product Manager','Define product vision, strategy, and roadmap for Nepali startups and fintechs (eSewa, Khalti, Fonepay) and software houses. Work cross-functionally with engineering, design, and business teams.','Mid to Senior','{\"max\": 2160000, \"min\": 600000}','High demand — Nepal\'s startup and digital payment ecosystems are expanding','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3784-7352-af2c-910e1e23bfe8','DevOps Engineer','Build and maintain CI/CD pipelines, manage cloud infrastructure, and automate deployments for Nepali software companies, ISPs, and hosting providers.','Mid to Senior','{\"max\": 1920000, \"min\": 540000}','High demand — cloud adoption is accelerating among Nepali enterprises','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-37a2-726b-9410-7ce4aff16bd9','Frontend Developer','Build responsive, performant user interfaces for Nepali web agencies, startups, and remote clients. Work with modern frameworks to create engaging web experiences.','Entry to Senior','{\"max\": 1440000, \"min\": 336000}','Steady demand — most Nepali software houses hire frontend developers regularly','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-37c0-7266-bc2d-f8200abd1ff1','Backend Developer','Design and build server-side logic, APIs, and database integrations. Laravel and Node.js stacks are especially in demand across Nepali software companies.','Entry to Senior','{\"max\": 1560000, \"min\": 336000}','Steady demand — Laravel remains the most requested stack in Nepal','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37d8-719c-a80b-00d5d5d1b51d','Mobile App Developer','Build Android/iOS apps for Nepali fintech, food delivery, and e-wallet startups. Flutter and native Android skills are most in demand locally.','Entry to Senior','{\"max\": 1440000, \"min\": 336000}','High demand — mobile-first products dominate Nepal\'s startup scene','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37e6-7005-9193-d9ca1880c9c9','UI/UX Designer','Design intuitive user interfaces and experiences for Nepali digital products — from banking apps to e-commerce. Translate user research into wireframes and prototypes.','Entry to Senior','{\"max\": 1080000, \"min\": 300000}','Growing demand as Nepali companies invest in digital products','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37f2-730c-9ae2-df2731d43334','QA Engineer','Test software products to ensure quality before release. Manual and automated testing skills are sought by Nepali software houses and outsourcing firms.','Entry to Mid','{\"max\": 960000, \"min\": 288000}','Steady demand — quality processes are becoming standard in Nepali IT companies','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-37fe-7155-b77b-620e09b59216','Digital Marketing Specialist','Run social media, SEO, and paid campaigns for Nepali brands, e-commerce, and tourism businesses. Facebook/Instagram and Google Ads dominate the local landscape.','Entry to Mid','{\"max\": 840000, \"min\": 240000}','High demand — every Nepali business is moving online','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-380c-7381-a8fe-233a0a6de6dc','Accountant','Manage bookkeeping, tax filings (VAT, PAN), and financial reporting for Nepali businesses. Knowledge of NRB regulations and Tally ERP is expected in most local jobs.','Entry to Senior','{\"max\": 720000, \"min\": 264000}','Steady demand — every registered business in Nepal needs accounting staff','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3819-7312-a609-c940fabb87e5','Civil Engineer','Plan and supervise construction of roads, hydropower projects, and buildings. Nepal\'s infrastructure boom and hydropower sector offer strong opportunities.','Entry to Senior','{\"max\": 1200000, \"min\": 300000}','Steady demand — driven by hydropower, road, and urban development projects','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3825-73c2-92d9-93de72d89dc0','Registered Nurse','Provide patient care in hospitals, clinics, and community health posts. Nursing remains one of the most stable careers in Nepal, with strong demand at home and abroad.','Entry to Senior','{\"max\": 600000, \"min\": 192000}','High demand — chronic shortage of nurses in Nepali hospitals and abroad','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-382e-7228-8a57-34058e89982f','Bank Officer','Work in commercial banks or microfinance institutions handling credit, operations, or customer relations. Banking remains one of the most sought-after careers in Nepal.','Entry to Senior','{\"max\": 1200000, \"min\": 360000}','Steady demand — Nepal\'s banking and microfinance sectors keep expanding','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3839-7017-a6af-9744f959e57a','Network Engineer','Design, install, and maintain network infrastructure for ISPs, telecoms, banks, and data centers. Networking certifications (CCNA/CCNP) are highly valued in Nepal.','Entry to Senior','{\"max\": 1200000, \"min\": 300000}','Steady demand — internet penetration and data centers are growing in Nepal','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3845-7274-87e7-b016598e3b08','Customer Service Representative','Handle customer queries for banks, telecoms, and BPO/KPO call centers serving international clients. Strong English and Nepali communication is essential.','Entry to Mid','{\"max\": 480000, \"min\": 216000}','Growing demand — Nepal\'s BPO industry is expanding with international clients','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-384d-7301-b006-161ecee1fa69','Graphic Designer','Create visual assets for brands, agencies, and digital platforms — logos, social media creatives, print materials. Freelancing and agency work are common in Nepal.','Entry to Mid','{\"max\": 720000, \"min\": 216000}','Steady demand — advertising and social media marketing keep growing','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3857-735b-8a15-4937f5748005','Content Writer','Write blogs, website copy, and marketing content for Nepali businesses and international clients. English fluency and SEO knowledge open remote work opportunities.','Entry to Mid','{\"max\": 600000, \"min\": 192000}','Growing demand — content marketing and remote freelancing are on the rise','2026-09-28 06:42:32','2026-09-28 06:42:32');
/*!40000 ALTER TABLE `career_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `education`
--

DROP TABLE IF EXISTS `education`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `education` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `institution` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `degree` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_of_study` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `education_profile_id_foreign` (`profile_id`),
  CONSTRAINT `education_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `education`
--

LOCK TABLES `education` WRITE;
/*!40000 ALTER TABLE `education` DISABLE KEYS */;
/*!40000 ALTER TABLE `education` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gap_assessment_items`
--

DROP TABLE IF EXISTS `gap_assessment_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gap_assessment_items` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gap_assessment_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `skill_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `required_level` smallint unsigned NOT NULL DEFAULT '1',
  `current_level` smallint unsigned NOT NULL DEFAULT '0',
  `gap_size` smallint NOT NULL DEFAULT '0',
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'moderate',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gap_assessment_items_gap_assessment_id_foreign` (`gap_assessment_id`),
  KEY `gap_assessment_items_skill_id_foreign` (`skill_id`),
  CONSTRAINT `gap_assessment_items_gap_assessment_id_foreign` FOREIGN KEY (`gap_assessment_id`) REFERENCES `gap_assessments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gap_assessment_items_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gap_assessment_items`
--

LOCK TABLES `gap_assessment_items` WRITE;
/*!40000 ALTER TABLE `gap_assessment_items` DISABLE KEYS */;
INSERT INTO `gap_assessment_items` VALUES ('01a0e6c1-da9f-7147-a1cc-8c70a0016a67','01a0e6c1-da9c-70ff-a743-b3a7e655e282','01a0e6c0-35f5-7390-bcae-48a1244683dd',4,3,1,'high','2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-daa4-70ec-990d-89a6e8bb2d2a','01a0e6c1-da9c-70ff-a743-b3a7e655e282','01a0e6c0-35f9-702c-9038-32b8badb545d',3,0,3,'high','2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-daa8-737d-9eb0-d5647239ab26','01a0e6c1-da9c-70ff-a743-b3a7e655e282','01a0e6c0-35fb-70d6-bb8f-47578366fd4c',3,0,3,'high','2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-daac-713a-bae7-00138115bd22','01a0e6c1-da9c-70ff-a743-b3a7e655e282','01a0e6c0-3629-7041-b7d0-e6237205e8eb',3,0,3,'high','2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-daaf-707c-8983-92de44f37f06','01a0e6c1-da9c-70ff-a743-b3a7e655e282','01a0e6c0-362d-70c2-88ff-89ea9abaea66',4,0,4,'high','2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-dab3-7381-915a-af4ffb0c88ac','01a0e6c1-da9c-70ff-a743-b3a7e655e282','01a0e6c0-363e-7170-b084-26b0ad23d302',4,0,4,'high','2026-09-28 06:44:19','2026-09-28 06:44:19');
/*!40000 ALTER TABLE `gap_assessment_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gap_assessments`
--

DROP TABLE IF EXISTS `gap_assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gap_assessments` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_role_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_job_posting_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `overall_readiness_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `skills_fingerprint` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gap_assessments_profile_id_foreign` (`profile_id`),
  KEY `gap_assessments_target_role_id_foreign` (`target_role_id`),
  KEY `gap_assessments_target_job_posting_id_foreign` (`target_job_posting_id`),
  CONSTRAINT `gap_assessments_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gap_assessments_target_job_posting_id_foreign` FOREIGN KEY (`target_job_posting_id`) REFERENCES `job_postings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `gap_assessments_target_role_id_foreign` FOREIGN KEY (`target_role_id`) REFERENCES `career_roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gap_assessments`
--

LOCK TABLES `gap_assessments` WRITE;
/*!40000 ALTER TABLE `gap_assessments` DISABLE KEYS */;
INSERT INTO `gap_assessments` VALUES ('01a0e6c1-da9c-70ff-a743-b3a7e655e282','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-375b-737b-b57f-7a7a273b9d06',NULL,40.00,'8d0ab4f247eb497875afaea9210d8a66','2026-09-28 06:44:19','2026-09-28 06:44:19');
/*!40000 ALTER TABLE `gap_assessments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gap_recommendations`
--

DROP TABLE IF EXISTS `gap_recommendations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gap_recommendations` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gap_assessment_item_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `learning_resource_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rank` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gap_recommendations_gap_assessment_item_id_foreign` (`gap_assessment_item_id`),
  KEY `gap_recommendations_learning_resource_id_foreign` (`learning_resource_id`),
  CONSTRAINT `gap_recommendations_gap_assessment_item_id_foreign` FOREIGN KEY (`gap_assessment_item_id`) REFERENCES `gap_assessment_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gap_recommendations_learning_resource_id_foreign` FOREIGN KEY (`learning_resource_id`) REFERENCES `learning_resources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gap_recommendations`
--

LOCK TABLES `gap_recommendations` WRITE;
/*!40000 ALTER TABLE `gap_recommendations` DISABLE KEYS */;
INSERT INTO `gap_recommendations` VALUES ('01a0e6c1-dabb-724c-98cf-5c0d8a4c42db','01a0e6c1-da9f-7147-a1cc-8c70a0016a67','01a0e6c0-3934-7262-9ab1-30795971bc10',1,'2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-dabe-731d-a324-b44d84ddfe53','01a0e6c1-daa4-70ec-990d-89a6e8bb2d2a','01a0e6c0-393b-73cc-9b5e-cc0638dd20e0',1,'2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-dac1-72c1-9cf1-be03e6a6db1c','01a0e6c1-daa4-70ec-990d-89a6e8bb2d2a','01a0e6c0-38e9-73d3-8a91-15f2bbeefe3a',2,'2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-dac7-71a3-ac13-1ac6bb427907','01a0e6c1-daa8-737d-9eb0-d5647239ab26','01a0e6c0-3904-7085-bccb-05a4cb918ba6',1,'2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-dacb-703e-b5d0-1bb72bd9183c','01a0e6c1-daac-713a-bae7-00138115bd22','01a0e6c0-392e-7179-89fe-01ca489110d8',1,'2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-dacf-70b5-b315-2a31f888c322','01a0e6c1-daaf-707c-8983-92de44f37f06','01a0e6c0-3934-7262-9ab1-30795971bc10',1,'2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-dad1-70e6-a86e-039c76b2994a','01a0e6c1-daaf-707c-8983-92de44f37f06','01a0e6c0-3904-7085-bccb-05a4cb918ba6',2,'2026-09-28 06:44:19','2026-09-28 06:44:19');
/*!40000 ALTER TABLE `gap_recommendations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_postings`
--

DROP TABLE IF EXISTS `job_postings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_postings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal',
  `external_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raw_description` text COLLATE utf8mb4_unicode_ci,
  `seniority_level` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_postings`
--

LOCK TABLES `job_postings` WRITE;
/*!40000 ALTER TABLE `job_postings` DISABLE KEYS */;
INSERT INTO `job_postings` VALUES ('01a0e6c0-394a-722d-b602-fdec52fbac50','internal',NULL,'Senior Laravel Developer','Leapfrog Technology','Build and scale web platforms for international clients. Strong Laravel and API experience required; remote-friendly hybrid work from our Durbarmarg office.','Senior','Kathmandu, Nepal','2026-09-26 06:42:32','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-395d-734e-adad-f8281df80466','internal',NULL,'Machine Learning Engineer','Fusemachines','Design and deploy production ML pipelines for global AI products. Work with a world-class AI team from our Kathmandu office.','Mid to Senior','Kathmandu, Nepal','2026-09-23 06:42:32','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-396b-734d-9daf-f3996f8900c6','internal',NULL,'Full-Stack Developer (Laravel + Vue)','F1Soft International','Develop features for Nepal\'s leading fintech products including e-banking and digital wallet platforms.','Mid','Lalitpur, Nepal','2026-09-27 06:42:32','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-397a-730d-b515-4bf48e58ea96','internal',NULL,'Flutter Developer','Khalti Digital Wallet','Build and improve Nepal\'s most-used digital wallet app. Experience with payment integrations and REST APIs preferred.','Mid','Kathmandu, Nepal','2026-09-25 06:42:32','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3985-72ca-b77c-9b039e037910','internal',NULL,'DevOps Engineer','CloudFactory','Own CI/CD pipelines and cloud infrastructure for AI data-processing workloads. AWS and Kubernetes experience essential.','Senior','Kathmandu, Nepal (Hybrid)','2026-09-21 06:42:32','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3991-7363-824d-5f7583d2f99a','internal',NULL,'Frontend Developer (React)','Cotiviti Nepal','Create polished healthcare-analytics dashboards with React and TypeScript. Great opportunity for fresh graduates with strong fundamentals.','Entry','Kathmandu, Nepal','2026-09-22 06:42:32','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-399b-73e6-bb51-2223ea2ac6ed','internal',NULL,'Data Scientist','Deerwalk Institute','Analyze healthcare data for US clients; build predictive models and dashboards. Healthcare data experience is a plus.','Mid','Kathmandu, Nepal','2026-09-20 06:42:32','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39a5-7383-a676-5050a9eef6a6','internal',NULL,'QA Engineer','Young Innovations','Perform manual and automated testing for web and mobile products. Freshers with ISTQB certification encouraged to apply.','Entry to Mid','Lalitpur, Nepal','2026-09-19 06:42:32','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39af-72fd-a0eb-99ce866af2b4','internal',NULL,'Digital Marketing Officer','Daraz Nepal','Plan and run social media and performance campaigns for Nepal\'s largest e-commerce platform during campaign seasons like Dashain-Tihar sales.','Entry to Mid','Kathmandu, Nepal','2026-09-24 06:42:32','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39b7-7154-a5ac-8f9075570599','internal',NULL,'Junior Accountant','NIC Asia Bank','Assist in daily bookkeeping, VAT/PAN filings, and reconciliation. Proficiency in Tally ERP and MS Excel required.','Entry','Kathmandu, Nepal','2026-09-18 06:42:32','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39be-73b3-99b4-0850f59e106a','internal',NULL,'Customer Service Representative','WorldLink Communications','Handle customer queries for Nepal\'s largest ISP via phone and chat. Fluent Nepali and English communication required.','Entry','Lalitpur, Nepal','2026-09-17 06:42:32','2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39c5-73fe-8353-90f9d89d5461','internal',NULL,'Business Data Analyst','Nabil Bank','Analyze banking operations data and build MIS dashboards for management. Banking domain knowledge preferred.','Mid','Kathmandu, Nepal','2026-09-16 06:42:32','2026-09-28 06:42:32','2026-09-28 06:42:32');
/*!40000 ALTER TABLE `job_postings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_required_skills`
--

DROP TABLE IF EXISTS `job_required_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_required_skills` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_posting_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `skill_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `importance` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'required',
  `min_proficiency` smallint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `job_required_skills_job_posting_id_skill_id_unique` (`job_posting_id`,`skill_id`),
  KEY `job_required_skills_skill_id_foreign` (`skill_id`),
  CONSTRAINT `job_required_skills_job_posting_id_foreign` FOREIGN KEY (`job_posting_id`) REFERENCES `job_postings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_required_skills_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_required_skills`
--

LOCK TABLES `job_required_skills` WRITE;
/*!40000 ALTER TABLE `job_required_skills` DISABLE KEYS */;
INSERT INTO `job_required_skills` VALUES ('01a0e6c0-394c-709a-8750-9d1f324652cc','01a0e6c0-394a-722d-b602-fdec52fbac50','01a0e6c0-3613-7054-9390-bf62b06dae57','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-394e-730a-b8bc-4be5975dac72','01a0e6c0-394a-722d-b602-fdec52fbac50','01a0e6c0-3616-73ea-8ae0-976dabeab294','required',5,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3950-7306-b7f0-b824a4acd68f','01a0e6c0-394a-722d-b602-fdec52fbac50','01a0e6c0-35f5-7390-bcae-48a1244683dd','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3952-7329-ba01-e72360d70e83','01a0e6c0-394a-722d-b602-fdec52fbac50','01a0e6c0-362f-7173-92f2-1955adbd3860','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3955-7217-b98f-97e3bd86c863','01a0e6c0-394a-722d-b602-fdec52fbac50','01a0e6c0-361d-71f5-98ad-4b6dbd2d5d13','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3959-724a-bc89-36415b181088','01a0e6c0-394a-722d-b602-fdec52fbac50','01a0e6c0-361a-7317-a319-748bf2e5c1a0','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-395b-7183-aebf-20096042084b','01a0e6c0-394a-722d-b602-fdec52fbac50','01a0e6c0-3618-7246-b587-ba1ee64d7045','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-395f-71bf-80d0-a05328424015','01a0e6c0-395d-734e-adad-f8281df80466','01a0e6c0-35f2-7181-a65c-b178a8e29886','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3960-709e-9777-0f24dc53c979','01a0e6c0-395d-734e-adad-f8281df80466','01a0e6c0-35f7-7158-ac90-fa87bba4699c','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3962-7248-bb1d-0d7c63011b45','01a0e6c0-395d-734e-adad-f8281df80466','01a0e6c0-35fd-7099-9c0f-acfa9672940e','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3964-72af-aadf-d682277ec672','01a0e6c0-395d-734e-adad-f8281df80466','01a0e6c0-361a-7317-a319-748bf2e5c1a0','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3966-72ec-ac2d-4f7ec4ff87ad','01a0e6c0-395d-734e-adad-f8281df80466','01a0e6c0-3618-7246-b587-ba1ee64d7045','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3967-73ad-9dc0-edf315008075','01a0e6c0-395d-734e-adad-f8281df80466','01a0e6c0-35f9-702c-9038-32b8badb545d','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3969-72f8-a112-6515c5f3b233','01a0e6c0-395d-734e-adad-f8281df80466','01a0e6c0-361d-71f5-98ad-4b6dbd2d5d13','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-396d-7186-a9fd-c00e41a7db2b','01a0e6c0-396b-734d-9daf-f3996f8900c6','01a0e6c0-3613-7054-9390-bf62b06dae57','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-396e-71a5-acff-2fa9ef747dcf','01a0e6c0-396b-734d-9daf-f3996f8900c6','01a0e6c0-3616-73ea-8ae0-976dabeab294','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3970-7068-917f-e47a42aec86e','01a0e6c0-396b-734d-9daf-f3996f8900c6','01a0e6c0-3608-708d-af8f-f7c58115f235','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3973-73b0-8287-47caa150eca7','01a0e6c0-396b-734d-9daf-f3996f8900c6','01a0e6c0-360e-7048-af3d-e7191ff95094','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3975-72b2-a84d-d5123f71c2ed','01a0e6c0-396b-734d-9daf-f3996f8900c6','01a0e6c0-35f5-7390-bcae-48a1244683dd','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3977-738b-93bf-b35acdecdb91','01a0e6c0-396b-734d-9daf-f3996f8900c6','01a0e6c0-362f-7173-92f2-1955adbd3860','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3978-701c-b1e3-ea71f2ff7802','01a0e6c0-396b-734d-9daf-f3996f8900c6','01a0e6c0-361d-71f5-98ad-4b6dbd2d5d13','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-397c-73a7-b143-3b4e05f59fca','01a0e6c0-397a-730d-b515-4bf48e58ea96','01a0e6c0-3630-7315-bc70-5ad68ca4a347','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-397d-701c-acab-406e032f4257','01a0e6c0-397a-730d-b515-4bf48e58ea96','01a0e6c0-3632-7156-bc8b-2a552f38cb3c','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-397f-71cd-ac1c-7c29ccedfc3a','01a0e6c0-397a-730d-b515-4bf48e58ea96','01a0e6c0-362f-7173-92f2-1955adbd3860','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3980-73ae-a68a-536bcca5480c','01a0e6c0-397a-730d-b515-4bf48e58ea96','01a0e6c0-361d-71f5-98ad-4b6dbd2d5d13','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3982-738f-84c6-4b020344c805','01a0e6c0-397a-730d-b515-4bf48e58ea96','01a0e6c0-3608-708d-af8f-f7c58115f235','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3983-7017-9476-75e5ee246a7e','01a0e6c0-397a-730d-b515-4bf48e58ea96','01a0e6c0-362b-7248-95a4-1327d7839264','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3987-71ed-b86f-5ac566222cee','01a0e6c0-3985-72ca-b77c-9b039e037910','01a0e6c0-3618-7246-b587-ba1ee64d7045','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3988-71fc-a7f5-b3d01375f554','01a0e6c0-3985-72ca-b77c-9b039e037910','01a0e6c0-361a-7317-a319-748bf2e5c1a0','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-398a-717b-a042-4bd6110deed0','01a0e6c0-3985-72ca-b77c-9b039e037910','01a0e6c0-361b-73c7-b9da-2a14aa5b2d53','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-398b-700d-a77e-d5e5bd3a4a0e','01a0e6c0-3985-72ca-b77c-9b039e037910','01a0e6c0-3620-72a7-8760-86d16086540e','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-398d-73ca-82ef-a931e1e1c67a','01a0e6c0-3985-72ca-b77c-9b039e037910','01a0e6c0-361d-71f5-98ad-4b6dbd2d5d13','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-398e-7337-8d79-74ffedd6c1cc','01a0e6c0-3985-72ca-b77c-9b039e037910','01a0e6c0-3641-72d1-a110-d5d2e2683714','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-398f-71db-9fac-6bc293375969','01a0e6c0-3985-72ca-b77c-9b039e037910','01a0e6c0-35f2-7181-a65c-b178a8e29886','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3992-726b-8388-95ef1c5c2018','01a0e6c0-3991-7363-824d-5f7583d2f99a','01a0e6c0-3608-708d-af8f-f7c58115f235','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3994-7345-b0a3-978cd151803a','01a0e6c0-3991-7363-824d-5f7583d2f99a','01a0e6c0-360c-7298-8ff4-331995ad00ce','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3995-725f-ba95-6f6d23f88f49','01a0e6c0-3991-7363-824d-5f7583d2f99a','01a0e6c0-360a-71ff-bfb5-990002c37f1b','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3997-7210-9294-06ad815ca492','01a0e6c0-3991-7363-824d-5f7583d2f99a','01a0e6c0-361d-71f5-98ad-4b6dbd2d5d13','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3998-734b-979f-60f1afa17ad7','01a0e6c0-3991-7363-824d-5f7583d2f99a','01a0e6c0-3644-732b-ace8-88fba8d7ff79','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-399a-72c1-bd99-a8b96cac50d0','01a0e6c0-3991-7363-824d-5f7583d2f99a','01a0e6c0-362f-7173-92f2-1955adbd3860','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-399d-7167-b9cb-85e603abc5b3','01a0e6c0-399b-73e6-bb51-2223ea2ac6ed','01a0e6c0-35f2-7181-a65c-b178a8e29886','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-399e-7311-802c-86535447660d','01a0e6c0-399b-73e6-bb51-2223ea2ac6ed','01a0e6c0-35f7-7158-ac90-fa87bba4699c','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39a0-722d-8ae1-0d544eda46a2','01a0e6c0-399b-73e6-bb51-2223ea2ac6ed','01a0e6c0-35f5-7390-bcae-48a1244683dd','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39a1-7103-9add-0da555b52781','01a0e6c0-399b-73e6-bb51-2223ea2ac6ed','01a0e6c0-35f9-702c-9038-32b8badb545d','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39a3-710b-8a07-8480c5109306','01a0e6c0-399b-73e6-bb51-2223ea2ac6ed','01a0e6c0-35fb-70d6-bb8f-47578366fd4c','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39a4-7275-8cb7-94aef0cba4d9','01a0e6c0-399b-73e6-bb51-2223ea2ac6ed','01a0e6c0-363e-7170-b084-26b0ad23d302','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39a7-739d-ba4f-a8f695ab7d5f','01a0e6c0-39a5-7383-a676-5050a9eef6a6','01a0e6c0-364d-70a2-8a0b-e1017b0bf56b','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39a8-726f-bb62-ac9622afcfb3','01a0e6c0-39a5-7383-a676-5050a9eef6a6','01a0e6c0-3621-7326-bb47-443fc3ade530','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39aa-71a3-81bb-f098f8499db9','01a0e6c0-39a5-7383-a676-5050a9eef6a6','01a0e6c0-35f5-7390-bcae-48a1244683dd','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39ab-7035-9339-a350bd26de46','01a0e6c0-39a5-7383-a676-5050a9eef6a6','01a0e6c0-362f-7173-92f2-1955adbd3860','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39ad-72f9-a882-e56f86cf7047','01a0e6c0-39a5-7383-a676-5050a9eef6a6','01a0e6c0-3625-7112-8595-b785a4fea8e6','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39b0-72b3-9614-c74be83bde6b','01a0e6c0-39af-72fd-a0eb-99ce866af2b4','01a0e6c0-3635-7382-9f58-49510a91fc29','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39b2-736e-9ef6-32c6c24d5742','01a0e6c0-39af-72fd-a0eb-99ce866af2b4','01a0e6c0-3638-713b-9670-c7e263ae68df','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39b3-705b-995f-5722c2ca4f4a','01a0e6c0-39af-72fd-a0eb-99ce866af2b4','01a0e6c0-3637-73ae-a37e-1f9f4d4c28fc','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39b4-736d-8f2b-36110c1c5d90','01a0e6c0-39af-72fd-a0eb-99ce866af2b4','01a0e6c0-362d-70c2-88ff-89ea9abaea66','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39b6-7253-ac5a-b71549e8464d','01a0e6c0-39af-72fd-a0eb-99ce866af2b4','01a0e6c0-3649-7386-821b-31bafa74aceb','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39b9-71c9-8ae2-9ea6a3389cac','01a0e6c0-39b7-7154-a5ac-8f9075570599','01a0e6c0-363a-7325-aaf8-9395fd7dd481','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39ba-7244-b915-4e330455881a','01a0e6c0-39b7-7154-a5ac-8f9075570599','01a0e6c0-363c-7096-8d4f-bac06ca77c5c','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39bb-71d3-a9a7-46df60bc0e52','01a0e6c0-39b7-7154-a5ac-8f9075570599','01a0e6c0-363e-7170-b084-26b0ad23d302','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39bd-7182-8752-c7675defa21f','01a0e6c0-39b7-7154-a5ac-8f9075570599','01a0e6c0-3625-7112-8595-b785a4fea8e6','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39c0-7144-8475-7520e61136f0','01a0e6c0-39be-73b3-99b4-0850f59e106a','01a0e6c0-364b-70f8-a6aa-b3dc64144859','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39c1-72f0-9e46-059bf86c3ab0','01a0e6c0-39be-73b3-99b4-0850f59e106a','01a0e6c0-3625-7112-8595-b785a4fea8e6','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39c3-71ae-98fa-0b09a7d746b9','01a0e6c0-39be-73b3-99b4-0850f59e106a','01a0e6c0-362b-7248-95a4-1327d7839264','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39c4-73f7-945c-85705cbe283b','01a0e6c0-39be-73b3-99b4-0850f59e106a','01a0e6c0-363e-7170-b084-26b0ad23d302','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39c7-7160-8e28-00e51686b508','01a0e6c0-39c5-73fe-8353-90f9d89d5461','01a0e6c0-35f5-7390-bcae-48a1244683dd','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39c8-72a7-bf37-bfacb43f6ab1','01a0e6c0-39c5-73fe-8353-90f9d89d5461','01a0e6c0-362d-70c2-88ff-89ea9abaea66','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39ca-714c-aa57-f5c066a0cf00','01a0e6c0-39c5-73fe-8353-90f9d89d5461','01a0e6c0-363e-7170-b084-26b0ad23d302','required',4,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39cb-7174-bd70-4b645f91387c','01a0e6c0-39c5-73fe-8353-90f9d89d5461','01a0e6c0-35fb-70d6-bb8f-47578366fd4c','required',3,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-39cd-72f1-a2cf-84c179006bbf','01a0e6c0-39c5-73fe-8353-90f9d89d5461','01a0e6c0-35f9-702c-9038-32b8badb545d','preferred',3,'2026-09-28 06:42:32','2026-09-28 06:42:32');
/*!40000 ALTER TABLE `job_required_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `learning_resource_skills`
--

DROP TABLE IF EXISTS `learning_resource_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `learning_resource_skills` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `learning_resource_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `skill_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `learning_resource_skills_learning_resource_id_skill_id_unique` (`learning_resource_id`,`skill_id`),
  KEY `learning_resource_skills_skill_id_foreign` (`skill_id`),
  CONSTRAINT `learning_resource_skills_learning_resource_id_foreign` FOREIGN KEY (`learning_resource_id`) REFERENCES `learning_resources` (`id`) ON DELETE CASCADE,
  CONSTRAINT `learning_resource_skills_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `learning_resource_skills`
--

LOCK TABLES `learning_resource_skills` WRITE;
/*!40000 ALTER TABLE `learning_resource_skills` DISABLE KEYS */;
INSERT INTO `learning_resource_skills` VALUES ('01a0e6c0-38d7-739d-994c-8b5a1aed8b2e','01a0e6c0-38cf-717b-8083-06479a7096e9','01a0e6c0-3613-7054-9390-bf62b06dae57',NULL,NULL),('01a0e6c0-38dd-716e-a562-8d57da077d60','01a0e6c0-38da-72fe-8343-1e424c069b95','01a0e6c0-3616-73ea-8ae0-976dabeab294',NULL,NULL),('01a0e6c0-38df-7121-b8f4-3f9039e40937','01a0e6c0-38da-72fe-8343-1e424c069b95','01a0e6c0-3613-7054-9390-bf62b06dae57',NULL,NULL),('01a0e6c0-38e4-70b7-a110-708cb36892d2','01a0e6c0-38e1-7335-bb09-043ae587dd89','01a0e6c0-3608-708d-af8f-f7c58115f235',NULL,NULL),('01a0e6c0-38e6-727d-8f55-0a0c50d043fd','01a0e6c0-38e1-7335-bb09-043ae587dd89','01a0e6c0-360c-7298-8ff4-331995ad00ce',NULL,NULL),('01a0e6c0-38e8-7244-b30b-b16cac5b6364','01a0e6c0-38e1-7335-bb09-043ae587dd89','01a0e6c0-360a-71ff-bfb5-990002c37f1b',NULL,NULL),('01a0e6c0-38ec-73a7-b949-bc19fb476fa4','01a0e6c0-38e9-73d3-8a91-15f2bbeefe3a','01a0e6c0-35f7-7158-ac90-fa87bba4699c',NULL,NULL),('01a0e6c0-38ed-72d4-a119-bcaf9c918d09','01a0e6c0-38e9-73d3-8a91-15f2bbeefe3a','01a0e6c0-35f2-7181-a65c-b178a8e29886',NULL,NULL),('01a0e6c0-38ee-725a-9cad-33df11604208','01a0e6c0-38e9-73d3-8a91-15f2bbeefe3a','01a0e6c0-35f9-702c-9038-32b8badb545d',NULL,NULL),('01a0e6c0-38f3-71c0-ae32-c0f1ac185e01','01a0e6c0-38f0-71a9-8c3f-dc96e6c0b226','01a0e6c0-35fd-7099-9c0f-acfa9672940e',NULL,NULL),('01a0e6c0-38f4-73ae-a652-1bcb13cd0e4c','01a0e6c0-38f0-71a9-8c3f-dc96e6c0b226','01a0e6c0-35f7-7158-ac90-fa87bba4699c',NULL,NULL),('01a0e6c0-38f6-7261-b324-fc8cbd5dfc37','01a0e6c0-38f0-71a9-8c3f-dc96e6c0b226','01a0e6c0-35f2-7181-a65c-b178a8e29886',NULL,NULL),('01a0e6c0-38fa-736c-ba3b-6272624b8925','01a0e6c0-38f8-73dd-8954-45ff0efb6f6f','01a0e6c0-35f2-7181-a65c-b178a8e29886',NULL,NULL),('01a0e6c0-38fe-70ed-a064-9e6a83ee254c','01a0e6c0-38fc-71b3-9832-b031d8225c7b','01a0e6c0-361a-7317-a319-748bf2e5c1a0',NULL,NULL),('01a0e6c0-3902-7215-9c8c-7c40d5bef957','01a0e6c0-3900-70e0-b97e-e527833804e0','01a0e6c0-362b-7248-95a4-1327d7839264',NULL,NULL),('01a0e6c0-3907-7300-ae49-e14d38ccadf9','01a0e6c0-3904-7085-bccb-05a4cb918ba6','01a0e6c0-362d-70c2-88ff-89ea9abaea66',NULL,NULL),('01a0e6c0-3909-7076-8214-570d53e30d67','01a0e6c0-3904-7085-bccb-05a4cb918ba6','01a0e6c0-35fb-70d6-bb8f-47578366fd4c',NULL,NULL),('01a0e6c0-390f-70a9-8d91-e4347cefc6d8','01a0e6c0-390b-7300-92f4-78cf1002ee66','01a0e6c0-3601-70ed-90f3-aa6ba1b2474e',NULL,NULL),('01a0e6c0-3911-7094-9176-1a0d3cbbd635','01a0e6c0-390b-7300-92f4-78cf1002ee66','01a0e6c0-35f2-7181-a65c-b178a8e29886',NULL,NULL),('01a0e6c0-3915-7276-8acb-0d8c67dc85ee','01a0e6c0-3913-7325-8e15-46e9baac7c0d','01a0e6c0-3618-7246-b587-ba1ee64d7045',NULL,NULL),('01a0e6c0-3916-7062-972f-eea407ec8d33','01a0e6c0-3913-7325-8e15-46e9baac7c0d','01a0e6c0-3620-72a7-8760-86d16086540e',NULL,NULL),('01a0e6c0-391c-70f6-8bd1-fd728b4c439a','01a0e6c0-3919-715a-9717-e546cd9952aa','01a0e6c0-3618-7246-b587-ba1ee64d7045',NULL,NULL),('01a0e6c0-391e-7286-b3db-e19564ea89a7','01a0e6c0-3919-715a-9717-e546cd9952aa','01a0e6c0-361a-7317-a319-748bf2e5c1a0',NULL,NULL),('01a0e6c0-3920-7185-a40b-fecfd44acc80','01a0e6c0-3919-715a-9717-e546cd9952aa','01a0e6c0-361b-73c7-b9da-2a14aa5b2d53',NULL,NULL),('01a0e6c0-3925-73f6-8233-f8429061af1b','01a0e6c0-3922-723b-96b3-4fca461f5887','01a0e6c0-3621-7326-bb47-443fc3ade530',NULL,NULL),('01a0e6c0-3926-70d7-b2f4-87645d047bc4','01a0e6c0-3922-723b-96b3-4fca461f5887','01a0e6c0-3623-72fd-9687-58098d44d6af',NULL,NULL),('01a0e6c0-392a-73ec-ac17-6cfd7fa1cbcf','01a0e6c0-3928-72e2-99f6-389e0f3b00ba','01a0e6c0-35fe-7024-80d2-402bf66f684e',NULL,NULL),('01a0e6c0-392c-73ee-9063-221d3fb5af51','01a0e6c0-3928-72e2-99f6-389e0f3b00ba','01a0e6c0-35f2-7181-a65c-b178a8e29886',NULL,NULL),('01a0e6c0-3931-7379-87e1-262e54e8ac5f','01a0e6c0-392e-7179-89fe-01ca489110d8','01a0e6c0-3625-7112-8595-b785a4fea8e6',NULL,NULL),('01a0e6c0-3932-737a-b280-f05a5112e1ae','01a0e6c0-392e-7179-89fe-01ca489110d8','01a0e6c0-3629-7041-b7d0-e6237205e8eb',NULL,NULL),('01a0e6c0-3937-7283-9c3c-b13e400aa2dc','01a0e6c0-3934-7262-9ab1-30795971bc10','01a0e6c0-362d-70c2-88ff-89ea9abaea66',NULL,NULL),('01a0e6c0-3938-7109-afe4-2d3b8f5e3628','01a0e6c0-3934-7262-9ab1-30795971bc10','01a0e6c0-35f5-7390-bcae-48a1244683dd',NULL,NULL),('01a0e6c0-393d-73c9-8e60-9e15c3a149b6','01a0e6c0-393b-73cc-9b5e-cc0638dd20e0','01a0e6c0-3603-71dd-84e1-653e4a6d5345',NULL,NULL),('01a0e6c0-393e-7293-b11c-00352fd06ea4','01a0e6c0-393b-73cc-9b5e-cc0638dd20e0','01a0e6c0-35f9-702c-9038-32b8badb545d',NULL,NULL),('01a0e6c0-3942-717d-8401-291154b11cff','01a0e6c0-3940-70e1-b1db-3d347056ccb0','01a0e6c0-3608-708d-af8f-f7c58115f235',NULL,NULL),('01a0e6c0-3944-705f-b77c-c790e29a3737','01a0e6c0-3940-70e1-b1db-3d347056ccb0','01a0e6c0-3611-70aa-87da-1c05e53cb832',NULL,NULL);
/*!40000 ALTER TABLE `learning_resource_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `learning_resources`
--

DROP TABLE IF EXISTS `learning_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `learning_resources` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'course',
  `duration_hours` decimal(8,2) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `learning_resources`
--

LOCK TABLES `learning_resources` WRITE;
/*!40000 ALTER TABLE `learning_resources` DISABLE KEYS */;
INSERT INTO `learning_resources` VALUES ('01a0e6c0-38cf-717b-8083-06479a7096e9','PHP for Beginners','Laracasts','https://laracasts.com/series/php-for-beginners','course',10.00,0.00,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38da-72fe-8343-1e424c069b95','Laravel Bootcamp','Laravel','https://bootcamp.laravel.com/','course',12.00,0.00,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38e1-7335-bb09-043ae587dd89','Full-Stack React with TypeScript','Udemy','https://www.udemy.com/','course',25.00,49.99,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38e9-73d3-8a91-15f2bbeefe3a','Machine Learning Specialization','Coursera','https://www.coursera.org/','course',40.00,0.00,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38f0-71a9-8c3f-dc96e6c0b226','Deep Learning Fundamentals','DeepLearning.AI','https://www.deeplearning.ai/','course',20.00,49.00,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38f8-73dd-8954-45ff0efb6f6f','The Python Tutorial','Python.org','https://docs.python.org/3/tutorial/','article',6.00,0.00,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-38fc-71b3-9832-b031d8225c7b','Docker for Developers','Dev.to','https://dev.to/t/docker','article',3.00,0.00,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3900-70e0-b97e-e527833804e0','Clean Code: A Handbook of Agile Software Craftsmanship','Robert C. Martin',NULL,'book',8.00,35.00,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3904-7085-bccb-05a4cb918ba6','Data Visualization: A Practical Introduction','Kieran Healy','https://socviz.co/','book',10.00,0.00,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-390b-7300-92f4-78cf1002ee66','Intro to Computer Vision with Python','YouTube','https://www.youtube.com/results?search_query=computer+vision+python','video',5.00,0.00,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3913-7325-8e15-46e9baac7c0d','AWS Certified Solutions Architect','AWS','https://aws.amazon.com/certification/','certification',35.00,150.00,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3919-715a-9717-e546cd9952aa','Google Cloud Associate Cloud Engineer','Google Cloud','https://cloud.google.com/learn/certification','certification',40.00,125.00,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3922-723b-96b3-4fca461f5887','Scrum Master Certification Prep','Scrum.org','https://www.scrum.org/','certification',15.00,0.00,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3928-72e2-99f6-389e0f3b00ba','NLP with Transformers','Hugging Face','https://huggingface.co/learn/nlp-course','course',14.00,0.00,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-392e-7179-89fe-01ca489110d8','Effective Communication for Engineers','LinkedIn Learning','https://www.linkedin.com/learning/','other',4.00,29.99,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3934-7262-9ab1-30795971bc10','SQL for Data Analysis','Kaggle','https://www.kaggle.com/learn/','course',5.00,0.00,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-393b-73cc-9b5e-cc0638dd20e0','Introduction to R Programming','DataCamp','https://www.datacamp.com/','course',8.00,0.00,'2026-09-28 06:42:32','2026-09-28 06:42:32'),('01a0e6c0-3940-70e1-b1db-3d347056ccb0','Node.js Event Loop Explained','YouTube','https://www.youtube.com/results?search_query=nodejs+event+loop','video',2.00,0.00,'2026-09-28 06:42:32','2026-09-28 06:42:32');
/*!40000 ALTER TABLE `learning_resources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_07_20_000001_add_role_to_users_table',1),(5,'2026_07_20_000002_create_profiles_table',1),(6,'2026_07_20_000003_create_skills_table',1),(7,'2026_07_20_000004_create_resumes_table',1),(8,'2026_07_20_000005_create_career_roles_table',1),(9,'2026_07_20_000006_create_job_postings_table',1),(10,'2026_07_20_000007_create_ats_scans_table',1),(11,'2026_07_20_000008_create_gap_assessments_table',1),(12,'2026_07_20_000009_create_learning_resources_table',1),(13,'2026_07_20_000010_create_career_recommendations_table',1),(14,'2026_07_21_000001_create_skill_categories_table',1),(15,'2026_08_04_000001_add_google_oauth_to_users_table',1),(16,'2026_08_06_000001_consolidate_user_roles',1),(17,'2026_09_06_000001_create_settings_table',1),(18,'2026_09_09_000001_add_rationale_source_to_career_recommendations_table',1),(19,'2026_09_09_000001_add_skills_fingerprint_to_gap_assessments_table',1),(20,'2026_09_11_000001_add_onboarding_skipped_at_to_profiles_table',1),(21,'2026_09_26_000001_drop_unused_embedding_columns',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profiles`
--

DROP TABLE IF EXISTS `profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `profiles` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `headline` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `years_experience` int unsigned DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_roles` json DEFAULT NULL,
  `target_industries` json DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `onboarding_skipped_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profiles_user_id_foreign` (`user_id`),
  CONSTRAINT `profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profiles`
--

LOCK TABLES `profiles` WRITE;
/*!40000 ALTER TABLE `profiles` DISABLE KEYS */;
INSERT INTO `profiles` VALUES ('01a0e6c0-4ccb-705e-a832-1b599eeda651',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-28 06:42:37','2026-09-28 06:42:37');
/*!40000 ALTER TABLE `profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progress_tracking`
--

DROP TABLE IF EXISTS `progress_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `progress_tracking` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gap_assessment_item_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_started',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `progress_tracking_profile_id_gap_assessment_item_id_unique` (`profile_id`,`gap_assessment_item_id`),
  KEY `progress_tracking_gap_assessment_item_id_foreign` (`gap_assessment_item_id`),
  CONSTRAINT `progress_tracking_gap_assessment_item_id_foreign` FOREIGN KEY (`gap_assessment_item_id`) REFERENCES `gap_assessment_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `progress_tracking_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progress_tracking`
--

LOCK TABLES `progress_tracking` WRITE;
/*!40000 ALTER TABLE `progress_tracking` DISABLE KEYS */;
INSERT INTO `progress_tracking` VALUES ('01a0e6c1-daa1-70c6-9304-b7a14016d561','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c1-da9f-7147-a1cc-8c70a0016a67','not_started','2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-daa6-7271-bd33-e811f9f300bf','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c1-daa4-70ec-990d-89a6e8bb2d2a','not_started','2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-daaa-73e5-a24c-cc1b0e06edf0','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c1-daa8-737d-9eb0-d5647239ab26','not_started','2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-daad-71c6-937a-030fb09819a6','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c1-daac-713a-bae7-00138115bd22','not_started','2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-dab1-7196-a835-1147015d018f','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c1-daaf-707c-8983-92de44f37f06','not_started','2026-09-28 06:44:19','2026-09-28 06:44:19'),('01a0e6c1-dab5-723e-a1f7-2dc34be243d0','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c1-dab3-7381-915a-af4ffb0c88ac','not_started','2026-09-28 06:44:19','2026-09-28 06:44:19');
/*!40000 ALTER TABLE `progress_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resume_sections`
--

DROP TABLE IF EXISTS `resume_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resume_sections` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resume_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `order_index` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resume_sections_resume_id_foreign` (`resume_id`),
  CONSTRAINT `resume_sections_resume_id_foreign` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resume_sections`
--

LOCK TABLES `resume_sections` WRITE;
/*!40000 ALTER TABLE `resume_sections` DISABLE KEYS */;
INSERT INTO `resume_sections` VALUES ('01a0e6c0-6f6c-71f7-9b6a-3b4ec83be478','01a0e6c0-6f65-701d-bc10-68862deafb18','contact','NAYAN TANDUKAR\nBagbazar-28, Kathmandu | +977 9843949573 | nayantandukar@gmail.com |\nnayantandukar.com.np',0,'2026-09-28 06:42:46','2026-09-28 06:42:46'),('01a0e6c0-6f6e-7371-ad63-bd750479b817','01a0e6c0-6f65-701d-bc10-68862deafb18','summary','Student currently pursuing BCA who is enthusiastic, friendly, hardworking, eager, quick to\nlearn new skills accepting criticism and motivated to see the job through to completion.\nSkills and Abilities\nCommunication Technical skills MS-Office PHP HTML\nManagement Problem solving CSS Java MySQL',1,'2026-09-28 06:42:46','2026-09-28 06:42:46'),('01a0e6c0-6f6f-7082-87d1-de2137dec8a5','01a0e6c0-6f65-701d-bc10-68862deafb18','projects','Pneumonia Detection System June 2025 – August 2025\nCreated a Pneumonia prediction system using CNN algorithm and PHP, Python, HTML, CSS\nand WSL for python integration through XAMPP\nOnline Album Store August 2024 – November 2024\nCreated a web based online music store with demo eSewa payment gateway integrated with\nPHP, HTML and CSS through XAMPP\nStudent Information Management System January 2024 – May 2024\nCreated a basic CRUD application using PHP and MySQL, HTML and CSS having admin and\nuser dashboards through XAMPP',2,'2026-09-28 06:42:46','2026-09-28 06:42:46'),('01a0e6c0-6f72-71a0-a451-792c967990b3','01a0e6c0-6f65-701d-bc10-68862deafb18','education','Trinity International SS & College | BCA 2022- present\nMajor: Bachelors in Computer Application\nTrinity International SS & College | +2 2019-2021\nMajor: Science\nRoyal Public School | SEE 2019\nTraining / Certificate\nMicrosoft Office Package Training 2024\nOxford Academic Foundation',3,'2026-09-28 06:42:46','2026-09-28 06:42:46'),('01a0e6c0-6f74-72ce-bc4a-7f76ea417f5b','01a0e6c0-6f65-701d-bc10-68862deafb18','references','Avisekh Kuikel\nAssistant Coordinator\nTrinity International College\n+977 9843443299',4,'2026-09-28 06:42:46','2026-09-28 06:42:46');
/*!40000 ALTER TABLE `resume_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resumes`
--

DROP TABLE IF EXISTS `resumes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resumes` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raw_text` text COLLATE utf8mb4_unicode_ci,
  `parsed_json` json DEFAULT NULL,
  `version` int unsigned NOT NULL DEFAULT '1',
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `uploaded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resumes_profile_id_foreign` (`profile_id`),
  CONSTRAINT `resumes_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resumes`
--

LOCK TABLES `resumes` WRITE;
/*!40000 ALTER TABLE `resumes` DISABLE KEYS */;
INSERT INTO `resumes` VALUES ('01a0e6c0-6f65-701d-bc10-68862deafb18','01a0e6c0-4ccb-705e-a832-1b599eeda651','resumes/67xiIBIIASQ1rCMePTkPr9FcsFn2NrWvSPYUHvP7.pdf','pdf','NAYAN TANDUKAR \nBagbazar-28, Kathmandu | +977 9843949573 | nayantandukar@gmail.com | \nnayantandukar.com.np \nObjective \n \nStudent currently pursuing BCA who is enthusiastic, friendly, hardworking, eager, quick to \nlearn new skills accepting criticism and motivated to see the job through to completion. \n \nSkills and Abilities \nCommunication Technical skills MS-Office PHP HTML \nManagement Problem solving CSS Java MySQL \n \nProjects \n \nPneumonia Detection System June 2025 – August 2025 \n \nCreated a Pneumonia prediction system using CNN algorithm and PHP, Python, HTML, CSS \nand WSL for python integration through XAMPP \n \nOnline Album Store August 2024 – November 2024 \n \nCreated a web based online music store with demo eSewa payment gateway integrated with \nPHP, HTML and CSS through XAMPP \n \nStudent Information Management System January 2024 – May 2024 \n \nCreated a basic CRUD application using PHP and MySQL, HTML and CSS having admin and \nuser dashboards through XAMPP \n \nEducation \nTrinity International SS & College | BCA 2022- present \nMajor: Bachelors in Computer Application \nTrinity International SS & College | +2 2019-2021 \nMajor: Science \n \nRoyal Public School | SEE 2019 \n \nTraining / Certificate \nMicrosoft Office Package Training 2024 \nOxford Academic Foundation \n \nReferences \nAvisekh Kuikel \nAssistant Coordinator \nTrinity International College \n+977 9843443299','{\"skills\": [{\"id\": \"01a0e6c0-35f2-7181-a65c-b178a8e29886\", \"name\": \"Python\"}, {\"id\": \"01a0e6c0-35f5-7390-bcae-48a1244683dd\", \"name\": \"SQL\"}, {\"id\": \"01a0e6c0-3605-736f-94f8-df8aff767363\", \"name\": \"Java\"}, {\"id\": \"01a0e6c0-3613-7054-9390-bf62b06dae57\", \"name\": \"PHP\"}, {\"id\": \"01a0e6c0-3625-7112-8595-b785a4fea8e6\", \"name\": \"Communication\"}, {\"id\": \"01a0e6c0-362b-7248-95a4-1327d7839264\", \"name\": \"Problem Solving\"}], \"sections\": [{\"type\": \"contact\", \"method\": \"dictionary\", \"content\": \"NAYAN TANDUKAR\\nBagbazar-28, Kathmandu | +977 9843949573 | nayantandukar@gmail.com |\\nnayantandukar.com.np\"}, {\"type\": \"summary\", \"method\": \"dictionary\", \"content\": \"Student currently pursuing BCA who is enthusiastic, friendly, hardworking, eager, quick to\\nlearn new skills accepting criticism and motivated to see the job through to completion.\\nSkills and Abilities\\nCommunication Technical skills MS-Office PHP HTML\\nManagement Problem solving CSS Java MySQL\"}, {\"type\": \"projects\", \"method\": \"dictionary\", \"content\": \"Pneumonia Detection System June 2025 – August 2025\\nCreated a Pneumonia prediction system using CNN algorithm and PHP, Python, HTML, CSS\\nand WSL for python integration through XAMPP\\nOnline Album Store August 2024 – November 2024\\nCreated a web based online music store with demo eSewa payment gateway integrated with\\nPHP, HTML and CSS through XAMPP\\nStudent Information Management System January 2024 – May 2024\\nCreated a basic CRUD application using PHP and MySQL, HTML and CSS having admin and\\nuser dashboards through XAMPP\"}, {\"type\": \"education\", \"method\": \"dictionary\", \"content\": \"Trinity International SS & College | BCA 2022- present\\nMajor: Bachelors in Computer Application\\nTrinity International SS & College | +2 2019-2021\\nMajor: Science\\nRoyal Public School | SEE 2019\\nTraining / Certificate\\nMicrosoft Office Package Training 2024\\nOxford Academic Foundation\"}, {\"type\": \"references\", \"method\": \"dictionary\", \"content\": \"Avisekh Kuikel\\nAssistant Coordinator\\nTrinity International College\\n+977 9843443299\"}], \"source_filename\": \"NayanCV.pdf\"}',1,1,'2026-09-28 06:42:46','2026-09-28 06:42:46','2026-09-28 06:42:46');
/*!40000 ALTER TABLE `resumes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('8BB3niwSIKQJy3dkf4NBxGGvX9uecjuVvqySn7gb',2,'192.168.65.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','eyJfdG9rZW4iOiIzM0gxN2lCY0pOVVhPOUdKMUNqcEVIeXZNRmdjSlpYaEhuaUd1TDRKIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6Mn0=',1791176699),('hyY4XnqjuCMWzENH4ZlNvXCMKZA0DyNzgEzpVrnl',NULL,'172.18.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJLQUFyWnJKYjdLN2VMWGZBUWxKMG5PQWhmam1JWlR1UEdNVmN2cklYIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1790659142);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('01a0e6c1-53a2-7235-b070-7bce15328bf7','site_name','\"Ujjwol Bato\"','2026-09-28 06:43:44','2026-09-28 06:43:44'),('01a0e6c1-53a4-7206-85a0-1e2cb32ab81e','currency_symbol','\"Rs.\"','2026-09-28 06:43:44','2026-10-05 05:04:59'),('01a0e6c1-53a6-70d1-a02c-e8e8e98b3218','currency_position','\"before\"','2026-09-28 06:43:44','2026-09-28 06:43:44'),('01a0e6c1-53a9-73cf-9f13-277430e2af3b','ats_weights','{\"keyword\": 50, \"structure\": 15, \"formatting\": 15, \"readability\": 20}','2026-09-28 06:43:44','2026-09-28 06:43:44'),('01a0e6c1-53ab-713b-a608-9c08f47f6f63','top_matches','10','2026-09-28 06:43:44','2026-09-28 06:43:44'),('01a0e6c1-53ad-7386-a4e4-8d55a0c766fe','admin_page_size','20','2026-09-28 06:43:44','2026-09-28 06:43:44'),('01a0e6c1-53af-71bb-9a9b-665a0e701db9','recommendation_weights','{\"overlap\": 60, \"semantic\": 40}','2026-09-28 06:43:44','2026-09-28 06:43:44'),('01a0e6c1-53b1-71fd-8273-bacf25ab3605','recommendation_path_bonus','5','2026-09-28 06:43:44','2026-09-28 06:43:44'),('01a0e6c1-53b4-714e-b6f8-86e4990e5c06','llm_rationale','\"enabled\"','2026-09-28 06:43:44','2026-09-28 06:43:44'),('01a0e6c1-53b6-709e-acba-1703d02bd78a','llm_provider','\"groq\"','2026-09-28 06:43:44','2026-09-28 06:43:44'),('01a0e6c1-53b8-70f2-94e8-597946b1ef2e','llm_prompt_version','\"detailed\"','2026-09-28 06:43:44','2026-09-28 06:43:44'),('01a10a88-adb1-7160-b02f-4c3937f6a21e','ml_enabled','\"enabled\"','2026-10-05 05:28:11','2026-10-05 05:28:14');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skill_aliases`
--

DROP TABLE IF EXISTS `skill_aliases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `skill_aliases` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `skill_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `skill_aliases_skill_id_alias_unique` (`skill_id`,`alias`),
  CONSTRAINT `skill_aliases_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skill_aliases`
--

LOCK TABLES `skill_aliases` WRITE;
/*!40000 ALTER TABLE `skill_aliases` DISABLE KEYS */;
INSERT INTO `skill_aliases` VALUES ('01a0e6c0-369b-73bd-a6af-c79a87b1969a','01a0e6c0-35f5-7390-bcae-48a1244683dd','MySQL'),('01a0e6c0-36a0-720a-97fb-9dc367adf6ae','01a0e6c0-35f5-7390-bcae-48a1244683dd','Postgres'),('01a0e6c0-369e-727e-8794-23c2aea2627f','01a0e6c0-35f5-7390-bcae-48a1244683dd','PostgreSQL'),('01a0e6c0-3656-71cb-9687-5ef990887c65','01a0e6c0-35f7-7158-ac90-fa87bba4699c','ML'),('01a0e6c0-3696-713d-b5d6-258294c9e638','01a0e6c0-35fb-70d6-bb8f-47578366fd4c','Dashboarding'),('01a0e6c0-3698-73ca-a358-e01db60a0201','01a0e6c0-35fb-70d6-bb8f-47578366fd4c','Data Viz'),('01a0e6c0-3658-7376-9754-074e69a41b2e','01a0e6c0-35fe-7024-80d2-402bf66f684e','NLP'),('01a0e6c0-365b-7190-b9c6-6a055d310c6d','01a0e6c0-3608-708d-af8f-f7c58115f235','JS'),('01a0e6c0-365d-735f-bc52-48273e3bc156','01a0e6c0-360a-71ff-bfb5-990002c37f1b','TS'),('01a0e6c0-3663-7353-ba68-f12ba87d8080','01a0e6c0-360c-7298-8ff4-331995ad00ce','React.js'),('01a0e6c0-3661-7125-ac88-b761474b9719','01a0e6c0-360c-7298-8ff4-331995ad00ce','ReactJS'),('01a0e6c0-3668-7366-9cec-2769090b752d','01a0e6c0-360e-7048-af3d-e7191ff95094','Vue'),('01a0e6c0-366d-7242-aefd-cceed1de1239','01a0e6c0-360e-7048-af3d-e7191ff95094','Vue.js'),('01a0e6c0-366b-734d-9515-3d5d40a39a5c','01a0e6c0-360e-7048-af3d-e7191ff95094','VueJS'),('01a0e6c0-3670-70ff-b2ab-2dac32363a6f','01a0e6c0-3611-70aa-87da-1c05e53cb832','Node'),('01a0e6c0-3672-705a-a457-7945c476f3fe','01a0e6c0-3611-70aa-87da-1c05e53cb832','NodeJS'),('01a0e6c0-3675-708b-990a-e2401fcc7960','01a0e6c0-361b-73c7-b9da-2a14aa5b2d53','K8s'),('01a0e6c0-3693-731e-8f7d-ebec6d2da314','01a0e6c0-361d-71f5-98ad-4b6dbd2d5d13','Version control'),('01a0e6c0-3685-731b-91b2-ef0e5885355d','01a0e6c0-3621-7326-bb47-443fc3ade530','Agile'),('01a0e6c0-3687-71ba-869a-9462195e19d5','01a0e6c0-3621-7326-bb47-443fc3ade530','Scrum'),('01a0e6c0-368b-7377-bf3d-48d93918a248','01a0e6c0-3623-72fd-9687-58098d44d6af','PM'),('01a0e6c0-368e-7003-a57f-10ed373803c1','01a0e6c0-3623-72fd-9687-58098d44d6af','Projectmanager'),('01a0e6c0-367a-738a-babf-39a1f61f18f5','01a0e6c0-362f-7173-92f2-1955adbd3860','API'),('01a0e6c0-3678-714b-bf7b-328fe86065cc','01a0e6c0-362f-7173-92f2-1955adbd3860','APIs'),('01a0e6c0-367d-70f2-9d1f-fce67e1536fe','01a0e6c0-362f-7173-92f2-1955adbd3860','REST'),('01a0e6c0-367f-7194-bd2f-98c186120493','01a0e6c0-362f-7173-92f2-1955adbd3860','REST API'),('01a0e6c0-3682-7035-aba3-dbe03046f6fe','01a0e6c0-362f-7173-92f2-1955adbd3860','REST APIs');
/*!40000 ALTER TABLE `skill_aliases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skill_categories`
--

DROP TABLE IF EXISTS `skill_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `skill_categories` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `skill_categories_name_unique` (`name`),
  UNIQUE KEY `skill_categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skill_categories`
--

LOCK TABLES `skill_categories` WRITE;
/*!40000 ALTER TABLE `skill_categories` DISABLE KEYS */;
INSERT INTO `skill_categories` VALUES ('01a0e6c0-35cd-700b-a500-1a6a26fc3987','Programming','programming','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35d2-72c5-97a3-ac0a697d2aea','Data','data','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35d4-71d5-8595-9bcf8009c31c','AI/ML','aiml','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35d7-7074-ba11-ac91509d307a','Frontend','frontend','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35d9-73ea-8370-d7c614fd7deb','Backend','backend','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35db-713d-b40c-c365e0bec688','Mobile','mobile','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35dd-7385-86e0-6c0c1810f382','Cloud','cloud','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35df-7214-b56d-dfb3d951c0e3','DevOps','devops','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35e1-728d-a6bd-b9838995e794','Tools','tools','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35e3-72e4-833f-48bfd69152b0','Process','process','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35e6-737d-9675-d1ac0f85c5db','Management','management','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35e8-73e8-9cb8-f7f2b244b91e','Soft Skills','soft-skills','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35ea-72c1-8471-e5bc18c1b8a6','Marketing','marketing','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35ec-7311-bc35-4ae4ab00f9fc','Finance','finance','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35ee-71e5-99f8-15cc8628ef18','Design','design','2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35f0-7216-b84e-41f8090485ee','Healthcare','healthcare','2026-09-28 06:42:31','2026-09-28 06:42:31');
/*!40000 ALTER TABLE `skill_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `skills` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taxonomy_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `skills_name_unique` (`name`),
  KEY `skills_category_id_foreign` (`category_id`),
  CONSTRAINT `skills_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `skill_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skills`
--

LOCK TABLES `skills` WRITE;
/*!40000 ALTER TABLE `skills` DISABLE KEYS */;
INSERT INTO `skills` VALUES ('01a0e6c0-35f2-7181-a65c-b178a8e29886','Python','01a0e6c0-35cd-700b-a500-1a6a26fc3987',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35f5-7390-bcae-48a1244683dd','SQL','01a0e6c0-35d2-72c5-97a3-ac0a697d2aea',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35f7-7158-ac90-fa87bba4699c','Machine Learning','01a0e6c0-35d4-71d5-8595-9bcf8009c31c',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35f9-702c-9038-32b8badb545d','Statistics','01a0e6c0-35d2-72c5-97a3-ac0a697d2aea',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35fb-70d6-bb8f-47578366fd4c','Data Visualization','01a0e6c0-35d2-72c5-97a3-ac0a697d2aea',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35fd-7099-9c0f-acfa9672940e','Deep Learning','01a0e6c0-35d4-71d5-8595-9bcf8009c31c',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-35fe-7024-80d2-402bf66f684e','Natural Language Processing','01a0e6c0-35d4-71d5-8595-9bcf8009c31c',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3601-70ed-90f3-aa6ba1b2474e','Computer Vision','01a0e6c0-35d4-71d5-8595-9bcf8009c31c',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3603-71dd-84e1-653e4a6d5345','R Programming','01a0e6c0-35cd-700b-a500-1a6a26fc3987',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3605-736f-94f8-df8aff767363','Java','01a0e6c0-35cd-700b-a500-1a6a26fc3987',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3608-708d-af8f-f7c58115f235','JavaScript','01a0e6c0-35cd-700b-a500-1a6a26fc3987',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-360a-71ff-bfb5-990002c37f1b','TypeScript','01a0e6c0-35cd-700b-a500-1a6a26fc3987',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-360c-7298-8ff4-331995ad00ce','React','01a0e6c0-35d7-7074-ba11-ac91509d307a',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-360e-7048-af3d-e7191ff95094','Vue.js','01a0e6c0-35d7-7074-ba11-ac91509d307a',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3611-70aa-87da-1c05e53cb832','Node.js','01a0e6c0-35d9-73ea-8370-d7c614fd7deb',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3613-7054-9390-bf62b06dae57','PHP','01a0e6c0-35cd-700b-a500-1a6a26fc3987',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3616-73ea-8ae0-976dabeab294','Laravel','01a0e6c0-35d9-73ea-8370-d7c614fd7deb',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3618-7246-b587-ba1ee64d7045','AWS','01a0e6c0-35dd-7385-86e0-6c0c1810f382',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-361a-7317-a319-748bf2e5c1a0','Docker','01a0e6c0-35df-7214-b56d-dfb3d951c0e3',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-361b-73c7-b9da-2a14aa5b2d53','Kubernetes','01a0e6c0-35df-7214-b56d-dfb3d951c0e3',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-361d-71f5-98ad-4b6dbd2d5d13','Git','01a0e6c0-35e1-728d-a6bd-b9838995e794',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3620-72a7-8760-86d16086540e','CI/CD','01a0e6c0-35df-7214-b56d-dfb3d951c0e3',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3621-7326-bb47-443fc3ade530','Agile Methodologies','01a0e6c0-35e3-72e4-833f-48bfd69152b0',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3623-72fd-9687-58098d44d6af','Project Management','01a0e6c0-35e6-737d-9675-d1ac0f85c5db',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3625-7112-8595-b785a4fea8e6','Communication','01a0e6c0-35e8-73e8-9cb8-f7f2b244b91e',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3628-730e-9001-25e806b5eec9','Leadership','01a0e6c0-35e8-73e8-9cb8-f7f2b244b91e',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3629-7041-b7d0-e6237205e8eb','Critical Thinking','01a0e6c0-35e8-73e8-9cb8-f7f2b244b91e',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-362b-7248-95a4-1327d7839264','Problem Solving','01a0e6c0-35e8-73e8-9cb8-f7f2b244b91e',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-362d-70c2-88ff-89ea9abaea66','Data Analysis','01a0e6c0-35d2-72c5-97a3-ac0a697d2aea',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-362f-7173-92f2-1955adbd3860','API Design','01a0e6c0-35d9-73ea-8370-d7c614fd7deb',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3630-7315-bc70-5ad68ca4a347','Flutter','01a0e6c0-35db-713d-b40c-c365e0bec688',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3632-7156-bc8b-2a552f38cb3c','Android Development','01a0e6c0-35db-713d-b40c-c365e0bec688',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3633-7166-86b9-f417b4e746ce','React Native','01a0e6c0-35db-713d-b40c-c365e0bec688',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3635-7382-9f58-49510a91fc29','Digital Marketing','01a0e6c0-35ea-72c1-8471-e5bc18c1b8a6',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3637-73ae-a37e-1f9f4d4c28fc','SEO','01a0e6c0-35ea-72c1-8471-e5bc18c1b8a6',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3638-713b-9670-c7e263ae68df','Social Media Marketing','01a0e6c0-35ea-72c1-8471-e5bc18c1b8a6',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-363a-7325-aaf8-9395fd7dd481','Accounting','01a0e6c0-35ec-7311-bc35-4ae4ab00f9fc',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-363c-7096-8d4f-bac06ca77c5c','Tally ERP','01a0e6c0-35ec-7311-bc35-4ae4ab00f9fc',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-363e-7170-b084-26b0ad23d302','Microsoft Excel','01a0e6c0-35e1-728d-a6bd-b9838995e794',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-363f-70ae-9537-ba00a2ef73ad','Network Security','01a0e6c0-35dd-7385-86e0-6c0c1810f382',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3641-72d1-a110-d5d2e2683714','Networking','01a0e6c0-35dd-7385-86e0-6c0c1810f382',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3642-7286-8f91-c158e433f1f6','Graphic Design','01a0e6c0-35ee-71e5-99f8-15cc8628ef18',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3644-732b-ace8-88fba8d7ff79','UI/UX Design','01a0e6c0-35ee-71e5-99f8-15cc8628ef18',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3646-72ba-b9e5-08e61f69c38b','Figma','01a0e6c0-35ee-71e5-99f8-15cc8628ef18',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3647-7362-befa-3f2db9a9b263','Adobe Photoshop','01a0e6c0-35ee-71e5-99f8-15cc8628ef18',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3649-7386-821b-31bafa74aceb','Content Writing','01a0e6c0-35ea-72c1-8471-e5bc18c1b8a6',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-364b-70f8-a6aa-b3dc64144859','Customer Service','01a0e6c0-35e8-73e8-9cb8-f7f2b244b91e',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-364d-70a2-8a0b-e1017b0bf56b','Quality Assurance','01a0e6c0-35e3-72e4-833f-48bfd69152b0',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-364e-7095-b3f7-0be41503fbae','AutoCAD','01a0e6c0-35ee-71e5-99f8-15cc8628ef18',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3650-7125-bd4c-10c429309277','Surveying','01a0e6c0-35ee-71e5-99f8-15cc8628ef18',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31'),('01a0e6c0-3651-721e-bfba-701473464648','Patient Care','01a0e6c0-35f0-7216-b84e-41f8090485ee',NULL,'2026-09-28 06:42:31','2026-09-28 06:42:31');
/*!40000 ALTER TABLE `skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_skills`
--

DROP TABLE IF EXISTS `user_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_skills` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `skill_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `proficiency_level` smallint unsigned NOT NULL DEFAULT '1',
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'self_reported',
  `evidence_ref` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `last_used_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_skills_profile_id_skill_id_unique` (`profile_id`,`skill_id`),
  KEY `user_skills_skill_id_foreign` (`skill_id`),
  CONSTRAINT `user_skills_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_skills_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_skills`
--

LOCK TABLES `user_skills` WRITE;
/*!40000 ALTER TABLE `user_skills` DISABLE KEYS */;
INSERT INTO `user_skills` VALUES ('01a0e6c0-6f76-7256-be1b-49e8c15667b0','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-35f2-7181-a65c-b178a8e29886',3,'resume_extracted',NULL,NULL,NULL,'2026-09-28 06:42:46','2026-09-28 06:42:46'),('01a0e6c0-6f79-72f1-8e6f-2f23545e09ea','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-35f5-7390-bcae-48a1244683dd',3,'resume_extracted',NULL,NULL,NULL,'2026-09-28 06:42:46','2026-09-28 06:42:46'),('01a0e6c0-6f7c-729e-9bc6-27d46e518a7e','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-3605-736f-94f8-df8aff767363',3,'resume_extracted',NULL,NULL,NULL,'2026-09-28 06:42:46','2026-09-28 06:42:46'),('01a0e6c0-6f7e-73e7-a74d-1ace94497bf2','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-3613-7054-9390-bf62b06dae57',3,'resume_extracted',NULL,NULL,NULL,'2026-09-28 06:42:46','2026-09-28 06:42:46'),('01a0e6c0-6f80-703d-ae76-6d1c04ca64c1','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-3625-7112-8595-b785a4fea8e6',3,'resume_extracted',NULL,NULL,NULL,'2026-09-28 06:42:46','2026-09-28 06:42:46'),('01a0e6c0-6f82-725b-881b-6c6e77ada4c5','01a0e6c0-4ccb-705e-a832-1b599eeda651','01a0e6c0-362b-7248-95a4-1327d7839264',3,'resume_extracted',NULL,NULL,NULL,'2026-09-28 06:42:46','2026-09-28 06:42:46');
/*!40000 ALTER TABLE `user_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_google_id_unique` (`google_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Test User','test@example.com',NULL,NULL,'2026-09-28 06:42:32','$2y$12$xHIRGWYbRT3F1zQxkl7pjupN4zLHjg.BbjbSO.z21lmxUBxBVMc3i','user',NULL,'2026-09-28 06:42:32','2026-09-28 06:42:32'),(2,'Admin User','admin@example.com',NULL,NULL,'2026-09-28 06:42:32','$2y$12$HrbkDULSXuO8tVAKgImMHuSeDNQWwqtvlV7V3g.WE21mJDlLlbQdC','admin',NULL,'2026-09-28 06:42:32','2026-09-28 06:42:32');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `work_experience`
--

DROP TABLE IF EXISTS `work_experience`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `work_experience` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `work_experience_profile_id_foreign` (`profile_id`),
  CONSTRAINT `work_experience_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `work_experience`
--

LOCK TABLES `work_experience` WRITE;
/*!40000 ALTER TABLE `work_experience` DISABLE KEYS */;
/*!40000 ALTER TABLE `work_experience` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'career_guidance'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-05  5:30:01
