<?php

namespace modules\fop2;

use includes\Modules\OmbutelModule;
use includes\Validation\Validate;

class fop2 extends OmbutelModule {

	protected function tabs() {
		parent::addTab('general', _('General'));
	}

}

?>
