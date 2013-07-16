Using Evolugrid with the Javascript standalone library
======================================================

In this tutorial, we will see how to use Evolugrid in Javascript only (using the evolugrid.js file).

Let's get started with a very simple use case:

#### HTML Part
```html
<div id="#list"></div>
```

#### Javascript part
```js
$(document).ready(function() {
	$("#liste").evolugrid({
		url: "/mypath/search",
		limit  : 50
	});
});
```

As you can see, the model is not passed as an option to the evolugrid plugin.
This is because we will pass with the data returned by /mypath/search.

#### Data returned by the Ajax call (/mypath/search)

The Ajax call will be performed on your server.
Evolugrid will add automatically those GET parameters:

- **limit**: The maximum number of rows that should be returned
- **offset**: The place in the dataset where we should start
- **output**: This is either "json", or "csv" (in case the user requested an Excel/CSV export)
- **sort_key**: The key to sort upon (if a sort has been requested on a column)
- **sort_order**: The sort order (if a sort has been requested on a column). Can be "ASC" or "DESC"

Note: evolugrid can add additional parameters (see [search filters](searcj_filters.md) for more information)

```js
{
	'descriptor': {
		'columns': [
			{
				'title': 'Label',
				'display': 'label',
				'sortable': 'true',
			},
			{
				'title': 'Creation date',
				'display': 'creation_date'
			}
		]
	},
	'data': [
		{
			'id': 12,
			'label': 'row one',
			'creation_date': '12/12/2012'
		},
		{
			'id': 15,
			'label': 'row two',
			'creation_date': '02/10/2014'
		},
		...
	],
	'count': 142
}
```

As you can notice, the data is passed as a JSON string.
It is split in 3 sections:

- **descriptor**: the descriptor contains additional configuration data for the grid. Almost any parameter that can
be passed as an option to the evolugrid plugin can also be passed/overwritten in the Ajax call (hence the name "evolugrid")
- **data**: this is the actual data that will be displayed in the grid (you only pass what needs to be displayed)
- **count**: the total number of rows the result set contains

[Have a look at the list of available options](options.md)