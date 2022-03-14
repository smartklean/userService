# BUILD STAGE 1 - BASE
######################

# Pull base image
FROM ubuntu:20.04 as base

# Install common tools
RUN apt-get update \
    && apt-get install -y --no-install-recommends curl software-properties-common  \
    # PHP prerequisite
    && LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php \
    # Packages to install
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    wget curl vim nano inetutils-ping htop openssh-server git unzip bzip2 locales jq dirmngr gpg-agent \
    php7.4-fpm php7.4-common php7.4-curl php7.4-mysql \
    php7.4-mbstring php7.4-json php7.4-xml php7.4-bcmath \
    # SSH setup
    && mkdir -p /var/run/sshd \
    # PHP setup
    && mkdir -p /var/run/php \
    # Nginx setup
    && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys ABF5BD827BD9BF62 \
    && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C \
    && echo "deb http://nginx.org/packages/ubuntu/ trusty nginx" >> /etc/apt/sources.list \
    && echo "deb-src http://nginx.org/packages/ubuntu/ trusty nginx" >> /etc/apt/sources.list \
    && apt-get install -y --no-install-recommends nginx \
    # Composer setup
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    # Supervisor setup
    && apt-get install -y --no-install-recommends supervisor \
    && mkdir -p /var/log/supervisor \
    # Clean up
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

ENV TERM=xterm
ENV TZ=Etc/UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Set working directory
WORKDIR /var/www/html

# Config fpm to use TCP instead of unix socket
ADD resources/www.conf /etc/php/7.4/fpm/pool.d/www.conf

# Nginx config
ADD resources/default /etc/nginx/sites-enabled/
ADD resources/nginx.conf /etc/nginx/

# Configure supervisor
ADD resources/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Add a startup script
COPY docker-ssh-setup.sh /usr/local/bin/docker-ssh-setup.sh
COPY migrate-db.sh /usr/local/bin/migrate-db.sh
RUN chmod +x /usr/local/bin/docker-ssh-setup.sh \
    && chmod +x /usr/local/bin/migrate-db.sh \
    && useradd --no-create-home nginx

# Expose port 22 & 80
EXPOSE 22 80

# Set supervisor to manage container processes
ENTRYPOINT ["/usr/bin/supervisord"]

# BUILD STAGE 2 - DEPLOY
########################
FROM base as deploy

# Bundle web service source
COPY src /var/www/html

RUN  cd /var/www/html \
    # Install app dependencies
    && composer install --no-interaction \
    # Container Config - Aliasing the nginx logs to stdout and stderr
    && ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log \
    # Container Config - Ownership & Log and cache folders writable
    && chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache || echo ""
