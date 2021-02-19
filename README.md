Intallation
git clone https://github.com/ArthurBrunet/SymfonyExam.git
cd SymfonyExam
composer install
Créer un dossier jwt dans le dossier config et générer des clés SSH avec Openssl

openssl genrsa -out config/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
Créer un .env à la racine du projetet configuré le avec l'aide du .env.test

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
php -S localhost:8000 -t public
Et voila ! un compte admin est présent pour le test de la route /api/film/create

/api/login body:{"username":"admin","password":"admin"}
