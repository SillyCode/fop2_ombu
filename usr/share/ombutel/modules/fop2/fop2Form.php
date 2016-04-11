<?php

namespace modules\fop2;

use includes\Modules\ModulesForm;

class fop2Form extends ModulesForm {

	protected function form($menuLabel, $has_menu = TRUE) {
		$this->module->formBegin();
		$this->getTabs();
		$this->module->formEnd();
	}

	protected function tabGeneral() {
// 		$this->module->beginRow();
// 		$this->module->button(_('Pop Out'), ['class' => 'btn btn-sm btn-info fop2_popout']);
// 		$this->module->html->beginModal(_('fop2popout'), 'modal-fop2', false, false, ['params' => ['id' => 'modal-form-epm-addedit', 'class' => 'form-horizontal']]);
		$this->module->custom('<div id="fop2modal" class="modal" role="dialog">');
		$this->module->custom('<div class="modal-header"><h2>FOP2</h2>');
			$this->module->custom('</div>');
			$this->module->custom('<div class="model-body">');
				$this->module->custom('<iframe id="fop2" src="fop2/admin"></iframe>');
			$this->module->custom('</div>');
		$this->module->custom('</div>');

// 		$this->module->custom('<iframe id="fop2" src="fop2/admin"></iframe>');
// 		$this->module->html->endModal([[]], true, 'fop2popout');
// 		$this->module->endRow();
	}
}

?>
