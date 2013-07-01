<?php
namespace Mouf\Html\Widgets\EvoluGrid\Controllers;

use Mouf\Html\Template\TemplateInterface;

use SQLParser\Query\StatementFactory;

use SQLParser\SQLParser;

use Mouf\MoufManager;

use Mouf\Mvc\Splash\Controllers\Controller;

use Mouf\Html\HtmlElement\HtmlBlock;

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
	public function doCreateEvolugrid($name, $sql, $url, $selfedit="false") {
		if (empty($url)) {
			$url = "evolugrid/".urlencode($name);
		}
		
		$parser = new SQLParser();
		$parsed = $parser->parse($sql);
		$select = StatementFactory::toObject($parsed);
		
		$moufManager = MoufManager::getMoufManagerHiddenInstance();
		$dbConnectionInstanceDescriptor = $moufManager->getInstanceDescriptor("dbConnection");
		
		$selectInstanceDescriptor = $select->toInstanceDescriptor($moufManager);
		
		$queryResultInstanceDescriptor = $moufManager->createInstance("Mouf\\Database\\QueryWriter\\QueryResult");
		$queryResultInstanceDescriptor->getProperty("select")->setValue($selectInstanceDescriptor);
		$queryResultInstanceDescriptor->getProperty("connection")->setValue($dbConnectionInstanceDescriptor);

		$countNbResultsInstanceDescriptor = $moufManager->createInstance("Mouf\\Database\\QueryWriter\\CountNbResult");
		$countNbResultsInstanceDescriptor->getProperty("select")->setValue($selectInstanceDescriptor);
		$countNbResultsInstanceDescriptor->getProperty("connection")->setValue($dbConnectionInstanceDescriptor);
		
		$evolugridResultSetInstanceDescriptor = $moufManager->createInstance("Mouf\\Html\\Widgets\\EvoluGrid\\EvoluGridResultSet");
		$evolugridResultSetInstanceDescriptor->getProperty("results")->setValue($queryResultInstanceDescriptor);
		$evolugridResultSetInstanceDescriptor->getProperty("totalRowsCount")->setValue($countNbResultsInstanceDescriptor);
		$evolugridResultSetInstanceDescriptor->getProperty("url")->setValue($url);
		
		
		$evolugridInstanceDescriptor = $moufManager->createInstance("Mouf\\Html\\Widgets\\EvoluGrid\\EvoluGrid");
		$evolugridInstanceDescriptor->getProperty("url")->setValue($evolugridResultSetInstanceDescriptor);
		$evolugridInstanceDescriptor->getProperty("class")->setValue("table");
		$evolugridInstanceDescriptor->setName($name);
		
		$moufManager->rewriteMouf();
		
		header("Location: ".ROOT_URL."ajaxinstance/?name=".urlencode($name)."&selfedit=".$selfedit);
	}
}