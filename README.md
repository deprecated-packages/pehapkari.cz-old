# Pehapkari.cz - [Statie](https://github.com/Symplify/Statie) based web

[![Build Status](https://img.shields.io/travis/pehapkari/pehapkari.cz.svg?style=flat-square)](https://travis-ci.org/pehapkari/pehapkari.cz)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/pehapkari/pehapkari.cz.svg?style=flat-square)](https://scrutinizer-ci.com/g/pehapkari/pehapkari.cz)
[![Quality Score](https://img.shields.io/scrutinizer/g/pehapkari/pehapkari.cz.svg?style=flat-square)](https://scrutinizer-ci.com/g/pehapkari/pehapkari.cz)


## How to run it?

Install project with dependencies

```sh
composer create-project pehapkari/website pehapkari.cz @dev
```

Generate website to static HTML with live reload

```sh
cd pehapkari.cz
vendor/bin/statie generate --server
```

And open `http://localhost:8000` in your browser


*Are you using Docker? [Here is how to run this project](docs/docker.md) in it.*  
