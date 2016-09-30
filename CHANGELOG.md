New in 5.2
==========



Breaking changes (PHP side)
---------------------------

Compared to v5.1, Evolgrid does not feature anymore the `JSColumn` and `HtmlColumn` classes. Since those classes where mostly useless (they have been superseded by `TwigColumn`), nobody was using them. We therefore took the decision to remove them even if this is breaking the semantic versionning.
