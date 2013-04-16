Evolugrid's search filters
==========================

When developping an Ajax grid, it is quite common to provide search filters.

Evolugrid will not generate the search form for you (the search form might be quite complex).
However, it will help you to bind the search form to your grid easily.

Here is a sample (using the JS library, but you can do this using the PHP library as easily):

#### HTML Part
```html
<form id="searchForm">
	<label>Search by name</label>
	<input type="text" name="name" />
	<button type="submit">Search</button>
</form>

<div id="#list"></div>
```

#### Javascript part
```js
$(document).ready(function() {
	$("#liste").evolugrid({
		url: "/mypath/search",
		limit  : 50,
		filterForm : $("#searchForm")
	});
});
```

As you can see, we add a search form right above the Evolugrid.
In Evolugrid, we add a "filterForm" parameter pointing to the search form.

Now, each time we submit the form, the fields of the form will be also passed in the Ajax request.

Assuming we are using the Splash MVC framework, our controller would look like this:

```php
class MyController extends Controller {

	...

	/**
	 * @URL /userlist
	 */
	public function userlist($name=null, $limit=null, $offset=null, $output="json") {
		// Retrieve the rows in database
		$rows = $this->getDataByName($name, $limit, $offset);
		
		$evoluGrid = Mouf::getMyEvoluGrid();
		
		// You should return only the pages to be displayed.
		$evoluGrid->setRows($obj);
		
		// The second parameter is the download name of the file generated (should the user
		// click on the "export" button).
		// The output method can generate the JSON or the CSV output.
		$evoluGrid->output($output, "filename.csv");
	}
}
```
