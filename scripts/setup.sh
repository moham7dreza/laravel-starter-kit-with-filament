./composer.phar install --optimize-autoloader --no-dev
npm install
npm run build
cp .env.example .env
php artisan key:generate
cp .env .env.testing
cp .env .env.dusk
php artisan migrate:fresh --seed
php artisan optimize:clear
php artisan permissions:sync
php artisan serve
php artisan queue:work
