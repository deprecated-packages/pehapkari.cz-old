# [Pehapkari.cz](https://pehapkari.cz) - Web of Czech and Slovak PHP Community

[![Build Status](https://img.shields.io/travis/pehapkari/pehapkari.cz/master.svg?style=flat-square)](https://travis-ci.org/pehapkari/pehapkari.cz)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/pehapkari/pehapkari.cz.svg?style=flat-square)](https://scrutinizer-ci.com/g/pehapkari/pehapkari.cz)


## How to Run Locally to Contribute

1. Fork

2. `git clone <your-fork>`

3. Install dependencies

This project requires a gulp library installed globally. If you don't have it install it with this command:
```bash
npm install --global gulp-cli
```

```bash
cd pehapkari.cz
composer update
npm install
```

4. Run Website

```sh
gulp
```

And open [http://localhost:8000](http://localhost:8000) in your browser.

That's all!



### Docker Enabled

Are you using Docker? [Here is how to run this project](docs/docker.md) in it.
