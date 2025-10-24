FROM php:8.3-cli

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Expose port
EXPOSE 8000

# Create startup script
RUN echo '#!/bin/bash\n\
set -e\n\
git config --global --add safe.directory /var/www\n\
if [ ! -d "vendor" ]; then\n\
  echo "Installing composer dependencies..."\n\
  composer install --no-interaction --prefer-dist || composer update --no-interaction --prefer-dist\n\
fi\n\
if [ ! -f ".env" ]; then\n\
  echo "Creating .env file..."\n\
  cp .env.example .env || true\n\
fi\n\
if grep -q "APP_KEY=$" .env 2>/dev/null; then\n\
  echo "Generating application key..."\n\
  php artisan key:generate --no-interaction\n\
fi\n\
chmod -R 775 storage bootstrap/cache 2>/dev/null || true\n\
echo "Starting Laravel development server..."\n\
exec php artisan serve --host=0.0.0.0 --port=8000' > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

# Start Laravel with dependency check
CMD ["/usr/local/bin/start.sh"]
