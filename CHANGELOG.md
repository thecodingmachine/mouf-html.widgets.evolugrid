New in 5.2
==========



Breaking changes (PHP side)
---------------------------

Compared to v5.1, Evolgrid does not feature anymore the `JSColumn` and `HtmlColumn` classes. Since those classes where mostly useless (they have been superseded by `TwigColumn`), nobody was using them. We therefore took the decision to remove them even if this is breaking the semantic versioning.


Other changes
-------------

The `SimpleColumn` and `TwigColumn` can now consume objects in a more intelligent way.

`SimpleColumn` now uses [symfony/property_access](http://symfony.com/doc/current/components/property_access.html) under the hood.

This means you can write columns like:

```php
$simpleColumn = new SimpleColumn('Country', 'country.name');
```

Behind the scenes, this could access a "deep" property of the result set, using getters if needed (so this might be transformed into a `$obj->getCountry()->getName()` internally).

Also, the default for exporting columns in CSV has changed. Unless a value is set for the "isExported" field, columns are now exportable only if their output is escaped in HTML. The rationale is that if a column is not HTML escaped, it means it contains HTML. If a column contains HTML, it is likely you don't want to export it in CSV.
