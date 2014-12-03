<?php
namespace Craft;

class FormBuilder_EntriesService extends BaseApplicationComponent
{

	/**
	 * 
	 * Gell all entries
	 * 
	 */
	public function getAllEntries()
	{
		$entries = FormBuilder_EntryRecord::model()->findAll();
		return $entries;
	}

	/**
	 * 
	 * Gell all forms
	 * 
	 */
	public function getAllForms()
	{
		$forms = FormBuilder_FormRecord::model()->findAll();
		return $forms;
	}

	/**
	 * 
	 * Get forms by handle name
	 * 
	 */
	public function getFormByHandle($handle)
	{
		$formRecord = FormBuilder_FormRecord::model()->findByAttributes(array(
			'handle' => $handle,
		));

		if (!$formRecord) {	return false; }
		return FormBuilder_FormModel::populateModel($formRecord);
	}

	/**
	 * 
	 * Get entry by id
	 * 
	 */
	public function getFormEntryById($id)
	{
		return craft()->elements->getElementById($id, 'FormBuilder');
	}

	/**
	 * 
	 * Save Form Entry
	 * 
	 */
	public function saveFormEntry(FormBuilder_EntryModel $entry)
	{
		$entryRecord = new FormBuilder_EntryRecord();

		// Set attributes
		$entryRecord->formId = $entry->formId;
		$entryRecord->title = $entry->title;
		$entryRecord->data   = $entry->data;

		$entryRecord->validate();
		$entry->addErrors($entryRecord->getErrors());

		if (!$entry->hasErrors()) {
			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try {
				if (craft()->elements->saveElement($entry))	{
					$entryRecord->id = $entry->id;
					$entryRecord->save(false);

					if ($transaction !== null) { $transaction->commit(); }
					return $entryRecord->id;
				} else { return false; }
			} catch (\Exception $e) {
				if ($transaction !== null) { $transaction->rollback(); }
				throw $e;
			}
			return true;
		}	else { return false; }
	}

	/**
	 * 
	 * Send Email notification
	 * 
	 */
	public function sendEmailNotification($form, $message, $html = true, $email = null)
	{
		// Generic errors bool
		$errors = false;

		$email = new EmailModel();

		// $email->fromEmail = $form->fromEmail;
		// $email->replyTo   = $form->replyToEmail;
		// $email->sender    = $form->fromEmail;
		$email->fromName  = 'FormBuilder Plugin';
		$email->subject   = $form->subject;
		$email->htmlBody  = $message;

		// Support for sending multiple emails
		$emailTo = explode(',', $form->toEmail);

		foreach ($emailTo as $emailAddress) {
			$email->toEmail = trim($emailAddress);
			if (!craft()->email->sendEmail($email)) {
				$errors = true;
			}
		}
		return $errors ? false : true;
	}
}