## Site déployé à l'adresse suivante :
https://my-events-symfony.onrender.com/

## Réservation d'évènements payants :
Les évènements payants sont à payer avec des fausses cartes de crédit.
Utilisez pour les numéros de carte via https://docs.stripe.com/testing?locale=fr-FR&testing-method=card-numbers
Par ex : 4242424242424242 et 3 chiffres aléatoires pour le CVC.
et une date postérieure à la date du jour.




## Commandes utiles

### Compiler Sass
```php bin/console sass:build```

### Rendre dispo les assets (pas besoin en env=dev)
```php bin/console asset-map:compile```

### Installer les packages sous Asset Mapper
```php bin/console importmap:install```
