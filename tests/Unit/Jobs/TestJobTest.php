<?php

namespace Tests\Unit\Jobs;

use App\Jobs\TestJob;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TestJobTest extends TestCase
{
    #[Test]
    public function puede_crear_job(): void
    {
        $job = new TestJob;

        $this->assertInstanceOf(TestJob::class, $job);
    }

    #[Test]
    public function implementa_should_queue(): void
    {
        $job = new TestJob;

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $job);
    }
}
