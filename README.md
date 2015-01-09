##Run a highcharts graph generation server

For quick start:

* Spin up a Cent OS 6.5 instance
* Install the puppet configs from here: https://github.com/mrjgreen/puppet/tree/master/puppet which installs/sets up:
    * Nginx
    * Phantomjs and dependencies
    * PHP-FPM
    * Tools and utils like nano/git
    * IPTables rules for HTTP/SSH
    * NTPd and sets UTC timezone
    * Disables SELinux
* Download the highcharts export library https://github.com/highslide-software/highcharts.com/tree/master/exporting-server/phantomjs
* Download and install this PHP application to handle requests, pass to phantomjs/highcharts and redirect to generated chart

Here is a handy copy and paste for all the above
~~~
#Install the puppet configs using a bootstrap file
bash -c "$(curl -fsSL http://git.io/1qTpDw)" -s highcharts

#Install the highcharts library
git clone https://github.com/highslide-software/highcharts.com /srv/highcharts

#Install the PHP application
git clone https://github.com/mrjgreen/highcharts-webserver /srv/web/highcharts-webserver
/srv/web/highcharts-webserver/composer.phar install --working-dir /srv/web/highcharts-webserver

#Create the output directory with correct permissions
mkdir /srv/web/highcharts-webserver/public/charts
chmod a+w /srv/web/highcharts-webserver/public/charts
~~~

And away you go!

POST a request at your new server with the correct parameters:

~~~
http://charts.example.com/?infile=%7B%22xAxis%22%3A+%7B%22categories%22%3A+%5B%22Jan%22%2C+%22Feb%22%2C+%22Mar%22%2C+%22Apr%22%2C+%22May%22%2C+%22Jun%22%2C+%22Jul%22%2C+%22Aug%22%2C+%22Sep%22%2C+%22Oct%22%2C+%22Nov%22%2C+%22Dec%22%5D%7D%2C+%22series%22%3A+%5B+%7B%22data%22%3A+%5B29.9%2C+71.5%2C+106.4%2C+129.2%2C+144.0%2C+176.0%2C+135.6%2C+148.5%2C+216.4%2C+194.1%2C+95.6%2C+54.4%5D%7D+%5D+%7D&width=1000
~~~

##NB. Filenames
Currently the filename is generated from a SHA1 hash of the parsed infile/callback parameters along with the width/constr/scale parameters. THERE MAY BE HASH COLLISIONS.

This code is not intended to run with public access. It is a very basic set up intended for internal "trusted" users, with the ability to write/delete/overwrite any generated charts/graphs

 > The source code could easily be altered to add a user api key with privileges based on folder names etc.. and generate a unique filename for each request.

##Description of URL parameters

**infile**: The highcharts JSON configuration to convert. The script will try to fix unquoted keys and single quotes, which may have undesired effects. See `useraw` below.

**noredirect**: Return the url of the generated image rather than a 302 redirect response

**id**: A unique identifier for the chart. If you supply this, we will sha1 encode the supplied value to generate the filename. You can supply the same id again and to overwrite the previous version.

**useraw**: Force the script to use the JSON as given, without trying to correct incorrectly quoted JSON keys.

**scale**: Default 2.5. To set the zoomFactor of the page rendered by PhantomJS. For example, if the chart.width option in the chart configuration is set to 600 and the scale is set to 2, the output raster image will have a pixel width of 1200. So this is a convenient way of increasing the resolution without decreasing the font size and line widths in the chart. This is ignored if the width parameter is set.

**width**: Set the exact pixel width of the exported image or pdf. This overrides the scale parameter.

**constr**: Default Chart. The constructor name. Can be one of Chart or StockChart. This depends on whether you want to generate Highstock or basic Highcharts.

**callback**: The callback is a function which will be called in the constructor of Highcharts to be executed on chart load. All code of the callback must be enclosed by a function. See this example of contents of the callback parameter:

**delete**: If this argument is supplied or if the request method is DELETE, the filename that would be generated based on the above parameters will instead be removed, if it exists

###S3 Params

Set the following parameters to automatically push charts to s3 instead of storing locally

**awskey**: Your amazon api key

**awssecret**: Your amazon api secret

**awsbucket**: The amazon bucket to push to

**awsregion**: The region your bucket is in

**awsprefix**: A string prefix to prefix the object name with (optional)
