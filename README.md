# Event [![Build Status](https://travis-ci.org/arkphp/event.png)](https://travis-ci.org/arkphp/event)

Simple event dispatching library for PHP.

This library is a fork from [igorw/evenement](https://github.com/igorw/evenement).

## Installation

```
composer require ark/event
```

## Usage

### Creating an Emitter

```php
<?php
$emitter = new Ark\Event\EventEmitter();
```

### Adding Listeners

```php
<?php
$emitter->on('user.created', function (User $user) use ($logger) {
    $logger->log(sprintf("User '%s' was created.", $user->getLogin()));
});
```

### Emitting Events

```php
<?php
$emitter->emit('user.created', array($user));
```