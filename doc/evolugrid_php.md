Using Evolugrid in PHP
======================

Evolugrid comes with a number of PHP classes.
Those PHP classes can be used to generate the Javascript and the JSON data.

They can be used with or without the Mouf framework.

- [Using pure PHP code](#plainphp)
- [Using Mouf](#moufstandard)
- [Using Mouf and the SQL query generator](#moufcomplete)


Introduction
------------

There are 2 main classes:

- `EvoluGrid` is in charge of generating the HTML/Javascript code that displays the Evolugrid.
  It has a `toHtml()` method that will output the HMTL/Javascript code into your page.
- `EvoluGridResultSet` is in charge of outputing the JSON AJAX response that contains both
  the data and the column model (and additional parameters if needed). 

<a name="plainphp"></a>
Using Evolugrid in plain PHP
----------------------------

###Generating the HTML/JS for the Evolugrid

```php
$evoluGrid = new EvoluGrid();
$evoluGrid->setUrl('/path/to/dataset'); // Set the Ajax URL to be called

// You can configure parameters via setters:
$evoluGrid->setLimit(100); // Set the maximum number of rows displayed
$evoluGrid->setId('myGridId'); // Set the HTML ID of the grid
$evoluGrid->setClass('table'); // Set the CSS class of the grid
$evoluGrid->setExportCSV(true); // Whether CSV export is available
$evoluGrid->setInfiniteScroll(true); // Whether to use infinite scroll or pagination

// Outputs the HTML and Javascript to display the grid.
$evoluGrid->toHtml();
```

###Generating the JSON dataset

The EvoluGrid can also help you return the dataset (and the column model associated).
The `EvoluGridResultSet` class is designed for this very purpose.
This code should be returned to the Ajax call of Evolugrid:

```php
$evoluGridRs = new EvoluGridResultSet();

// Let's add simple columns
$evoluGridRs->addColumn(new SimpleColumn("Name", "name"));
$evoluGridRs->addColumn(new SimpleColumn("Date", "date"));
// You can put advanced expressions in the key column.
// Thanks to the symfony/property-access component bundled with Evolugrid, you can
// access properties via getters
$evoluGridRs->addColumn(new SimpleColumn("Country", "country.name")); // can actually run .getCountry().getName() if the result set contains objects.


// Need more power? A Twig renderer is available!
// Let's add a column with Twig rendering (http://twig.sensiolabs.org)
$twigCol = new TwigColumn("Edit", "<% if (id > 0) %><a href='/edit?id={{id}}'>Edit</a><% endif %>");

$data = array(
	0=>array(id=>1, "name"=>"Doe", "first_name"=>"John", "date"=>"12/12/12"),
	1=>array(id=>2, "name"=>"Smith", "first_name"=>"Jim", "date"=>"04/01/13"),
);

// You should return only the pages to be displayed.
$evoluGridRs->setResults($data);

// The total number of rows (used to know how many pages of results are returned)
// Note that this is not required if you are using "infinite scroll"
$evoluGridRs->setTotalRowsCount(42);

// Triggers the display of the JSON message
$evoluGridRs->output();
```

Note: Evolugrid will pass a number of GET parameters in the Ajax request:

- limit: the maximum number of rows your Ajax callback should return 
- offset: the offset in the dataset where you should start returning data
- output: this is either "json" or "csv". If "csv", it means the user clicked the export button and is expecting a CSV dataset. 
- sort_key: the key to sort upon. The key name can be configured in each column descriptor
- sort_order: the order to apply to the sort (this is "ASC" or "DESC").

###Exporting CSV instead of JSON

In order to enable CSV export, you just need to call the `setExportCSV` method.

```
$evoluGrid->setExportCSV(true); // Whether CSV export is available
```

If you select the `exportCSV` option, an export button will appear in the grid.

Evolugrid can help you generate the CSV for almost no additional cost.
All you have to do it pass 2 more arguments to the "output" method:

```php
// This will trigger a CSV file download when the user clicks the export button in Evolugrid.
$evoluGridRs->output($_GET['output'], 'filename.csv');
```

The default encoding for CSV files is "CP1252", this is the default encoding for West European Windows. If you want to change the encoding of the output file, you should use the `setCsvEncoding` function.

```php
// This will change the encoding of the output file to "UTF-8" (encoding compatible with LibreOffice)
$evoluGridRs->setCsvEncoding("UTF-8");
```

###Complete EvoluGrid sample using Splash MVC framework and pure PHP code

In the example below, we create the EvoluGrid instances in the controller. In the next chapter we will
see how to do this using Mouf (which can drastically reduce the number of lines of code).

####Controller file: UserController.php
```php
/**
 * Sample controller in charge of displaying a fictive list of users
 */
class UserController extends Controller {

	/**
	 * The template used by this controller.
	 * @var TemplateInterface
	 */
	private $template;
	
	/**
	 * The main content block of the page.
	 * @var HtmlBlock
	 */
	private $content;
	
	/**
	 * The DAO factory object.
	 * @var DaoFactory
	 */
	private $daoFactory;

	/**
	 * The Twig environment (used to render Twig templates).
	 * @var Twig_Environment
	 */
	private $twig;

	/**
	 * Controller's constructor.
	 * @param TemplateInterface $template The template used by this controller
	 * @param HtmlBlock $content The main content block of the page
	 * @param DaoFactory $daoFactory The object in charge of retrieving DAOs
	 * @param Twig_Environment $twig The Twig environment (used to render Twig templates)
	 */
	public function __construct(TemplateInterface $template, HtmlBlock $content, DaoFactory $daoFactory, Twig_Environment $twig) {
		$this->template = $template;
		$this->content = $content;
		$this->daoFactory = $daoFactory;
		$this->twig = $twig;
	}
	
	/**
	 * This action will display a page with an Evolugrid containing the users list.
	 *
	 * @URL users/
	 * @Get
	 */
	public function index() {
		$evoluGrid = new EvoluGrid();
		$evoluGrid->setUrl('users/ajaxlist'); // Set the Ajax URL to be called (it is defined below)
		
		// You can configure parameters via setters:
		$evoluGrid->setLimit(100); // Set the maximum number of rows displayed
		$evoluGrid->setId('myGridId'); // Set the HTML ID of the grid
		$evoluGrid->setClass('table table-stripped'); // Set the CSS class of the grid
		$evoluGrid->setExportCSV(true); // Whether CSV export is available
		$evoluGrid->setInfiniteScroll(true); // Whether to use infinite scroll or pagination
			
		// Let's add the twig file to the template.
		$this->content->addHtmlElement(new TwigTemplate($this->twig, 'src/views/users-list.twig', array("usersList"=>$evolugrid)));
		$this->template->toHtml();
	}
	/**
	 * This action will return the Ajax response for the Evolugrid.
	 * 
     * @URL users/ajaxlist
	 * @Get
	 */
	public function ajaxList($limit = null, $offset = null, $output = "json", $sort_key = "", $sort_order = "") {
        $evoluGridRs = new EvoluGridResultSet();
		
		// Let's add simple columns
		$evoluGridRs->addColumn(new SimpleColumn("Login", "login"));
		$evoluGridRs->addColumn(new SimpleColumn("Last name", "last_name"));
		// The TWIG column allows for complex processing of the original dataset before display
		$evoluGridRs->addColumn(new TwigColumn("Email", "<a href="mailto:{{ email }}">{{ email }}</a>"));
		
		// We assume there is an existing "getUserList" function that returns an array of rows,
		// each row being an array itself.
		$data = $this->daoFactory->getUserDao()->getUserList($limit, $offset, $sort_key, $sort_order);
        
        // Let's set the result in the EvoluGridSet
        $evoluGridRs->setResults($data);
        
        // Note: because we use the "infiniteScroll" technique, we don't need to tell evolugrid
        // how many total results there are. If we were using the "pagination" technique, we
        // would need to call the setTotalRowsCount method and pass it the total number of users.
        
        // Let's output the JSON answer.
        $evoluGridRs->output($output);
	}
}
```

####Template file: user-list.twig
```twig
<h1>Users list</h1>

{# The toHtml() function calls the toHtml method on the usersList object #}
{{ toHtml(usersList) }}
```

TODO: screenshot

Adding filters to the EvoluGrid
-------------------------------

TODO

<a name="moufstandard"></a>
Using Evolugrid with Mouf
-------------------------

If you are using Mouf (you should!), you can configure most of the grid graphically.
Here is a sample instance:

![Instance image](images/sample_instance.png)

As you can see, you can directly configure the columns, the Ajax callback, whether or not you want CSV exports, etc...

###Inserting an evolugrid declared in Mouf in your code

Inserting an evolugrid declared in Mouf in your code is a simple line of code:

```php
Mouf::getMyEvoluGrid->toHtml();
```

This will render the evolugrid in your page.

###Implementing the Ajax response

Assuming you are using the Splash MVC framework, your controller would look like this:

```php
class MyController extends Controller {

	...

	/**
	 * @URL /userlist
	 */
	public function userlist($limit=null, $offset=null, $output="json", $sort_key = null, $sort_order = null) {
		// Retrieve the rows in database
		$rows = $this->getData($limit, $offset, $sort_key, $sort_order);
		
		$evoluGridRs = Mouf::getMyEvoluGridResultSet();
		
		// You should return only the pages to be displayed.
		$evoluGridRs->setResults($rows);
		
		// The second parameter is the download name of the file generated (should the user
		// click on the "export" button).
		// The output method can generate the JSON or the CSV output.
		$evoluGridRs->output($output, "filename.csv");
	}
}
```

<a name="moufcomplete"></a>
Using Evolugrid with Mouf and standard SQL queries
--------------------------------------------------