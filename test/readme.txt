How to run the tests
====================

1.  You'll need to install the Selenium IDE to start with (from here: http://seleniumhq.org/download/).

2.  Copy the file setenv-sample.sh and rename the copy to setenv.sh.  Edit the file and set the mysql username and password
    to the root user for your environment (or at least a user who can create and grant privileges).
    
3.  Run the file setup.sh, which will create a database 'wptest' and user 'dba'.

4.  Install WordPress.  Set the wordpress databaes to wptest, user to 'dba' and password to 'dba'.

5.  Open Firefox.  Select Tools->Selenium IDE.  Click the File menu and the Open Test Suite.  Navigate to this directory,
    and select the file 'tests'.
    
6.  Play the entire test suite.  All going well, the tests should all be green at the end of the run.