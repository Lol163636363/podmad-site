<?php
/**
 * SYSTÈME DE DÉPLOIEMENT AUTOMATISÉ - CIEL
 * Restriction Stricte : Mode Full Code / HTML Uniquement
 */

session_start();

// --- CONFIGURATION ---
$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['project_name'])) {
        die("❌ Erreur : Données trop lourdes.");
    }

    // 1. RÉCUPÉRATION ET NETTOYAGE
    $niveau = $_POST['niveau'] ?? 'TCIEL';
    $projectType = $_POST['project_type'] ?? 'fullcode';
    $projectName = str_replace(' ', '-', strtolower(trim($_POST['project_name'])));
    $projectName = preg_replace('/[^a-z0-9_-]/', '', $projectName);
    
    if (empty($projectName)) {
        die("❌ Nom de projet invalide.");
    }

    // --- SÉCURITÉ : BLOCAGE WORDPRESS ---
    if ($projectType === 'wordpress') {
        die("❌ Erreur : L'installation WordPress est désactivée. Veuillez utiliser le mode Full Code (HTML/CSS).");
    }

    // 2. DÉFINITION DES CHEMINS
    $baseDir = __DIR__ . "/deployments";
    $targetDir = $baseDir . "/" . $niveau . "/" . $projectName;

    // 3. GESTION DE L'UPLOAD ZIP
    if (!isset($_FILES['portfolio_zip']) || $_FILES['portfolio_zip']['error'] !== UPLOAD_ERR_OK) {
        die("❌ Erreur lors de l'upload du ZIP.");
    }

    // 4. PRÉPARATION DU DOSSIER
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    } else {
        array_map('unlink', glob("$targetDir/*")); 
    }

    // 5. EXTRACTION DU ZIP
    $zip = new ZipArchive;
    if ($zip->open($_FILES['portfolio_zip']['tmp_name']) === TRUE) {
        $zip->extractTo($targetDir);
        $zip->close();
        
        // Détection de sous-dossier pour la redirection automatique
        $subFolder = "";
        $files = array_diff(scandir($targetDir), array('.', '..'));
        foreach ($files as $file) {
            if (is_dir($targetDir . '/' . $file)) {
                $subFolder = $file;
                file_put_contents($targetDir . '/.real_path', $subFolder);
                break;
            }
        }

        // Création forcée d'un index de redirection si un sous-dossier existe
        if (!empty($subFolder) && !file_exists($targetDir . '/index.php') && !file_exists($targetDir . '/index.html')) {
            $redir = "<?php header('Location: ./" . $subFolder . "/'); exit(); ?>";
            file_put_contents($targetDir . '/index.php', $redir);
        }
    } else {
        die("❌ Échec de l'extraction.");
    }

    header("Location: index.php?success=1");
    exit();
}