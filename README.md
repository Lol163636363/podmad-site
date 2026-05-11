# CIEL Portfolio Manager — Fedora 44

## Structure des fichiers

```
.
├── Containerfile              ← image basée sur fedora:44
├── ciel-deployer.container    ← unité Quadlet (systemd)
├── ciel-deployments.volume    ← volume Quadlet (persistance)
├── setup-fedora.sh            ← script d'installation complet
├── docker/
│   ├── httpd.conf             ← config Apache (httpd Fedora)
│   └── php.ini                ← limites upload / mémoire
├── index.php
└── deploy.php
```

---

## Installation (recommandée)

```bash
sudo bash setup-fedora.sh
```

Le script fait tout : build, Quadlet, pare-feu, démarrage.

---

## Installation manuelle étape par étape

### 1. Construire l'image
```bash
podman build -t ciel-deployer:latest .
```

### 2. Installer les unités Quadlet
```bash
sudo cp ciel-deployer.container  /etc/containers/systemd/
sudo cp ciel-deployments.volume  /etc/containers/systemd/
sudo systemctl daemon-reload
```

### 3. Démarrer le service
```bash
sudo systemctl enable --now ciel-deployer
# Vérifier
sudo systemctl status ciel-deployer
```

### 4. Ouvrir le pare-feu
```bash
sudo firewall-cmd --permanent --add-port=8080/tcp
sudo firewall-cmd --reload
```

### 5. Accéder à l'application
```
http://<IP_DU_SERVEUR>:8080
```

---

## Commandes utiles

```bash
# Logs en temps réel
podman logs -f ciel-deployer

# Redémarrer
sudo systemctl restart ciel-deployer

# Arrêter
sudo systemctl stop ciel-deployer

# Reconstruire après modif du code
podman build -t ciel-deployer:latest . && sudo systemctl restart ciel-deployer

# Accéder au shell du conteneur
podman exec -it ciel-deployer bash
```

---

## Notes Fedora 44

| Sujet      | Détail |
|------------|--------|
| SELinux    | Le volume utilise `:Z` → contexte SELinux appliqué automatiquement |
| Pare-feu   | `firewalld` est actif par défaut sur Fedora Server |
| PHP        | PHP 8.3 installé via les dépôts officiels Fedora 44 |
| Apache     | `httpd` (Fedora) au lieu de `apache2` (Debian) |
| Quadlet    | Remplace `podman-compose` — intégration native systemd |
| Persistance| Volume `ciel-deployments` survive aux mises à jour de l'image |
