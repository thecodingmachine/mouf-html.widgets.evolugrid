<?php
/* @var $this Mouf\Html\Widgets\EvoluGrid\Controllers\EvolugridAdminController */
?>
<h1>Edit evolugrid <em><?php echo $this->instanceName ?></em></h1>

<div class="alert alert-error"><?php echo $this->errorMessage; ?></div>
<a href="<?php echo ROOT_URL."ajaxinstance/?name=".urlencode($this->instanceName); ?>" class="btn btn-primary">Back to instance</a>