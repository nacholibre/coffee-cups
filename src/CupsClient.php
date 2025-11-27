<?php

declare(strict_types=1);

namespace CoffeeCups;

use CoffeeCups\Exceptions\ConnectionException;
use CoffeeCups\Exceptions\IppException;
use CoffeeCups\Ipp\IppAttribute;
use CoffeeCups\Ipp\IppOperation;
use CoffeeCups\Ipp\IppRequest;
use CoffeeCups\Ipp\IppResponse;

/**
 * Client for communicating with CUPS server using IPP protocol.
 */
class CupsClient
{
    private string $host;
    private int $port;
    private bool $secure;
    private ?string $username;
    private ?string $password;
    private int $timeout;

    /**
     * @param string $host CUPS server hostname
     * @param int $port CUPS server port (default: 631)
     * @param bool $secure Use HTTPS (default: false)
     * @param string|null $username Username for authentication
     * @param string|null $password Password for authentication
     * @param int $timeout Connection timeout in seconds
     */
    public function __construct(
        string $host = 'localhost',
        int $port = 631,
        bool $secure = false,
        ?string $username = null,
        ?string $password = null,
        int $timeout = 30,
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->secure = $secure;
        $this->username = $username;
        $this->password = $password;
        $this->timeout = $timeout;
    }

    /**
     * Get the base URI for the CUPS server.
     */
    public function getBaseUri(): string
    {
        $scheme = $this->secure ? 'ipps' : 'ipp';

        return "{$scheme}://{$this->host}:{$this->port}";
    }

    /**
     * Get the URI for a specific printer.
     */
    public function getPrinterUri(string $printerName): string
    {
        return $this->getBaseUri() . "/printers/{$printerName}";
    }

    /**
     * Print a job to the specified printer.
     *
     * @throws ConnectionException
     * @throws IppException
     */
    public function print(string $printerName, Job $job): PrintResult
    {
        $printerUri = $this->getPrinterUri($printerName);

        $request = new IppRequest(IppOperation::PRINT_JOB);
        $request->addOperationAttribute(IppAttribute::printerUri($printerUri));

        if ($this->username !== null) {
            $request->addOperationAttribute(IppAttribute::requestingUserName($this->username));
        }

        if ($job->getName() !== '') {
            $request->addOperationAttribute(IppAttribute::jobName($job->getName()));
        }

        if ($job->getDocumentName() !== '') {
            $request->addOperationAttribute(IppAttribute::documentName($job->getDocumentName()));
        }

        $request->addOperationAttribute(IppAttribute::documentFormat($job->getDocumentFormat()));

        // Add job attributes
        foreach ($job->getAttributes() as $attribute) {
            $request->addJobAttribute($attribute);
        }

        // Set document content
        if ($job->hasContent()) {
            $request->setData($job->getContent());
        }

        $response = $this->sendRequest($request, "/printers/{$printerName}");

        return PrintResult::fromResponse($response);
    }

    /**
     * Get list of all printers.
     *
     * @return Printer[]
     * @throws ConnectionException
     * @throws IppException
     */
    public function getPrinters(): array
    {
        $request = new IppRequest(IppOperation::CUPS_GET_PRINTERS);

        if ($this->username !== null) {
            $request->addOperationAttribute(IppAttribute::requestingUserName($this->username));
        }

        $response = $this->sendRequest($request, '/');

        $printers = [];
        $printerAttributes = $response->getPrinterAttributes();

        if (!empty($printerAttributes)) {
            // Single printer response
            $uri = $printerAttributes['printer-uri-supported'] ?? '';
            $printers[] = Printer::fromAttributes($uri, $printerAttributes);
        }

        return $printers;
    }

    /**
     * Get printer information.
     *
     * @throws ConnectionException
     * @throws IppException
     */
    public function getPrinter(string $printerName): Printer
    {
        $printerUri = $this->getPrinterUri($printerName);

        $request = new IppRequest(IppOperation::GET_PRINTER_ATTRIBUTES);
        $request->addOperationAttribute(IppAttribute::printerUri($printerUri));

        if ($this->username !== null) {
            $request->addOperationAttribute(IppAttribute::requestingUserName($this->username));
        }

        $response = $this->sendRequest($request, "/printers/{$printerName}");

        if (!$response->isSuccessful()) {
            throw new IppException(
                $response->getStatusMessage() ?? 'Failed to get printer attributes',
                $response->getStatusCode(),
            );
        }

        return Printer::fromAttributes($printerUri, $response->getPrinterAttributes());
    }

    /**
     * Get the default printer.
     *
     * @throws ConnectionException
     * @throws IppException
     */
    public function getDefaultPrinter(): ?Printer
    {
        $request = new IppRequest(IppOperation::CUPS_GET_DEFAULT);

        if ($this->username !== null) {
            $request->addOperationAttribute(IppAttribute::requestingUserName($this->username));
        }

        $response = $this->sendRequest($request, '/');

        if (!$response->isSuccessful()) {
            return null;
        }

        $printerAttributes = $response->getPrinterAttributes();
        if (empty($printerAttributes)) {
            return null;
        }

        $uri = $printerAttributes['printer-uri-supported'] ?? '';

        return Printer::fromAttributes($uri, $printerAttributes);
    }

