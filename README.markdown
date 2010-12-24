Tadpole CMS
===========
Basically, the idea behind Tadpole CMS was simply to take the flexibility and control that Frog CMS (which is a PHP port of the great Radiant CMS) for the frontend of the website to the backend as well. What I mean by this is that with Tadpole CMS, just like you can edit every page on the frontend of your website using Frog CMS, you can edit the administration interface itself (which is basically all that Tadpole CMS is, an administration interface) just as if it were a normal page on your website. This means that you have complete and total control over the backend/administration interface of the site and can edit and rework it to do anything that you want.

One benefit of this is that, if you're making a website for a very technologically-challenged person, you can make the administration interface extremely bare and simple. On the other hand, if there is some important feature that is missing from the CMS that you want (which there probably is since it is currently a very simple CMS), then you can go ahead and code that feature yourself without having to learn how to write a module or muck around with hacking the CMS too much (since it's meant to be hacked!).

I've also created a nice little abstraction interface for the database which can be found in includes/db.mdb2.php. I don't plan to use only MDB2 to access the database, in the future I plan to make a db.mysql.php, db.pgsql.php, db.sqlite.php and so on, but for now I'll let MDB2 do the abstraction for me. That being said, Tadpole has only been tested with MySQL so far, so it is highly likely that it will not work with any other database backend.

If you have any questions about the design or how it works feel free to ask me and send me a message (my email is butchler at gmail.com)! I would be very happy if anybody were interested in my simple little CMS and wanted to learn more about it or even help improve it.

Steps for installation
----------------------
1. Copy Tadpole somewhere into your web server.
2. From your web browser, go to {wherever you installed Tadpole}/install
3. Follow the instructions and hopefully everything will go well!
