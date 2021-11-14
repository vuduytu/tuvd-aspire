cp .env.example .env
composer update

read -p "Enter your db host(localhost): " db_host

if [[ $db_host = '' ]]
then
    db_host="localhost"
fi

read -p "Enter your database name: " db_name
while [[ $db_name = '' ]]
do
    read -p "Enter your database name: " db_name
done

read -p "Enter your database user: " db_user
while [[ $db_user = '' ]]
do
    read -p "Enter your database user: " db_user
done

read -p "Enter your database password: " db_pwd


echo "$(sed "s+DB_HOST=127.0.0.1+DB_HOST=$db_host+g" .env)" > .env
echo "$(sed "s+DB_DATABASE=laravel+DB_DATABASE=$db_name+g" .env)" > .env
echo "$(sed "s+DB_USERNAME=root+DB_USERNAME=$db_user+g" .env)" > .env
echo "$(sed "s+DB_PASSWORD=+DB_PASSWORD=$db_pwd+g" .env)" > .env

php artisan migrate
php artisan db:seed
php artisan jwt:secret
