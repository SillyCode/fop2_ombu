<?php

namespace modules\fop2;

use includes\Modules\ModulesForm;

class fop2Form extends ModulesForm {

	protected function form($menuLabel, $has_menu = TRUE) {
		$this->module->formBegin();
		$this->_form_actions();
		$this->getTabs();
		$this->module->formEnd();
	}

	private function _form_actions(){
		$this->module->beginFormActionsContainer();
		$this->module->custom('<button type="button" class="btn btn-primary fop2popout" data-toggle="modal" data-target=".large_modal-lg">FOP2 popoup</button>');
		$this->module->formActionsData();
		$this->module->endFormActionsContainer();
	}

	protected function tabGeneral() {
		$this->module->custom('<iframe id="fop2internal" src="fop2/admin"></iframe>');
		$this->module->custom('
		<div class="modal fade modal-fullscreen large_modal-lg" id="fop2ext" tabindex="-1" role="dialog" data-backdrop="static">
			<div class="fop2-modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close fop2model_close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"></h4>
					</div>
					<iframe id="fop2" src="fop2/admin"></iframe>
					<div class="modal-footer">
						<button type="button" class="btn btn-default fop2model_close" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		');
	}
}

?>
