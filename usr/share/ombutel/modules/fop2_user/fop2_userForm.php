<?php

namespace modules\fop2_user;

use includes\Modules\ModulesForm;

class fop2_userForm extends ModulesForm {

	protected function form($menuLabel, $has_menu = TRUE) {
		$this->module->formBegin();
		$this->_form_actions();
		$this->getTabs();
		$this->module->formEnd();
	}

	private function _form_actions(){
		$this->module->beginFormActionsContainer();
		$this->module->custom('<button type="button" class="btn btn-primary fop2user_popout" data-toggle="modal" data-target=".large_modal-lg">FOP2 popoup</button>');
		$this->module->formActionsData();
		$this->module->endFormActionsContainer();
	}

	protected function tabGeneral() {
		$this->module->custom('<iframe id="fop2_userinternal" src="fop2"></iframe>');
		$this->module->custom('
		<div class="modal fade modal-fullscreen large_modal-lg" id="fop2_userext" tabindex="-1" role="dialog" data-backdrop="static">
			<div class="fop2_user-modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close fop2user_model_close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"></h4>
					</div>
					<iframe id="fop2_user" src="fop2"></iframe>
					<div class="modal-footer">
						<button type="button" class="btn btn-default fop2user_model_close" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		');
	}
}

?>
