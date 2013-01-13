Boardsolution
=============

Boardsolution is an open-source discussion board written in PHP, uses a MySQL
backend and is based on [FrameWorkSolution](/script-solution/FrameWorkSolution).
It provides everything that you would expect from a discussion board like
forums, topics, posts, polls, appointments, private messages, moderators, user
groups, a search function, a calendar, a portal, a sophisticated BBCode engine,
multiple languages, a feature-rich admin control panel and much more.
Additionally, it uses a [BBCodeEditor](/script-solution/BBCodeEditor) to allow
the user to write posts in a what-you-see-is-what-you-get way.  
Since it is template-based and constructed in a very modular way, it is easy to
customize and extend.

Getting started
---------------

When you want to try it directly from the git repository, please do the
following steps:

1. Clone this repo
2. `git submodule update`
3. Rename `_install.php` to `install.php`
4. Follow the installation instructions below

Installation
------------

1. Upload all files to your server.
2. Open the `install.php` in your browser and follow the installation.
3. Now delete the file `install.php`.
4. If you want to include Boardsolution into another PHP script, it may cause
   trouble (e.g. you might see an error like "header already sent by ..."). You
   can prevent that by doing the following:  
   Put the following code at the very beginning (This is **really** important!)
   of the file in which you include Boardsolution (if this file will be
   included, too, you have to put this in the "root-file"):  
   `<?php ob_start(); ?>`  
   and at the end of this file you add the following:  
   `<?php ob_end_flush(); ?>`  
5. Now you can login to the board with the username and password you've set in
   the installation and configure Boardsolution like you desire. Please note
   also the options in the file `config/userdef.php`.

If you have problems with Boardsolution of any kind, feel free to ask for help
in the [support-board](http://www.script-solution.de/community/support-board).

Updates
-------

An update is only possible from Boardsolution v1.3x. If you have an older
version you have to update step by step.  
Please backup the complete board in every case. Just to be sure. "Complete"
means that you should save the database-content (you can do that for example
with Boardsolution (Adminarea -> Database-backup), phpMyAdmin or something
similar) and save all files of the board.

Please delete at first all files and folders of the old version to prevent
issues. But be careful: Don't delete important data (added by users or by
yourself). These are the following folders:

* dbbackup/
* images/avatars/
* images/smileys/
* uploads

Please do also note the following:  
If you have changed the design of a theme in the adminarea, the file
themes/$theme/style.css has been changed. If you have changed templates in the
adminarea this has been done in themes/$theme/templates/. Perhaps you have also
changed something in the config/userdef.php.

Besides the above mentioned things, the only difference in the installation
compared to a full installation is that you have to choose "Update".

