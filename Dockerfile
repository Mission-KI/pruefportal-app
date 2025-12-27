FROM php:8.4.10-cli

# Install system dependencies including Node.js for building frontend assets
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    curl \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_pgsql intl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2.8.9 /usr/bin/composer /usr/local/bin/composer

# Copy custom PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Set working directory
WORKDIR /app

# Copy composer files for dependency resolution
COPY composer.json composer.lock* ./

# Install PHP dependencies including dev dependencies for development
RUN composer install --optimize-autoloader

# Copy package.json and package-lock.json for npm dependencies
COPY package*.json ./

# Install npm dependencies (omit devDependencies like Cypress)
RUN npm ci --omit=dev

# Copy the rest of the application (excluding vendor and node_modules via .dockerignore)
COPY . .

# Build frontend assets (CSS and JS) - this will generate the bundle with correct version
RUN npm run build

# Change to webroot directory for serving
WORKDIR /app/webroot

# Expose port
EXPOSE 8070

# Start PHP built-in server from webroot
CMD ["php", "-S", "0.0.0.0:8070", "-t", ".", "index.php"]
