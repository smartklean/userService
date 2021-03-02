# Service Docker Scaffold

### **Description**

This will create a dockerized stack for our service, made up of one container that houses the images (Nginx, PHP7.4 PHP7.4-fpm, Composer, NPM, Node.js v10.x) needed to run our Lumen application.

In the `docker-compse.yml` file, the container name is set to ServiceScaffold. You should change this

### **Directory Structure**
```
+-- .git
+-- resources
|   +-- default
|   +-- nginx.conf
|   +-- supervisord.conf
|   +-- www.conf
+-- src <project root>
+-- .gitignore
+-- docker-compose.yml
+-- Dockerfile
+-- Dockerfile.nonlocal
+-- readme.md <this file>
```

### **Prerequisites**

Depending on your OS, the appropriate version of Docker Community Edition has to be installed on your machine.  ([Download Docker Community Edition](https://hub.docker.com/search/?type=edition&offering=community))

### **How To Install**

1. Create a new directory in which your OS user has full read/write access and clone this repository inside. Clone using your app password.

2. Open a new terminal/CMD, navigate to this repository root (where `docker-compose.yml` exists).

3. Update the remote upstream to point to your repo using: `git remote set-url origin <repo_url>`.

4. Update the values of services.app.container_name and services.app.ports as required. **N.B:** Remember to branch off master before making edits to the source code.

5. Execute the following command:

    ```
    $ docker-compose up -d
    ```

    This will download/build all the required images and start the stack containers. It usually takes a bit of time, so grab a cup of coffee.

6. After the whole stack is up, ssh into the app container by running the following command:

    ```
    $ docker exec -it [app_container_name] bash
    ```
    Replace `app_container_name` with the name of your app container. If successful, the command would open up an interactive shell in the /var/www/html folder.

7. In that shell, run `$ composer update` to install all your Lumen dependencies.

8. Copy .env.example to .env:

    ```
    $ cp .env.example .env
    ```

9. In the `.env` file, you need to assign a value to **APP_KEY**. Laravel allows you to easily generate an app key with `php artisan key:generate` command but Lumen being extremely light weight doesn't come with a lot of artisan commands, so you're going to have to [do this manually](http://www.unit-conversion.info/texttools/random-string-generator/).

10. Ensure that you have a MySQL server instance running on your local machine. Enter the MySQL connection values into your `.env` file. The host should be set to host.docker.internal.

11. In your terminal, run `php artisan migrate --seed` to migrate existing tables to your database.

12. That's it! Connect to [http://localhost:your_app_port](http://localhost:your_app_port) on your browser or via Postman. The endpoint should return a 'Welcome to CashEnvoy!' message as part of a JSON response object.
