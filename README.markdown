Tadpole CMS
===========
Tadpole CMS is a simple content management system loosely based off of Frog CMS. The special thing about it is that you can edit the administration interface of the CMS, the '/admin' area of Frog, in exactly the same way that you can edit regular pages on your website. This means that you can easily change the administration interface of your site to meet the specific needs of that particular site, whether it needs to be dead simple and super user-friendly, or very complex and have loads of added functionality.

Even though you can change the CMS as much as you want as long as you know HTML and PHP, you can still use it out of the box as a standalong CMS similar to Frog.

Design Overview
---------------
Tadpole is split into three main parts. The first part and by far the smallest and simplest one is the index.php file. The sole purpose of it is to 1) connect to the database using the settings stored in the config.php file, and 2) load the 'Loader' page from the database, which does all the actual work of loading pages, and execute it.

The next part is the includes/ folder. This folder simply contains a collection of .php include files containing useful utility functions for working with the database, working with pages, and dealing with user authentication. If you need more functionality, you can simply make your own include files and drop them into the folder. Then you can include them in your code using the INCLUDE_PATH constant.

The last part is simply all the pages and other data stored in the database, which is put there by the install scripts. This is the part that does all of the actual work of the CMS, such as loading the pages, providing an administration interface for you to manage the website, and so on.

Steps for installation
----------------------
1. Copy Tadpole somewhere into your web server.
2. From your web browser, go to {wherever you installed Tadpole}/install
3. Follow the instructions and hopefully everything will go well!

