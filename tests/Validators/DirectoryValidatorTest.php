<?php

declare(strict_types=1);

namespace Tests\Unit\Validators;

use PHPUnit\Framework\TestCase;
use Vigihdev\MockForge\Validators\DirectoryValidator;
use Vigihdev\MockForge\Exceptions\DirectoryException;

class DirectoryValidatorTest extends TestCase
{
    private string $testDir;
    private string $testFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDir = sys_get_temp_dir() . '/mockforge-test-' . uniqid();
        $this->testFile = $this->testDir . '/test.txt';
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
    public function it_validates_directory_exists(): void
    {
        mkdir($this->testDir, 0755, true);

        $validator = DirectoryValidator::validate($this->testDir);
        $result = $validator->mustExist();

        $this->assertInstanceOf(DirectoryValidator::class, $result);
    }

    /** @test */
    public function it_throws_exception_when_directory_not_found(): void
    {
        $this->expectException(DirectoryException::class);
        $this->expectExceptionMessage('not found');

        DirectoryValidator::validate($this->testDir)->mustExist();
    }

    /** @test */
    public function it_creates_directory_if_missing(): void
    {
        $validator = DirectoryValidator::validate($this->testDir);
        $result = $validator->ensureExists();

        $this->assertInstanceOf(DirectoryValidator::class, $result);
        $this->assertDirectoryExists($this->testDir);
    }

    /** @test */
    public function it_validates_directory_is_readable(): void
    {
        mkdir($this->testDir, 0000, true); // Tidak readable

        $this->expectException(DirectoryException::class);
        $this->expectExceptionMessage('not readable');

        DirectoryValidator::validate($this->testDir)->mustBeReadable();
    }

    /** @test */
    public function it_validates_directory_is_writable(): void
    {
        mkdir($this->testDir, 0555, true); // Read only

        $this->expectException(DirectoryException::class);
        $this->expectExceptionMessage('not writable');

        DirectoryValidator::validate($this->testDir)->mustBeWritable();
    }

    /** @test */
    public function it_validates_directory_is_empty(): void
    {
        mkdir($this->testDir, 0755, true);

        $validator = DirectoryValidator::validate($this->testDir);
        $result = $validator->mustBeEmpty();

        $this->assertInstanceOf(DirectoryValidator::class, $result);
    }

    /** @test */
    public function it_throws_exception_when_directory_not_empty(): void
    {
        mkdir($this->testDir, 0755, true);
        file_put_contents($this->testFile, 'test');

        $this->expectException(DirectoryException::class);
        $this->expectExceptionMessage('not empty');

        DirectoryValidator::validate($this->testDir)->mustBeEmpty();
    }

    /** @test */
    public function it_checks_if_directory_is_not_empty(): void
    {
        mkdir($this->testDir, 0755, true);

        // Kosong
        $validator = DirectoryValidator::validate($this->testDir);
        $this->assertFalse($validator->isNotEmpty());

        // Tidak kosong
        file_put_contents($this->testFile, 'test');
        $this->assertTrue($validator->isNotEmpty());
    }

    /** @test */
    public function it_chains_multiple_validations(): void
    {
        mkdir($this->testDir, 0755, true);

        $validator = DirectoryValidator::validate($this->testDir)
            ->mustExist()
            ->mustBeReadable()
            ->mustBeWritable();

        $this->assertInstanceOf(DirectoryValidator::class, $validator);
    }
}
