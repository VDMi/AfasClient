<?php

namespace AfasClient;

use PracticalAfas\Connection;
use PracticalAfas\NusoapNtlmClient;

class AfasClient extends NusoapNtlmClient {

  /**
   * This overwrites the NusoapNtlmClient constructor. The domain is removed
   * from the required parameters because it isn't used without ntlm.
   *
   * Constructor.
   *
   * Since there is no other way of setting options, we check them inside the
   * constructor and throw an exception if we know any AFAS calls will fail.
   *
   * @param array $options
   *   Configuration options. see SoapNtlmClient::__construct() for values,
   *   except 'soapClientClass'.
   *
   * @throws \InvalidArgumentException
   *   If some option values are missing / incorrect.
   */
  public function __construct(array $options) {
    foreach (array(
               'urlBase',
               'environmentId',
               'userId',
               'password'
             ) as $required_key) {
      if (empty($options[$required_key])) {
        $classname = get_class($this);
        throw new \InvalidArgumentException("Required configuration parameter for $classname missing: $required_key.", 1);
      }
    }

    // Add defaults for the SOAP client.
    $options += array(
      'soap_defencoding' => 'utf-8',
      'xml_encoding' => 'utf-8',
      'decode_utf8' => FALSE,
    );

    $this->options = $options;
  }

  /**
   * This overwrites the getSoapClient in NusoapNtlmClient. The ntlm
   * autentication is removed and curl is reconfigured to not require ssl.
   *
   * Returns a SOAP client object, configured with options previously set.
   *
   * @param string $type
   *   Type of AFAS connector. (This determines the SOAP endpoint URL.)
   *
   * @return \nusoap_client
   *   Initialized client object.
   *
   * @throws \Exception
   *   If we failed to construct a nusoap_client class.
   */
  protected function getSoapClient($type) {
    // Make sure the aging nuSOAP code does not make PHP5.3 give strict timezone
    // warnings.
    // Note: date_default_timezone_set() is also called in D7's standard
    // drupal_session_initialize() / D8's drupal_set_configured_timezone().
    // So I don't think this is necessary... Still, to be 100% sure:
    if (!ini_get('date.timezone')) {
      if (!$timezone = variable_get('date_default_timezone')) {
        $timezone = @date_default_timezone_get();
      }
      date_default_timezone_set($timezone);
    }

    // available: get/update/report/subject/dataconnector.
    $endpoint = trim($this->options['urlBase'], '/') . '/' . strtolower($type) . 'connector.asmx';

    $options = $this->options + array('useWSDL' => FALSE);
    if ($options['useWSDL']) {
      $endpoint .= '?WSDL';

      if (!empty($this->options['cacheWSDL'])) {
        // Get cached WSDL
        $cache = new \wsdlcache(file_directory_temp(), $this->options['cacheWSDL']);
        $wsdl = $cache->get($endpoint);
        if (is_null($wsdl)) {
          $wsdl = new \wsdl();
          $wsdl->fetchWSDL($endpoint);
          if ($error = $wsdl->getError()) {
            // We should ideally have an exception type where we can store debug
            // details in a separate property. But let's face it, noone is going
            // to use this anymore anyway.
            throw new \RuntimeException("Error getting WSDL: $error. Debug details: " . $wsdl->getDebug(), 24);
          }
          $cache->put($wsdl);
        }
        $endpoint = $wsdl;
      }
    }
    $client = new \nusoap_client($endpoint, $options['useWSDL']);
    $client->useHTTPPersistentConnection();

    // Disable SSL
    $client->setCurlOption(CURLOPT_SSL_VERIFYHOST, 0);
    $client->setCurlOption(CURLOPT_SSL_VERIFYPEER, 0);

    // Specific connection properties can be set by the caller.
    // About timeouts:
    // AFAS has their 'timeout' value on the server set to 5 minutes, and gives
    // no response until it sends the result of a call back. So changing the
    // 'timeout' (default 0) has no effect; the 'response_timeout' can be upped
    // to max. 5 minutes.
    foreach (array(
               'soap_defencoding',
               'xml_encoding',
               'timeout',
               'response_timeout',
               'soap_defencoding',
               'decode_utf8'
             ) as $opt) {
      if (isset($options[$opt])) {
        $client->$opt = $options[$opt];
      }
    }

    return $client;
  }

  /**
   * This overwrites callAfas in the NusoapNtlmClient. The options are now
   * included in the call together with the arguments.
   */
  public function callAfas($connector_type, $function, array $arguments) {
    // Even though this may not be necessary, we want to restrict the connector
    // types to those we know. When adding a new one, we want to carefully check
    // whether we're not missing any arguments that we should be preprocessing.
    if (!in_array($connector_type, array(
      'get',
      'update',
      'report',
      'subject',
      'data'
    ))
    ) {
      throw new \InvalidArgumentException("Invalid connector type $connector_type", 40);
    }

    $client = $this->getSoapClient($connector_type);

    if ($client->endpointType === 'wsdl') {
      $response = $client->call($function, $this->options + $arguments);
    }
    else {
      $response = $client->call($function, $this->options + $arguments, 'urn:Afas.Profit.Services', 'urn:Afas.Profit.Services/' . $function, FALSE, NULL, 'document', 'literal wrapped');
    }
    if ($error = $client->getError()) {
      if (isset($response->detail)) {
        // NuSOAP's $client->getDebug() is just unusable. It includes
        // duplicate info and lots of HTML font colors etc (or is that my
        // settings influencing var_dump output? That still doesn't change
        // the fact that it's unusable though).
        // There are some details in there that are not in $response, like the
        // parameters (but we already have those in
        // $afas_soap_connection->lastCallInfo) and HTTP headers sent/received
        // $response now actually is an array with 'faultcode', 'faultstring'
        // and 'detail' keys - 'detail' contains 'ProfitApplicationException'
        // containing 'ErrorNumber', 'Message' (== faultstring) and 'Detail'.
        $details = print_r($response, TRUE);
      }
      else {
        // Too bad; we don't have anything else than this...
        // (If ->detail isn't set, then probably $response is false. If it is
        // not false, we don't know yet which option is better.)
        $details = $client->getDebug();
      }
      // We should ideally have an exception type where we can store debug
      // details in a separate property. But let's face it, noone is going
      // to use this anymore anyway.
      throw new \RuntimeException("Error calling SOAP endpoint: $error. Debug details: $details", 23);
    }

    if (isset($response[$function . 'Result'])) {
      return $response[$function . 'Result'];
    }
    throw new \UnexpectedValueException('Unknown response format: ' . json_encode($response), 24);
  }

  /**
   * Create a new AfasClient to make a call.
   */
  public function afas_select($connectorId) {
    $client = new AfasClient($this, $connectorId);
    return $client;
  }
}
