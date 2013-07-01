<?php
namespace Mouf\Database\QueryWriter\Controllers;

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

	protected $sql;
	
	/**
	 * Admin page used to display the DAO generation form.
	 *
	 * @Action
	 */
	public function newEvolugrid($selfedit="false") {
		$this->content->addFile(dirname(__FILE__)."/../../../../views/createQuery.php", $this);
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
	public function doCreateEvolugrid($name, $sql,$selfedit="false") {
		$parser = new SQLParser();
		$parsed = $parser->parse($sql);
		$select = StatementFactory::toObject($parsed);
		
		$moufManager = MoufManager::getMoufManagerHiddenInstance();
		$instanceDescriptor = $select->toInstanceDescriptor($moufManager);
		$instanceDescriptor->setName($name);
		$moufManager->rewriteMouf();
		
		header("Location: ".ROOT_URL."ajaxinstance/?name=".urlencode($name)."&selfedit=".$selfedit);
	}
}