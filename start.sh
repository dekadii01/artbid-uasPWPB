php artisan serve --host=0.0.0.0 --port=$PORT &
php artisan reverb:start --host=0.0.0.0 --port=8080 &
php artisan queue:work --sleep=3 --tries=3 &
php artisan schedule:work &
wait
