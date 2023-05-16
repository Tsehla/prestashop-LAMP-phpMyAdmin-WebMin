# prestashop-LAMP-phpMyAdmin-WebMin
Docker Prestashop vlatest/v8.0.4-8.1/v1.7.8.9/v1.7.8.5, with Mariadb-Apache-PhP + MyPhP + WebMin

> To install prestashop specific version, Edit docker file and comment/uncomment any of this lines :
    # FROM prestashop/prestashop:latest
    # FROM prestashop/prestashop:8.0.4-8.1
    FROM prestashop/prestashop:1.7.8.9
    # FROM prestashop/prestashop:1.7.8.5


> PHP my admin login
    link : ip-address/phpmyadmin/
    username : psuser
    password : admin

> To change phpmyadmin db user and login details: 

    edit in Dockerfile - 
    RUN service mariadb start && mysql -uroot mysql -e "CREATE USER 'psuser'@localhost IDENTIFIED BY 'admin';GRANT ALL PRIVILEGES ON *.* TO 'psuser'@localhost IDENTIFIED BY 'admin';FLUSH PRIVILEGES;"

    and edit / config.inc.php


> WebMin login
    link : ip-address/8080
    username : psuser
    password : admin

> To change Webmin login
    edit in Dockerfile -
    ENV WEBMIN_USER=psuser
    ENV WEBMIN_PASS=admin

> To access prestashop
    link : ip-address


> Build image 
    in a folder containing this dockerfile and related files run
        - docker build -t prestashop:1.7.8.9 .


    - Run image | 80:80 for prestashop port | 8080:10000 for webmin port
        docker run -d -p80:80 -p 8080:10000 --name prestashop-container prestashop:1.7.8.9


> prestashop install configuration
after installing prestashop and before accessing webmin do :

    - run to remove install folder 
        docker exec -ti prestashop-container rm -rf /var/www/html/install

    - run to rename admin folder to adminps
        docker exec -ti prestashop-container mv /var/www/html/admin /var/www/html/adminps

    - run to save this changes on the docker container to Docker built image. this will save current changes on docker container to the image used/refered to when bulding the container, so its sort of taking backup of current docker container at that moment. next time when you create another container based on the image, it will contain new changes, sort of like restoring backup 
        docker commit prestashop-container prestashop:1.7.8.9



> Prestashop backoffice issues on some prestashop version
    - if you get redirected constantly when clicking links in prestashop menu, do
    -- open phpmyadmin
    ip-address/phpmyadmin/

    -- login and select database the prestashop is using, then run my sql code
    UPDATE ps_configuration SET `value`=0 WHERE `name`="PS_COOKIE_CHECKIP";

    --** By the way – This is just a setting from Administration > Preferences menu which we are modifying via SQL because our BackOffice connection is broken and this value won’t be saved from admin panel.

- this is used by my project that auto manages products and stock using webservices, so ignore or comment out, im leaving it here to make things easier for me during deployment
    # Copy PrestaShop webservice management
    COPY eeza-prestashop-api/. /var/www/html/

- prestashop free themes, im leaving it here for my convininence, im not sure about copyright thought, so read their usage policies probably contained in them
    prestashop-theme/







