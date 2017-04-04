AfasClient
==========
AfasClient is a package to make easy soap requests. For now this only supports NuSoap requests and removes NTLM authentication and CURL login. Later on this wil be configurable.

Usage
-----
First you have to "configure" AfasClient to make a call. You can do this by adding the information in an array in a new AfasClient object.
```
$this->afas = new AfasClient([
      'urlBase' => 'https://example.com/profitservices/',
      'environmentId' => 'YourEnviromentId',
      'useWSDL' => TRUE,
      'userId' => 'YourUserId',
      'password' => 'YourPassword',
    ]);
```
To make the call you have to construct a query. This will be done by chaining functions.
The functions you can use are: 
* range
* filter
* orderBy
* option
```
$query = $this->afas->get('some_connector')
      ->range(10, 10) // Take 10, Skip 10.
      ->filter('title', 'WIP:%', AfasClient::OP_STARTS_WITH) // Title starts with "WIP:".
      ->orderBy('title', AfasClient::OP_ASC) // Order by title ascending.
      ->option('Outputmode', AfasClient::GET_OUTPUTMODE_ARRAY) // Return array.
```
Finnaly you want to make the call. 
```
$result = $query->execute();
```

Installation
------------
You can install this package with composer by executing
`$ composer install vdmi/afas-client`.

