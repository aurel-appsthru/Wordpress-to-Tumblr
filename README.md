#Wordpress to Tumblr 

##About 

PHP script that help to export Wordpress posts to Tumblr.

It can serve as a basis because it contains the resolution of several export issues :

 
##Features

- Import Tags

- Import posts types according to categories

- Use custom fields to import

- Make a list of source/destination posts URL to easily create redirections rules ( eg. redirect permanent 301 Apache )

- Deals with [sourcode] and [caption] Wordpress shortcodes ( using the [backpress](http://backpress.org/)  shortcodes lib )

 
##Configuration

Copy the sample configuration file __config.php.sample__ to __config.php__

##Redirection

In the config file, indicate a file name for $logFile.

The log file is in the CSV format :

     source-URL ; destination-URL
 
So you can easily create rules for your web server. 

    Redirect permanent source-URL destination-URL 

###Disclaimer

This script is based on the [Wordpress-to-Tumblr](https://github.com/thetylerhayes/Wordpress-to-Tumblr.git) script, but it's not a Fork in view of all changes and additions that are made.
 
