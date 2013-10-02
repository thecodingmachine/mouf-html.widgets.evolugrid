List of options accepted by Evolugrid
=====================================

Here is a sample of all options available. Explanation is below:
	
	{
		url: url, // The Ajax URL
		limit  : 100, // The maximum number of rows to be returned in one page
		tableClasses : "table", // The CSS class of the table
		pagerId : 'listePager' // The ID of the pager,
		export_csv: true, // Whether we can export to CSV or not,
		loadOnInit: true, // Whether we should start loading the table (true) or wait for the user to submit the search form (false),
		filterForm: "selector", // A jQuery selector pointing to the form containing the filters (if any)
		filterFormSubmitButton: "selector", // A jQuery selector pointing to the button that will trigger search. This is optional, and can only be used if the filterForm option is used. If not passed, any submit button on the form will trigger a search.
	  	filterCallback: function, // A function taking 0 arguments and returning a map of filters (passed as arguments to the Ajax URL). This is applied before the filterForm.
		rowCssClass: "key", // If set, for each row, we will look in the dataset for the row, for the "key" passed in parameter. The associated value will be used as a class of the tr row. 
		loaderImgDiv: "selector", // A jQuery selector pointing to a div that contains a ajax loader gif
		trClickable: false, // Whether we should click on tr. this call js function evolugridTrClickable(rowObject)
		columns: [
			{
				"title": "Name",
				"key": "name",
				"sortable": true
			},
			{
				"title": "First name",
				"key": "first_name"
			},
			{
				"title": "Edit",
				"jsrenderer": function(row) {
					return $("&lt;a/&gt;").text(row["name"]).attr("href", "/mylink.php?id="+row.id)
				}
			}
		] 
	} 

<div class="alert alert-info">This list of options can be passed to the evolugrid
constructor OR via the "descriptor" key of the Ajax callback.</div>

<table class="table">
	<tr>
		<th>Name</th>
		<th>Behaviour</th>
		<th>Comment</th>
	</tr>
	<tr>
		<td>url</td>
		<td>Compulsory</td>
		<td>The Ajax URL that will be called to get the data</td>
	</tr>
	<tr>
		<td>limit</td>
		<td>Default: 100</td>
		<td>The maximum number of rows to be displayed</td>
	</tr>
	<tr>
		<td>loadOnInit</td>
		<td>Default: true</td>
		<td>Whether the table should be loaded as soon as the page is loaded. If false, you will have to call the <em>refresh</em> method or wait for the search form to be submitted.</td>
	</tr>
	<tr>
		<td>tableClasses</td>
		<td></td>
		<td>The CSS class applied to the table</td>
	</tr>
	<tr>
		<td>pagerId</td>
		<td></td>
		<td>The ID that will be applied to the pager component. Please note that the pager is always created by evolugrid. It will just add the ID you pass to the pager it creates.</td>
	</tr>
	<tr>
		<td>export_csv</td>
		<td>Default: false</td>
		<td>Set to true to add a CSV export icon in the pager. When the icon is clicked, the Ajax callback will be called, with the output=csv GET parameter passed.</td>
	</tr>
	<tr>
		<td>rowCssClass</td>
		<td></td>
		<td>If this is set, for each row, Evolugrid will look in the dataset for this value and apply it to as a CSS class to the row.
		For instance, let's assume you set:
		<pre>{
	...
	rowCssClass: "css"
}</pre>
		Now, in your dataset, let's assumer there is a "css" property associated to the data of a row:
		<pre>[
	{
		"label": "mouf",
		...
		"css": "important"
	}
]</pre>
		Then the "important" class will be applied to the row.
		</td>
	</tr>
	<tr>
		<td>loaderImgDiv</td>
		<td></td>
		<td>A jQuery selector pointing to a div that contains a ajax loader gif image. This loader image
		will be displayed each time a search is ongoing.</td>
	</tr>
	<tr>
		<td>filterForm</td>
		<td></td>
		<td>A jQuery selector pointing to a form containing filters (that will be passed to the Ajax request)</td>
	</tr>
	<tr>
		<td>filterFormSubmitButton</td>
		<td></td>
		<td>A jQuery selector pointing to the button that will trigger search. This is optional, and can only be used if the filterForm option is used. 
		If not passed, any submit button on the form will trigger a search.</td>
	</tr>
	<tr>
		<td>filterCallback</td>
		<td></td>
		<td>A function taking 0 arguments and returning a map of filters (passed as arguments to the Ajax URL). This is applied before the filterForm.
		Returned parameters must match this format:
<pre>[{
	"name": "param1",
	"value": "paramValue" 
}]</pre>
		</td>
	</tr>
	<tr>
		<td>columns</td>
		<td>Compulsory (in options or in callback)</td>
		<td>An array of columns to be displayed in the table (see below)</td>
	</tr>
</table>

Columns descriptors
-------------------

The "columns" key of the options contains an array of column descriptors.
A column descriptor contains:

<table class="table">
	<tr>
		<th>Name</th>
		<th>Behaviour</th>
		<th>Comment</th>
	</tr>
	<tr>
		<td>title</td>
		<td>Compulsory</td>
		<td>The title of the column to display</td>
	</tr>
	<tr>
		<td>key</td>
		<td>Compulsory (if no jsrenderer)</td>
		<td>The key to map to in the Ajax dataset. This is also the key that will be returned to the Ajax
		callback when you try to sort on the column (if "sortable" is set)</td>
	</tr>
	<tr>
		<td>jsrenderer</td>
		<td>Optionnal</td>
		<td>If set, this JS function will be used to render the cell.
		This should contain an anonymous Javascript function, taking one parameter (the data row being displayed)
		and returning a jQuery node representing the HTML to put in the cell.<br/>Here is a sample to display a link
		(assuming the data row contains an "id" and a "name" parameter):
	 	<pre>function(row) {
	return $("&lt;a/&gt;").text(row["name"]).attr("href", "/mylink.php?id="+row.id)
}</pre></td>
	</tr>
	<tr>
		<td>sortable</td>
		<td>Boolean, optionnal</td>
		<td>Whether we can sort upon this column or not. Defaults to false.</td>
	</tr>
	<tr>
		<td>width</td>
		<td>Optionnal</td>
		<td>The width of the column. Just like the CSS width property, you can express it in
		%, px, em, etc...</td>
	</tr>
</table>