<?php

namespace Icinga\Module\Itop;

use Icinga\Application\Benchmark;
use Icinga\Data\ConfigObject;
use Icinga\Data\ResourceFactory;
use Icinga\Exception\ConfigurationError;
use Icinga\Exception\IcingaException as Exception;

/**
 * Class RestApiClient
 * @package Icinga\Module\Itop
 */
class RestApiClient
{
	/**
	 * @var resource
	 */
	protected $curl;

	/**
	 * @var ConfigObject
	 */
	protected $resource;

	/**
	 * RestApiClient constructor.
	 * @param string $resourceName
	 */
	public function __construct($resourceName = null)
	{
		if (!is_null($resourceName)) $this->setResource($resourceName);
	}

	/**
	 * @param string|ConfigObject $resource
	 */
	public function setResource($resource)
	{
		if (is_string($resource))
		{
			$this->resource = ResourceFactory::getResourceConfig($resource);
		}
		else
		{
			$this->resource = $resource;
		}
	}

	/**
	 * @param int|string $query
	 * @param int $no_localize
	 * @param string $fields
	 * @return array
	 * @throws ConfigurationError
	 * @throws Exception
	 */
	public function doExport($query, $no_localize = 1, $fields = '')
	{
		$data = array(
			'login_mode' => 'basic',
			'format' => 'csv',
			'no_localize' => $no_localize,
		);

		if (is_int($query)) $data['query'] = $query;
		elseif (!empty($query) && !empty($fields))
		{
			$data['expression'] = $query;
			$data['fields'] = $fields;
		}

		$url = sprintf('%s/webservices/export-v2.php?%s', $this->resource->url, http_build_query($data));

		$headers = array(
			'Accept: text/csv',
		);

		$response = $this->curlRequest('GET', $url, $headers);

		// If it has html code, something went wrong
		if (!strncmp($response, '<!DOCTYPE html', 14))
		{
			if (preg_match('#^ERROR: (.*)$#mi', strip_tags($response), $match))
			{
				throw new Exception($match[1]);
			}

			// Unknown reason
			throw new ConfigurationError('Response contains HTML code instead of CSV');
		}

		// process the data
		$export = $fields = array();
		$tempFile = new \SplTempFileObject();
		$tempFile->fwrite($response);
		$tempFile->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
		$tempFile->setCsvControl(',','"','"');

		foreach ($tempFile as $key => $row)
		{
			if ($key == 0)
			{
				$fields = $row;
			}
			elseif (count($fields) == count($row))
			{
				$export[] = (object) array_combine($fields, $row);
			}
			else
			{
				throw new Exception('Column count in row %d does not match columns in header row', $key);
			}
		}

		return $export;
	}

	/**
	 * @param $operation
	 * @param array $data
	 * @return mixed
	 * @throws ConfigurationError
	 * @throws Exception
	 */
	public function doRestCall($operation, $data = array())
	{
		if (empty($operation))
			throw new ConfigurationError('No REST operation given');

		$url = sprintf('%s/webservices/rest.php?version=1.3', $this->resource->url);

		$headers = array(
			'Accept: application/json',
		);

		$fields = array(
			'json_data' => json_encode(array('operation' => $operation) + $data),
		);

		$response = @json_decode($this->curlRequest('POST', $url, $headers, $fields));

		if ($response === null) {
			switch (json_last_error()) {
				case JSON_ERROR_DEPTH:
					$message = 'The maximum stack depth has been exceeded';
					break;
				case JSON_ERROR_CTRL_CHAR:
					$message = 'Control character error, possibly incorrectly encoded';
					break;
				case JSON_ERROR_STATE_MISMATCH:
					$message = 'Invalid or malformed JSON';
					break;
				case JSON_ERROR_SYNTAX:
					$message = 'Syntax error';
					break;
				case JSON_ERROR_UTF8:
					$message = 'Malformed UTF-8 characters, possibly incorrectly encoded';
					break;
				default:
					$message = 'An error occured when parsing a JSON string';
			}
			throw new Exception('Parsing JSON result failed: ' . $message);
		}

		if (!isset($response->code) or $response->code != 0)
		{
			throw new Exception($response->message);
		}

		return $response;
	}

	/**
	 * @return resource
	 * @throws Exception
	 */
	protected function curlInit()
	{
		if (is_null($this->curl))
		{
			$this->curl = curl_init($this->resource->url);
			if (! $this->curl)
			{
				throw new Exception('CURL INIT ERROR: ' . curl_error($this->curl));
			}
		}

		return $this->curl;
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param array $headers
	 * @param array $fields
	 * @return string
	 * @throws ConfigurationError
	 * @throws Exception
	 */
	protected function curlRequest($method, $url, $headers = array(), $fields = array())
	{
		if (!function_exists('curl_version'))
			throw new Exception('No CURL extension detected, it must be installed and enabled');

		$auth = sprintf('%s:%s', $this->resource->username, $this->resource->password);
		$curl = $this->curlInit();
		$opts = array(
			CURLOPT_URL            => $url,
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_USERPWD        => $auth,
			CURLOPT_CUSTOMREQUEST  => strtoupper($method),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 3,

			// TODO: Fix this!
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
		);

		if (!empty($fields)) {
			$opts[CURLOPT_POST] = count($fields);
			$opts[CURLOPT_POSTFIELDS] = http_build_query($fields);
		}

		curl_setopt_array($curl, $opts);
		// TODO: request headers, validate status code

		Benchmark::measure('Rest Api, sending ' . $url);
		$res = curl_exec($curl);
		if ($res === false) {
			throw new Exception('CURL ERROR: ' . curl_error($curl));
		}

		$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($statusCode === 401) {
			throw new ConfigurationError('Unable to authenticate, please check your API credentials');
		}

		Benchmark::measure('Rest Api, got response');

		return $res;
	}
}
