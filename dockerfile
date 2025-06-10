FROM composer:latest AS composer

FROM php:8.3-fpm

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git

# Installer les extensions PHP (sans pdo car déjà inclus)
RUN docker-php-ext-install pgsql pdo_pgsql zip

# Installer Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier TOUT le projet d'abord
COPY . .

# Installer les dépendances PHP (avec --no-scripts pour éviter l'erreur symfony-cmd)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Créer un script de démarrage
RUN echo '#!/bin/bash\n\
    set -e\n\
    echo "=== Starting application setup ==="\n\
    composer dump-autoload --optimize --no-dev\n\
    php bin/console importmap:install || echo "Importmap install failed, continuing..."\n\
    php bin/console asset-map:compile || echo "Asset compilation failed, continuing..."\n\
    php bin/console sass:build || echo "Sass build failed, continuing..."\n\
    php bin/console cache:clear --env=prod --no-debug || echo "Cache clear failed, continuing..."\n\
    php bin/console cache:warmup --env=prod --no-debug || echo "Cache warmup failed, continuing..."\n\
    echo "=== Checking migrations status ==="\n\
    php bin/console doctrine:migrations:status --env=prod\n\
    echo "=== Running migrations ==="\n\
    # Exécuter toutes les migrations dans l\'ordre\n\
    php bin/console doctrine:migrations:migrate --no-interaction --env=prod\n\
    echo "=== Migrations completed successfully ==="\n\
    echo "=== Starting Messenger consumer in background ==="\n\
    # Démarrer le consumer avec une meilleure gestion des erreurs et des logs\n\
    while true; do\n\
    php bin/console messenger:consume async --env=prod --time-limit=3600 --memory-limit=256M --limit=1000 --failure-limit=10 --sleep=1000 2>&1 | tee -a /var/log/messenger.log\n\
    if [ $? -ne 0 ]; then\n\
    echo "Consumer crashed, restarting in 5 seconds..." >> /var/log/messenger.log\n\
    sleep 5\n\
    fi\n\
    done &\n\
    echo "=== Starting PHP server ==="\n\
    php -S 0.0.0.0:8000 -t public' > /start.sh && chmod +x /start.sh

# Exposer le port
EXPOSE 8000

# Commande de démarrage
CMD ["/start.sh"]