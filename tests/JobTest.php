<?php

declare(strict_types=1);

namespace CoffeeCups\Tests;

use CoffeeCups\Job;
use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{
    public function testCreateJob(): void
    {
        $job = new Job('Test Job');

        $this->assertSame('Test Job', $job->getName());
    }

    public function testCreateJobWithoutName(): void
    {
        $job = new Job();
        $job->setName('Test Job');

        $this->assertSame('Test Job', $job->getName());
    }

    public function testJobWithCopies(): void
    {
        $job = new Job('Test');
        $job->setCopies(5);

        $attributes = $job->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertSame('copies', $attributes[0]->name);
        $this->assertSame(5, $attributes[0]->value);
    }

    public function testJobWithDuplexEnabled(): void
    {
        $job = new Job('Test');
        $job->setDuplex(true);

        $attributes = $job->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertSame('sides', $attributes[0]->name);
        $this->assertSame('two-sided-long-edge', $attributes[0]->value);
    }

    public function testJobWithDuplexShortEdge(): void
    {
        $job = new Job('Test');
        $job->setDuplex(true, false);

        $attributes = $job->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertSame('sides', $attributes[0]->name);
        $this->assertSame('two-sided-short-edge', $attributes[0]->value);
    }

    public function testJobWithDuplexDisabled(): void
    {
        $job = new Job('Test');
        $job->setDuplex(false);

        $attributes = $job->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertSame('sides', $attributes[0]->name);
        $this->assertSame('one-sided', $attributes[0]->value);
    }

    public function testJobWithContent(): void
    {
        $content = 'Hello, World!';

        $job = new Job('Test');
        $job->setContent($content)
            ->setFormat('text/plain');

        $this->assertTrue($job->hasContent());
        $this->assertSame($content, $job->getContent());
        $this->assertSame('text/plain', $job->getDocumentFormat());
    }

    public function testJobAutoDetectsFormatFromFile(): void
    {
        $job = new Job('Test');
        $job->setFile('/tmp/document.pdf');

        $this->assertSame('application/pdf', $job->getDocumentFormat());
        $this->assertSame('document.pdf', $job->getDocumentName());
    }

    public function testJobWithMultipleSettings(): void
    {
        $job = new Job('Complex Job');
        $job->setCopies(3)
            ->setDuplex(true)
            ->setColor(true)
            ->setMediaSize('a4')
            ->setQuality('high');

        $attributes = $job->getAttributes();

        $this->assertCount(5, $attributes);
    }

    public function testJobMediaSizeShortcuts(): void
    {
        $job = new Job('Test');
        $job->setMediaSize('a4');

        $attributes = $job->getAttributes();

        $this->assertSame('media', $attributes[0]->name);
        $this->assertSame('iso_a4_210x297mm', $attributes[0]->value);
    }

    public function testJobOrientationValues(): void
    {
        $job = new Job('Test');
        $job->setOrientation('landscape');

        $attributes = $job->getAttributes();

        $this->assertSame('orientation-requested', $attributes[0]->name);
        $this->assertSame(4, $attributes[0]->value);
    }

    public function testJobQualityValues(): void
    {
        $job = new Job('Test');
        $job->setQuality('high');

        $attributes = $job->getAttributes();

        $this->assertSame('print-quality', $attributes[0]->name);
        $this->assertSame(5, $attributes[0]->value);
    }

    public function testFluentInterface(): void
    {
        $job = new Job('Test');

        $this->assertSame($job, $job->setName('New Name'));
        $this->assertSame($job, $job->setCopies(2));
        $this->assertSame($job, $job->setDuplex(true));
        $this->assertSame($job, $job->setOrientation('portrait'));
        $this->assertSame($job, $job->setColor(true));
    }

    public function testJobColorModes(): void
    {
        $job = new Job('Test');
        $job->setColor(true);

        $attributes = $job->getAttributes();
        $this->assertSame('color', $attributes[0]->value);

        $job2 = new Job('Test');
        $job2->setColor(false);

        $attributes2 = $job2->getAttributes();
        $this->assertSame('monochrome', $attributes2[0]->value);
    }

    public function testJobHold(): void
    {
        $job = new Job('Test');
        $job->setHold(true);

        $attributes = $job->getAttributes();

        $this->assertSame('job-hold-until', $attributes[0]->name);
        $this->assertSame('indefinite', $attributes[0]->value);
    }
}
