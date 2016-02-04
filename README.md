# Chadicus\Slim\OAuth2\Routes

[![Build Status](https://travis-ci.org/chadicus/slim-oauth2-middleware.svg?branch=master)](https://travis-ci.org/chadicus/slim-oauth2-middleware)
[![Code Quality](https://scrutinizer-ci.com/g/chadicus/slim-oauth2-middleware/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/chadicus/slim-oauth2-middleware/?branch=master)
[![Code Coverage](https://coveralls.io/repos/github/chadicus/slim-oauth2-middleware/badge.svg?branch=master)](https://coveralls.io/github/chadicus/slim-oauth2-middleware?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/55b908ed653762001a00133c/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55b908ed653762001a00133c)

[![Latest Stable Version](https://poser.pugx.org/chadicus/slim-oauth2-middleware/v/stable)](https://packagist.org/packages/chadicus/slim-oauth2-middleware)
[![Latest Unstable Version](https://poser.pugx.org/chadicus/slim-oauth2-middleware/v/unstable)](https://packagist.org/packages/chadicus/slim-oauth2-middleware)
[![License](https://poser.pugx.org/chadicus/slim-oauth2-middleware/license)](https://packagist.org/packages/chadicus/slim-oauth2-middleware)

[![Total Downloads](https://poser.pugx.org/chadicus/slim-oauth2-middleware/downloads)](https://packagist.org/packages/chadicus/slim-oauth2-middleware)
[![Daily Downloads](https://poser.pugx.org/chadicus/slim-oauth2-middleware/d/daily)](https://packagist.org/packages/chadicus/slim-oauth2-middleware)
[![Monthly Downloads](https://poser.pugx.org/chadicus/slim-oauth2-middleware/d/monthly)](https://packagist.org/packages/chadicus/slim-oauth2-middleware)

[![Documentation](https://img.shields.io/badge/reference-phpdoc-blue.svg?style=flat)](http://pholiophp.org/chadicus/slim-oauth2-middleware)

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

//By default templates are found in this repositories templates folder.
$slim->config('templates.path', '/path/to/chadicus/slim-oauth2-routes/templates');

Routes\Token::register($slim, $server);
//You can add your custom authorize template here
Routes\Authorize::register($slim, $server, 'authorize.phtml');
//You can add your custom receive-code template here
Routes\ReceiveCode::register($slim, 'receive-code.phtml');

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
