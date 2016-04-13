<?php

namespace modules\fop2_user;

use includes\Modules\OmbutelModule;
use includes\Validation\Validate;

class fop2_user extends OmbutelModule {

	protected function tabs() {
		parent::addTab('general', _('General'));
	}

}

?>
