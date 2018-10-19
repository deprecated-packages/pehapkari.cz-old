### Develop in [Docker](https://www.docker.com/) container

Make sure that you have this tools installed:

- [Docker](https://www.docker.com/products/overview#/install_the_platform)
- [Docker compose](https://docs.docker.com/compose/install/)

Clone the project:

```
git clone https://github.com/pehapkari/pehapkari.cz.git
cd pehapkari.cz
```

and run:

```sh
$ docker-compose up app
```

and open `http://localhost:8000` or some other host (e.g. `http://default:8000`) if you are using some Vitual Machine provider (e.g. Virtualbox, Parallels).

If you get "Connection interrupted" in your browser, try stopping the Docker app (`CTRL+C`) and running it again (`docker-compose up app`).
