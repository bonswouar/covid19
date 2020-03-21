# COVID-19 API & Chart

## WHAT

This is just a quick project I made because I couldn't find any (free) vizualisation of the contamination trajectories.

Totally inspired by "Country by country : How coronavirus cases trajectories compare" by the Financial Times.

## REQUIREMENT

- PHP 7.2.5 or higher and these PHP extensions (which are installed and enabled by default in most PHP 7 installations): Ctype, iconv, JSON, PCRE, Session, SimpleXML, and Tokenizer, Curl;
- Composer : https://getcomposer.org/download/
- Some database, tested only with MySQL, should work with most
- A web server like Apache or Nginx, or [Symfony's one](https://symfony.com/doc/current/setup/symfony_server.html)

## INSTALLATION

Like any PHP Symfony project. If you don't know, read the [docs](https://symfony.com/doc/current/index.html).

Don't forget to add your custom `.env.local` file for your database configuration.

```
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migration:migrate
[...]
```

## USAGE

To refresh your database with online data just use the command :
```
php bin/console app:update-data
```
With optional parameters `--force` to force refresh even if already updated, and `--clear` to previously empty your database.

You can add this command to a CRON to update every day.

## Visualization

For now I've just added one, you can try it from the base url of your webserver previously configured *(for example http://127.0.0.1:8080/)*

Should look like this :
![screenshot](https://user-images.githubusercontent.com/615053/77235504-7935a480-6bb6-11ea-8ac2-4b168f4ae162.png)

You can add more countries or start from something else than the 100th case, by setting `$minCases`and `$maxCountries` in `ApiController.php`. I'll do something cleaner / more user-friendly later, maybe.

## TODO

- [ ] Clean code
- [ ] More API endpoint examples
- [ ] More charts examples
- [ ] Dynamic charts with custom params

## CONTRIBUTING

Feel free to create a new issue in case you find a bug/want to have a feature added. Proper PRs are also welcome.

## SOURCES

Datas from European Centre for Disease Prevention and Control.

You can find the XLSX file here : https://www.ecdc.europa.eu/en/publications-data/download-todays-data-geographic-distribution-covid-19-cases-worldwide

Using [Google Charts](https://developers-dot-devsite-v2-prod.appspot.com/chart/interactive/docs/gallery/linechart) for the one chart.
