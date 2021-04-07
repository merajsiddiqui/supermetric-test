<?php

namespace Supermetrics\SDK;

use GuzzleHttp\Client;


/**
 * Class Authenticate
 * @package Supermetrics\SDK
 */
class Authenticate
{

    /**
     * @var string $clientId
     */
    protected string $clientId;

    /**
     * @var string $email
     */
    protected string $email;

    /**
     * @var string $name
     */
    protected string $name;

    /**
     * @var string
     */
    private string $registerURL = "https://api.supermetrics.com/assignment/register";

    /**
     * Authenticate constructor.
     * @param string $clientId
     * @param string $email
     * @param string $name
     */
    public function __construct(string $clientId, string $email, string $name)
    {
        $this->clientId = $clientId;
        $this->email = $email;
        $this->name = $name;
    }


    /**
     * @return string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function getSLToken(): string|null
    {
        $client = new Client();
        $registrationResponse = $client->request(
            'POST',
            $this->registerURL,
            [
                'json' => [
                    'client_id' => $this->clientId,
                    'email'     => $this->email,
                    'name'      => $this->name
                ]
            ]
        );
        $statusCode = $registrationResponse->getStatusCode();
        $registrationResponseContent = json_decode((string)$registrationResponse->getBody());
        if ($statusCode != 200) {
            throw new \Exception($registrationResponseContent->error?->message);
        }
        return $registrationResponseContent->data?->sl_token;
    }
}