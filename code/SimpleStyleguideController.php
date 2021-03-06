<?php

class SimpleStyleguideController extends Controller {

	private static $allowed_actions = array(
		'index'
	);

	/**
	 * Runs the permissiion checks, and setup of the controller view.
	 */
	public function index() {
		if(!Director::isDev() && !Permission::check('ADMIN')) {
			return Security::permissionFailure();
		}

		$page = Page::get()->first();
		$controller = ModelAsController::controller_for($page);
		$controller->init();

		return $controller
			->customise($this->getStyleGuideData())
			->renderWith(array('SimpleStyleguideController', 'Page'));
	}

	/**
	 * Provides access to any custom function on the controller for use on the template output.
	 * @return Array
	 */
	public function getStyleguideData() {
		$data = new ArrayData(array(
			'Title' => 'Styleguide',
			'TestForm' => $this->TestForm(),
			'Content' => $this->getContent()
		));

		// extensions for adding/overriding template data.
		$this->extend('updateStyleguideData', $data);

		return $data;
	}
	
	/**
	 * Return a form with fields to match rendering through controller/template output.
	 * @return Form
	 */
	public function TestForm() {
		$fields = new FieldList(
			new TextField('SimpleText', 'Simple Text Field'),
			new NumericField('Number', 'Number Field'),
			new EmailField('Email', "Email Field"),
			new DropdownField('Dropdown', 'Normal dropdown', array(
				'1' => 'One option',
				'2' => 'Two option'
			)),
			TextField::create('Text', 'Text')
				->setDescription('This is a description')
		);

		$actions = new FieldList(
			new FormAction('doForm', 'Submit')
		);

		$required = new RequiredFields(
			'SimpleText',
			'Email',
			'Checkbox',
			'Dropdown'
		);

		Session::set("FormInfo.Form_TestForm", array(
			'errors' => array(
				array(
					'fieldName' => 'FirstName',
					'message' => 'Please fill out the required field',
					'messageType' => 'bad'
				),
				array(
					'fieldName' => 'Email',
					'message' => 'Please enter a valid email',
					'messageType' => 'validation'
				)
			)
		));

		$form = new Form($this, 'TestForm', $fields, $actions, $required);
		$form->setMessage('This is a form wide message. See the alerts component for site wide messages.', 'warning');

		return $form;
	}

	/**
	 * Emulate an HTMLEditorField output useful for testing shortcodes and output extensions etc.
	 * @return HTMLText
	 */
	public function getContent() {
		$content = '';

		// add file link to html content
		$file = File::get()->filter('ClassName', 'File')->first();
		if($file) {
			$content .= '<p>This is an internal <a href="[file_link,id=' . $file->ID . ']">link to a file</a> inside content</p>';
		}

		// add external link to html content
		$content .= '<p>This is an external <a href="http://google.com">link to google</a> inside content.</p>';

		return DBField::create_field('HTMLText', $content);
	}

}
