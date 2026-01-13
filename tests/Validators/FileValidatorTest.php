<?php

declare(strict_types=1);

namespace Tests\Unit\Validators;

use PHPUnit\Framework\TestCase;
use Vigihdev\MockForge\Validators\FileValidator;
use Vigihdev\MockForge\Exceptions\FileException;

class FileValidatorTest extends TestCase
{
    private string $testDir;
    private string $testFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDir = sys_get_temp_dir() . '/mockforge-test-' . uniqid();
        $this->testFile = $this->testDir . '/test.json';

        mkdir($this->testDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }

        if (is_dir($this->testDir)) {
            rmdir($this->testDir);
        }

        parent::tearDown();
    }

    /** @test */
    public function it_validates_file_does_not_exist(): void
    {
        // File belum ada, harus sukses
        $validator = FileValidator::validate($this->testFile);
        $result = $validator->mustBeNotExist();

        $this->assertInstanceOf(FileValidator::class, $result);
    }

    /** @test */
    public function it_throws_exception_when_file_exists(): void
    {
        // Buat file dulu
        file_put_contents($this->testFile, 'test');

        $this->expectException(FileException::class);
        $this->expectExceptionMessageMatches('/already exists/');

        FileValidator::validate($this->testFile)->mustBeNotExist();
    }

    /** @test */
    public function it_validates_file_extension(): void
    {
        $validator = FileValidator::validate($this->testFile);

        // Valid extension
        $result = $validator->mustHaveExtension()->mustBeExtension('json');
        $this->assertInstanceOf(FileValidator::class, $result);

        // Invalid extension
        $txtFile = $this->testDir . '/test.txt';

        $this->expectException(FileException::class);
        $this->expectExceptionMessageMatches('/json|csv/');

        FileValidator::validate($txtFile)
            ->mustHaveExtension()
            ->mustBeExtension('json', 'csv');
    }

    /** @test */
    public function it_validates_file_exists(): void
    {
        // File belum ada
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('not found');

        FileValidator::validate($this->testFile)->mustExist();
    }

    /** @test */
    public function it_validates_file_is_readable(): void
    {
        // Buat file
        file_put_contents($this->testFile, 'test');
        chmod($this->testFile, 0000); // Tidak readable

        $this->expectException(FileException::class);
        $this->expectExceptionMessage('not readable');

        FileValidator::validate($this->testFile)->mustBeReadable();
    }

    /** @test */
    public function it_validates_file_is_writable(): void
    {
        // Buat file
        file_put_contents($this->testFile, 'test');
        chmod($this->testFile, 0444); // Read only

        $this->expectException(FileException::class);
        $this->expectExceptionMessage('not writable');

        FileValidator::validate($this->testFile)->mustBeWritable();
    }

    /** @test */
    public function it_validates_file_is_not_empty(): void
    {
        // Buat empty file
        file_put_contents($this->testFile, '');

        $this->expectException(FileException::class);
        $this->expectExceptionMessage('empty');

        FileValidator::validate($this->testFile)->mustNotBeEmpty();
    }

    /** @test */
    public function it_validates_file_size(): void
    {
        // Buat file dengan content besar
        $content = str_repeat('x', 1025); // 1KB + 1 byte
        file_put_contents($this->testFile, $content);

        $this->expectException(FileException::class);
        $this->expectExceptionMessage('maximum');

        FileValidator::validate($this->testFile)->mustNotExceedSize(1024);
    }

    /** @test */
    public function it_gets_file_size(): void
    {
        $content = 'test content';
        file_put_contents($this->testFile, $content);

        $size = FileValidator::validate($this->testFile)->getSize();

        $this->assertEquals(strlen($content), $size);
    }

    /** @test */
    public function it_chains_multiple_validations(): void
    {
        // Test fluent interface
        $validator = FileValidator::validate($this->testFile)
            ->mustHaveExtension()
            ->mustBeExtension('json');

        $this->assertInstanceOf(FileValidator::class, $validator);
    }
}
