<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ValidarSofiaJob;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidarSofiaJobTest extends TestCase
{
    #[Test]
    public function puede_crear_job(): void
    {
        $job = new ValidarSofiaJob(1, 1, 1);

        $this->assertInstanceOf(ValidarSofiaJob::class, $job);
        $this->assertEquals(1, $job->complementarioId);
    }
}
