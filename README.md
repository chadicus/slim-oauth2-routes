# Chadicus\Slim\OAuth2\Routes

[![Build Status](http://img.shields.io/travis/chadicus/slim-oauth2-routes.svg?style=flat)](https://travis-ci.org/chadicus/slim-oauth2-routes)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/chadicus/slim-oauth2-routes.svg?style=flat)](https://scrutinizer-ci.com/g/chadicus/slim-oauth2-routes/)
[![Code Coverage](http://img.shields.io/coveralls/chadicus/slim-oauth2-routes.svg?style=flat)](https://coveralls.io/r/chadicus/slim-oauth2-routes)
[![Latest Stable Version](http://img.shields.io/packagist/v/chadicus/slim-oauth2-routes.svg?style=flat)](https://packagist.org/packages/chadicus/slim-oauth2-routes)
[![Total Downloads](http://img.shields.io/packagist/dt/chadicus/slim-oauth2-routes.svg?style=flat)](https://packagist.org/packages/chadicus/slim-oauth2-routes)
[![License](http://img.shields.io/packagist/l/chadicus/slim-oauth2-routes.svg?style=flat)](https://packagist.org/packages/chadicus/slim-oauth2-routes)
[![Documentation](https://img.shields.io/badge/reference-phpdoc-blue.svg?style=flat)](http://chadicus.github.io/slim-oauth2-routes)

OAuth2 routes for use within a Slim Framework API

## Requirements

Chadicus\Slim\OAuth2\Routes requires PHP 5.4 (or later).

##Composer
To add the library as a local, per-project dependency use [Composer](http://getcomposer.org)! Simply add a dependency on
`chadicus/slim-oauth2-routes` to your project's `composer.json` file such as:

```json
{
    "require": {
        "chadicus/slim-oauth2-routes": "dev-master"
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

##Example Usage
```php
use Chadicus\Slim\OAuth2\Routes;

//Set-up the OAuth2 Server
$storage = new OAuth2\Storage\Pdo(array('dsn' => $dsn, 'username' => $username, 'password' => $password));
$server = new OAuth2\Server($storage);
$server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));
$server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));

//Set-up the Slim Application
$slim = new \Slim\Slim();

Routes\Token::register($slim, $server);
Routes\Authorize::register($slim, $server);

//Add custom routes
$slim->get('/foo', function() use ($slim) {
    if(!isset($slim->request->headers['Authorization'])) {
        $slim->response->headers->set('Content-Type', 'application/json');
        $slim->response->setStatus(400);
        $slim->response->setBody(json_encode(['error' => 'Access credentials not supplied']));
        return;
    }

    $authorization = $slim->request->headers['Authorization'];

    //validate access token against your storage

    $slim->response->setBody('valid credentials');
});

$slim->run();
```
