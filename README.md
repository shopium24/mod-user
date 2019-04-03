mod-user
===========
Module for shopium24

[![Latest Stable Version](https://poser.pugx.org/shopium24/mod-user/v/stable)](https://packagist.org/packages/shopium24/mod-user)
[![Total Downloads](https://poser.pugx.org/shopium24/mod-user/downloads)](https://packagist.org/packages/shopium24/mod-user)
[![Monthly Downloads](https://poser.pugx.org/shopium24/mod-user/d/monthly)](https://packagist.org/packages/shopium24/mod-user)
[![Daily Downloads](https://poser.pugx.org/shopium24/mod-user/d/daily)](https://packagist.org/packages/shopium24/mod-user)
[![Latest Unstable Version](https://poser.pugx.org/shopium24/mod-user/v/unstable)](https://packagist.org/packages/shopium24/mod-user)
[![License](https://poser.pugx.org/shopium24/mod-user/license)](https://packagist.org/packages/shopium24/mod-user)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist shopium24/mod-user "*"
```

or add

```
"shopium24/mod-user": "*"
```

to the require section of your `composer.json` file.

Add to web config.
```
'modules' => [
    'user' => ['class' => 'shopium24\user\Module'],
],


and

'components' => [
    'user' => [
        'class' => 'shopium24\mod\user\components\User',
        // 'identityClass' => 'shopium24\mod\user\models\User',
        // 'enableAutoLogin' => false,
    ],
    ...
]
```