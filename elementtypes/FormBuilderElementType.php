<?php
namespace Craft;

class FormBuilderElementType extends BaseElementType
{
  public function getName()
  {
    return Craft::t('FormBuilder');
  }

  public function getSources($context = null)
  {
    $sources = array(
      '*' => array(
        'label' => Craft::t('All Submissons'),
      ),
    );

    foreach (craft()->formBuilder_forms->getAllForms() as $form) {
      $key = 'formId:' . $form->id;
      $sources[$key] = array(
        'label'    => $form->name,
        'criteria' => array('formId' => $form->id)
      );
    }
    return $sources;
  }

  public function defineSearchableAttributes()
  {
    return array('id', 'data');
  }

  public function defineTableAttributes($source = null)
  {
    return array(
      'id'          => Craft::t('ID'),
      // 'formId'      => Craft::t('Form ID'),
      'title'       => Craft::t('Title'),
      'dateCreated' => Craft::t('Date'),
      'data'        => Craft::t('Submission Data'),
    );
  }

  /**
   * Returns the table view HTML for a given attribute.
   *
   * @param BaseElementModel $element
   * @param string $attribute
   * @return string
   */
  public function getTableAttributeHtml(BaseElementModel $element, $attribute)
  {
    switch ($attribute) {
      case 'data':
        $data = $element->_normalizeDataForElementsTable();
        return $element->data;
        break;
      default:
        return parent::getTableAttributeHtml($element, $attribute);
        break;
    }
  }

  public function defineCriteriaAttributes()
  {
    return array(
      'formId' => AttributeType::Mixed,
      'order'  => array(AttributeType::String, 'default' => 'formbuilder_entries.dateCreated desc'),
    );
  }

  /**
   * Modifies an element query targeting elements of this type.
   *
   * @param DbCommand $query
   * @param ElementCriteriaModel $criteria
   * @return mixed
   */
  public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
  {
    $query
      ->addSelect('formbuilder_entries.formId, formbuilder_entries.title, formbuilder_entries.data')
      ->join('formbuilder_entries formbuilder_entries', 'formbuilder_entries.id = elements.id');

    if ($criteria->formId) {
      $query->andWhere(DbHelper::parseParam('formbuilder_entries.formId', $criteria->formId, $query->params));
    }
  }

  /**
   * Populates an element model based on a query result.
   *
   * @param array $row
   * @return array
   */
  public function populateElementModel($row, $normalize = false)
  {
    $entry = FormBuilder_EntryModel::populateModel($row);

    if ($normalize) {
      $entry = $entry->_normalizeDataForElementsTable();
    }
    return $entry;
  }

}