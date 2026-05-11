<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CIEL Deployer | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-900">

    <nav class="bg-blue-700 text-white p-4 flex justify-between items-center shadow-lg">
        <div class="flex items-center gap-2">
            <i class="fas fa-network-wired"></i>
            <span class="font-bold text-xl tracking-tight">CIEL Portfolio Manager</span>
        </div>
        <button onclick="toggleModal()" class="bg-white text-blue-700 hover:bg-blue-50 h-10 w-10 rounded-full flex items-center justify-center transition-all shadow">
            <i class="fas fa-plus"></i>
        </button>
    </nav>

    <main class="p-8 max-w-7xl mx-auto">
        <div id="project-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $baseDir = "deployments";
            $niveaux = ['TCIEL', '1erCIEL', '2ndCIEL'];

            foreach ($niveaux as $niv) {
                $path = $baseDir . "/" . $niv;
                if (is_dir($path)) {
                    $projets = array_diff(scandir($path), array('..', '.'));
                    foreach ($projets as $projet) {
                        $fullPath = $path . "/" . $projet;
                        if(!is_dir($fullPath)) continue;

                        $finalUrl = "deployments/$niv/$projet";
                        $sub = file_exists($fullPath . '/.real_path') ? trim(file_get_contents($fullPath . '/.real_path')) : null;
                        
                        // Détection WordPress pour affichage Maintenance
                        $checkDir = $sub ? $fullPath . '/' . $sub : $fullPath;
                        $isWP = file_exists($checkDir . '/wp-config.php');
                        ?>
                        
                        <div class="bg-white rounded-2xl p-6 border <?php echo $isWP ? 'border-red-200 shadow-red-50' : 'border-slate-200'; ?> shadow-sm transition-all">
                            <div class="flex justify-between mb-4 text-xs font-black uppercase">
                                <span class="text-blue-600"><?php echo $niv; ?></span>
                                <?php if ($isWP): ?>
                                    <span class="text-red-600 bg-red-50 px-2 py-0.5 rounded-md border border-red-100">
                                        <i class="fas fa-tools mr-1"></i> Maintenance
                                    </span>
                                <?php else: ?>
                                    <span class="text-emerald-500">
                                        <i class="fas fa-code mr-1"></i> HTML / CSS
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3 class="text-lg font-bold mb-4 capitalize"><?php echo str_replace('-', ' ', $projet); ?></h3>
                            
                            <?php if ($isWP): ?>
                                <button disabled class="w-full bg-slate-100 text-slate-400 py-3 rounded-xl font-bold cursor-not-allowed">
                                    Accès restreint
                                </button>
                            <?php else: ?>
                                <a href="<?php echo $finalUrl; ?>/" target="_blank" class="block w-full text-center bg-blue-50 text-blue-700 py-3 rounded-xl font-bold hover:bg-blue-600 hover:text-white transition-all">
                                    Accéder au site
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                }
            }
            ?>
        </div>
    </main>

    <div id="uploadModal" class="hidden fixed inset-0 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl relative">
            <h2 class="text-2xl font-bold mb-6 text-blue-700 text-center">Nouveau Déploiement HTML</h2>
            
            <form action="deploy.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="project_type" value="fullcode">
                
                <select name="niveau" class="w-full p-3 border border-slate-200 rounded-xl bg-slate-50 outline-none">
                    <option value="TCIEL">Terminale CIEL</option>
                    <option value="1erCIEL">Première CIEL</option>
                    <option value="2ndCIEL">Seconde CIEL</option>
                </select>

                <input type="text" name="project_name" placeholder="Nom du projet" required 
                       class="w-full p-3 border border-slate-200 rounded-xl bg-slate-50 outline-none">

                <div class="relative group border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:border-blue-500 transition-all">
                    <input type="file" name="portfolio_zip" accept=".zip" required class="absolute inset-0 opacity-0 cursor-pointer">
                    <i class="fas fa-file-archive text-3xl text-slate-300 mb-2"></i>
                    <p class="text-xs font-bold text-slate-500 uppercase">Fichier ZIP (HTML Uniquement)</p>
                </div>

                <button class="w-full bg-blue-700 text-white py-4 rounded-xl font-bold shadow-lg hover:bg-blue-800 transition-all">
                    Lancer l'installation
                </button>
                <button type="button" onclick="toggleModal()" class="w-full text-slate-400 py-1 text-sm">Annuler</button>
            </form>
        </div>
    </div>

    <script>
        function toggleModal() { document.getElementById('uploadModal').classList.toggle('hidden'); }
    </script>
</body>
</html>