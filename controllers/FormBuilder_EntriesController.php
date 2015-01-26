<?php
namespace Craft;

class FormBuilder_EntriesController extends BaseController
{
	protected $allowAnonymous = true;
	protected $defaultEmailTemplate = 'formbuilder/email/default';
	
	/**
	 * View Form Entry
	 */
	public function actionEntriesIndex()
	{
		// Get the data
		$variables['entries'] = craft()->formBuilder_entries->getAllEntries();
		$variables['tabs'] = $this->_getTabs();

		// Render the template!
		$this->renderTemplate('formbuilder/entries/index', $variables);
	}

	public function actionViewEntry(array $variables = array())
	{
		$entry              = craft()->formBuilder_entries->getFormEntryById($variables['entryId']);
		$variables['entry'] = $entry;

		if (empty($entry)) { throw new HttpException(404); }

		$variables['form']        = craft()->formBuilder_forms->getFormById($entry->formId);
		$variables['tabs']        = $this->_getTabs();
		$variables['selectedTab'] = 'entries';
		$variables['data']        = $this->_filterPostKeys(unserialize($entry->data));

		$this->renderTemplate('formbuilder/entries/_view', $variables);
	}


	/**
	 * Save Form Entry
	 */
	public function actionSaveFormEntry()
	{
		// Require a post request
		$this->requirePostRequest();

		// Honeypot validation
		$honeypot = craft()->request->getPost('formHoneypot');
		if ($honeypot) { throw new HttpException(404); }

		// Set the required errors array
		$errors['required'] = array();

		// Get the form
		$formBuilderHandle = craft()->request->getPost('formHandle');
		if (!$formBuilderHandle) { throw new HttpException(404);}

		// Required attributes
		$required = craft()->request->getPost('required');
		if ($required){
			foreach ($required as $key => $message)	{
				$value = craft()->request->getPost($key);
				if (empty($value)) {
					$errors['required'][$key] = $message;
				}
			}
		}

		if (!empty($errors['required'])) {
			craft()->userSession->setError($errors);
			craft()->userSession->setFlash('post', craft()->request->getPost());
			$this->redirect(craft()->request->getUrl());
		}

		// Get the form model, need this to save the entry
		$form = craft()->formBuilder_entries->getFormByHandle($formBuilderHandle);
		if (!$form) { throw new HttpException(404); }

		// @todo Need to exclude certain keys
		$excludedPostKeys = array();

		// Form data
		$data = serialize(craft()->request->getPost());

		// New form entry model
		$formBuilderEntry = new FormBuilder_EntryModel();

		// Set entry attributes
		$formBuilderEntry->formId		= $form->id;
		$formBuilderEntry->title   	= $form->name;
		$formBuilderEntry->data   	= $data;

		// Save it
		if (craft()->formBuilder_entries->saveFormEntry($formBuilderEntry)) {
			// Time to make the notifications
			if ($this->_sendEmailNotification($formBuilderEntry, $form)) {
				// Set the message
				if (!empty($form->successMessage)) {
					$message = $form->successMessage;
				} else {
					$message =  Craft::t('Thank you, we have received your submission and we\'ll be in touch shortly.');
				}
				craft()->userSession->setFlash('success', $message);
				$this->redirectToPostedUrl();
			} else {
				craft()->userSession->setError(Craft::t('We\'re sorry, but something has gone wrong.'));
			}
			craft()->userSession->setNotice(Craft::t('Entry saved.'));
			$this->redirectToPostedUrl($formBuilderEntry);
		} else {
			craft()->userSession->setNotice(Craft::t("Couldn't save the form."));
		}

		// Send the saved form back to the template
		craft()->urlManager->setRouteVariables(array(
			'entry' => $formBuilderEntry
		));
	}

	/**
	 * Delete Entry
	 */
	public function actionDeleteEntry()
	{
		$this->requirePostRequest();

		$entryId = craft()->request->getRequiredPost('entryId');

		if (craft()->elements->deleteElementById($entryId)) {
			craft()->userSession->setNotice(Craft::t('Entry deleted.'));
			$this->redirectToPostedUrl();
			craft()->userSession->setError(Craft::t('Couldn’t delete entry.'));
		}

	}

	/**
	 * Send Email Notification
	 */
	protected function _sendEmailNotification($record, $form)
	{
		// Put in work setting up data for the email template.
		$data = new \stdClass($data);
		$data->entryId   = $record->id;

		$postData = unserialize($record->data);
		$postData = $this->_filterPostKeys($postData);

		foreach ($postData as $key => $value) {
			$data->$key = $value;
		}

		// Email template
		if (craft()->templates->findTemplate($form->notificationTemplatePath)) {
			$template = $form->notificationTemplatePath;
		}

		if (!$template) {
			$template = $this->defaultEmailTemplate;
		}

		$variables = array(
			'data'  => $postData,
			'form'  => $form,
			'entry' => $record,
		);

		$message  = craft()->templates->render($template, $variables);

		// Send the message
		if (craft()->formBuilder_entries->sendEmailNotification($form, $message, true, null)) {
			return true;
		} else {
			return false;
		}
	}


	protected function _filterPostKeys($post)
	{
		$filterKeys = array(
			'action',
			'redirect',
			'formhandle',
			'honeypot',
			'required',
		);
		if (isset($post['honeypot'])) {
			$honeypot = $post['honeypot'];
			array_push($filterKeys, $honeypot);
		}
		if (is_array($post)) {
			foreach ($post as $k => $v) {
				if (in_array(strtolower($k), $filterKeys)) {
					unset($post[$k]);
				}
			}
		}
		return $post;
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
