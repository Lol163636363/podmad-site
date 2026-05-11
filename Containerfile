# ============================================================
#  CIEL Portfolio Manager — Image Podman
#  Base : Fedora 44 (dnf + httpd + PHP 8.3)
# ============================================================
FROM fedora:44

LABEL maintainer="CIEL Deployer"
LABEL description="Portfolio Manager — déploiement HTML/CSS automatisé"

# ------------------------------------------------------------
# 1. Dépendances système + PHP + Apache (httpd)
# ------------------------------------------------------------
RUN dnf install -y --setopt=install_weak_deps=False \
        httpd \
        php \
        php-zip \
        php-mysqlnd \
        php-mbstring \
        php-fileinfo \
        php-json \
        php-session \
        php-xml \
        unzip \
        zip \
    && dnf clean all \
    && rm -rf /var/cache/dnf

# ------------------------------------------------------------
# 2. Configuration Apache (httpd)
#    - mod_rewrite actif par défaut sur Fedora
#    - AllowOverride All pour les .htaccess
# ------------------------------------------------------------
COPY docker/httpd.conf /etc/httpd/conf.d/ciel.conf

# Supprimer la page de bienvenue Fedora par défaut
RUN rm -f /etc/httpd/conf.d/welcome.conf

# ------------------------------------------------------------
# 3. Configuration PHP personnalisée
# ------------------------------------------------------------
COPY docker/php.ini /etc/php.d/99-ciel-custom.ini

# ------------------------------------------------------------
# 4. Application
# ------------------------------------------------------------
WORKDIR /var/www/html

COPY index.php .
COPY deploy.php .

# Création des dossiers de déploiement
RUN mkdir -p \
        deployments/TCIEL \
        deployments/1erCIEL \
        deployments/2ndCIEL \
    && chown -R apache:apache /var/www/html \
    && chmod -R 755 /var/www/html/deployments

# ------------------------------------------------------------
# 5. Démarrage httpd au premier plan
# ------------------------------------------------------------
EXPOSE 80

CMD ["/usr/sbin/httpd", "-D", "FOREGROUND"]
