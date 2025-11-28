<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ValidarDocumentoJob;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidarDocumentoJobTest extends TestCase
{
    #[Test]
    public function puede_crear_job(): void
    {
        $job = new ValidarDocumentoJob(1, 1, 1);

        $this->assertInstanceOf(ValidarDocumentoJob::class, $job);
        $this->assertEquals(1, $job->complementarioId);
    }
}
