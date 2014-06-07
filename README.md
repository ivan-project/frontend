TRUEDETECTION FRONTEND
======================
This is only first draft. Things in existing paragraphs can more or less change.

1. VIEWS REPOSITORY FOR TESTERS
-------------------------------

Screenshots made by @karolkochan with use of Google Chrome Browser v.35.0.1916.114m are available in: 
ivan-project/frontend/design/views/*

Known issues:
- Not every browser supports HTML5 form validators

2. DATABASE
---------

Users Collection fields:
- _id
- email
- name
- role
- hash
- salt
- token
- created_at
- updated_at

Documents Collection fields:
- _id
- _owner
- title
- author
- email
- created_at
- updated_at
