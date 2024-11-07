optimize:
		php artisan optimize:clear
		php artisan filament:cache-components
		php artisan icon:cache

test:
		php artisan test --stop-on-failure

migrate:
		php artisan migrate

fresh:
		php artisan migrate:fresh 

fresh-seed:
		php artisan migrate:fresh --seed

list:
		php artisan route:list