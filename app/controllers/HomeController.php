<?php

use ninja\mailers\Mailer;

class HomeController extends BaseController {

	protected $layout = 'master';
	protected $mailer;

	public function __construct(Mailer $mailer)
	{
		parent::__construct();

		$this->mailer = $mailer;
	}	

	public function showIndex()
	{
		if (Utils::isNinja())
		{
			return View::make('public.splash');
		}
		else
		{
			if (Account::count() == 0)
			{
				return Redirect::to('/invoice_now');
			}
			else
			{
				return Redirect::to('/login');
			}
		}
	}


	public function doContactUs()
	{
		$email = Input::get('email');
		$name = Input::get('name');
		$message = Input::get('message');

		$data = [		
			'text' => $message
		];

		$this->mailer->sendTo(CONTACT_EMAIL, $email, $name, 'Factura Virtual Feedback', 'contact', $data);

		$message = trans('texts.sent_message');
		Session::flash('message', $message);

		return View::make('public.contact_us');
	}


	public function invoiceNow()
	{
		if (Auth::check())
		{
			return Redirect::to('invoices/create');				
		}
		else
		{
			return View::make('public.header', ['invoiceNow' => true]);
		}
	}

	public function logError()
	{
		return Utils::logError(Input::get('error'), 'JavaScript');
	}
}