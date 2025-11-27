# ☕ Coffee Cups

A simple, modern PHP library for CUPS IPP (Internet Printing Protocol) communication with **zero dependencies**.

[![CI](https://github.com/nacholibre/coffee-cups/actions/workflows/ci.yml/badge.svg)](https://github.com/nacholibre/coffee-cups/actions/workflows/ci.yml)
[![GitHub](https://img.shields.io/github/stars/nacholibre/coffee-cups?style=flat&logo=github)](https://github.com/nacholibre/coffee-cups)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg)](https://php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![No Dependencies](https://img.shields.io/badge/dependencies-none-success.svg)](composer.json)

## Features

- 🖨️ Print documents to CUPS printers using IPP protocol
- 📋 Query printer status and capabilities
- 📄 Manage print jobs (cancel, hold, release)
- 🎯 Simple, fluent API for job configuration
- 🔐 Basic and digest authentication support
- 📦 **Zero dependencies** — only uses PHP's built-in cURL extension

## Requirements

- PHP 8.1 or higher
- cURL extension
- CUPS server

## Installation

```bash
composer require nacholibre/coffee-cups
```

## Quick Start

### Print a File

```php
use CoffeeCups\CupsClient;
use CoffeeCups\Job;

// Create a client (defaults to localhost:631)
$client = new CupsClient();

// Create and configure a print job
$job = new Job('My Document');
$job->setFile('/path/to/document.pdf')
    ->setCopies(2)
    ->setDuplex(true)
    ->setColor(true);

// Send to printer
$result = $client->print('HP_LaserJet', $job);

if ($result->isSuccessful()) {
    echo "Job ID: " . $result->getJobId();
}
```

### Print Raw Content

```php
use CoffeeCups\CupsClient;
use CoffeeCups\Job;

$client = new CupsClient();

$job = new Job('Receipt');
$job->setContent("Hello, World!\n\nThis is a test print.")
    ->setFormat('text/plain');

$result = $client->print('Receipt_Printer', $job);
```

## Job Configuration

The `Job` class provides a fluent API for configuring print jobs:

### Creating a Job

```php
// With name
$job = new Job('My Document');

// Without name (set later)
$job = new Job();
$job->setName('My Document');
```

### Document Source

```php
// From file (auto-detects format from extension)
$job->setFile('/path/to/document.pdf');

// From raw content
$job->setContent($pdfContent);
```

### Document Format

```php
$job->setFormat('application/pdf');      // PDF
$job->setFormat('application/postscript'); // PostScript
$job->setFormat('text/plain');           // Plain text
$job->setFormat('image/png');            // PNG image
```

### Copies

```php
$job->setCopies(3);
```

### Duplex (Two-Sided Printing)

```php
$job->setDuplex(true);          // Two-sided, flip on long edge
$job->setDuplex(true, false);   // Two-sided, flip on short edge
$job->setDuplex(false);         // Single-sided
```

### Orientation

```php
$job->setOrientation('portrait');
$job->setOrientation('landscape');
$job->setOrientation('reverse-portrait');
$job->setOrientation('reverse-landscape');
```

### Print Quality

```php
$job->setQuality('draft');    // Draft quality
$job->setQuality('normal');   // Normal quality
$job->setQuality('high');     // High quality
```

### Paper Size

```php
// Common shortcuts
$job->setMediaSize('a4');      // A4 paper
$job->setMediaSize('letter');  // US Letter
$job->setMediaSize('legal');   // US Legal
$job->setMediaSize('a3');      // A3 paper

// Full IPP media size names also work
$job->setMediaSize('iso_a4_210x297mm');
$job->setMediaSize('na_letter_8.5x11in');
```

### Media Type

```php
$job->setMediaType('stationery');
$job->setMediaType('transparency');
$job->setMediaType('envelope');
```

### Color Mode

```php
$job->setColor(true);   // Color printing
$job->setColor(false);  // Monochrome/grayscale
```

### Job Priority

```php
$job->setPriority(75);  // 1-100, higher is more urgent
```

### Hold Job

```php
$job->setHold(true);   // Hold the job for later release
$job->setHold(false);  // Don't hold (default)
```

### Custom Attributes

```php
use CoffeeCups\Ipp\IppAttribute;

$job->addAttribute(IppAttribute::keyword('finishings', 'staple'));
$job->addAttribute(IppAttribute::integer('number-up', 4));
```

## CupsClient API

### Connecting to CUPS

```php
use CoffeeCups\CupsClient;

// Local CUPS server
$client = new CupsClient();

// Remote server
$client = new CupsClient(
    host: 'print-server.local',
    port: 631,
    secure: false
);

// With authentication
$client = new CupsClient(
    host: 'print-server.local',
    username: 'admin',
    password: 'secret'
);
```

### Printing

```php
$result = $client->print('Printer_Name', $job);

if ($result->isSuccessful()) {
    echo "Job ID: " . $result->getJobId();
    echo "Job URI: " . $result->getJobUri();
} else {
    echo "Error: " . $result->getMessage();
}
```

### Printer Operations

```php
// Get all printers
$printers = $client->getPrinters();

// Get specific printer
$printer = $client->getPrinter('HP_LaserJet');

echo $printer->name;
echo $printer->state;  // 'idle', 'processing', 'stopped'
echo $printer->isAcceptingJobs;

// Check capabilities
if ($printer->supportsColor()) {
    echo "Color printing supported";
}

if ($printer->supportsDuplex()) {
    echo "Duplex printing supported";
}

// Get default printer
$default = $client->getDefaultPrinter();
```

### Job Management

```php
// Get jobs for a printer
$jobs = $client->getJobs('HP_LaserJet');

// Get only my jobs
$myJobs = $client->getJobs('HP_LaserJet', myJobs: true);

// Get completed jobs
$completed = $client->getJobs('HP_LaserJet', whichJobs: 'completed');

// Cancel a job
$client->cancelJob('HP_LaserJet', $jobId);

// Hold a job
$client->holdJob('HP_LaserJet', $jobId);

// Release a held job
$client->releaseJob('HP_LaserJet', $jobId);
```

### Printer Control

```php
// Pause printer
$client->pausePrinter('HP_LaserJet');

// Resume printer
$client->resumePrinter('HP_LaserJet');
```

## Error Handling

```php
use CoffeeCups\CupsClient;
use CoffeeCups\Job;
use CoffeeCups\Exceptions\ConnectionException;
use CoffeeCups\Exceptions\IppException;

try {
    $client = new CupsClient();
    
    $job = new Job('Test');
    $job->setFile('/path/to/file.pdf');
    
    $result = $client->print('Printer_Name', $job);
    
    if (!$result->isSuccessful()) {
        echo "Print failed: " . $result->getMessage();
    }
} catch (ConnectionException $e) {
    echo "Connection failed: {$e->getMessage()}";
} catch (IppException $e) {
    echo "IPP error ({$e->getStatusCode()}): {$e->getMessage()}";
}
```

## Printer URI Format

CUPS uses URIs to identify printers:

- `ipp://localhost:631/printers/Printer_Name` - IPP protocol
- `ipps://localhost:631/printers/Printer_Name` - IPP over TLS

The library handles URI construction automatically when you provide a printer name.

## Testing

### Unit Tests

Run unit tests (no external dependencies required):

```bash
composer install
./vendor/bin/phpunit --testsuite unit
```

### Integration Tests

Integration tests run against a real CUPS server via Docker:

```bash
# Start CUPS server and run integration tests
make test-integration

# Or run everything via Docker
make test-all
```

You can also start the CUPS server manually:

```bash
# Start CUPS server (available at localhost:6631)
make cups-up

# Run integration tests
CUPS_HOST=localhost CUPS_PORT=6631 ./vendor/bin/phpunit --testsuite integration --group integration

# Stop CUPS server
make cups-down
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request
