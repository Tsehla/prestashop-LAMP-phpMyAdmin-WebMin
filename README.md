# prestashop-LAMP-phpMyAdmin-WebMin
Docker Prestashop vlatest/v8.0.4-8.1/v1.7.8.9/v1.7.8.5, with Mariadb-Apache-PhP + MyPhP + WebMin <br><br>

-> To install prestashop specific version, Edit docker file and comment/uncomment any of this lines :<br>
    # FROM prestashop/prestashop:latest<br>
    # FROM prestashop/prestashop:8.0.4-8.1<br>
    FROM prestashop/prestashop:1.7.8.9<br>
    # FROM prestashop/prestashop:1.7.8.5<br><br>


-> PHP my admin login<br>
    link : ip-address/phpmyadmin/<br>
    username : psuser<br>
    password : admin<br>

-> To change phpmyadmin db user and login details: <br><br>

edit in Dockerfile - <br>
    RUN service mariadb start && mysql -uroot mysql -e "CREATE USER 'psuser'@localhost IDENTIFIED BY 'admin';GRANT ALL PRIVILEGES ON *.* TO 'psuser'@localhost IDENTIFIED BY 'admin';FLUSH PRIVILEGES;" <br>

and edit / config.inc.php<br><br>


-> WebMin login<br>
    link : ip-address/8080<br>
    username : root<br>
    password : admin<br>

-> To change Webmin login<br>
    edit in Dockerfile -<br>
    ENV WEBMIN_PASS=admin<br><br>

-> To access prestashop<br>
    link : ip-address/<br>

-> Build image
in a folder containing this dockerfile and related files run
```
docker build -t prestashop:1.7.8.9 .
```


- Run image | 80:80 for prestashop port | 8080:10000 for webmin port
```
        docker run -d -p80:80 -p 8080:10000 --name prestashop-container prestashop:1.7.8.9
```


-> prestashop install configuration<br>
after installing prestashop and before accessing webmin do :<br>

- run to remove install folder
```
     docker exec -ti prestashop-container rm -rf /var/www/html/install
```

- run to rename admin folder to adminps
```
    docker exec -ti prestashop-container mv /var/www/html/admin /var/www/html/adminps
```

- run to save this changes on the docker container to Docker built image. this will save current changes on docker container to the image used/refered to when bulding the container, so its sort of taking backup of current docker container at that moment. next time when you create another container based on the image, it will contain new changes, sort of like restoring backup 
```
    docker commit prestashop-container prestashop:1.7.8.9
```



-> Prestashop backoffice issues on some prestashop version<br>
- if you get redirected constantly when clicking links in prestashop menu, do<br>
-- open phpmyadmin
```
    ip-address/phpmyadmin/
```

-- login and select database the prestashop is using, then run my sql code
```
    UPDATE ps_configuration SET `value`=0 WHERE `name`="PS_COOKIE_CHECKIP";
```

--** By the way – This is just a setting from Administration > Preferences menu which we are modifying via SQL because our BackOffice connection is broken and this value won’t be saved from admin panel.<br><br>

-> this is used by my project that auto manages products and stock using webservices, so ignore or comment out, im leaving it here to make things easier for me during deployment<br>
    --Copy PrestaShop webservice management
```
    COPY eeza-prestashop-api/. /var/www/html/
```

-> prestashop free themes, im leaving it here for my convininence, im not sure about copyright thought, so read their usage policies probably contained in them
    prestashop-theme/<br><br>


-> docker commands<br>
    -- see all active docker containers
```
    docker ps -a
```

-- stop docker containe of Container-ID
```
    docker stop Container-ID
```

-- start docker of Container-ID
```
    docker start Container-ID
```

-- remove container of Container-ID
```
    docker rm Container-ID
```

-- see all images
```
    docker images
```

-- remove image of Image-ID
```
    docker rmi Image-ID
```

-- force remove image of Image-ID
```
    docker rmi -f Image-ID
```

-- build image from docker file in current directory
```
    docker build -t .
```

-- build image from docker file in current directory with image name Image-Name
```
    docker build -t Image-Name .
```

-- run image of Image-Name on port 80 of server and link it to port 81 of a service in docker container/image
```
    docker run -p 80:81 Image-Name
```

-- run image of Image-Name on port 80 of server and link it to port 81 of a service in docker container/image, give container name of Container-Name
```
    docker run -p 80:81 --name Container-Name Image-Name
```

-- run image of Image-Name on port 80 of server and link it to port 81 of a service in docker container/image, give container name of Container-Name, and have docker process/console detached
```
    docker run -d -p 80:81 --name Container-Name Image-Name
```


-> Dockerfile github repository<br>
    https://github.com/Tsehla/prestashop-LAMP-phpMyAdmin-WebMin<br>






