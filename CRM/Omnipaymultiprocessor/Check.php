<?php

class CRM_Omnipaymultiprocessor_Check extends CRM_Utils_Check_Component {

  public function checkOmnipayPaymentExpressPxPay() {
    $messages = [];
    $pxpayPaymentProcessorIDs = civicrm_api3('PaymentProcessor', 'get', [
      'return' => ['id'],
      'payment_processor_type_id' => 'omnipay_PaymentExpress_PxPay',
      'signature' => ['IS NULL' => 1],
      'subject' => ['IS NULL' => 1],
      'options' => ['or' => [["signature", "subject"]]],
    ]);
    if (empty($pxpayPaymentProcessorIDs['count'])) {
      return $messages;
    }
    //PxPost setup is not configured in payment express processor.
    $pxpayPaymentProcessorIDs = array_column($pxpayPaymentProcessorIDs['values'], 'id');
    $contributionPages = civicrm_api3('ContributionPage', 'get', [
      'return' => ['id'],
      'payment_processor' => ['IN' => $pxpayPaymentProcessorIDs],
      'is_recur' => 1,
    ]);
    if (empty($contributionPages['count'])) {
      return $messages;
    }
    //Recur payment is enabled in contribution page. The recur will fail as pxpost setup is missing.
    $pageIds = array_column($contributionPages['values'], 'id');

    $message = new CRM_Utils_Check_Message(
      __FUNCTION__ . 'omnipay_requirements',
      ts('PxPost config is not added in the PxPay payment processor but is used for recur payments on contribution pages(page ids - %1).',
        [
          1 => implode(', ', $pageIds),
        ]),
      ts('Omnipay PxPay: Missing PxPost configuration'),
      \Psr\Log\LogLevel::ERROR,
      'fa-money'
    );
    $messages[] = $message;
    return $messages;
  }

}
