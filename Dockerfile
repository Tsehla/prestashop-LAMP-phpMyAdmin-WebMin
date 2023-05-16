# FROM prestashop/prestashop:latest
# FROM prestashop/prestashop:8.0.4-8.1
FROM prestashop/prestashop:1.7.8.9
# FROM prestashop/prestashop:1.7.8.5

# Install necessary packages
RUN apt-get update && apt-get install -y \
    mariadb-server \
    wget

# Enable mysqli extension
RUN docker-php-ext-install mysqli

# Create user 'psuser' with password 'admin' and assign privileges
RUN service mariadb start && mysql -uroot mysql -e "CREATE USER 'psuser'@localhost IDENTIFIED BY 'admin';GRANT ALL PRIVILEGES ON *.* TO 'psuser'@localhost IDENTIFIED BY 'admin';FLUSH PRIVILEGES;"

# Copy PrestaShop webservice management
COPY eeza-prestashop-api/. /var/www/html/

# Install phpMyAdmin
WORKDIR /var/www/html
RUN wget https://files.phpmyadmin.net/phpMyAdmin/5.1.1/phpMyAdmin-5.1.1-all-languages.tar.gz && \
    tar -xf phpMyAdmin-5.1.1-all-languages.tar.gz && \
    mv phpMyAdmin-5.1.1-all-languages phpmyadmin && \
    rm phpMyAdmin-5.1.1-all-languages.tar.gz

COPY config.inc.php /var/www/html/phpmyadmin/

# Configure phpMyAdmin
COPY apache.conf /etc/phpmyadmin/apache.conf
RUN echo "Include /etc/phpmyadmin/apache.conf" >> /etc/apache2/apache2.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Install Webmin
RUN apt install gnupg -y
RUN wget -qO - http://www.webmin.com/jcameron-key.asc | apt-key add -
RUN echo "deb http://download.webmin.com/download/repository sarge contrib" >> /etc/apt/sources.list.d/webmin.list
RUN apt install apt-transport-https -y
RUN apt update
RUN apt install webmin -y

# Configure Apache to proxy requests to Webmin
#RUN echo "ProxyPass /webmin http://localhost:10000/" >> /etc/apache2/apache2.conf

# Set Webmin login details using environment variables
ENV WEBMIN_PASS=admin

# Expose ports
EXPOSE 80
EXPOSE 10000

# Clean up cached package files
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Start Apache, MariaDB, and Webmin
CMD echo "root:${WEBMIN_PASS}" | chpasswd && service apache2 start && service mariadb start && service webmin start && apt-get autoremove -y && sleep infinity

