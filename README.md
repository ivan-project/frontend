TRUEDETECTION FRONTEND
======================
This is only first draft. Things in existing paragraphs can more or less change.

1. VIEWS REPOSITORY FOR TESTERS
-------------------------------

Screenshots made by @karolkochan with use of Google Chrome Browser v.35.0.1916.114m are available in: 
ivan-project/frontend/design/views/*

Known issues:
- Not every browser supports HTML5 form validators

2. DATABASE IMPORT
---------

SSH to Vagrant
```bash
$ vagrant ssh
```

Move to frontend directory 
```bash
$ cd /var/ivan/frontend
```

Drop old database (optional) and import newest
```bash
$ mongo ivan --eval "db.dropDatabase()"
$ mongorestore -d ivan db/ivan
```