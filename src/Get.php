<?php

use PracticalAfas\Connection;

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
   * Add an option to your call.
   *
   * Some examples are: Skip, Take, Outputmode and Outputoptions.
   *
   * @param string $option
   * @param mixed $value
   * @return Get
   */
  public function option($option, $value) {
    $this->arguments += [$option => $value];
    return $this;
  }

  /**
   * Request an amount of rows, starting from a row.
   *
   * Skip can be left empty to start from 0.
   *
   * @param int $take
   * @param int $skip
   * @return Get
   */
  public function range($take, $skip = 0) {
    $this->option('Take', $take);
    $this->option('Skip', $skip);
    return $this;
  }

  /**
   * Add a filter to your call.
   *
   * For example: ->filter('FIELD', '%STRING%', OP_LIKE)
   * This wil filter the field: FIELD, and look if the value contains STRING.
   *
   * @param string $fieldId
   * @param string null $searchValue
   * @param int $operator
   * @return Get
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
   * Make the call.
   *
   * @return array $result
   */
  public function execute() {
    if (empty($this->arguments['Outputoptions'])) {
      $this->option('Outputoptions', Connection::GET_OUTPUTOPTIONS_XML_INCLUDE_EMPTY);
    }
    if (empty($this->arguments['Outputmode'])) {
      $this->option('Outputmode', Connection::GET_OUTPUTMODE_ARRAY);
    }
    $connection = new Connection($this->afasClient);
    $result = $connection->getData('website_project',
      ['filters' => $this->filters],
      $this->dataType,
      ['options' => $this->arguments]);
    return $result;
  }
}