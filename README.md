# Chadicus\Slim\OAuth2\Routes

[![Build Status](https://travis-ci.org/chadicus/slim-oauth2-routes.svg?branch=master)](https://travis-ci.org/chadicus/slim-oauth2-routes)
[![Code Quality](https://scrutinizer-ci.com/g/chadicus/slim-oauth2-routes/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/chadicus/slim-oauth2-routes/?branch=master)
[![Code Coverage](https://coveralls.io/repos/github/chadicus/slim-oauth2-routes/badge.svg?branch=master)](https://coveralls.io/github/chadicus/slim-oauth2-routes?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/55b908ed653762001a00133c/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55b908ed653762001a00133c)

[![Latest Stable Version](https://poser.pugx.org/chadicus/slim-oauth2-routes/v/stable)](https://packagist.org/packages/chadicus/slim-oauth2-routes)
[![Latest Unstable Version](https://poser.pugx.org/chadicus/slim-oauth2-routes/v/unstable)](https://packagist.org/packages/chadicus/slim-oauth2-routes)
[![License](https://poser.pugx.org/chadicus/slim-oauth2-routes/license)](https://packagist.org/packages/chadicus/slim-oauth2-routes)

[![Total Downloads](https://poser.pugx.org/chadicus/slim-oauth2-routes/downloads)](https://packagist.org/packages/chadicus/slim-oauth2-routes)
[![Daily Downloads](https://poser.pugx.org/chadicus/slim-oauth2-routes/d/daily)](https://packagist.org/packages/chadicus/slim-oauth2-routes)
[![Monthly Downloads](https://poser.pugx.org/chadicus/slim-oauth2-routes/d/monthly)](https://packagist.org/packages/chadicus/slim-oauth2-routes)

[![Documentation](https://img.shields.io/badge/reference-phpdoc-blue.svg?style=flat)](http://pholiophp.org/chadicus/slim-oauth2-routes)

[OAuth2 Server](http://bshaffer.github.io/oauth2-server-php-docs/) route callbacks for use within a [Slim 3 Framework](http://www.slimframework.com/) API

## Requirements

Chadicus\Slim\OAuth2\Routes requires PHP 5.6 (or later).

##Composer
To add the library as a local, per-project dependency use [Composer](http://getcomposer.org)! Simply add a dependency on
`chadicus/slim-oauth2-routes` to your project's `composer.json` file such as:

```json
{
    "require": {
        "chadicus/slim-oauth2-routes": "~3.0"
    }
}
```

##Contact
Developers may be contacted at:

 * [Pull Requests](https://github.com/chadicus/slim-oauth2-routes/pulls)
 * [Issues](https://github.com/chadicus/slim-oauth2-routes/issues)

##Project Build
With a checkout of the code get [Composer](http://getcomposer.org) in your PATH and run:

```sh
./composer install
./vendor/bin/phpunit
```

## A Note on Using Views
The `authorize` and `receive-code` route require `view` objects. The given view object must implement a render method such as the one found in [slim/twig-view](https://github.com/slimphp/Twig-View/blob/master/src/Twig.php#L103) and [slim/php-view](https://github.com/slimphp/PHP-View/blob/master/src/PhpRenderer.php#L64). It would be best if there was a common `ViewInterface` which both implementing but as of now such an interface does not exist.

##Example Usage
```php
use Chadicus\Slim\OAuth2\Routes;
use OAuth2;
use OAuth2\GrantType;
use OAuth2\Storage;
use Slim;
use Slim\Views;

//Set-up the OAuth2 Server
$storage = new Storage\Pdo(array('dsn' => $dsn, 'username' => $username, 'password' => $password));
$server = new OAuth2\Server($storage);
$server->addGrantType(new GrantType\AuthorizationCode($storage));
$server->addGrantType(new GrantType\ClientCredentials($storage));

//Set-up the Slim Application
$app = new Slim\App(
    [
        'view' => new Views\PhpRenderer('/path/to/chadicus/slim-oauth2-routes/templates'),
    ]
);

$container = $app->getContainer();

$app->map(['GET', 'POST'], Routes\Authorize::ROUTE, new Routes\Authorize($server, $container['view']))->setName('authorize');
$app->post(Routes\Token::ROUTE, new Routes\Token($server)->setName('token');
$app->map(['GET', 'POST'], Routes\ReceiveCode::ROUTE, new Routes\ReceiveCode($server, $container['view']))->setName('receive-code');
$app->post(Routes\Revoke::ROUTE, new Routes\Revoke($server))->setName('revoke');

//Add custom routes
$slim->get('/foo', function($request, $response, $args) {
    if(!isset($request->getHeaders()['Authorization'])) {
        return $response->withStatus(400);
    }

    $authorization = $slim->request->headers['Authorization'];

    //validate access token against your storage

    return $response->withStatus(200);
});

//run the app
$app->run();
```
