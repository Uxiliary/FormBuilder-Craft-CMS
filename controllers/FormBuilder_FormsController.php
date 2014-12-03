<?php
namespace Craft;

class FormBuilder_FormsController extends BaseController
{
	/**
	 * Form index
	 */
	public function actionFormIndex()
	{	
		$variables['forms'] = craft()->formBuilder_forms->getAllForms();
		$variables['tabs'] = $this->_getTabs();
		return $this->renderTemplate('formbuilder/forms', $variables);
	}

	/**
	 * Edit a from.
	 *
	 * @param array $variables
	 * @throws HttpException
	 * @throws Exception
	 */
	public function actionEditForm(array $variables = array())
	{
		$variables['brandNewForm'] = false;

		if (!empty($variables['formId'])) {
			if (empty($variables['form'])) {
				$variables['form'] = craft()->formBuilder_forms->getFormById($variables['formId']);
				if (!$variables['form']) { throw new HttpException(404); }
			}
			$variables['title'] = $variables['form']->name;
		} else {
			if (empty($variables['form'])) {
				$variables['form'] = new FormBuilder_FormModel();
				$variables['brandNewForm'] = true;
			}
			$variables['title'] = Craft::t('Create a new form');
		}

		$variables['tabs'] = $this->_getTabs();

		// $variables['crumbs'] = array(
		// 	array('label' => Craft::t('Entries'), 'url' => UrlHelper::getUrl('entries')),
		// 	array('label' => Craft::t('Forms'), 'url' => UrlHelper::getUrl('formbuilder/forms')),
		// );

		$this->renderTemplate('formbuilder/forms/_edit', $variables);
	}

	/**
	 * Saves a form
	 */
	public function actionSaveForm()
	{
		$this->requirePostRequest();

		$form = new FormBuilder_FormModel();

		// Shared attributes
		$form->id         									= craft()->request->getPost('formId');
		$form->name       									= craft()->request->getPost('name');
		$form->handle     									= craft()->request->getPost('handle');
		$form->toEmail     									= craft()->request->getPost('toEmail');
		$form->subject     									= craft()->request->getPost('subject');
		$form->notificationTemplatePath     = craft()->request->getPost('notificationTemplatePath');

		// Set the field layout
		$fieldLayout = craft()->fields->assembleLayoutFromPost();
		$fieldLayout->type = ElementType::Asset;
		$form->setFieldLayout($fieldLayout);

		// Save it
		if (craft()->formBuilder_forms->saveForm($form)) {
			craft()->userSession->setNotice(Craft::t('Form saved.'));
			$this->redirectToPostedUrl($form);
		}	else {
			craft()->userSession->setError(Craft::t('Couldn’t save form.'));
		}

		// Send the form back to the template
		craft()->urlManager->setRouteVariables(array(
			'form' => $form
		));
	}

	/**
	 * Deletes a form.
	 */
	public function actionDeleteForm()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$formId = craft()->request->getRequiredPost('id');

		craft()->formBuilder_forms->deleteFormById($formId);
		$this->returnJson(array('success' => true));
	}

	protected function _getTabs()
	{
		return array(
			'forms' => array(
				'label' => "Forms", 
				'url'   => UrlHelper::getUrl('formbuilder/'),
			),
			'entries' => array(
				'label' => "Entries", 
				'url'   => UrlHelper::getUrl('formbuilder/entries'),
			),
		);
	}
}
