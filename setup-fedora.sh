#!/usr/bin/env bash
# ============================================================
#  CIEL Deployer — Script d'installation Fedora 44
#  Usage : sudo bash setup-fedora.sh
# ============================================================

set -euo pipefail

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo " CIEL Portfolio Manager — Setup Fedora 44"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 1. Podman installé ?
if ! command -v podman &>/dev/null; then
    echo "📦 Installation de Podman..."
    dnf install -y podman
fi

# 2. Construction de l'image
echo ""
echo "🔨 Construction de l'image ciel-deployer..."
podman build -t ciel-deployer:latest .

# 3. Quadlet — copie des fichiers systemd
echo ""
echo "⚙️  Installation des unités Quadlet..."
mkdir -p /etc/containers/systemd

cp ciel-deployer.container   /etc/containers/systemd/
cp ciel-deployments.volume   /etc/containers/systemd/

# 4. Rechargement systemd + démarrage
echo ""
echo "🚀 Démarrage du service..."
systemctl daemon-reload
systemctl enable --now ciel-deployer

# 5. Pare-feu : ouverture du port 8080
echo ""
echo "🔥 Ouverture du port 8080 dans firewalld..."
if systemctl is-active --quiet firewalld; then
    firewall-cmd --permanent --add-port=8080/tcp
    firewall-cmd --reload
    echo "   ✅ Port 8080 ouvert."
else
    echo "   ⚠️  firewalld inactif — pensez à ouvrir le port 8080 manuellement."
fi

# 6. SELinux info
echo ""
if command -v getenforce &>/dev/null && [ "$(getenforce)" != "Disabled" ]; then
    echo "🔒 SELinux actif ($(getenforce)) — le flag :Z sur le volume gère le contexte automatiquement."
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo " ✅ Installation terminée !"
SERVER_IP=$(hostname -I | awk '{print $1}')
echo "    → http://${SERVER_IP}:8080"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
