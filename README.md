# COVID-19 API & Chart

## WHAT

This is just a quick project I made because I couldn't find any (free) vizualisation of the contamination trajectories.

Totally inspired by "Country by country : How coronavirus cases trajectories compare" by the Financial Times.

## REQUIREMENT

- PHP 7.2.5 or higher and these PHP extensions (which are installed and enabled by default in most PHP 7 installations): Ctype, iconv, JSON, PCRE, Session, SimpleXML, and Tokenizer, Curl
- Composer : https://getcomposer.org/download/
- Some database, tested only with MySQL, should work with most
- A web server like Apache or Nginx, or [Symfony's one](https://symfony.com/doc/current/setup/symfony_server.html)

## INSTALLATION

Like any PHP Symfony project. If you don't know, read the [docs](https://symfony.com/doc/current/index.html).

```
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migration:migrate
[...]
```

Don't forget to add your custom `.env.local` file for your database configuration.

## USAGE

To refresh your database with online datas just use the command :
```
php bin/console app:update-data
```
With optional parameters `--force` to force refresh even if already updated, and `--clear` to previously empty your database.

You can add this command to a CRON to update every day.

## Visualization

For now I've just added one, you can try it from the base url of your webserver previously configured *(for example http://127.0.0.1:8080/)*

Should look like this :

<p align="center">Cases, starting at the 100th, with 30 countries</p>


![Screenshot_2020-03-23 Covid-19 Charts](https://user-images.githubusercontent.com/615053/77332071-d8fc8e80-6d21-11ea-865e-51a5380989ae.png)

<p align="center">Deaths, starting at the 10th, with 4 countries</p>

![Screenshot_2020-03-23 Covid-19 Charts2](https://user-images.githubusercontent.com/615053/77332072-d9952500-6d21-11ea-92dd-52dce4e1d6fc.png)

## TODO

- [ ] Clean code
- [ ] More API endpoint examples
- [ ] More charts examples
- [x] Dynamic charts with custom params

## CONTRIBUTING

Feel free to create a new issue in case you find a bug/want to have a feature added. Proper PRs are also welcome.

## SOURCES

Datas from ECDC, the European Centre for Disease Prevention and Control *(more accurate and consistent than World Health Organization's ones)*.

You can find the XLSX file here : https://www.ecdc.europa.eu/en/publications-data/download-todays-data-geographic-distribution-covid-19-cases-worldwide

Using [Google Charts](https://developers-dot-devsite-v2-prod.appspot.com/chart/interactive/docs/gallery/linechart) for the one chart, and [Skeleton](https://skeleton-framework.github.io/) + [Chosen](https://harvesthq.github.io/chosen/) for the basic template.
