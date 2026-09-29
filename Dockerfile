FROM serversideup/php:8.5-fpm-nginx AS app

# Switch to root so we can do root things
USER root

# Install the intl and bcmath extensions with root permissions
RUN install-php-extensions intl bcmath gd

# git needed for the backup workflow's post-backup `docker exec ... git pull`
RUN apt-get update && apt-get install -y --no-install-recommends git && rm -rf /var/lib/apt/lists/*

# Drop back to our unprivileged user
USER www-data

# Same image plus the Claude CLI; works the "summaries" queue (see App\Jobs\SummarizeReport)
FROM app AS claude

USER root
RUN apt-get update && apt-get install -y --no-install-recommends curl && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /opt/claude-home && chown www-data:www-data /opt/claude-home

USER www-data
ENV HOME=/opt/claude-home \
    PATH=/opt/claude-home/.local/bin:$PATH \
    DISABLE_AUTOUPDATER=1
RUN curl -fsSL https://claude.ai/install.sh | bash && claude --version
