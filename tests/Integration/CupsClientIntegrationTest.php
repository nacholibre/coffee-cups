<?php

declare(strict_types=1);

namespace CoffeeCups\Tests\Integration;

use CoffeeCups\CupsClient;
use CoffeeCups\Exceptions\ConnectionException;
use CoffeeCups\Exceptions\IppException;
use CoffeeCups\Job;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests that require a running CUPS server.
 *
 * Run with: docker-compose up --build --abort-on-container-exit
 * Or locally: CUPS_HOST=localhost CUPS_PORT=6631 ./vendor/bin/phpunit --testsuite integration
 *
 * @group integration
 */
class CupsClientIntegrationTest extends TestCase
{
    private CupsClient $client;
    private string $printerName = 'TestPrinter';

    protected function setUp(): void
    {
        $host = getenv('CUPS_HOST') ?: 'localhost';
        $port = (int) (getenv('CUPS_PORT') ?: 6631);

        $this->client = new CupsClient($host, $port);
    }

    public function testCanConnectToCupsServer(): void
    {
        $printer = $this->client->getPrinter($this->printerName);

        $this->assertSame($this->printerName, $printer->name);
    }

    public function testGetPrinterAttributes(): void
    {
        $printer = $this->client->getPrinter($this->printerName);

        $this->assertNotEmpty($printer->uri);
        $this->assertNotEmpty($printer->state);
        $this->assertContains($printer->state, ['idle', 'processing', 'stopped']);
    }

    public function testGetDefaultPrinter(): void
    {
        $printer = $this->client->getDefaultPrinter();

        $this->assertNotNull($printer);
        $this->assertSame($this->printerName, $printer->name);
    }

    public function testPrintTextContent(): void
    {
        $job = new Job('Integration Test - Text');
        $job->setContent("Hello from Coffee Cups!\n\nThis is an integration test.")
            ->setFormat('text/plain');

        $result = $this->client->print($this->printerName, $job);

        $this->assertTrue($result->isSuccessful(), 'Print failed: ' . $result->getMessage());
        $this->assertNotNull($result->getJobId());
        $this->assertGreaterThan(0, $result->getJobId());
    }

    public function testPrintWithCopies(): void
    {
        $job = new Job('Integration Test - Copies');
        $job->setContent('Test document with multiple copies')
            ->setFormat('text/plain')
            ->setCopies(2);

        $result = $this->client->print($this->printerName, $job);

        $this->assertTrue($result->isSuccessful(), 'Print failed: ' . $result->getMessage());
    }

    public function testPrintWithJobSettings(): void
    {
        $job = new Job('Integration Test - Settings');
        $job->setContent('Test document with various settings')
            ->setFormat('text/plain')
            ->setOrientation('landscape')
            ->setQuality('high');

        $result = $this->client->print($this->printerName, $job);

        $this->assertTrue($result->isSuccessful(), 'Print failed: ' . $result->getMessage());
    }

    public function testPrintAndGetJobs(): void
    {
        // First, print a job
        $job = new Job('Integration Test - Get Jobs');
        $job->setContent('Test for job listing')
            ->setFormat('text/plain');

        $printResult = $this->client->print($this->printerName, $job);
        $this->assertTrue($printResult->isSuccessful());

        // Give CUPS a moment to process
        usleep(500000); // 0.5 seconds

        // Get all jobs (including completed)
        $jobs = $this->client->getJobs($this->printerName, whichJobs: 'all');

        // Jobs should be an array (may be empty if processed quickly)
        $this->assertIsArray($jobs);
    }

    public function testPrintWithHoldAndRelease(): void
    {
        // Create a held job
        $job = new Job('Integration Test - Hold');
        $job->setContent('This job should be held')
            ->setFormat('text/plain')
            ->setHold(true);

        $result = $this->client->print($this->printerName, $job);

        $this->assertTrue($result->isSuccessful(), 'Print failed: ' . $result->getMessage());
        $jobId = $result->getJobId();
        $this->assertNotNull($jobId);

        // Release the job
        $released = $this->client->releaseJob($this->printerName, $jobId);
        $this->assertTrue($released);
    }

    public function testCancelJob(): void
    {
        // Create a held job so we can cancel it
        $job = new Job('Integration Test - Cancel');
        $job->setContent('This job will be cancelled')
            ->setFormat('text/plain')
            ->setHold(true);

        $result = $this->client->print($this->printerName, $job);

        $this->assertTrue($result->isSuccessful());
        $jobId = $result->getJobId();

        // Cancel the job
        $cancelled = $this->client->cancelJob($this->printerName, $jobId);
        $this->assertTrue($cancelled);
    }

    public function testPrinterNotFound(): void
    {
        $this->expectException(IppException::class);

        $this->client->getPrinter('NonExistentPrinter');
    }

    public function testPrintToNonExistentPrinter(): void
    {
        $job = new Job('Test');
        $job->setContent('Test')
            ->setFormat('text/plain');

        $result = $this->client->print('NonExistentPrinter', $job);

        $this->assertFalse($result->isSuccessful());
    }

    public function testConnectionToInvalidHost(): void
    {
        $this->expectException(ConnectionException::class);

        $client = new CupsClient('invalid-host-that-does-not-exist.local', 631, timeout: 2);
        $client->getPrinter('TestPrinter');
    }

    public function testPrintWithoutContent(): void
    {
        $job = new Job('Empty Job');

        $result = $this->client->print($this->printerName, $job);

        // Empty documents may or may not be accepted depending on CUPS configuration
        // We just verify it doesn't throw an exception
        $this->assertIsBool($result->isSuccessful());
    }

    public function testGetPrinterSupportedFormats(): void
    {
        $printer = $this->client->getPrinter($this->printerName);

        $formats = $printer->getSupportedFormats();

        $this->assertIsArray($formats);
        $this->assertNotEmpty($formats);
    }

    public function testPrinterState(): void
    {
        $printer = $this->client->getPrinter($this->printerName);

        // Printer should be idle or processing
        $this->assertTrue(
            $printer->isIdle() || $printer->isProcessing(),
            "Printer should be idle or processing, got: {$printer->state}",
        );

        // Should be accepting jobs
        $this->assertTrue($printer->isAcceptingJobs);
    }
}
