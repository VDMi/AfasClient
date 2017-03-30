<?php

use PracticalAfas\Connection;

interface Constants {
  // Constants for data type, named after connector names.
  const DATA_TYPE_GET = 'get';
  const DATA_TYPE_REPORT = 'report';
  const DATA_TYPE_SUBJECT = 'subject';
  const DATA_TYPE_DATA = 'data';
  const DATA_TYPE_TOKEN = 'token';
  const DATA_TYPE_VERSION_INFO = 'versioninfo';
  // 'alias' constants that are more descriptive than the connector names.
  const DATA_TYPE_ATTACHMENT = 'subject';
  const DATA_TYPE_SCHEMA = 'data';

  // Constants for filter operators.
  const OP_EQUAL = 1;
  const OP_LARGER_OR_EQUAL = 2;
  const OP_SMALLER_OR_EQUAL = 3;
  const OP_LARGER_THAN = 4;
  const OP_SMALLER_THAN = 5;
  const OP_LIKE = 6;
  const OP_NOT_EQUAL = 7;
  const OP_EMPTY = 8;
  const OP_NOT_EMPTY = 9;
  const OP_STARTS_WITH = 19;
  const OP_NOT_LIKE = 11;
  const OP_NOT_STARTS_WITH = 12;
  const OP_ENDS_WITH = 13;
  const OP_NOT_ENDS_WITH = 4;
  // 'alias' constants because "like" is a bit ambiguous.
  const OP_CONTAINS = 6;
  const OP_NOT_CONTAINS = 11;

  // Constants representing the 'Outputmode' option for GetConnectors. There are
  // numeric values which are supported as-is by the AFAS endpoint, and other
  // values which represent us needing to process the returned (XML) value to
  // some other format. Default is ARRAY.
  const GET_OUTPUTMODE_ARRAY = 'Array';
  const GET_OUTPUTMODE_SIMPLEXML = 'SimpleXMLElement';
  const GET_OUTPUTMODE_XML = 1;
  // TEXT is defined here, but not supported by this class!
  const GET_OUTPUTMODE_TEXT = 2;

  // Constants representing the (XML) 'Outputoptions' option for GetConnectors.
  // EXCLUDE means that empty column values will not be present in the row
  // representation. (This goes for all 'XML' output modes i.e. also for ARRAY.)
  const GET_OUTPUTOPTIONS_XML_EXCLUDE_EMPTY = 2;
  const GET_OUTPUTOPTIONS_XML_INCLUDE_EMPTY = 3;
  // For text mode, this is what the documentation says and what we have not
  // implemented:
  // "1 = Puntkomma (datums en getallen in formaat van regionale instellingen)
  //  2 = Tab       (datums en getallen in formaat van regionale instellingen)
  //  3 = Puntkomma (datums en getallen in vast formaat)
  //  4 = Tab       (datums en getallen in vast formaat)
  //  Vast formaat betekent: dd-mm-yy voor datums en punt als decimaal scheidingteken voor getallen."

  // Constants representing the 'Metadata' option for GetConnectors.
  const GET_METADATA_NO = 0;
  const GET_METADATA_YES = 1;
}
