Composer Archive Creator
========================

The idea of this project is to create a ready to use archive from a git repository using composer.

## Features

  * Neutral install (no errors possible due to local changes)
  * Download git repository
  * Fetch project's dependencies
  * Create archives with or without VCS files
  * Log
  * Alerting on fealure
  * Start unit tests on each dependency

## Usages

### Create standard archives

    php bin/composer-archiver package sonata-sandbox-v1.0.0 git@github.com:sonata-project/sandbox.git test

This will create 2 archives :

  * sonata-sandbox-v1.0.0.zip : this will contains all files in a zip archive without vcs files
  * sonata-sandbox-v1.0.0.tar.gz : this will contains all files in a tarball archive without vcs files

### Create standard archives including VCS files

    php bin/composer-archiver package sonata-sandbox-v1.0.0 git@github.com:sonata-project/sandbox.git test --vcs

This will create 4 archives :

  * sonata-sandbox-v1.0.0.zip : this will contains all files in a zip archive without vcs files
  * sonata-sandbox-v1.0.0_vcs.zip : this will contains all files in a zip archive with vcs files
  * sonata-sandbox-v1.0.0.tar.gz : this will contains all files in a tarball archive without vcs files
  * sonata-sandbox-v1.0.0_vcs.tar.gz : this will contains all files in a tarball archive with vcs files

### Create standard archives including only VCS files

    php bin/composer-archiver package sonata-sandbox-v1.0.0 git@github.com:sonata-project/sandbox.git test --only-vcs

This will create 2 archives :

  * sonata-sandbox-v1.0.0_vcs.zip : this will contains all files in a zip archive with vcs files
  * sonata-sandbox-v1.0.0_vcs.tar.gz : this will contains all files in a tarball archive with vcs files

### Create zip archive with only VCS files

    php bin/composer-archiver package sonata-sandbox-v1.0.0 git@github.com:sonata-project/sandbox.git test --reuse --only-vcs --format=zip

This will create 1 archive :

  * sonata-sandbox-v1.0.0_git.zip : this will contains all files in a zip archive with vcs files

## Use cases

  * create archive for client delivery
  * create archive to be download from a website
