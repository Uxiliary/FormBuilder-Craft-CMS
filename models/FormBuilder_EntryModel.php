<?php
namespace Craft;

class FormBuilder_EntryModel extends BaseElementModel
{

	protected $elementType = 'FormBuilder';

	function __toString()
	{
		return $this->id;
	}

	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array_merge(parent::defineAttributes(), array(
			'id'     => AttributeType::Number,
			'formId' => AttributeType::Number,
			'title'  => AttributeType::String,
			'data'   => AttributeType::Mixed,
		));
	}

	/**
	 * Returns whether the current user can edit the element.
	 *
	 * @return bool
	 */
	public function isEditable()
	{
		return true;
	}

	/**
	 * Returns the element's CP edit URL.
	 *
	 * @return string|false
	 */
	public function getCpEditUrl()
	{
		return UrlHelper::getCpUrl('formbuilder/entries/'.$this->id);
	}

	/**
	 * Normalize Data For Elements Table
	 *
	 */
	public function _normalizeDataForElementsTable()
	{
		$data = unserialize($this->data);
		$data = $this->_filterPostKeys($data);

		// Pop off the first (4) items from the data array
		$data = array_slice($data, 0, 5);

		$newData = '<ul>';
		foreach ($data as $key => $value) {	
			$capitalize = ucfirst($key);
			$addSpace = preg_replace('/(?<!\ )[A-Z]/', ' $0', $capitalize);
			$valueArray = is_array($value);
			if ($valueArray == '1') {
				$newData .= '<li class="left icon text" style="margin-right:10px;"><strong>' . $addSpace . '</strong>: ';
				foreach ($value as $item) {
					$newData .= ' ' . $item;
				}
				$newData .= '</li>';
			} else {
				$newData .= '<li class="left icon text" style="margin-right:10px;"><strong>' . $addSpace . '</strong>: ' . $value . '</li>';
			}
		}

		$newData .= "</ul>";

		$this->__set('data', $newData);
		return $this;
	}


	private function _filterPostKeys($post)
	{
		$filterKeys = array(
			'required',
			'action',
			'formhandle',
			'redirect'
		);

		if (is_array($post)) {
			foreach ($post as $k => $v) {
				if (in_array(strtolower($k), $filterKeys) || empty($v)) {
					unset($post[$k]);
				}
			}
		}
		return $post;
	}
}