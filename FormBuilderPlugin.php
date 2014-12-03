<?php

/*
Plugin Name: FormBuilder
Plugin Url: http://github.com/roundhouse/formbuilder
Author: Vadim Goncharov (https://github.com/owldesign)
Author URI: http://roundhouseagency.com
Description: Form builder for craft cms. Lets you build multiple forms with custom fields. Dynamically display the forms in your templates. Upon submission the forms are saved and stored in the database as well as notification sent to the form's owner.
Version: 1.0
*/

namespace Craft;

class FormBuilderPlugin extends BasePlugin
{
	public function getName()
	{
	    return 'FormBuilder';
	}

	public function getVersion()
	{
	    return '1.0';
	}

	public function getDeveloper()
	{
	    return 'Roundhouse Agency';
	}

	public function getDeveloperUrl()
	{
	    return 'http://roundhouseagency.com';
	}

	public function addTwigExtension()  
	{
	  Craft::import('plugins.formbuilder.twigextensions.FormBuilderTwigExtension');
	  return new FormBuilderTwigExtension();
	}

	public function hasCpSection()
	{
		return true;
	}

	public function registerCpRoutes()
	{
		return array(
			'formbuilder'                                       => array('action' => 'formBuilder/forms/formIndex'),
			'formbuilder/forms'                                 => array('action' => 'formBuilder/forms/formIndex'),
			'formbuilder/forms/new'                             => array('action' => 'formBuilder/forms/editForm'),
			'formbuilder/forms/(?P<formId>\d+)'                 => array('action' => 'formBuilder/forms/editForm'),
			'formbuilder/entries'                 							=> array('action' => 'formBuilder/entries/entriesIndex'),
			'formbuilder/entries/(?P<entryId>\d+)' 							=> array('action' => 'formBuilder/entries/viewEntry'),
		);
	}
}
