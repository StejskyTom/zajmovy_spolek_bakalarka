# Web pro zájmový spolek

## Nastavení
- Vytvořte sobor `.env.local` zkopírovaný z `.env`
- Nastavte `DATABASE_URL` proměnnou
- Spusťte `docker-compose up -d` pro spuštění docker containeru
- Spusťte `docker-compose exec www /bin/bash` pro přístup do bash aplikace
- (Pokud by jste se dostali do bash a nešel vám následující příkaz, je nutné v bash spustit `cd ..`)
- Spusťte `composer install` pro instalaci závislostí
- Spusťte `php bin/console doctrine:migrations:migrate` pro migraci databáze
- Spusťte `php bin/console doctrine:fixtures:load` pro vytvoření administrátora

## Vývoj
### Práce s databází
- Spusťte `docker-compose exec www /bin/bash` pro přístup do bash aplikace
- (Pokud by jste se dostali do bash a nešel vám následující příkaz, je nutné v bash spustit `cd ..`)
- Spusťte `php bin/console make:migration` pro vygenerování migrací po provedení změn v entitách
- Spusťte `php bin/console doctrine:migrations:migrate` pro migraci databáze
