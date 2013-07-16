<?php
namespace Mouf\Html\Widgets\EvoluGrid\Controllers;

use Mouf\Html\Template\TemplateInterface;

use SQLParser\Query\StatementFactory;

use SQLParser\SQLParser;

use Mouf\MoufManager;

use Mouf\Mvc\Splash\Controllers\Controller;

use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\Database\QueryWriter\Utils\FindParametersService;

/**
 * The controller to generate automatically an evolugrid from a SQL request.
 * Sweet!
 * 
 * @author David Négrier <d.negrier@thecodingmachine.com>
 * @Logged
 */
class EvolugridAdminController extends Controller {

	/**
	 * @var TemplateInterface
	 */
	public $template;
	
	/**
	 * @var HtmlBlock
	 */
	public $content;

	protected $instanceName;
	protected $sql;
	protected $url;
	protected $editMode = false;
	protected $errorMessage;
	
	/**
	 * Admin page used to display the DAO generation form.
	 *
	 * @Action
	 */
	public function newEvolugrid($selfedit="false") {
		$this->content->addFile(dirname(__FILE__)."/../../../../../views/createEvolugrid.php", $this);
		$this->template->toHtml();
	}

	/**
	 * This action generates the objects from the SQL query and creates a new SELECT instance.
	 *
	 * @Action
	 * @param string $name
	 * @param string $sql
	 * @param string $selfedit
	 */
	public function doCreateEvolugrid($name, $sql, $url, $buildSearchForm = 0, $selfedit="false") {
		if (empty($url)) {
			$url = "evolugrid/".urlencode($name);
		}
		
		$parser = new SQLParser();
		$parsed = $parser->parse($sql);
		$select = StatementFactory::toObject($parsed);
		
		$moufManager = MoufManager::getMoufManagerHiddenInstance();
		$dbConnectionInstanceDescriptor = $moufManager->getInstanceDescriptor("dbConnection");
		
		$selectInstanceDescriptor = $select->toInstanceDescriptor($moufManager);
		$parameters = FindParametersService::findParameters($selectInstanceDescriptor);
		
		$queryResultInstanceDescriptor = $moufManager->createInstance("Mouf\\Database\\QueryWriter\\QueryResult");
		$queryResultInstanceDescriptor->getProperty("select")->setValue($selectInstanceDescriptor);
		$queryResultInstanceDescriptor->getProperty("connection")->setValue($dbConnectionInstanceDescriptor);

		$countNbResultsInstanceDescriptor = $moufManager->createInstance("Mouf\\Database\\QueryWriter\\CountNbResult");
		$countNbResultsInstanceDescriptor->getProperty("queryResult")->setValue($queryResultInstanceDescriptor);
		$countNbResultsInstanceDescriptor->getProperty("connection")->setValue($dbConnectionInstanceDescriptor);
		
		$evolugridResultSetInstanceDescriptor = $moufManager->createInstance("Mouf\\Html\\Widgets\\EvoluGrid\\EvoluGridResultSet");
		$evolugridResultSetInstanceDescriptor->getProperty("results")->setValue($queryResultInstanceDescriptor);
		$evolugridResultSetInstanceDescriptor->getProperty("totalRowsCount")->setValue($countNbResultsInstanceDescriptor);
		$evolugridResultSetInstanceDescriptor->getProperty("url")->setValue($url);
		
		if ($moufManager->instanceExists($name)) {
			$evolugridInstanceDescriptor = $moufManager->getInstanceDescriptor($name);
		} else {
			$evolugridInstanceDescriptor = $moufManager->createInstance("Mouf\\Html\\Widgets\\EvoluGrid\\EvoluGrid");
			$evolugridInstanceDescriptor->setName($name);
			$evolugridInstanceDescriptor->getProperty("class")->setValue("table table-striped table-hover");
		}
		$evolugridInstanceDescriptor->getProperty("url")->setValue($evolugridResultSetInstanceDescriptor);
		
		if ($buildSearchForm && $evolugridInstanceDescriptor->getProperty('searchForm')->getValue() == null) {
			$searchFormInstanceDescriptor = $moufManager->createInstance("Mouf\\Html\\HtmlElement\\HtmlFromFile");
			$searchFormInstanceDescriptor->getProperty('fileName')->setValue('vendor/mouf/html.widgets.evolugrid/src/views/searchForm.php');
			$searchFormInstanceDescriptor->getProperty('relativeToRootPath')->setValue(true);
			$evolugridInstanceDescriptor->getProperty('searchForm')->setValue($searchFormInstanceDescriptor);
		}
		
		$parameterInstanceDescriptors = array();
		foreach ($parameters as $parameter) {
			$parameterInstanceDescriptor = $moufManager->createInstance("Mouf\\Utils\\Value\\RequestParam");
			$parameterInstanceDescriptor->getProperty("paramName")->setValue($parameter);
			$parameterInstanceDescriptors[$parameter] = $parameterInstanceDescriptor;
		}
		$queryResultInstanceDescriptor->getProperty("parameters")->setValue($parameterInstanceDescriptors);
		
		$moufManager->rewriteMouf();
		
		header("Location: ".ROOT_URL."ajaxinstance/?name=".urlencode($name)."&selfedit=".$selfedit);
	}
	
	/**
	 * Admin page used to display the edit form for the Evolugrid.
	 *
	 * @Action
	 */
	public function updateFromSql($name, $selfedit="false") {
		$moufManager = MoufManager::getMoufManagerHiddenInstance();
		$evolugridInstanceDescriptor = $moufManager->getInstanceDescriptor($name);
		$this->instanceName = $name;
		$this->editMode = true;
		
		$evolugridResultSetInstanceDescriptor = $evolugridInstanceDescriptor->getProperty("url")->getValue();
		if ($evolugridResultSetInstanceDescriptor == null) {
			$this->newEvolugrid($selfedit);
			return;
		}
		
		$this->errorMessage = "Sorry, Evolugrid does not support initializing from SQL query if the 'url' parameter
				is not first set to <em>null</em>. This is to avoid you loosing important configuration settings
				by using the wizard. If you are sure you want to use this SQL query wizard, you can set the 'url'
				parameter to <em>null</em> and click again the 'Generate from SQL' button.";
		$this->content->addFile(dirname(__FILE__)."/../../../../../views/unableEvolugrid.php", $this);
		$this->template->toHtml();
	}
	
}