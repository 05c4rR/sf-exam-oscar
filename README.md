# Projet Symfony

### Oscar Boguszewski - HB-R6-Vill

## Présentation du projet

Ce projet est un e-commerce. J'ai essayé d'intégrer la plupart des fonctionnalités que l'on retrouve sur toutes les boutiques en ligne. Un utilisateur peut naviguer sur la boutiques et filtrer ses recherches, remplir un panier de commandes et effectuer un paiement. 
Une légère interface admin permet de gérer les produits, les catégories et les promotions.

### Fonctionnalités Utilisateur

* Inscription et connexion
* Une fois connecter l'utilisateur peut cliquer sur son email dans la nav pour consulter les commandes qu'il a passé
* L'utilisateur peut remplir son panier d'achat même s'il n'est pas connecté. Au moment de valider son panier par contre, il sera demandé de se connecter ou de s'inscrire pour continuer
* J'ai utilisé l'api Stripe pour simuler les paiements bancaires. Par contre je n'ai pas réussi à l'utiliser correctement, je rencontre un bug au moment de la redirection vers le portail de paiement. Pour accéder au portail de Stripe, il faut décommenter la ligne 
```php
dd($session->url); 
``` 
juste avant la redirection et copier l'url. Désolé pour ces étapes supplémentaires, mais je n'ai pas réussi à trouvé de solutions.
* Un email de confirmation est envoyé après le paiement de la commande _en théories_**. J'ai ajouté le service d'email mais je ne sais pas comment le tester, alors _je pense que ça marche_** mais je l'ai pas testé. J'ai commenté la ligne
```php 
$email->sendOrderConfirmationEmail($this->getUser(), $order);
```
pour pas causer de bugs.

### Fonctionnalités Admin

* Crud pour les produits
* Crud pour les catégories
* Crud pour les promotions


## Bugs et axes d'améliorations

* Ni le service d'email ni le module de paiement ne fonctionne. 
* Le visuel général du site est complètement aux fraises.
* La page 'promotion' est très maladroite, je l'ai ajouté un peu à la va vite sans y réfléchir. J'aurais dû en faire un filtre plutôt.
