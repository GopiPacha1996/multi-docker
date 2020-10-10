# multi-docker

#Dockerizing mutli-service application

# step:-1

clone the project and cd to the project

$ git clone https://github.com/GopiPacha1996/multi-docker.git && cd Laravel

# step-2:-

$ docker-compose up -d

# step -3:- install the composer and do artisan and migrations

$ docker-compose exec app composer install

$ docker-compose exec app php artisan

$ docker-compose exec app php artisan migrate

# step-4:- give permissions to the folder

$ docker-compose exec app chmod -R 777 /var/www

Now check on the port your application ruuning on 8000 port
