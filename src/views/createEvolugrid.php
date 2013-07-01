<?php
/* @var $this Mouf\Html\Widgets\EvoluGrid\Controllers\EvolugridAdminController */
?>
<h1>Create a new Evolugrid</h1>

<p>This screen creates a working <code>EvoluGrid</code> that will retrieve data directly from a SQL database.
This is not the only way to create an <code>EvoluGrid</code>. You should create the <code>EvoluGrid</code> directly
from the Mouf UI if you need to tap into another datasource.</p>

<form action="doCreateEvolugrid" method="post" class="form-horizontal">
	<div class="control-group">
		<label class="control-label">Instance name*: </label>
		<div class="controls">
			<input type="text" name="name" value="<?php echo plainstring_to_htmlprotected($this->instanceName) ?>" required />
			<span class="help-block">The name of the <code>EvoluGrid</code> instance that will be created.</span>
		</div>
	</div>
	
	<div class="control-group">
		<label class="control-label">SQL query*: </label>
		<div class="controls">
			<textarea rows=10 name="sql" class="span10" required><?php echo plainstring_to_htmlprotected($this->sql) ?></textarea>
			<span class="help-block">You can use <strong>parameters</strong> using prepared statement notation. For instance: 
			<code>select * from users where country_id = :country_id</code>. If you do so, the parameters will be read
			from the Ajax URL that will be set up. You can customize this behaviour later, using Mouf.</span>
		</div>
	</div>
	
	<div class="control-group">
		<label class="control-label">URL: </label>
		<div class="controls">
			<input type="text" name="url" value="<?php echo plainstring_to_htmlprotected($this->url) ?>" />
			<span class="help-block">The Ajax URL that will be called by the Evolugrid. Defaults to "evolugrid/{instanceName}".
			If you want to restrict access to this URL, you can edit the <code>EvoluGridResultSet::condition</code> property using
			Mouf UI.</span>
		</div>
	</div>
	
	<div class="control-group">
		<div class="controls">
			<button name="action" value="parse" type="submit" class="btn btn-danger">Create Evolugrid</button>
		</div>
	</div>
		
</form>

<script type="text/javascript">
$(function () { $("input,select,textarea").not("[type=submit]").jqBootstrapValidation(); } );
</script>