    /**
     * Get jobs for a printer.
     *
     * @param string $printerName The printer name
     * @param bool $myJobs Only return jobs owned by the requesting user
     * @param string $whichJobs 'completed', 'not-completed', or 'all'
     * @return array<int, array<string, mixed>>
     * @throws ConnectionException
     * @throws IppException
     */
    public function getJobs(string $printerName, bool $myJobs = false, string $whichJobs = 'not-completed'): array
    {
        $printerUri = $this->getPrinterUri($printerName);

        $request = new IppRequest(IppOperation::GET_JOBS);
        $request->addOperationAttribute(IppAttribute::printerUri($printerUri));

        if ($this->username !== null) {
            $request->addOperationAttribute(IppAttribute::requestingUserName($this->username));
        }

        $request->addOperationAttribute(IppAttribute::boolean('my-jobs', $myJobs));
        $request->addOperationAttribute(IppAttribute::keyword('which-jobs', $whichJobs));

        $response = $this->sendRequest($request, "/printers/{$printerName}");

        if (!$response->isSuccessful()) {
            throw new IppException(
                $response->getStatusMessage() ?? 'Failed to get jobs',
                $response->getStatusCode(),
            );
        }

        return $response->getJobAttributes();
    }

    /**
     * Cancel a job.
     *
     * @throws ConnectionException
     * @throws IppException
     */
    public function cancelJob(string $printerName, int $jobId): bool
    {
        $printerUri = $this->getPrinterUri($printerName);

        $request = new IppRequest(IppOperation::CANCEL_JOB);
        $request->addOperationAttribute(IppAttribute::printerUri($printerUri));
        $request->addOperationAttribute(IppAttribute::integer('job-id', $jobId));

        if ($this->username !== null) {
            $request->addOperationAttribute(IppAttribute::requestingUserName($this->username));
        }

        $response = $this->sendRequest($request, "/printers/{$printerName}");

        return $response->isSuccessful();
    }

    /**
     * Hold a job.
     *
     * @throws ConnectionException
     * @throws IppException
     */
    public function holdJob(string $printerName, int $jobId): bool
    {
        $printerUri = $this->getPrinterUri($printerName);

        $request = new IppRequest(IppOperation::HOLD_JOB);
        $request->addOperationAttribute(IppAttribute::printerUri($printerUri));
        $request->addOperationAttribute(IppAttribute::integer('job-id', $jobId));

        if ($this->username !== null) {
            $request->addOperationAttribute(IppAttribute::requestingUserName($this->username));
        }

        $response = $this->sendRequest($request, "/printers/{$printerName}");

        return $response->isSuccessful();
    }

    /**
     * Release a held job.
     *
     * @throws ConnectionException
     * @throws IppException
     */
    public function releaseJob(string $printerName, int $jobId): bool
    {
        $printerUri = $this->getPrinterUri($printerName);

        $request = new IppRequest(IppOperation::RELEASE_JOB);
        $request->addOperationAttribute(IppAttribute::printerUri($printerUri));
        $request->addOperationAttribute(IppAttribute::integer('job-id', $jobId));

        if ($this->username !== null) {
            $request->addOperationAttribute(IppAttribute::requestingUserName($this->username));
        }

        $response = $this->sendRequest($request, "/printers/{$printerName}");

        return $response->isSuccessful();
    }

    /**
     * Pause a printer.
     *
     * @throws ConnectionException
     * @throws IppException
     */
    public function pausePrinter(string $printerName): bool
    {
        $printerUri = $this->getPrinterUri($printerName);

        $request = new IppRequest(IppOperation::PAUSE_PRINTER);
        $request->addOperationAttribute(IppAttribute::printerUri($printerUri));

        if ($this->username !== null) {
            $request->addOperationAttribute(IppAttribute::requestingUserName($this->username));
        }

        $response = $this->sendRequest($request, "/printers/{$printerName}");

        return $response->isSuccessful();
    }

    /**
     * Resume a paused printer.
     *
     * @throws ConnectionException
     * @throws IppException
     */
    public function resumePrinter(string $printerName): bool
    {
        $printerUri = $this->getPrinterUri($printerName);

        $request = new IppRequest(IppOperation::RESUME_PRINTER);
        $request->addOperationAttribute(IppAttribute::printerUri($printerUri));

        if ($this->username !== null) {
            $request->addOperationAttribute(IppAttribute::requestingUserName($this->username));
        }

        $response = $this->sendRequest($request, "/printers/{$printerName}");

        return $response->isSuccessful();
    }

    /**
     * Send an IPP request and get the response.
     *
     * @throws ConnectionException
     * @throws IppException
     */
    private function sendRequest(IppRequest $request, string $path): IppResponse
    {
        $scheme = $this->secure ? 'https' : 'http';
        $url = "{$scheme}://{$this->host}:{$this->port}{$path}";

        $requestData = $request->build();

        $ch = curl_init($url);
        if ($ch === false) {
            throw new ConnectionException('Failed to initialize cURL');
        }

        $headers = [
            'Content-Type: application/ipp',
            'Content-Length: ' . strlen($requestData),
        ];

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $requestData,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
        ]);

        if ($this->username !== null && $this->password !== null) {
            curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC | CURLAUTH_DIGEST);
        }

        if ($this->secure) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }

        $responseData = curl_exec($ch);

        if ($responseData === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            throw new ConnectionException("cURL error ({$errno}): {$error}");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new ConnectionException("HTTP error: {$httpCode}");
        }

        if (!is_string($responseData)) {
            throw new IppException('Invalid response data');
        }

        return new IppResponse($responseData);
    }
}
