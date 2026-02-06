FROM php:8.4-apache                                                                                                
                                                                                                                     
  # Install system dependencies & PHP extensions
  RUN apt-get update && apt-get install -y \
      pkg-config \
      libpng-dev \
      libjpeg-dev \
      libfreetype6-dev \
      libonig-dev \
      libxml2-dev \
      libzip-dev \
      zip \
      unzip \
      && docker-php-ext-configure gd \
          --with-freetype=/usr/include/freetype2 \
          --with-jpeg \
      && docker-php-ext-install \
          pdo_mysql \
          mysqli \
          gd \
          zip \
          opcache \
      && rm -rf /var/lib/apt/lists/*

  # Copy application files to container
  COPY . /var/www/html/

  # Set permissions
  RUN chown -R www-data:www-data /var/www/html/ && \
      chmod -R 755 /var/www/html/

  # Enable Apache modules
  RUN a2enmod rewrite headers

  # PHP Configuration
  RUN echo "upload_max_filesize = 10M" > /usr/local/etc/php/conf.d/custom.ini && \
      echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/custom.ini && \
      echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/custom.ini && \
      echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/custom.ini
