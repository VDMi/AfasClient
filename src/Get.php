<?php

namespace AfasClient;

class Get {

  private $afasClient = null;
  private $dataType = 'get';
  private $connectionId = '';
  private $filters = [];
  private $arguments = [];

  function __construct($afasClient, $connectorId) {
    $this->afasClient = $afasClient;
    $this->connectionId = $connectorId;
  }

  /**
   * Add a filter to your call.
   *
   * For example: ->filter('FIELD', '%STRING%', OP_LIKE)
   * This wil filter the field: FIELD, and look if the value contains STRING.
   */
  public function filter($fieldId, $searchValue = NULL, $operator = 1) {
    $this->filters['#op'] = $operator;
    if ($operator != 8
      && $operator != 9
      && $searchValue != NULL
    ) {
      $this->filters[$fieldId] = $searchValue;
    }
    return $this;
  }

  /**
   * Add an option to your call.
   *
   * Some examples are: Skip, Take, Outputmode and Outputoptions.
   */
  public function option($option, $value) {
    $this->arguments += [$option => $value];
    return $this;
  }

  /**
   * Make the call.
   */
  public function execute() {
    if (empty($this->arguments['Outputoptions'])) {
      $this->arguments['Outputoptions'] = Connection::GET_OUTPUTOPTIONS_XML_INCLUDE_EMPTY;
    }
    if (empty($this->arguments['Outputmode'])) {
      $this->arguments['Outputmode'] = Connection::GET_OUTPUTMODE_ARRAY;
    }
    $connection = new Connection($this->afasClient);
    $result = $connection->getData('website_project',
      ['filters' => $this->filters],
      $this->dataType,
      ['options' => $this->arguments]);
    return $result;
  }
}