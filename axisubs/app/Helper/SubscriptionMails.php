<?php

namespace Axisubs\Helper;

use Axisubs\Helper\SendMails;
use Axisubs\Models\Site\Plans;

class SubscriptionMails{

	public static $instance = null;

	public function __construct($properties=null) {
	}

	/**
	 * get an instance
	 * @param array $config
	 * @return 
	 * * */
	public static function getInstance(array $config = array())
	{
		if (!self::$instance)
		{
			self::$instance = new self($config);
		}
		return self::$instance;
	}

	/**
	 * For Subscription Pending
	 * */
	public static function subscriptionPending($subscription_id, $status = ''){
		$data['subscription'] = $subscription = Plans::loadSubscriber($subscription_id);
		$subscriptionMeta = $subscription->meta;
		$subscriptionPrefix = $subscription->ID.'_'.$subscription->post_type.'_';
		$data['plan'] = $plan = Plans::loadPlan($subscriptionMeta[$subscriptionPrefix.'plan_id'], 1);
		$planMeta = $plan->meta;
		$planPrefix = $plan->ID.'_'.$plan->post_type.'_';
		$mail = SendMails::getInstance();
		$mail->set('to', $subscriptionMeta[$subscriptionPrefix.'email']);
		$mail->set('subject', 'Subscription Created with pending status');
		$template = view('@Axisubs/Site/mailtemplates/payment_pending.twig', compact('data'));
		if($template->getStatusCode() == 200){
			$body = $template->getBody();
			$mail->set('body', $body);
			return $mail->sendMail();
		} else {
			return false;
		}
	}

	/**
	 * For Subscription Active
	 * */
	public static function subscriptionActive($subscription_id, $status = ''){
		$data['subscription'] = $subscription = Plans::loadSubscriber($subscription_id);
		$subscriptionMeta = $subscription->meta;
		$subscriptionPrefix = $subscription->ID.'_'.$subscription->post_type.'_';
		$data['plan'] = $plan = Plans::loadPlan($subscriptionMeta[$subscriptionPrefix.'plan_id'], 1);
		$planMeta = $plan->meta;
		$planPrefix = $plan->ID.'_'.$plan->post_type.'_';
		$mail = SendMails::getInstance();
		$mail->set('to', $subscriptionMeta[$subscriptionPrefix.'email']);
		$mail->set('subject', 'Subscription Activated');
		$template = view('@Axisubs/Site/mailtemplates/subscription_active.twig', compact('data'));
		if($template->getStatusCode() == 200){
			$body = $template->getBody();
			$mail->set('body', $body);
			return $mail->sendMail();
		} else {
			return false;
		}
	}

	/**
	 * For Subscription Expired
	 * */
	public static function subscriptionExpired($subscription_id, $status = ''){
		$data['subscription'] = $subscription = Plans::loadSubscriber($subscription_id);
		$subscriptionMeta = $subscription->meta;
		$subscriptionPrefix = $subscription->ID.'_'.$subscription->post_type.'_';
		$data['plan'] = $plan = Plans::loadPlan($subscriptionMeta[$subscriptionPrefix.'plan_id'], 1);
		$planMeta = $plan->meta;
		$planPrefix = $plan->ID.'_'.$plan->post_type.'_';
		$mail = SendMails::getInstance();
		$mail->set('to', $subscriptionMeta[$subscriptionPrefix.'email']);
		$mail->set('subject', 'Subscription Expired');
		$template = view('@Axisubs/Site/mailtemplates/subscription_expired.twig', compact('data'));
		if($template->getStatusCode() == 200){
			$body = $template->getBody();
			$mail->set('body', $body);
			return $mail->sendMail();
		} else {
			return false;
		}
	}

	/**
	 * For Subscription Payment Completed
	 * */
	public static function subscriptionPaymentCompleted($subscription_id, $status = ''){
		$data['subscription'] = $subscription = Plans::loadSubscriber($subscription_id);
		$subscriptionMeta = $subscription->meta;
		$subscriptionPrefix = $subscription->ID.'_'.$subscription->post_type.'_';
		$data['plan'] = $plan = Plans::loadPlan($subscriptionMeta[$subscriptionPrefix.'plan_id'], 1);
		$planMeta = $plan->meta;
		$planPrefix = $plan->ID.'_'.$plan->post_type.'_';
		$mail = SendMails::getInstance();
		$mail->set('to', $subscriptionMeta[$subscriptionPrefix.'email']);
		$mail->set('subject', 'Payment Completed');
		$template = view('@Axisubs/Site/mailtemplates/payment_completed.twig', compact('data'));
		if($template->getStatusCode() == 200){
			$body = $template->getBody();
			$mail->set('body', $body);
			return $mail->sendMail();
		} else {
			return false;
		}
	}

	/**
	 * For Subscription Payment Failed
	 * */
	public static function subscriptionPaymentFailed($subscription_id, $status = ''){
		$data['subscription'] = $subscription = Plans::loadSubscriber($subscription_id);
		$subscriptionMeta = $subscription->meta;
		$subscriptionPrefix = $subscription->ID.'_'.$subscription->post_type.'_';
		$data['plan'] = $plan = Plans::loadPlan($subscriptionMeta[$subscriptionPrefix.'plan_id'], 1);
		$planMeta = $plan->meta;
		$planPrefix = $plan->ID.'_'.$plan->post_type.'_';
		$mail = SendMails::getInstance();
		$mail->set('to', $subscriptionMeta[$subscriptionPrefix.'email']);
		$mail->set('subject', 'Payment Failed');
		$template = view('@Axisubs/Site/mailtemplates/payment_failed.twig', compact('data'));
		if($template->getStatusCode() == 200){
			$body = $template->getBody();
			$mail->set('body', $body);
			return $mail->sendMail();
		} else {
			return false;
		}
	}

	/**
	 * For Subscription Payment Canceled
	 * */
	public static function subscriptionPaymentCanceled($subscription_id, $status = ''){
		$data['subscription'] = $subscription = Plans::loadSubscriber($subscription_id);
		$subscriptionMeta = $subscription->meta;
		$subscriptionPrefix = $subscription->ID.'_'.$subscription->post_type.'_';
		$data['plan'] = $plan = Plans::loadPlan($subscriptionMeta[$subscriptionPrefix.'plan_id'], 1);
		$planMeta = $plan->meta;
		$planPrefix = $plan->ID.'_'.$plan->post_type.'_';
		$mail = SendMails::getInstance();
		$mail->set('to', $subscriptionMeta[$subscriptionPrefix.'email']);
		$mail->set('subject', 'Payment Canceled');
		$template = view('@Axisubs/Site/mailtemplates/payment_canceled.twig', compact('data'));
		if($template->getStatusCode() == 200){
			$body = $template->getBody();
			$mail->set('body', $body);
			return $mail->sendMail();
		} else {
			return false;
		}
	}
}