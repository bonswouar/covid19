# COVID-19 API & Chart

## WHAT

This is just a quick project I made because I couldn't find any (free) vizualisation of the contamination trajectories.

Totally inspired by "Country by country : How coronavirus cases trajectories compare" by the Financial Times.

**Live demo here : https://covid19.drhugs.com/**

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

<p align="center">Cases, starting at the 100th, with 20 countries</p>


![Screenshot_2020-03-26 Covid-19 Charts](https://user-images.githubusercontent.com/615053/77681028-0f8e0f80-6f95-11ea-8e65-d78dec9d5683.png)

<p align="center">Deaths, starting at the 10th, with 4 countries</p>

![Screenshot_2020-03-26 Covid-19 Charts(1)](https://user-images.githubusercontent.com/615053/77681029-1026a600-6f95-11ea-82f5-c92ed84f3a5f.png)

## TODO

- [x] Clean code
- [ ] More API endpoint examples
- [ ] More charts examples
- [x] Dynamic charts with custom params

## CONTRIBUTING

Feel free to create a new issue in case you find a bug/want to have a feature added. Proper PRs are also welcome.

## SOURCES

Datas from ECDC, the European Centre for Disease Prevention and Control *(more accurate and consistent than World Health Organization's ones)*.

You can find the XLSX file here : https://www.ecdc.europa.eu/en/publications-data/download-todays-data-geographic-distribution-covid-19-cases-worldwide

Using [Chart.js](https://www.chartjs.org/) for the one chart, and [Skeleton](https://skeleton-framework.github.io/) + [Chosen](https://harvesthq.github.io/chosen/) for the basic template.
