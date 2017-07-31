
Installation
============

When downloaded as a ZIP file from http://hubzero.org
unzip and place the resulting directory into YOUR_HUB_WEBROOT/app/components

The final result should look like:

    /app
    .. /components
    .. .. /com_dwho
    .. .. .. /admin
    .. .. .. /api
    .. .. .. /config
    .. .. .. /helpers
    .. .. .. /migrations
    .. .. .. /models
    .. .. .. /site
    .. .. .. install.sql
    .. .. .. drwho.xml

The install.sql file contains SQL for creating the needed database tables and populating them
with sample data. This may be manually added to the database or installed via the "discover"
feature of the extensions manager:

== Extensions Manager ==

Login to the administrator area. Go to "Extensions > Extensions Manager". Click the sub-menu
item "Discover". From that page, click "Discover" in the toolbar. If you see "Dr Who" show up
in the resulting list, click the checkbox next to it and click the "Install" button.

== Muse ==

Alternatively, the component comes with migrations that can be run via the command-line utility, 
Muse, that comes with all hubs. To do so, move the files from the component's /migrations
directory and place them in the /app/migrations folder.

The older migration is required fro the component to function properly and installs just the 
database tables and registers the component with the CMS. The other migration is optional and 
only installs sample content.

From the command-line, starting in your hub's root directory, you can run all migrations with:

    $ php muse migration -f