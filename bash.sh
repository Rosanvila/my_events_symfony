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


# Copier d'abord les fichiers de configuration
COPY composer.json composer.lock ./


# Installer les dépendances PHP d'abord
RUN composer install --no-dev --optimize-autoloader --no-scripts


# Maintenant copier tout le reste
COPY . .


# Installer/configurer Symfony Runtime manuellement
RUN composer require symfony/runtime --no-scripts || echo "Runtime already installed"


# Installer les assets JS (importmap)
RUN php bin/console importmap:install


# Créer un script de démarrage
RUN echo '#!/bin/bash\n\
    set -e\n\
    composer dump-autoload --optimize --no-dev\n\
    php bin/console sass:build || echo "Sass build failed, continuing..."\n\
    php bin/console cache:clear --env=prod --no-debug || echo "Cache clear failed, continuing..."\n\
    php bin/console cache:warmup --env=prod --no-debug || echo "Cache warmup failed, continuing..."\n\
    php bin/console doctrine:migrations:migrate --no-interaction --env=prod || echo "Migrations failed, continuing..."\n\
    php -S 0.0.0.0:8000 -t public' > /start.sh && chmod +x /start.sh


# Exposer le port
EXPOSE 8000


# Commande de démarrage
CMD ["/start.sh"]


