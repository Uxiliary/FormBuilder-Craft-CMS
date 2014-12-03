<?php
namespace Craft;

class FormBuilderVariable
{
	function entries()
	{
		return craft()->elements->getCriteria('FormBuilder_Entry');
	}

  function getFormByHandle($formHandle)
  {
    return craft()->formBuilder_forms->getFormByHandle($formHandle);
  }

}
