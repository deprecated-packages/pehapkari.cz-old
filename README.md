# Symfony.cz - Sculpin based web

[![Build Status](https://img.shields.io/travis/Symfonisti/symfony.cz.svg?style=flat-square)](https://travis-ci.org/Symfonisti/symfony.cz)
[![Gitter chat](	https://img.shields.io/gitter/room/webuni/symfony.js.svg?style=flat-square)](https://gitter.im/webuni/symfony)


## How to run it?

```sh
composer update
vendor/bin/sculpin generate --watch --server --port 8001 # it needs to be run from vendor, to autoload all composer classes 
```

And open `http://localhost:8001`.

## For production?

```sh
vendor/bin/sculpin generate --env=prod
```
