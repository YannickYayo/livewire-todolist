FROM php:7.3.16-apache-stretch

RUN apt-get update && apt-get install -y \
    wget \
    git \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# npm
RUN curl -sL https://deb.nodesource.com/setup_12.x | bash - && \
    apt-get update && apt-get install -y \
    nodejs \
    npm \
    build-essential

# Install dev dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libicu-dev \
    libfreetype6-dev \
    libpng-dev \
    libjpeg-dev \
    libxml2-dev \
    libzip-dev \
    zlib1g-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install production dependencies
RUN apt-get update && apt-get install -y \
    bash \
    git \
    mysql-client \
    yarn \
    openssh-client \
    rsync \
    unzip \
    vim \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Pour la récupération des durées
RUN apt-get update && apt-get install -y ffmpeg && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PECL and PEAR extensions
RUN pecl install \
    xdebug-2.9.4 \
    redis

# Enable PECL and PEAR extensions
RUN docker-php-ext-enable \
    xdebug \
    redis

# Configure php extensions
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-configure zip --with-libzip

# Install php extensions
RUN docker-php-ext-install \
    bcmath \
    calendar \
    exif \
    gd \
    iconv \
    intl \
    mbstring \
    mysqli \
    pdo \
    pdo_mysql \
    pcntl \
    tokenizer \
    xml \
    zip

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# xdebug custom conf
ADD xdebug.ini /usr/local/etc/php/conf.d/

# Enable rewrite module
RUN a2enmod rewrite

ARG USER_ID
ARG GROUP_ID

RUN groupadd -f --gid $GROUP_ID user
RUN adduser --disabled-password --gecos '' --uid $USER_ID --gid $GROUP_ID user
# uncomment the line below if you have permissions issues with the www-data user
RUN usermod -a -G www-data user 
USER user

WORKDIR /var/www/html

EXPOSE 9000