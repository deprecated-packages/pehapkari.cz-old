# Pehapkari.cz - Sculpin based web

[![Build Status](https://img.shields.io/travis/pehapkari/pehapkari.cz.svg?style=flat-square)](https://travis-ci.org/pehapkari/pehapkari.cz)

## How to run it?

```sh
composer update
vendor/bin/sculpin generate --watch --server
```

And open `http://localhost:8000`.

### Develop in [Docker](https://www.docker.com/) container

Make sure that you have this tools installed:

- [Docker](https://www.docker.com/products/overview#/install_the_platform)
- [Docker compose](https://docs.docker.com/compose/install/)

Just run:  

```sh
$ docker-compose up app
```