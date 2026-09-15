FROM serversideup/php:8.5-fpm-nginx

# Switch to root so we can do root things
USER root

# Install the intl and bcmath extensions with root permissions
RUN install-php-extensions intl bcmath gd

# Drop back to our unprivileged user
USER www-data