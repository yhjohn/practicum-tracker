FROM serversideup/php:8.5-fpm-nginx

# Switch to root so we can do root things
USER root

# Install the intl and bcmath extensions with root permissions
RUN install-php-extensions intl bcmath gd

# git needed for the backup workflow's post-backup `docker exec ... git pull`
RUN apt-get update && apt-get install -y --no-install-recommends git && rm -rf /var/lib/apt/lists/*

# Drop back to our unprivileged user
USER www-data
