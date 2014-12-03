<?php
namespace Craft;

class FormBuilder_FormModel extends BaseModel
{
	/**
	 * Use the translated form name as the string representation.
	 *
	 * @return string
	 */
	function __toString()
	{
		return Craft::t($this->name);
	}

	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'id'            								=> AttributeType::Number,
			'name'          								=> AttributeType::String,
			'handle'        								=> AttributeType::String,
			'toEmail'      									=> AttributeType::String,
			'subject'       								=> AttributeType::String,
			'notificationTemplatePath'      => AttributeType::String,
			'fieldLayoutId' 								=> AttributeType::Number,
		);
	}

	/**
	 * @return array
	 */
	public function behaviors()
	{
		return array(
			'fieldLayout' => new FieldLayoutBehavior('FormBuilder_Entry'),
		);
	}
}