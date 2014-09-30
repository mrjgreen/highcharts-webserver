##Run a highcharts graph generation server

For quick start, spin up a Cent OS 6.5 instance and install the correct puppet configs from here: https://github.com/joegreen0991/puppet/tree/master/puppet using the following bootstrap script in your home directory

~~~
bash -c "$(curl -fsSL http://git.io/1qTpDw)" -s highcharts
~~~

Install this PHP web application to handle requests and pass to phantomjs/highcharts

~~~
git clone https://github.com/joegreen0991/highcharts-webserver /srv/web/highcharts-webserver
/srv/web/highcharts-webserver/composer.phar install --working-dir /srv/web/highcharts-webserver
~~~

Then run the following command to download the highcharts files

~~~
git clone https://github.com/highslide-software/highcharts.com /srv/highcharts
~~~

And away you go!

POST a request at your new server with the correct parameters:

~~~

~~~
