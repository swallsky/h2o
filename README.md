H2O - As simple as water, as important as water
========================================
…or create a new repository on the command line

echo # H2O >> README.md
git init
git add README.md
git commit -m "first commit"
git remote add h2o https://github.com/ybluesky/h2o.git
git push -u h2o master

…or push an existing repository from the command line

git remote add h2o https://github.com/ybluesky/h2o.git
git push -u h2o master

…or import code from another repository

You can initialize this repository with code from a Subversion, Mercurial, or TFS project.

composer require "ybluesky/h2o":"0.1.*@dev"