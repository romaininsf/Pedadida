<?php
	require_javascript("og/ObjectPicker.js");
	require_javascript("og/modules/addTemplate.js");
	require_javascript("og/DateField.js");
	
	$genid = gen_id();
	$object = $cotemplate;
?>
<form  style='height:100%;background-color:white' class="internalForm" action="<?php echo $cotemplate->isNew() ? get_url('template', 'add') : $cotemplate->getEditUrl() ?>" method="post" enctype="multipart/form-data" onsubmit="return og.templateConfirmSubmit('<?php echo $genid ?>') &&  og.handleMemberChooserSubmit('<?php echo $genid; ?>', <?php echo $cotemplate->manager()->getObjectTypeId() ?>);">

<div class="template">
<div class="coInputHeader">
<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><table style="width:535px"><tr><td><?php echo $cotemplate->isNew() ? lang('new template') : lang('edit template') ?>
	</td><td style="text-align:right"><?php echo submit_button($cotemplate->isNew() ? lang('add template') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?></td></tr></table>
	</div>
</div>
	<div>
	<?php echo label_tag(lang('name'), $genid . 'templateFormName', true) ?>
	<?php echo text_field('template[name]', array_var($template_data, 'name'), 
		array('id' => $genid . 'templateFormName', 'class' => 'name long', 'tabindex' => '1')) ?>
	</div>
	
	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
	
	<div style="padding-top:5px">
		<a href="#" class="option" style="font-weight:bold" onclick="og.toggleAndBolden('<?php echo $genid ?>add_template_parameters_div',this)"><?php echo lang('parameters') ?></a>
		- <a href="#" class="option" style="font-weight:bold" onclick="og.toggleAndBolden('<?php echo $genid ?>add_template_objects_div',this)"><?php echo lang('objects') ?></a>
		
		<?php foreach ($categories as $category) { ?>
			- <a href="#" class="option" <?php if ($category['visible']) echo 'style="font-weight: bold"'; ?> onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this)"><?php echo lang($category['name'])?></a>
		<?php } ?>
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">	

	
	<div id="<?php echo $genid ?>add_template_parameters_div">
		<fieldset><legend><?php echo lang('parameters')?></legend>
			<a id="<?php echo $genid ?>params" href="#" onclick="og.promptAddParameter(this, 0)"><?php echo lang('add a parameter to template') ?></a>
		</fieldset>
	</div>
	
	<div id="<?php echo $genid ?>add_template_objects_div">
		<fieldset>
			<legend><?php echo lang('objects')?></legend>
			<br/>
			<a id="<?php echo $genid ?>before" href="#" onclick="og.pickObjectForTemplate(this)"><?php echo lang('add an object to template') ?></a>
		</fieldset>
	</div>
	
	<div>
	<?php echo label_tag(lang('description'), 'templateFormDescription', false) ?>
	<?php echo editor_widget('template[description]', array_var($template_data, 'description'), 
		array('id' => $genid . 'templateFormDescription', 'class' => 'long', 'tabindex' => '2')) ?>
	</div>
	<?php
		if (isset($add_to) && $add_to) {
			echo input_field("add_to", "true", array("type"=>"hidden"));
		}
	?>
	
	<?php foreach ($categories as $category) { ?>
	<div <?php if (!$category['visible']) echo 'style="display:none"' ?> id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?><?php if ($category['required']) echo ' <span class="label_required">*</span>'; ?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>
	
	<?php echo submit_button($cotemplate->isNew() ? lang('add template') : lang('save changes'),'s',
		array('style'=>'margin-top:0px', 'tabindex' => '3')) ?>
</div>
</div>
</form>

<script>
		og.loadTemplateVars();
		Ext.get('<?php echo $genid ?>templateFormName').focus();
	<?php
	if (is_array($objects)) {
		$count = 0;
		foreach ($objects as $o) {
			if (!$o instanceof ContentDataObject) continue;
	?>
	og.addObjectToTemplate(document.getElementById('<?php echo $genid ?>before'), {
		'manager': '<?php echo get_class($o->manager()) ?>',
		'object_id': <?php echo $o->getId() ?>,
		'type': '<?php echo $o->getObjectTypeName() ?>',
		'milestone_id': <?php echo $o instanceof ProjectTask ? $o->getMilestoneId() : '0' ?>,
		'name': <?php echo json_encode($o->getObjectName()) ?>
	}, true);
	<?php
			if(isset($object_properties) && is_array($object_properties)){
				$oid = $o->getObjectId();
				if(isset($object_properties[$oid])){
					foreach($object_properties[$oid] as $objProp){  
						$property = $objProp->getProperty();
						$value =  $objProp->getValue();
	
					?>
					og.addTemplateObjectProperty(<?php echo $oid ?>, <?php echo $count ?>, '<?php echo $property ?>', '<?php echo $value ?>');
			  <?php }
				}
			}
			$count++;
		}
	}
	if (isset($parameters) && is_array($parameters)) {
		foreach ($parameters as $param) { ?>
		og.addParameterToTemplate(document.getElementById('<?php echo $genid ?>params'), '<?php echo $param->getName() ?>','<?php echo $param->getType() ?>'); 
	<?php }
	}?>

	og.add_template_input_divs = [];
	var inputs = document.getElementById('<?php echo $genid ?>add_template_objects_div').getElementsByTagName('input');
	for (var i=0; i < inputs.length; i++) {
		if(inputs[i].className == 'objectID') {
			og.add_template_input_divs[inputs[i].value] = inputs[i].parentNode.parentNode.id;
		}
	}

	for (x=0; x<og.templateObjects.length; x++) {
		var tobj = og.templateObjects[x];
		if (tobj.type == 'task') og.drawTemplateObjectMilestonesCombo(Ext.get(og.add_template_input_divs[tobj.object_id]).dom, tobj);
	}

</script>