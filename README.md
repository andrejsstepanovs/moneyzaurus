[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/wormhit/moneyzaurus/badges/quality-score.png?s=db5f1345a86972d7d9f220f25182adc376a3e76c)]
(https://scrutinizer-ci.com/g/wormhit/moneyzaurus/)

moneyzaurus
=============

Simple expenditure planning system.


Setup and deployment
-------

Create new OpenShift account. Install rhc. Create app.

``` sh
    rhc app-create app php-5.4 mysql-5.5 --from-code=https://github.com/wormhit/moneyzaurus.git
```

After that, import db and you're ready.


Alert
-------

Code is still in active development phase.
