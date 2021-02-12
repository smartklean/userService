# Users Service Docker Scaffold

### **Description**

This will create a dockerized stack for our Users Service [Lumen](https://lumen.laravel.com) application, consisted of the following containers:

-  **app**, your PHP application container

        Nginx, PHP7.4 PHP7.4-fpm, Composer, NPM, Node.js v10.x

-  **mysql**, MySQL database container ([MySQL](https://hub.docker.com/_/mysql/) Official Docker Image)

- **phpmyadmin**, phpMyAdmin container ([phpMyAdmin](https://hub.docker.com/_/phpmyadmin/) Official Docker Image)

#### **Directory Structure**
```
+-- src <project root>
+-- resources
|   +-- default
|   +-- nginx.conf
|   +-- supervisord.conf
|   +-- www.conf
+-- .gitignore
+-- Dockerfile
+-- docker-compose.yml
+-- readme.md <this file>
```

### **Setup instructions**

**Prerequisites:**

* Depending on your OS, the appropriate version of Docker Community Edition has to be installed on your machine.  ([Download Docker Community Edition](https://hub.docker.com/search/?type=edition&offering=community))

**Installation Steps:**

1. Create a new directory in which your OS user has full read/write access and clone this repository inside.

2. Create two new textfiles named `db_root_password.txt` and `db_password.txt` and place your preferred database passwords inside:

    ```
    $ echo "myrootpass" > db_root_password.txt
    $ echo "myuserpass" > db_password.txt
    ```

3. Copy the content of your `db_root_password.txt` file and use it to update the value of phpmyadmin.environment.PMA_PASSWORD in the `docker-compose.yml` file.

4. Open a new terminal/CMD, navigate to this repository root (where `docker-compose.yml` exists) and execute the following command:

    ```
    $ docker-compose up -d
    ```

    This will download/build all the required images and start the stack containers. It usually takes a bit of time, so grab a cup of coffee.

    **N.B:** By default, the app container is mapped to port 80, if some other application on your local machine is using port 80, you can either free it up or edit the port settings for the app container in the `docker-composer.yml` file **before** you run `docker-compose up -d`. The same is true for the mysql and phpmyadmin containers which are mapped to ports 3306 and 8080 respectively.

5. After the whole stack is up, enter the app container:

    ```
    $ docker exec -it [app_container_name] bash
    ```
    Replace `app_container_name` with the name of your app container (if you did not change the default settings in your `docker-compose.yml` file, your app container name should be `users_app_container`).

6. Copy .env.example to .env:

    ```
    $ cp .env.example .env
    ```

7. In the `.env` file, you need to assign a value to APP_KEY and also update the db credentials to reflect what you have in your `docker-compose.yml` file. Ideally, you'd want a 32-character long unique string as your app key. Laravel allows you to easily generate an app key with `php artisan key:generate` command but Lumen being extremely light weight doesn't come with a lot of artisan commands, so you're going to have to [do this manually](http://www.unit-conversion.info/texttools/random-string-generator/).

8. In your terminal, run `php artisan migrate --seed` to migrate existing tables to your database.

9. That's it! Navigate to [http://localhost](http://localhost) to access the application.
