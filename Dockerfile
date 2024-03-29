# Use an official PHP runtime as a parent image
FROM php:8.2-fpm 

# Set the working directory to /app
WORKDIR /app

# Copy the current directory contents into the container at /app
COPY . /app

# Install any needed packages specified in requirements.txt
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    wget \
    mariadb-client \
    libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Symfony CLI
RUN wget https://get.symfony.com/cli/installer -O - | bash && mv /root/.symfony /root/.config

# Install Node.js and npm
RUN apt-get update && apt-get install -y \
    nodejs \
    npm

# Install project dependencies
RUN composer install --no-scripts --no-interaction

# Expose ports for Symfony and any other ports your app might need
EXPOSE 8000

# Run Symfony application
CMD ["symfony", "server:start", "--no-tls"]
